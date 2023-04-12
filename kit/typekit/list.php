<?php

@include "../../../main.inc.php";



require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';



require_once DOL_DOCUMENT_ROOT.'/custom/kit/class/typekit.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/kit.lib.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';





// Load translation files required by the page

$langs->loadLangs(array("materiel@materiel"));



$form = new Form($db);

$materiel = new Materiel($db);

$kit = new Kit($db);

$formmateriel = new FormMateriel($db);



/*

 * Data fetching

 */



$usercanread = ($user->rights->kit->read);

$usercancreate = ($user->rights->kit->create);

$usercandelete = ($user->rights->kit->delete);

$usercanmanagekittype = ($user->rights->kit->managekittype);



$action = GETPOST('action', 'alpha');

$massaction = GETPOST('massaction', 'alpha');

$toselect = GETPOST('toselect', 'array');

$confirm = GETPOST('confirm', 'alpha');

$sortfield = GETPOST("sortfield", 'alpha');

$sortorder = GETPOST("sortorder", 'alpha');



$search_indicatif = GETPOST("search_indicatif", 'alpha');

$search_type = GETPOST("search_type", 'alpha');

$search_type_materiel = GETPOST("search_type_materiel", 'alpha');





if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers

	{

		$search_indicatif = "";

		$search_type = "";

		$search_type_materiel = "";

	}





if (!$sortfield) $sortfield = "tk.rowid";

if (!$sortorder) $sortorder = "ASC";



// Security check

if (!$usercanmanagekittype) accessforbidden();



$socid = GETPOST('socid', 'int');

if (isset($user->socid) && $user->socid > 0)

{

	$action = '';

	$socid = $user->socid;

}



/*

 * Actions

 */





if (GETPOST('cancel', 'alpha')) { $massaction = ''; }

if (!$error && $action == 'delete' && $confirm == 'yes' && $usercandelete)

{

    $error = 0;

	foreach ($toselect as $toselectid)

	{

		// Check for existing kit using this type

		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."kit ";

		$sql .= "WHERE fk_type_kit = " . $toselectid;

		$sql .= " AND active = 1";

		$resql = $db->query($sql);

		$num = $db->num_rows($resql);

		if (!$num)

		{

			if (!deleteTypeKit($toselectid)) $error++;

		}

		else 

		{

			setEventMessages('Un élément n\'a pas pu être supprimé car il est associé à un kit existant', null, 'errors');

			$error++;

		}

	}

	if ($error > 0) setEventMessages('Erreur lors de la suppression d\'un ou plusieurs éléments', null, 'errors');

	else setEventMessages('Élément(s) supprimé(s) avec succès', null);

}







/*

 * View

 */

llxHeader("", "Types de kit");





$sql = "SELECT tk.rowid";

$sql.= " FROM ".MAIN_DB_PREFIX."c_type_kit as tk ";

$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_kit_det as tkdet on tk.rowid = tkdet.fk_type_kit";

$sql.=" WHERE 1=1";

if ($search_type_materiel && $search_type_materiel != '-1') {

    $sql .= natural_search('tkdet.fk_type_materiel', $search_type_materiel);

}

if ($search_indicatif) {

    $sql .= natural_search('tk.indicatif', $search_indicatif);

}

if ($search_type) {

    $sql .= natural_search('tk.type', $search_type);

}

$sql .= " GROUP BY tk.rowid";

$sql .= $db->order($sortfield, $sortorder);

$resql = $db->query($sql);







// Definition of fields for lists

$arrayfields = array(

	'tk.rowid'=>array('label'=>'ID', 'checked'=>1),

	'tk.indicatif'=>array('label'=>'Indicatif', 'checked'=>1, 'position'=>10),

	'tk.type'=>array('label'=>'Type de kit', 'checked'=>1, 'position'=>10),

	'tkdet.fk_type_materiel'=>array('label'=>'Types de matériel', 'checked'=>1, 'position'=>11));





print '<div class="fichecenter">';



print '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="formulaire">';

print '<input type="hidden" name="token" value="'.newToken().'">';

print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';

print '<input type="hidden" name="action" value="list">';

print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';

print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

print '<input type="hidden" name="showdeleted" value="'.$showdeleted.'">';



$arrayofselected = is_array($toselect) ? $toselect : array();





$arrayofmassactions['predelete'] = "<span class='fa fa-trash paddingrightonly'></span>".$langs->trans("Delete");

$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

$picto = 'kit';

if ($usercancreate) $newcardbutton = dolGetButtonTitle('Nouveau type de kit', '', 'fa fa-plus-circle', DOL_URL_ROOT.'/custom/kit/typekit/card.php?action=create', '', 1);

talm_print_barre_liste('Types de kit', 0, $_SERVER["PHP_SELF"], '', '', '',$massactionbutton, $num, $nbtotalofrecords, $picto, 0, $newcardbutton, '', $limit, 0, 0, 1);



include DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/typekit-massaction-pre.lib.php';









// Lines with input filters

print '<div class="div-table-responsive">';

print '<table class="tagtable liste">'."\n";

print '<tr class="liste_titre_filter">';



print '<td class="liste_titre left">';

print '<input class="flat" type="text" name="search_indicatif" size="8" value="'.dol_escape_htmltag($search_indicatif).'">';

print '</td>';



print '<td class="liste_titre left">';

print '<input class="flat" type="text" name="search_type" size="8" value="'.dol_escape_htmltag($search_type).'">';

print '</td>';



print '<td class="liste_titre ">';

$tm_dict = $materiel->getTypeMaterielDict();

print $form->selectarray('search_type_materiel', $tm_dict, $search_type_materiel, 1);

print '</td>';



print '<td class="liste_titre">';

print '</td>';



print '<td class="liste_titre center maxwidthsearch">';

$searchpicto = $form->showFilterButtons();

print $searchpicto;

print '</td>';



print '</tr>';







print '<tr class="liste_titre">';



print_liste_field_titre($arrayfields['tk.indicatif']['label'], $_SERVER["PHP_SELF"], "tk.indicatif", "", $param, "", $sortfield, $sortorder);

print_liste_field_titre($arrayfields['tk.type']['label'], $_SERVER["PHP_SELF"], "tk.type", "", $param, "", $sortfield, $sortorder);

print_liste_field_titre($arrayfields['tkdet.fk_type_materiel']['label'], $_SERVER["PHP_SELF"], "tkdet.fk_type_materiel", "", $param, "", $sortfield, $sortorder);

print '<td class="nowrap"></td>';

print '<td class="nowrap"></td>';



print '</tr>';



// Draft MyObject

if ($conf->kit->enabled == 1)

{

	$num = $db->num_rows($resql);

	if ($resql && $num)

	{

		$i = 0;

		while ($i < $num)

		{

			$obj = $db->fetch_object($resql);

			$typekit = new TypeKit($db);

			$typekit->fetch($obj->rowid);



			print '<tr class="oddeven">';



			print '<td class="tdoverflowmax200">';

			print $typekit->indicatif;

			print "</td>\n";



			print '<td class="tdoverflowmax200">';

			print $typekit->title;

			print "</td>\n";



			print '<td class="tdoverflowmax200">';

			$allowed_materiel_types_info = $typekit->getAllowedMaterielTypesInfo();

			foreach ($allowed_materiel_types_info as $allowed_materiel_type_info) {

				$label = $allowed_materiel_type_info['indicatif']; 

				print dolGetBadge($label, $label, 'primary', 'pill');

				print '&nbsp;';

			}

			print "</td>\n";



			print '<td class="tdoverflowmax200">';

			print '<a class="editfielda reposition" href="/custom/kit/typekit/card.php?action=edit&id='.$typekit->id.'">';

			print '<span class="fas fa-pencil-alt paddingrightonly" style=" color: #444;" title="Modifier"></span>';

			print '</a>';

			print "</td>\n";



			// Action

			print '<td class="nowrap center">';

			if ($usercandelete) {

				$selected = 0;

				if (in_array($typekit->id, $arrayofselected)) $selected = 1;

				print '<input id="cb'.$typekit->id.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$typekit->id.'"'.($selected ? ' checked="checked"' : '').'>';

			}



			print '</td>';

			print '</tr>';

			$i++;

		}



		print "</table><br>";

	}

	else

	{

		// No entry found / Query error

		print '<tr class="oddeven">';

		print '<td colspan="5" class="opacitymedium">Pas de type de kit correspondant.</td>';

		print '</tr>';

	}

}







	$db->free($resql);



	print "</table>";

	print "</div>";

	print '</form>';

print '<div class="fichethirdleft"></div><div class="fichetwothirdright"><div class="ficheaddleft">';







print '</div></div></div></div>';



// End of page

llxFooter();

$db->close();

