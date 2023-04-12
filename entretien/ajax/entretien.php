<?php
/* Display errors and warnings */
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// Load Dolibarr environnment
@include '../../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/entretien.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';

$action = GETPOST('action', 'alpha');
$materiel_id = GETPOST('materiel_id', 'int');


// Retourne '1' si le materiel est en exploitation
if ($action == 'checkifexploited')
{
	$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."exploitation_suivi";
	$sql .= " WHERE active = 1 AND fk_materiel = ".$materiel_id;
	$resql = $db->query($sql);
	$num = $db->num_rows($resql);
	if ($num) print '1';
}

// Retourne une liste de matériel de remplacement
elseif ($action == 'getreplacementoption')
{
	$replacement_list = getReplacementList($materiel_id);
	$replacement_option = '';
	$replacement_option .= '<option value="-1">--Sélectionnez un matériel--</option>';
	foreach ($replacement_list as $id=>$ref)
	{
		$replacement_option .= '<option value="'.(int)$id.'">';
		$replacement_option .= $ref;
		$replacement_option .= '</option>';
	}
	print $replacement_option;
}
