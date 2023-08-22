<?php

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/entretien.lib.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/exploitation.lib.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/exploitation.class.php';



/**
*	Classe de gestion des entretiens
*
* Permet la création et la gestion des données relatives aux entretiens
*
* @package Entretien
*/

class Entretien extends CommonObject

{


   public $id;
   public $element = 'entretien';
   public $table_element = 'entretien';



	public $materiel_id;

	public $replacement_materiel_id;

	public $materiel_object;

	public $replacement_materiel_object;

	public $description;

	public $commentaire;

	public $creation_timestamp;

	public $deadline_timestamp;

	public $suppression_timestamp;

	public $user_author_id;

	public $user_author_object;

	public $user_delete_id; // ID de l'utilisateur ayant supprimé (clôturé) l'entretien

	public $user_delete_object;

	public $active;



	public $etat_array = array(1=>array('label'=>'En cours', 'badge_code'=>5),

							   2=>array('label'=>'Terminé', 'badge_code'=>4));

	public $fk_etat;



	public $ref_prefix = "ENT-";



	public $error = 'UnknownError';


   public $picto = 'entretien';
   public $regeximgext = '\.gif|\.jpg|\.jpeg|\.png|\.bmp|\.webp|\.xpm|\.xbm'; // See also into images.lib.php









	// ======================================================================================

	/**
   *  Constructor
   *
   * @param DoliDB $db Database handler
   *
   * @return void
   */
   public function __construct($db)
   {
       $this->db = $db;
   }





	/**

	 * Appelle les fonctions de vérification des données et de création de l'entretien dans la base de données

	 * @param $user Utilisateur qui crée l'entretien

	 * @return int 1 if OK, 0 if KO

	 */

	public function create($user)

	{

		if (!$this->checkAndSanitizeDataForCreation()) {

			$this->error = 'InvalidDataForCreation';

			return 0;

		} elseif (!$this->insertToDatabase($user)) {

			$this->error = 'DatabaseError';

			return 0;

		} 

		if ($this->isInExploitation($this->materiel_id) && !empty($this->replacement_materiel_id)) {

			$this->insertReplacementToDatabase($user);

		}

		return 1;

	}



	private function insertToDatabase($user)

	{

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."entretien (";

		$sql .= "fk_materiel";

		$sql .= ", description";

		$sql .= ", deadline_timestamp";

		$sql .= ", fk_user_author";

		$sql .= ") VALUES (";

		$sql .= $this->materiel_id;

		$sql .= ", '". $this->description."'";

		$sql .= ", ". $this->deadline_datetime_sanitized; // On converti le timestamp en date pour insérer dans la bdd

		$sql .= ", ". $user->id;

		$sql .= ")";

		$result = $this->db->query($sql);

		if ($result) {

			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."entretien");

			return 1;

		} else return 0;

	}



	private function insertReplacementToDatabase($user)

	{

		// Add a row in exploitation_replacement to specify the link between the two matériel

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."exploitation_replacement (";

		$sql .= "fk_materiel";

		$sql .= ", fk_entretien";

		$sql .= ", fk_replacement_materiel";

		$sql .= ", fk_user_author";

		$sql .= ") VALUES (";

		$sql .= $this->materiel_id;

		$sql .= ", ". $this->id;

		$sql .= ", ". $this->replacement_materiel_id;

		$sql .= ", ". $user->id;

		$sql .= ")";

		$result = $this->db->query($sql);

		if (!$result) return 0;



		// Add replacement matériel to the exploitation

		$exploitation = new Exploitation($this->db);

		$replaced_materiel_object = new Materiel($this->db);

		$replaced_materiel_object->fetch($this->materiel_id);

		$kit_id = $replaced_materiel_object->fk_kit; // ID du kit contenant le materiel remplacé

		$exploitation->fetch($this->getExploitationID($this->materiel_id));

		$result = $exploitation->addMateriel($this->replacement_materiel_id, $kit_id, $user);

		if (!$result) return 0;

		return 1;

	}



	/**

	 * Vérification des données pour la création de l'entretien

	 */

	private function checkAndSanitizeDataForCreation()

	{

		if (empty($this->materiel_id)) return 0;

		if (!$this->checkForValidMaterielID($this->materiel_id)) return 0;

		if (empty($this->description)) return 0;



		$this->deadline_datetime_sanitized = ($this->deadline_timestamp ? "'" . date('Y-m-d', $this->deadline_timestamp) . "'" : 'null');



		return 1;

	}



	/**

	 * Vérifie la validité de l'ID du matériel

	 *

	 * Vérifie si le matériel n'est pas déjà en entretien et si le matériel existe bien

	 *

	 * @return 1 si valide, 0 si non valide

	 */

	private function checkForValidMaterielID($materiel_id)

	{

		// Check if ID exists in llx67_materiel

		$sql = "SELECT rowid";

		$sql.= " FROM ".MAIN_DB_PREFIX."materiel";

		$sql.= " WHERE rowid = ".$materiel_id;

		$sql.= " AND active = 1";

		$resql = $this->db->query($sql);

		$num = $this->db->num_rows($resql);

		if ($num < 1) return 0;



		// Check if there is no active row in llx67_entretien with that ID

		$sql = "SELECT rowid";

		$sql.= " FROM ".MAIN_DB_PREFIX."entretien";

		$sql.= " WHERE fk_materiel = ".$materiel_id;

		$sql.= " AND active = 1";

		$resql = $this->db->query($sql);

		$num = $this->db->num_rows($resql);

		if ($num > 0) return 0;



		return 1;

	}



	/**

	 * Vérifie si un matériel est en exploitation

	 *

	 * @return 1 si en exploitaion, sinon 0

	 */

	private function isInExploitation($materiel_id)

	{

		// Check if ID exists in exploitation_suivi

		$sql = "SELECT rowid";

		$sql.= " FROM ".MAIN_DB_PREFIX."exploitation_suivi";

		$sql.= " WHERE fk_materiel = ".$materiel_id;

		$sql.= " AND active = 1";

		$resql = $this->db->query($sql);

		$num = $this->db->num_rows($resql);

		if ($num < 1) return 0;

		return 1;

	}



	private function getExploitationID($materiel_id)

	{

		$sql = "SELECT fk_exploitation";

		$sql.= " FROM ".MAIN_DB_PREFIX."exploitation_suivi";

		$sql.= " WHERE fk_materiel = ".$materiel_id;

		$sql.= " AND active = 1";

		$resql = $this->db->query($sql);

		$fk_exploitation = $this->db->fetch_object($resql);

		return $fk_exploitation->fk_exploitation;

	}



	/**

	 * Récupération des infos de l'entretien dans la base de données

	 */

	public function fetch($id)
	{

		$sql = "SELECT *";

		$sql .= " FROM ".MAIN_DB_PREFIX."entretien as e";

		$sql .= " WHERE e.rowid = ".(int) $id;

		$resql = $this->db->query($sql);

		if (!$resql) {

			dol_print_error($this->db);

			return 0;

		} else {

			if ($this->db->num_rows($resql) > 0) {

				$obj = $this->db->fetch_object($resql);



				

				$cuser = new User($this->db);

				$cuser->fetch($obj->fk_user_author);

				$this->user_author_id = $cuser;

				$this->user_author_object = $cuser;

				$this->creation_timestamp = $obj->creation_timestamp;

				

				if (!$obj->active) {

					$duser = new User($this->db);

					$duser->fetch($obj->fk_user_delete);

					$this->delete_user_id = $obj->fk_user_delete;

					$this->delete_user_object = $duser;

					$this->suppression_timestamp = $obj->suppression_timestamp;

				}                

				

				$this->id                    = $obj->rowid;

				$this->active                    = $obj->active;

				$this->fk_etat				= ($obj->active ? 1 : 2); // Si active == 1, l'entretien est en cours donc fk_etat = 1

				$this->deadline_timestamp     = $this->db->jdate($obj->deadline_timestamp);

				$this->ref     = $this->ref_prefix . $obj->rowid;

				$this->description     = $obj->description;



				$this->materiel_id = $obj->fk_materiel;

				$mat = new Materiel($this->db);

				$mat->fetch($obj->fk_materiel);

				$this->materiel_object = $mat;



				// Check if this materiel is replaced, if so fill the $replaced_materiel_object variable

				$replacement_materiel_id = isMaterielReplaced($this->materiel_id);

				if ($replacement_materiel_id > 0) {

					$mat_replacement = new Materiel($this->db);

					$mat_replacement->fetch($replacement_materiel_id);

					$this->replacement_materiel_id = $replacement_materiel_id;

					$this->replacement_materiel_object = $mat_replacement;

				}



				$this->db->free($resql);



				return 1;

			} else {

				// Pas d'entrée correspondante pour cet ID

				return 0;

			} 

		}

	}


   /**
   * Retourne un lien vers l'entretien
   *
   * @param bool $notooltip Désactivation de la tooltip
   * @param string $style CSS supplémentaire
   *
   * @return string HTML du lien
   */
   public function getNomUrl($notooltip = 0, $style ='', $nopicto = 0)
   {
       $label = '<u>Entretien</u>';
       $label .= '<br><b>Description : </b> '.$this->description;
       $label .= '<br><br><b>Matériel : </b><br>';
       $label .=  '<span class="badge  badge-status4 badge-status" style="color:white;">'.$this->materiel_object->getNomURL(1, 'style="color:white;"').'</span>&nbsp ';
       $linkclose = '';


       if (empty($notooltip)) {
           $linkclose .= ' title="'.dol_escape_htmltag($label, 1, 1).'"';
           $linkclose .= ' class="nowraponall classfortooltip"';
       }
       $url = DOL_URL_ROOT.'/custom/entretien/card.php?action=view&id='.$this->id;
       $linkstart = '<a href="'.$url.'" '.$style;
       $linkstart .= $linkclose.'>';
       $linkend = '</a>';
       $result = $linkstart;
       if (!$nopicto) $result .= (talm_img_object(($notooltip ? '' : $label), 'entretien', ($notooltip ? 'class="paddingright"'.$style : 'class="paddingright classfortooltip"'.$style), 0, 0, $notooltip ? 0 : 1));

		$result .= $this->ref;
       $result .= $linkend;


       return $result;
   }



	/**
    *  Return if at least one photo is available
    *
    * @param  string $sdir Directory to scan
    * @return boolean                 True if at least one photo is available, False if not
    */
   public function is_photo_available($sdir)
   {
       include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
       include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';


       global $conf;


       $dir = $sdir;
       if (!empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO)) {
       	$dir .= '/'.get_exdir($this->id, 2, 0, 0, $this, 'entretien').$this->id."/photos/";
       } else {
       	$dir .= '/'.get_exdir(0, 0, 0, 0, $this, 'entretien').'/';
       }


       $nbphoto = 0;


       $dir_osencoded = dol_osencode($dir);
       if (file_exists($dir_osencoded)) {
           $handle = opendir($dir_osencoded);
           if (is_resource($handle)) {
               while (($file = readdir($handle)) !== false)
               {
                   if (!utf8_check($file)) {
                   	$file = utf8_encode($file); // To be sure data is stored in UTF8 in memory
                   }
                   if (dol_is_file($dir.$file) && image_format_supported($file) >= 0) {
                   	return true;
                   }
               }
           }
       }
       return false;
   }



	/**

	 * Récupère l'historique des actions d'entretien (table llx67_entretien_suivi)

	 * @return array|int Historique de suivi pour cet entretien, ou 0 si aucun historique n'est trouvé

	 */

	public function getSuiviHistoric()

	{

		$suivi_historic = array();



		$sql = "SELECT rowid, fk_user_author, description,fk_agent, tms FROM ";

		$sql .= MAIN_DB_PREFIX."entretien_suivi ";

		$sql .= "WHERE fk_entretien = ".$this->id;

		$sql .= " ORDER BY tms DESC";

		$resql = $this->db->query($sql);

		$num_row = $this->db->num_rows($resql);

		if ($num_row < 1) return 0;

		$i = 0;

		while ($i < $num_row)

		{

			$suivi_row = $this->db->fetch_object($resql);

			$suivi_historic[$suivi_row->rowid]['fk_user'] = $suivi_row->fk_user_author;

			$suivi_historic[$suivi_row->rowid]['fk_agent'] = $suivi_row->fk_agent;

			$suivi_historic[$suivi_row->rowid]['description'] = $suivi_row->description;

			$suivi_historic[$suivi_row->rowid]['date'] = $this->db->jdate($suivi_row->tms);

			$i++;

		}

		return $suivi_historic;

	}



	/**

	 * Insère une nouvelle ligne de suivi d'entretien dans la base de données

	 * @return int 1 si OK, 0 si KO

	 */

	public function addSuivi($description, $agent, $user)

	{	

		// Can't add if the materiel isn't in the warehouse
       $materiel_status = getMaterielSuiviStatus($this->materiel_id);
       if (isInExploitation($entretien->materiel_id) && ($materiel_status['fk_localisation'] != 1 || $materiel_status['etat_suivi'] != 1)) return 0;



		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'entretien_suivi ';

		$sql .= '(fk_entretien, description, fk_agent, fk_user_author) ';

		$sql .= 'VALUES (';

		$sql .= $this->id.', ';

		$sql .= "'".$description."', ";

		$sql .= $agent.', ';

		$sql .= $user->id;

		$sql .= ')';

		$result = $this->db->query($sql);

		if ($result)

		{

			$this->db->commit();

			return 1;

		} else return 0;

	}



	/**

	 * Clôture l'entretien

	 * Modifie la valeur du champs "active" et "fk_user_delete" correspondant à cet entretien dans la base de données

	 * @return 1 si OK, 0 si KO

	 */

	public function cloture($user)
	{

		$sql = 'UPDATE '.MAIN_DB_PREFIX.'entretien';
		$sql .= " SET active = 0,";
		$sql .= "fk_user_delete = ".$user->id;
		$sql .= ", suppression_timestamp = '".date('Y-m-d H:i:s') ."'";
		$sql .= " WHERE rowid = ".$this->id;
		if (!$this->db->query($sql)) return 0;
		if (!$this->db->commit()) return 0;

		// Si il y a une matériel de remplacement et qu'il est toujours à l'entrepôt, on le sort du kit
		if (is_object($this->replacement_materiel_object))
		{
			$kit = new Kit($this->db);
			$kit->fetch($this->replacement_materiel_object->fk_kit);
           $replacement_materiel_status = getMaterielSuiviStatus($this->replacement_materiel_id);
			$is_replacement_in_warehouse = 0;
           if ($replacement_materiel_status['etat_suivi'] == 1 && $replacement_materiel_status['fk_localisation'] == 1) $is_replacement_in_warehouse = 1;
			if (($key = array_search($this->replacement_materiel_id, $kit->fk_materiel)) !== false && $is_replacement_in_warehouse) {
				unset($kit->fk_materiel[$key]);
				$sql = "UPDATE ".MAIN_DB_PREFIX."exploitation_replacement set active = 0 WHERE fk_materiel = ".$this->materiel_id;
				$this->db->query($sql);
				$this->db->commit();
			}
			$kit->update($user, 1);
		}
		return 1;
	}

	public function ouverture($user)
	{
		$sql = 'UPDATE '.MAIN_DB_PREFIX.'entretien';
		$sql .= " SET active = 1,";
		$sql .= "fk_user_delete = ".$user->id;
		$sql .= ", suppression_timestamp = ''";
		$sql .= " WHERE rowid = ".$this->id;
		if (!$this->db->query($sql)) return 0;
		if (!$this->db->commit()) return 0;
		return 1;
	}
}

