<?php

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/kit/class/typekit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';

class Kit extends CommonObject
{
    /**
     * @var string ID to identify managed object
     */
    public $element = 'kit';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'kit';


    public $picto = 'materiel';

    public $oldcopy;

    public $regeximgext = '\.gif|\.jpg|\.jpeg|\.png|\.bmp|\.webp|\.xpm|\.xbm'; // See also into images.lib.php


    /**
     * Product description
     *
     * @var string
     */
    public $id;
    public $date_creation;
    public $user_creation;
    public $date_modification;
    public $user_modification;
    public $date_suppression;
    public $user_suppression;

    public $ac;

    public $fk_etat_etiquette;
    public $etat_etiquette;
    public $etat_etiquette_badge_code;

    public $ref;
    public $libelle;

    public $type_kit_id;
    public $type_kit;

    public $cote;

    public $fk_materiel_raw;
    public $fk_materiel = array();
    public $materiel_object = array();


    public $c_disponibilite =  array(1=>array('disponibilite'=>'Disponible', 'badge_code'=>'4'),
                                     2=>array('disponibilite'=>'En exploitation', 'badge_code'=>'5'),
                                     3=>array('disponibilite'=>'Inexploitable', 'badge_code'=>'8'));
    public $fk_disponibilite;
    public $disponibilite;
    public $disponibilite_badge_code;

    public $notes;

    public $fk_exploitation;
    public $exploitation_ind;
    public $exploitation_cote;
    public $exploitation_ref;



    public $fields = array(
        'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-2, 'notnull'=>1, 'index'=>1, 'position'=>1, 'comment'=>'Id'),
		'ref'   =>array('type'=>'tinytext', 'label'=>'Reference', 'enabled'=>1, 'visible'=>0, 'position'=>61),
        'fk_type_kit' =>array('type'=>'integer', 'label'=>'FkTypeKit', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'showoncombobox'=>1, 'index'=>1, 'position'=>10, 'searchall'=>1, 'comment'=>'Reference of object'),
        'fk_materiel' =>array('type'=>'text', 'label'=>'FkMateriel', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'showoncombobox'=>1, 'index'=>1, 'position'=>10, 'searchall'=>1, 'comment'=>'Reference of object'),
        'cote'        =>array('type'=>'integer', 'label'=>'Cote', 'enabled'=>1, 'visible'=>0, 'default'=>1, 'notnull'=>1, 'index'=>1, 'position'=>20),
        'notes'          =>array('type'=>'text', 'label'=>'Notes', 'enabled'=>1, 'visible'=>0, 'position'=>62),
    );


    /**
     *  Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->canvas = '';
        $this->type_kit = new TypeKit($this->db);
    }

	/**
	 *  Récupération des données du kit à partir de l'id
     *
     * @return int    null if KO, >0 if OK
	 */

    public function fetch($id = '')
    {
        global $langs, $conf, $fields;
        $this->materiel_object = array();

        // Check parameters
        if ($id == '') {
            $this->error = 'ErrorWrongParameters';
            dol_syslog(get_class($this)."::fetch ".$this->error);
            return null;
        }

        $sql = "SELECT k.rowid, k.fk_type_kit, k.cote, k.fk_etat_etiquette, k.notes, k.tms as date_modification, k.date_ajout, k.date_suppression, k.fk_user_author, k.fk_user_modif, k.fk_user_delete, k.active";
        $sql .= " ,ee.etat as etat_etiquette, ee.badge_code as etat_etiquette_badge_code";
        $sql .= " FROM ".MAIN_DB_PREFIX."kit as k ";
        $sql.="INNER JOIN ".MAIN_DB_PREFIX."c_etat_etiquette_kit as ee ON k.fk_etat_etiquette=ee.rowid ";
        $sql .= " WHERE k.rowid = ".(int) $id;

        $resql = $this->db->query($sql);

        if (!$resql) {
            dol_print_error($this->db);
            return null;
        }
        if (!$this->db->num_rows($resql)) {
            // No entry found
            return null;
        }

        $obj = $this->db->fetch_object($resql);

        if ($obj->fk_user_author) {
            $cuser = new User($this->db);
            $cuser->fetch($obj->fk_user_author);
            $this->user_creation = $cuser;
        }

        if ($obj->fk_user_modif) {
            $muser = new User($this->db);
            $muser->fetch($obj->fk_user_modif);
            $this->user_modification = $muser;
        }

        if ($obj->fk_user_delete) {
            $duser = new User($this->db);
            $duser->fetch($obj->fk_user_delete);
            $this->user_suppression = $duser;
        }
        $this->date_creation     = $this->db->jdate($obj->date_ajout);
        $this->date_modification = $this->db->jdate($obj->date_modification);
        $this->date_suppression = $this->db->jdate($obj->date_suppression);

        $this->id                           = $obj->rowid;
        $this->active                           = $obj->active;

        
        $this->fk_etat_etiquette            = $obj->fk_etat_etiquette;
        $this->etat_etiquette           = $obj->etat_etiquette;
        $this->etat_etiquette_badge_code                = $obj->etat_etiquette_badge_code;
        
        $this->type_kit->fetch($obj->fk_type_kit);
        
        $this->ref                       = $this->type_kit->indicatif . '-' . $obj->cote;

        $this->cote                         = $obj->cote;

        $this->notes                         = $obj->notes;

        $this->fk_disponibilite = 1;
        $this->disponibilite                         = 'Disponible';
        $this->disponibilite_badge_code                         = '4';

        $this->db->free($resql);

        $sql = "SELECT kc.fk_materiel";
        $sql .= " FROM ".MAIN_DB_PREFIX."kit_content as kc ";
        $sql .= " WHERE kc.fk_kit = ".$this->id;
        $sql .= ($this->active ?  " AND active = 1" : " AND active_history = 1");
        $resql = $this->db->query($sql);

        if (!$resql) return null;
        
        $num = $this->db->num_rows($resql);
        $i = 0;
        $this->fk_materiel = array();
        while ($i < $num) // Création de la liste de matériels du kit
        {
            $obj = $this->db->fetch_object($resql);
            $mat_fk = $obj->fk_materiel;
            $this->fk_materiel[] = $mat_fk;
            $this->materiel_object[$mat_fk] = new Materiel($this->db);
            $this->materiel_object[$mat_fk]->fetch($obj->fk_materiel);
            if ($this->materiel_object[$mat_fk]->fk_exploitabilite == 2) {
                $this->disponibilite = 'Inexploitable';
                $this->fk_disponibilite = 3;
                $this->disponibilite_badge_code = '8';
            }
            $i++;
        }


        // Libellé du kit, provient du premier matériel du kit
        $this->libelle = $this->type_kit->title . ' ' . $this->materiel_object[array_key_first ($this->materiel_object)]->marque . ' ' . $this->materiel_object[array_key_first ($this->materiel_object)]->modele;

        // Maintenant on récupère les données de l'exploitation dans lequel est le kit
        $sql = "SELECT e.rowid, e.cote, te.type, te.indicatif";
        $sql .= " FROM ".MAIN_DB_PREFIX."exploitation_content as ec ";
        $sql .="INNER JOIN ".MAIN_DB_PREFIX."exploitation as e ON ec.fk_exploitation=e.rowid ";
        $sql .="INNER JOIN ".MAIN_DB_PREFIX."c_type_exploitation as te ON e.fk_type_exploitation=te.rowid ";
        $sql .= "WHERE (";
        $sql .= "ec.fk_kit = " . $this->id;
        $sql .= " AND ec.active = 1";
        $sql .= ")";
        $resql = $this->db->query($sql);
        if (!$resql) {
            dol_print_error($this->db);
            return null;
        }
        
        $num = $this->db->num_rows($resql);
        if ($num > 0)
        {
            $obj = $this->db->fetch_object($resql);
            $this->fk_exploitation = $obj->rowid;
            $this->exploitation_ind = $obj->indicatif;
            $this->exploitation_cote = $obj->cote;
            $this->exploitation_ref = $obj->indicatif . '-'.$obj->cote;
            $this->fk_disponibilite = 2;
            $this->disponibilite = 'En exploitation';
            $this->disponibilite_badge_code = '5';
        }
        else
        {
                $this->fk_exploitation = 0;
        }

        $this->oldcopy = clone $this;
        return 1;
    }



    /**
     *  Création d'un nouveau kit avec injection dans la bdd
     *
     * @return mixed        null if KO, >0 if OK
     */
    public function create($user)
    {
        global $conf, $langs;
        $error = 0;
        $mat_in_kit = array();

        
        // Vérification des données
        if (empty($this->type_kit_id) || empty($this->fk_materiel)) {
            print 'test';
            return null;
        }
        $this->notes = ($this->notes ? $this->notes : '');
        
        /*
         *  Vérification si un matériel est déjà dans un autre kit ou si on essaye d'insérer le même materiel 2 fois dans le même kit
         */
        if(count(array_unique($this->fk_materiel)) < count($this->fk_materiel)){
            return 0;
        }

        $this->db->begin();
        $sql = "SELECT fk_materiel";
        $sql .= " FROM ".MAIN_DB_PREFIX."kit_content";
        $sql .= " WHERE active = 1";
        $resql = $this->db->query($sql);
        $num = $this->db->num_rows($resql);
        $i = 0;

        // Création de la liste de matériels déjà dans un kit
        while ($i < $num) 
        {
            $obj = $this->db->fetch_object($resql);
            $mat_fk = $obj->fk_materiel;
            $mat_in_kit[] = $mat_fk;
            $i++;
        }
        foreach($this->fk_materiel as $mat_to_include) {
            if (in_array($mat_to_include, $mat_in_kit)) return null;
        }


        /*
         *  Récuperation cote
         */
        $now = dol_now();
        $sql = "SELECT cote";
        $sql .= " FROM ".MAIN_DB_PREFIX."kit";
        $sql .= " WHERE fk_type_kit = ".$this -> type_kit_id;
        $sql .= " ORDER BY cote DESC";
        $result = $this->db->query($sql); 
        
        //On recupère la plus grosse cote des kit du même type pour avoir celle du kit qu'on insère
        $cote_list = $this->db->fetch_object($result);
        if (empty($cote_list))
        {
            $this->cote = 1;
        } else {
            $this->cote = $cote_list->cote + 1;
        }

        $sql = "INSERT INTO ".MAIN_DB_PREFIX."kit (";
        $sql .= "fk_type_kit";
        $sql .= ", cote";
        $sql .= ", fk_etat_etiquette";
        $sql .= ", notes";
        $sql .= ", date_ajout";
        $sql .= ", fk_user_author";
        $sql .= ") VALUES (";
        $sql .= $this -> type_kit_id;
        $sql .= ", ".$this->cote;
        $sql .= ", ".$this->fk_etat_etiquette;
        $sql .= ", '".$this->notes."'";
        $sql .= ", '".$this->db->idate($now)."'";
        $sql .= ", ".$user->id;
        $sql .= ")";
        $result = $this->db->query($sql);

        if (!$result) {
            $this->error = $this->db->lasterror();
            return null;
        }
        
        $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."kit");

        /*
         *   On insère dans kit_content les matériels inclus dans le kit
         */
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."kit_content (";
        $sql .= "fk_kit";
        $sql .= ", fk_materiel";
        $sql .= ", fk_user_author";
        $sql .= ") VALUES ";

        // Insertion d'une nouvelle entrée pour chaque materiel
        foreach($this->fk_materiel as $key=>$fk_mat) {
            $sql .= "(";
            $sql .= $this -> id;
            $sql .= ", ".$fk_mat;
            $sql .= ", ".$user->id;
            $sql .= ")";
            if ($key != array_key_last($this->fk_materiel)) $sql .= ",";
            $sql .= " ";
        }
        $result = $this->db->query($sql);
        if (!$result) {
            $error++;
            $this->error = $this->db->lasterror();
            return 0;
        }

        // Vérification des erreurs
        if (!$error) {
            $this->db->commit();
            return $this->id;
        }
        else
        {
            $this->db->rollback();
            return 0;
        }
    }


    /**
     *    Met à jour les données du kit
     *
     * @return int        <0 if KO, >0 if OK
     */

    public function update($user, $bypass_exploitation_verification = 0)
    {
        global $conf, $langs;
        $error = 0;

        if (!$this->active) return 0; // STOP si le kit est supprimé

        if ($this->fk_exploitation && !$bypass_exploitation_verification) return 0; // On vérifie si le kit est en exploitation ou non/

        if (empty($this->notes)) $this->notes = '';


        $this->db->begin();
        $sql = "UPDATE ".MAIN_DB_PREFIX."kit";
        $sql .= " SET";
        $sql .= " fk_etat_etiquette = " . $this->fk_etat_etiquette . ",";
        $sql .= " notes = '" . $this->notes . "',";
        $sql .= " fk_user_modif = " . $user->id;
        $sql .= " WHERE rowid = ".$this->id;
        if (!$result = $this->db->query($sql)) return 0;
        if (!$this->db->commit()) return 0;

        foreach($this->oldcopy->fk_materiel as $old_fk) {
            if (!in_array($old_fk, $this->fk_materiel)){ // Si le materiel n'est plus dans le kit
                $sql = "UPDATE ".MAIN_DB_PREFIX."kit_content";
                $sql .= " SET";
                $sql .= " date_suppression = '" . date('Y-m-d H:i:s') . "',";
                $sql .= " fk_user_delete = ".$user->id;
                $sql .= ", active = 0";
                $sql .= ", active_history = 0";
                $sql .= " WHERE fk_materiel = ".$old_fk;
                $sql .= " AND date_suppression IS NULL"; // On rajoute IS NULL sinon ça va update toutes les dates des evenements de ce materiel
                $sql .= " AND fk_kit = ".$this->id;
                if (!$result = $this->db->query($sql)) return 0;
                if (!$this->db->commit()) return 0;
            }
        }

        foreach($this->fk_materiel as $fk) {
            if (!in_array($fk, $this->oldcopy->fk_materiel)){ // Si le materiel n'etait pas dans le kit avant, on insere une nouvelle entrée dans kit_content
                $sql = "INSERT INTO ".MAIN_DB_PREFIX."kit_content (";
                $sql .= "fk_kit";
                $sql .= ", fk_materiel";
                $sql .= ", fk_user_author";
                $sql .= ") VALUES ";

                $sql .= "(";
                $sql .= $this -> id;
                $sql .= ", ".$fk;
                $sql .= ", ".$user->id;
                $sql .= ")";
                $result = $this->db->query($sql);
                if (!$result) {
                    $error++;
                    $this->error = $this->db->lasterror();
                    return 0;
                }
            }
        }
        $this->fetch($this->id);
        return 1;
    }


    /**
     *    Modifie la valeur de 'active' de la table kit dans la base de données
     *
     * @return int        <0 if KO, >0 if OK
     */
    public function delete($user)
    {
        global $conf;

        if ($this->fk_exploitation) return 0; // On vérifie si le kit est en exploitation ou non
        if (!$this->active) return 0; // STOP si le kit est supprimé

        include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        if ($this->id)
        {
          $sql = "UPDATE ";
          $sql .= MAIN_DB_PREFIX."kit";
          $sql .= " SET active = 0";
          $sql .= ", fk_user_delete = ". $user->id;
          $sql .= ", date_suppression = '" . date('Y-m-d H:i:s') . "'";
          $sql .= " WHERE rowid = ".$this->id;
          if (!$this->db->query($sql)) return 0;
          if (!$this->db->commit()) return 0;

          $sql = "UPDATE ";
          $sql .= MAIN_DB_PREFIX."kit_content";
          $sql .= " SET active = 0";
          $sql .= ", date_suppression = '" . date('Y-m-d H:i:s') . "'";
          $sql .= ", fk_user_delete = ". $user->id;
          $sql .= " WHERE fk_kit = ".$this->id;
    			if (!$this->db->query($sql)) return 0;
    			if (!$this->db->commit()) return 0;

            // We remove directory
            $ref = dol_sanitizeFileName($this->ref);
            if ($conf->kit->multidir_output[1]) {
                $dir = $conf->kit->multidir_output[1]."/".$ref;
                if (file_exists($dir)) {
                    $res = @dol_delete_dir_recursive($dir);
                }
            }
			return 1;
        }
        else
        {
            return 0;
        }
    }

    /* Récupération de l'URL avec la tooltip à utiliser dans la liste*/
    public function getNomUrl($notooltip = 0, $style ='')
    {
        global $conf, $langs;
        include_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';

        $new_notes = ($this->notes ? $this->notes : '<i>Pas de notes</i>');

        $label = '<u>Kit</u>';
        $label .= '<br><b>Type de kit : </b> '.$this->type_kit->title;
        $label .= '<br><b>Notes : </b> '.$new_notes;
        $label .= '<br><br><b>Matériels : </b><br>';
        foreach($this->materiel_object as $mat) {
		    if ($mat->fk_etat == 1  && $mat->fk_exploitabilite == 1) $label .=  '<span class="badge  badge-status4 badge-status" style="color:white;">'.$mat->getNomURL(1, 'style="color:white;"').'</span> &nbsp';
		    elseif ($mat->fk_etat == 2 && $mat->fk_exploitabilite == 1) $label .=  '<span class="badge  badge-status2 badge-status" style="color:white;">'.$mat->getNomURL(1, 'style="color:white;"').'</span> &nbsp';
		    elseif ($mat->fk_etat == 2 && $mat->fk_exploitabilite == 2) $label .=  '<span class="badge  badge-status4 badge-status" style="color:white; background-color:#905407;">'.$mat->getNomURL(1, 'style="color:white;"').'</span>&nbsp;';
		    elseif ($mat->fk_etat == 3 && $mat->fk_exploitabilite == 2) $label .=  '<span class="badge  badge-status8 badge-status" style="color:white;">'.$mat->getNomURL(1, 'style="color:white;"').'</span> &nbsp';
		    else $label .=  '<span class="badge  badge-status5 badge-status">'.$mat->getNomURL(0).'</span> &nbsp';
		}

        $linkclose = '';

        if (empty($notooltip)) {
            if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
                $label = $langs->trans("ShowProduct");
                $linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
            }

            $linkclose .= ' title="'.dol_escape_htmltag($label, 1, 1).'"';
            $linkclose .= ' class="nowraponall classfortooltip"';
        }
        $url = DOL_URL_ROOT.'/custom/kit/card.php?action=view&id='.$this->id;
        $linkstart = '<a href="'.$url.'" '.$style;
        $linkstart .= $linkclose.'>';
        $linkend = '</a>';

        $result = $linkstart;
        $result .= (talm_img_object(($notooltip ? '' : $label), 'kit', ($notooltip ? 'class="paddingright"'.$style : 'class="paddingright classfortooltip"'.$style), 0, 0, $notooltip ? 0 : 1));

        $result .= $this->ref;
        $result .= $linkend;

        return $result;
    }



    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
    /**
     *  Return if at least one photo is available
     *
     * @param  string $sdir Directory to scan
     * @return boolean                 True if at least one photo is available, False if not
     */
    public function is_photo_available($sdir)
    {
        // phpcs:enable
        include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';

        global $conf;

        $dir = $sdir;
        if (!empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO)) {
        	$dir .= '/'.get_exdir($this->id, 2, 0, 0, $this, 'kit').$this->id."/photos/";
        } else {
        	$dir .= '/'.get_exdir(0, 0, 0, 0, $this, 'kit').'/';
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


}
?>
