<?php

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';


/**
 * Prepare array with list of tabs
 *
 * @param   Product	$object		Object related to tabs
 * @return  array				Array of tabs to show
 */
function kit_prepare_head($object)
{
	global $db, $langs, $conf, $user;

	$label = 'Kit';

	$h = 0;
	$head = array();

	$head[$h][0] = DOL_URL_ROOT."/custom/kit/card.php?id=".$object->id;
	$head[$h][1] = $label;
	$head[$h][2] = 'card';
	$h++;

	$head[$h][0] = DOL_URL_ROOT."/custom/kit/document.php?id=".$object->id;
	$head[$h][1] = 'Documents';
	$head[$h][2] = 'documents';
	$h++;

	$head[$h][0] = DOL_URL_ROOT."/custom/kit/agenda.php?id=".$object->id;
	$head[$h][1] = 'Historique';
	$head[$h][2] = 'historique';
	$h++;

	return $head;
}



/**
 *    Retourne un dictionnaire avec les types de kit
 */
function getKitTypeDict()
{
		global $db, $langs, $conf;
		$array_kit_type = array();

		$sql = "SELECT rowid, indicatif, type";
		$sql .= " FROM ".MAIN_DB_PREFIX."c_type_kit";

		$resql = $db->query($sql);
		$num = $db->num_rows($resql);
		$i = 0;
		while ($i < $num)
		{
			$obj = $db->fetch_object($resql);
			$array_kit_type[$obj->rowid]['indicatif'] = $obj->indicatif;
			$array_kit_type[$obj->rowid]['type'] = $obj->type;
			$i++;
		}

		return $array_kit_type;
}


/**
 *    Supprime un type de kit
 */
function deleteTypeKit($id)
{
	global $db, $user;

	// Check for existing kit using this type
	$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."kit ";
	$sql .= "WHERE fk_type_kit = " . $id;
	$sql .= " AND active = 1";
	$resql = $db->query($sql);
	if (!$resql) return null;
	if ($db->num_rows($resql)) 
	{
		// Can't delete this kit type
		return null;
	}

	$sql = "DELETE FROM ";
	$sql .= MAIN_DB_PREFIX."c_type_kit";
	$sql .= " WHERE rowid = ".$id;
	if (!$db->query($sql)) return null;
	if (!$db->commit()) return null;
	
	$sql = "DELETE FROM ";
	$sql .= MAIN_DB_PREFIX."c_type_kit_det";
	$sql .= " WHERE fk_type_kit = ".$id;
	if (!$db->query($sql)) return null;
	if (!$db->commit()) return null;

	return 1;
}

function getEtatEtiquetteKitDict($with_badge = 0) {
    global $db;

    $etat_dict = array();
    $sql = "SELECT rowid, etat, badge_code";
    $sql.= " FROM ".MAIN_DB_PREFIX."c_etat_etiquette_kit";
    $sql.= " WHERE active = 1";

    $resql = $db->query($sql);
    $num = $db->num_rows($resql);

		$i = 0;
		while ($i < $num)
		{
			$obj = $db->fetch_object($resql);
			$etat_dict[$obj->rowid] = $obj->etat;
			if ($with_badge) $etat_dict[$obj->rowid]['badge_code'] = $obj->badge_code;
			$i++;
		}
		return $etat_dict;
}

/**
 * Recupère les matériels qui sont dans un kit
 * @return int[] Liste d'ID de matériel en kit
 */
function getMaterielInKit () {
	global $db;
	$materielInKit = array();
	$sql = "SELECT fk_materiel";
	$sql .= " FROM ".MAIN_DB_PREFIX."kit_content";
	$sql .= " WHERE active = 1";
	$resql = $db->query($sql);
	$num = $db->num_rows($resql);
	$i = 0;
	while ($i < $num) {
		$materielRow = $db->fetch_object($resql);
		$materielID = $materielRow->fk_materiel;
		$materielInKit[] = $materielID;
		$i++;
	}
	return $materielInKit;
}
