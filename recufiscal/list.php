<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

@include "../../main.inc.php";

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/recufiscal.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formrecufiscal.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/recufiscal.lib.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));


$action = GETPOST('action', 'alpha');
$massaction = GETPOST('massaction', 'alpha');
$toselect = GETPOST('toselect', 'array');
$confirm = GETPOST('confirm', 'alpha');

$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');


// Search fields values
$search_ref = GETPOST("search_ref", 'alpha');
$search_donateur = GETPOST("search_donateur", 'int');
$search_type = GETPOST("search_type", 'int');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'recufiscallist'; // To manage different context of search


$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
	// If $page is not defined, or '' or -1 or if we click on clear filters
	$page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;


if (!$sortfield) $sortfield = "r.rowid";
if (!$sortorder) $sortorder = "ASC";

if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers
{
	$search_ref = '';
	$search_donateur = '-1';
	$search_type = '-1';
}

$form = new Form($db);
$formrecufiscal = new FormRecuFiscal($db);

$usercanread = ($user->rights->materiel->read);
$usercancreate = ($user->rights->materiel->create);
$usercandelete = ($user->rights->materiel->delete);

$recufiscal = new RecuFiscal($db);

// Security check
if (!$usercanread) accessforbidden();

$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0)
{
	$action = '';
	$socid = $user->socid;
}

/*
 * Actions
 */
// if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') { $massaction = ''; }
// if (!$error && ($massaction == 'delete' || ($action == 'delete' && $confirm == 'yes')) && $usercandelete)
// {
// 	foreach ($toselect as $toselectid)
// 	{		
// 		$recufiscal_tmp = new RecuFiscal($db);
// 		$recufiscal_tmp->fetch($toselectid);
// 		$result = $recufiscal_tmp->delete();
// 		if (!$result) $error++;
// 	}
// 		$toselect = array();
// 		if (!$error) setEventMessages('Reçu fiscaux supprimés avec succès !', null);
// 		else setEventMessages('Une erreur est survenue lors la suppression d\'un ou plusieurs reçu fiscal.' , null, 'errors');
// }



/*
 * View
 */
llxHeader("", 'Reçu fiscaux - Liste');

$param = '';
if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
	$param .= '&contextpage='.urlencode($contextpage);
}
if ($limit > 0 && $limit != $conf->liste_limit) {
	$param .= '&limit='.urlencode($limit);
}
foreach ($search as $key => $val) {
	if (is_array($search[$key]) && count($search[$key])) {
		foreach ($search[$key] as $skey) {
			if ($skey != '') {
				$param .= '&search_'.$key.'[]='.urlencode($skey);
			}
		}
	} elseif ($search[$key] != '') {
		$param .= '&search_'.$key.'='.urlencode($search[$key]);
	}
}
if ($optioncss != '') {
	$param .= '&optioncss='.urlencode($optioncss);
}


print '<div class="fichecenter">';

$sql = "SELECT r.rowid";
$sql .= " FROM ".MAIN_DB_PREFIX."recu_fiscal as r ";
$sql .= " WHERE 1=1";
if ($search_ref != '' && $search_ref) $sql .= natural_search('r.ref', $search_ref);
if ($search_donateur != -1 && $search_donateur) $sql .= natural_search('r.fk_donateur', $search_donateur);
if ($search_type != -1 && $search_type) $sql .= natural_search('r.fk_type', $search_type);

$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	/* This old and fast method to get and count full list returns all record so use a high amount of memory.
	$resql = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($resql);
	*/
	/* The slow method does not consume memory on mysql (not tested on pgsql) */
	/*$resql = $db->query($sql, 0, 'auto', 1);
	while ($db->fetch_object($resql)) {
		$nbtotalofrecords++;
	}*/
	/* The fast and low memory method to get and count full list converts the sql into a sql count */
	$sqlforcount = preg_replace('/^SELECT[a-z0-9\._\s\(\),]+FROM/i', 'SELECT COUNT(*) as nbtotalofrecords FROM', $sql);
	$resql = $db->query($sqlforcount);
	$objforcount = $db->fetch_object($resql);
	$nbtotalofrecords = $objforcount->nbtotalofrecords;
	if (($page * $limit) > $nbtotalofrecords) {	// if total of record found is smaller than page * limit, goto and load page 0
		$page = 0;
		$offset = 0;
	}
	$db->free($resql);
}



$sql .= $db->order($sortfield, $sortorder);

if ($limit) {
	$sql .= $db->plimit($limit + 1, $offset);
}

$resql = $db->query($sql);

// Definition of fields for lists
$arrayfields = array(
	'r.ref'=>array('label'=>'Réf.', 'checked'=>1),
	'r.fk_donateur'=>array('label'=>'Donateur', 'checked'=>1, 'position'=>10),
	'r.montant'=>array('label'=>'Montant', 'checked'=>1, 'position'=>11),
	'r.fk_type'=>array('label'=>'Type', 'checked'=>1, 'position'=>11),
	'r.date_recu_fiscal'=>array('label'=>'Date du reçu fiscal', 'checked'=>1, 'position'=>12),
	'r.fk_statut'=>array('label'=>'État', 'checked'=>1, 'position'=>12),
	);


print '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="formulaire">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
$title = 'Liste des Reçus Fiscaux';
$arrayofselected = is_array($toselect) ? $toselect : array();

// Button to create new donor
$morehtmlright = dolGetButtonTitle('Nouveau reçu fiscal', '', 'fa fa-plus-circle paddingleft', DOL_URL_ROOT.'/custom/recufiscal/card.php', '', 1, array('morecss'=>'reposition'));
$arrayofmassactions['predelete'] = "<span class='fa fa-trash paddingrightonly'></span>".$langs->trans("Delete");
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

$picto = 'recufiscal';

//talm_print_barre_liste('Reçu fiscaux', 0, $_SERVER["PHP_SELF"], '', '', '', $massactionbutton, $num, $nbtotalofrecords, $picto, 0, $morehtmlright, '', $limit, 0, 0, 1);
if ($usercancreate) $newcardbutton = dolGetButtonTitle('Nouveau matériel', '', 'fa fa-plus-circle', DOL_URL_ROOT.'/custom/materiel/card.php?action=create', '', 1);
if($resql)
{
	$num = $db->num_rows($resql);
}
talm_print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'object_'.$object->picto, 0, $newcardbutton, '', $limit, 0, 0, 1);

// Gestion des actions de masse
require_once DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';

print '<div class="div-table-responsive">';
	print '<table class="tagtable liste">'."\n";

	print '<tr class="liste_titre_filter">';


	
	print '<td class="liste_titre maxwidthsearch">';
	print '<input type="text" name="search_ref" value = "'.$search_ref.'" />';
	print '</td>';	
	
	print '<td class="liste_titre maxwidthsearch">';
    print $form->selectarray('search_donateur', $formrecufiscal->getDonateurArray(), $search_donateur, 1, 0, 0, 'style="max-width:200px;"', 0, 0, 0, '', '', 1);
	print '</td>';

	print '<td class="liste_titre maxwidthsearch">';
	print '</td>';
	
	print '<td class="liste_titre center maxwidthsearch">';
	print '</td>';
	
	print '<td class="liste_titre center maxwidthsearch">';
    print $form->selectarray('search_type', getTypeArray(), $search_type, 1, 0, 0, 'style="min-width:200px;"', 0, 0, 0, '', '', 1);
	print '</td>';
	
	print '<td class="liste_titre center maxwidthsearch">';
	print '</td>';
	
	print '<td class="liste_titre center maxwidthsearch">';
	$searchpicto = $form->showFilterButtons();
	print $searchpicto;
	print '</td>';
	
	print '</tr>';


print '<tr class="liste_titre">';

print_liste_field_titre($arrayfields['r.ref']['label'], $_SERVER["PHP_SELF"], "r.ref", "", $param, "", $sortfield, $sortorder);
print_liste_field_titre($arrayfields['r.fk_donateur']['label'], $_SERVER["PHP_SELF"], "r.fk_donateur", "", $param, "", $sortfield, $sortorder);
print_liste_field_titre($arrayfields['r.montant']['label'], $_SERVER["PHP_SELF"], "r.montant", "", $param, "", $sortfield, $sortorder);
print_liste_field_titre($arrayfields['r.date_recu_fiscal']['label'], $_SERVER["PHP_SELF"], "r.date_recu_fiscal", "", $param, "align='center'", $sortfield, $sortorder);
print_liste_field_titre($arrayfields['r.fk_type']['label'], $_SERVER["PHP_SELF"], "r.fk_type", "", $param, "align='center'", $sortfield, $sortorder);
print_liste_field_titre($arrayfields['r.fk_statut']['label'], $_SERVER["PHP_SELF"], "r.fk_statut", "", $param, "align='center'", $sortfield, $sortorder);

print '<th></th>';




// Draft MyObject
if ($conf->materiel->enabled == 1)
{
	$langs->load("orders");
	if ($resql)
	{
		$total = 0;
		$num = $db->num_rows($resql);

		$var = true;
		if ($num > 0)
		{
			$i = 0;
			while ($i < $num)
			{

				$obj = $db->fetch_object($resql);
                $recufiscal->fetch($obj->rowid);

				print '<tr class="oddeven">';

				print '<td class="tdoverflowmax200">';
				print $recufiscal->getNomUrl();
				print "</td>\n";

				print '<td class="nowrap">';
                print $recufiscal->donateur_object->getNomUrl();
				print "</td>\n";

				print '<td class="nowrap">';
				print price($recufiscal->total_ttc, 1, '', 0, -1, -1, $conf->currency);
				print "</td>\n";

				print '<td class="nowrap center">';
    			print date('d/m/Y', $recufiscal->date_recu_fiscal);
				print "</td>\n";
			
				print '<td class="nowrap center">';		
				print '<span class="badgeneutral">';
				print $recufiscal->getLibType();
				print '</span>';
				print "</td>\n";

				print '<td class="nowrap center">';
    			print $recufiscal->LibStatus($recufiscal->fk_statut, 4);
				print "</td>\n";



				// Action
        		print '<td class="nowrap center">';
        		if ($usercandelete) {
        			$selected = 0;
        			if (in_array($recufiscal->id, $arrayofselected)) $selected = 1;
        			print '<input id="cb'.$recufiscal->id.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$recufiscal->id.'"'.($selected ? ' checked="checked"' : '').'>';
        		}
        		print '</td>';


				print '</tr>';
				
				$total++;
				$i++;
			}
			if (!$total) print '<tr class="oddeven"><td colspan="9" class="opacitymedium">Pas de reçu fiscal correspondant.</td></tr>';
		}
		else
		{

			print '<tr class="oddeven"><td colspan="9" class="opacitymedium">Pas de reçu fiscal correspondant.</td></tr>';
		}
		print "</table><br>";

		$db->free($resql);
	}
	else
	{
		dol_print_error($db);
	}
}

	$db->free($resql);

	print "</table>";
	print "</div>";
	print '</form>';
print '<div class="fichethirdleft"></div><div class="fichetwothirdright"><div class="ficheaddleft">';

print '</div></div></div>';

// End of page
llxFooter();
$db->close();