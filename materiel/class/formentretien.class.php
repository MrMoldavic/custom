<?php
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/exploitation.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

/**
 *	Gestion et création de formulaire relatifs aux entretiens
 *
 * @package Entretien
 */
class FormEntretien extends CommonObject
{

	private $materiel_cache = array(); // Array de matériel disponible pour la création d'entretien

	/**
    *  Constructor
	*
    * @param DoliDB $db Database handler
    * @return void
    */
    public function __construct($db)
    {
        $this->db = $db;
    }

	public function printSelectMaterielForCreation($htmlname, $selected = '', $empty = 1)
	{
		global $conf;
		$out = '';

		$this->loadAvailableMaterielForCreation();
		$materiel_count = count($this->materiel_cache);

		// Si ajax est activé, on affiche un select avec autocompletion
		if ($conf->use_javascript_ajax)
		{
			include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
			$comboenhancement = ajax_combobox($htmlname, $events);
			$out .= $comboenhancement;
		}

		$out .= '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'">';
		if ($empty) $out .= '<option value="-1">--Sélectionnez un matériel--</option>';
		foreach ($this->materiel_cache as $id => $ref)
		{
			$out .= '<option value="'.$id.'"';
			if ($selected == $id) $out .= ' selected';
			$out .= ' data-html="'.dol_escape_htmltag($ref).'"';
			$out .= '>';
			$out .= $ref;
			$out .= '</option>';
		}
		$out .= '</select>';
		return $out;
	}

	private function loadAvailableMaterielForCreation()
	{
		$sql = "SELECT m.rowid, tm.indicatif, m.cote FROM ";
		$sql .= MAIN_DB_PREFIX."materiel as m";
		$sql .= " INNER JOIN ".MAIN_DB_PREFIX."c_type_materiel as tm on tm.rowid = m.fk_type_materiel";
		$sql .= " WHERE m.rowid NOT IN (SELECT fk_materiel FROM ".MAIN_DB_PREFIX."entretien WHERE active = 1)";
		$sql .= " AND m.rowid NOT IN (SELECT fk_replacement_materiel FROM ".MAIN_DB_PREFIX."exploitation_replacement WHERE active = 1)";
		$resql = $this->db->query($sql);
		$num = $this->db->num_rows($resql);
		$i = 0;
		while ($i < $num)
		{
			$obj = $this->db->fetch_object($resql);
			$ref_materiel = $obj->indicatif . '-' . $obj->cote;
			$this->materiel_cache[$obj->rowid] = $ref_materiel;
			$i++;
		}
	}

	/**
	 * Affiche la localisation du matériel pour l'entretien
	 * Vérifie si le matériel est en exploitation et affiche sa localisation avec un code couleur
	 */
	public function printMaterielLocalisation($id)
	{
		$badge_status_code = '';
		$result = '<span class="badge badge-status badge-status';
		if (isInExploitation($id))
		{
			$status = getMaterielSuiviStatus($id);
			$fk_localisation = $status['fk_localisation'];
			$fk_etat = $status['etat_suivi'];
			if ($fk_localisation == 1 && $fk_etat == 1) {
				$localisation_label = 'À l\'entrepôt';
				$badge_status_code = 4;
			} elseif ($fk_localisation == 1 && $fk_etat == 2) {
				$localisation_label = 'En déplacement chez l\'exploitant';
				$badge_status_code = 8;
			} elseif ($fk_localisation == 2 && $fk_etat == 1) {
				$localisation_label = 'Chez l\'exploitant';
				$badge_status_code = 8;
			} else {
				$localisation_label = 'En déplacement vers l\'entrepôt';
				$badge_status_code = 8;
				print '<span class="badge  badge-status1 badge-status" style="color:white;">En déplacement vers l\'entrepôt</span>';
			}
		} 
		else 
		{
			$localisation_label = 'À l\'entrepôt';
			$badge_status_code = 4;
		}
		
		$result .= $badge_status_code . '" style="color:white;">';
		$result .= $localisation_label;
		$result .= '</span>';
		print $result;
	}

}
