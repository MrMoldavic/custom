<?php

/**
 * Prepare array with list of tabs
 *
 * @param   Entretien	$object		Object related to tabs
 * @return  array				Array of tabs to show
 */
function entretien_prepare_head($object)
{
    global $db, $langs, $conf, $user;

    $label = 'Entretien';
    $h = 0;

    $head = array();
    $head[$h][0] = DOL_URL_ROOT."/custom/entretien/card.php?id=".$object->id;
    $head[$h][1] = $label;
    $head[$h][2] = 'card';
    $h++;

    $head[$h][0] = DOL_URL_ROOT."/custom/entretien/document.php?id=".$object->id;
    $head[$h][1] = 'Documents';
    $head[$h][2] = 'documents';
    $h++;

    $head[$h][0] = DOL_URL_ROOT."/custom/entretien/agenda.php?id=".$object->id;
    $head[$h][1] = 'Historique';
    $head[$h][2] = 'historique';
    $h++;

    return $head;
}

/**
 * Vérifie s'il existe un entretien en cours du matériel correspondant à $id
 * 
 * @param  int	$id	ID du matériel
 * @return entretien_id si en entretien, 0 si non
 */
function isMaterielInEntretien($id)
{
	global $db;

	$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."entretien";
	$sql .=" WHERE fk_materiel = ".$id;
	$sql .= " AND active = 1";
	$resql = $db->query($sql);
    $num = $db->num_rows($resql);

    if ($num) 
    {
        $obj = $db->fetch_object($resql);
        return $obj->rowid;
    }
    else return 0; // No entry found
}


/**
 * Vérifie si le matériel correspondant à $id est un matériel de remplacement
 * 
 * @param int $id ID du matériel
 * @return entretien_id si remplacement, 0 si non
 */
function isMaterielReplacement($id)
{
	global $db;

	$sql = "SELECT fk_entretien FROM ".MAIN_DB_PREFIX."exploitation_replacement";
	$sql .=" WHERE fk_replacement_materiel = ".$id;
	$sql .= " AND active = 1";
	$resql = $db->query($sql);
    $num = $db->num_rows($resql);

    if ($num) 
    {
        $obj = $db->fetch_object($resql);
        return $obj->fk_entretien;
    }
    else return 0; // No entry found
}


/**
 * Vérifie si un matériel est remplacé
 * 
 * @param int $id ID du matériel
 * @return int ID du matériel de remplacement si remplacement, 0 si pas de remplacement
 */
function isMaterielReplaced($id)
{
	global $db;
	$sql = "SELECT fk_replacement_materiel FROM ".MAIN_DB_PREFIX."exploitation_replacement";
	$sql .=" WHERE fk_materiel = ".$id;
	$sql .= " AND active = 1";
	$resql = $db->query($sql);
    $num = $db->num_rows($resql);
    if ($num > 0) {
        $obj = $db->fetch_object($resql);
        return $obj->fk_replacement_materiel;
    } 
	else
    return 0;
}


/**
 * Récupération d'une liste de matériel de remplacement pour le materiel correspondant à $fk_materiel
 * 
 * @param int $id ID du matériel à remplacer
 * @return array Liste des matériels disponible à l'échange selon le matériel à échanger et le type de kit kit 
 * 
 */
function getReplacementList($fk_materiel)
{
    global $db;

    $materiel = new Materiel($db);
    $materiel->fetch($fk_materiel);
    
    $kit = new Kit($db);
    $kit->fetch($materiel->fk_kit);
    
    $fk_type_kit = $kit->type_kit->id;
    $type_materiel_to_get = array();
    $mat_array = array(); // Array materiels eligible to replacement

    // On récupère la liste des types de matériel dont a besoin le kit
    $sql = "SELECT tkdet.fk_type_materiel";
    $sql .= " FROM ".MAIN_DB_PREFIX."c_type_kit_det as tkdet";
    $sql .= " WHERE tkdet.fk_type_kit = ".$fk_type_kit;
    $resql = $db->query($sql);
    $obj = $db->fetch_object($resql);
    $type_materiel_to_get[] = ' AND (';
    
    $addOr = false;
    foreach ($obj as $key=>$fk) {
		// Si c'est le premier qu'on insère à la requête, on ne met pas de 'OR'
        if (!$addOr) $type_materiel_to_get[] = 'm.fk_type_materiel='.$fk;
        else $type_materiel_to_get[] = ' OR m.fk_type_materiel='.$fk;
        $addOr = true;
    }
    $type_materiel_to_get[] = ')';

    $sql = "SELECT m.rowid, m.cote, m.modele, t.indicatif, mq.marque";
    $sql .= " FROM ".MAIN_DB_PREFIX."materiel as m";
    $sql.=" INNER JOIN ".MAIN_DB_PREFIX."c_type_materiel as t ON m.fk_type_materiel=t.rowid";
    $sql.=" LEFT JOIN ".MAIN_DB_PREFIX."c_marque as mq ON m.fk_marque=mq.rowid";
    $sql.=" LEFT JOIN ".MAIN_DB_PREFIX."c_exploitabilite as ex ON m.fk_exploitabilite=ex.rowid";
    
    $sql.=" LEFT OUTER JOIN ".MAIN_DB_PREFIX."kit_content as kc ON m.rowid=kc.fk_materiel";
    $sql.=" LEFT OUTER JOIN ".MAIN_DB_PREFIX."entretien as entretien ON m.rowid=entretien.fk_materiel";
    
    $sql .= " WHERE m.active = 1";

    $sql .= " AND (kc.rowid IS null || kc.active = 0)"; // Select all materiel not included in an active kit
    $sql .= " AND (entretien.rowid IS null || entretien.active = 0)"; // Select all materiel not included in an active entretien
    $sql .= " AND m.fk_exploitabilite = 1";
    $sql .= " GROUP BY m.rowid";


    foreach ($type_materiel_to_get as $type_materiel_to_get_) {
        $sql .= $type_materiel_to_get_;
    }
    $sql .= " ORDER BY t.indicatif DESC";
    $resql = $db->query($sql);
    $num = $db->num_rows($resql);
    $i = 0;
    while ($i < $num) {
        $obj = $db->fetch_object($resql);
        $mat_array[$obj->rowid] = $obj->indicatif . '-' . $obj->cote . ' ' . $obj->marque . ' ' . $obj->modele;
        $i++;
    }
    return $mat_array;
}