<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

@include_once "../../../../main.inc.php";

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/preinventaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/source.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formpreinventaire.class.php';

// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));

$form = new Form($db);
$formpreinventaire = new FormPreinventaire($db);

/*
 * Data fetching
 */
$usercanread = ($user->rights->materiel->read);
$usercancreate = ($user->rights->materiel->create);
$usercandelete = ($user->rights->materiel->delete);

$source_type_array = getSourceTypeArray(1);

$viewmode = (GETPOST('viewmode', 'alpha') ? GETPOST('viewmode', 'alpha') : 'inventoriable');

$action = GETPOST('action', 'alpha');
$massaction = GETPOST('massaction', 'alpha');
$toselect = GETPOST('toselect', 'array');
$confirm = GETPOST('confirm', 'alpha');
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;

$search_ref = ((GETPOST("searchref", 'alpha') != '-1' && GETPOST("searchref", 'alpha')) ? GETPOST("searchref", 'alpha') : '');
$search_source_type = ((GETPOST("search_source_type", 'alpha') != '-1' && GETPOST("search_source_type", 'alpha')) ? GETPOST("search_source_type", 'alpha') : '');

$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
	// If $page is not defined, or '' or -1 or if we click on clear filters
	$page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

if (!$sortfield) $sortfield = "s.datec";
if (!$sortorder) $sortorder = "DESC";

if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers
{
    $search_ref = '';
    $search_source_type = '';
}



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
if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') { $massaction = ''; }
if (!$error && ($massaction == 'delete' || ($action == 'delete' && $confirm == 'yes')) && $usercancreate)
{
	foreach ($toselect as $toselectid)
	{		
		$source = new Source($db);
		$source->fetch($toselectid);
		$result = $source->delete();
		if (!$result) $error++;
	}
		$toselect = array();
		if (!$error) setEventMessages('Source(s) supprimée(s) avec succès. Les sources supprimées doivent êtres re-traitées.', null);
		else setEventMessages('Une erreur est survenue lors la suppression d\'une ou plusieurs sources. Vous ne pouvez pas supprimer une source associée à un matériel inventorié.' , null, 'errors');
}


/*
 * View
 */

llxHeader("", 'Liste des sources traitées');


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


// Fetch treated sources from the database
$sql = "SELECT DISTINCT s.rowid, s.fk_type_source, s.fk_origine, s.fk_status";
$sql.= " FROM " . MAIN_DB_PREFIX . "source as s WHERE s.inventoriable = " . ($viewmode == 'inventoriable' ? '1' : '0');

if ($search_source_type) {
	$sql .= natural_search('s.fk_type_source', $search_source_type);
}

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


print '<div class="fichecenter">';

print '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="formulaire">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="viewmode" value="'.$viewmode.'">';


$arrayofselected = is_array($toselect) ? $toselect : array();
$title = 'Liste des Sources';
$picto = 'materiel';

// Button to switch between inventoriable and non inventoriable sources
$morehtmlright = dolGetButtonTitle('Inventoriables', '', 'fa fa-pallet paddingleft', $_SERVER["PHP_SELF"].'?viewmode=inventoriable', '', 1, array('morecss'=>'reposition'.($viewmode == 'inventoriable' ? ' btnTitleSelected' : ' imgforviewmode')));
$morehtmlright .= dolGetButtonTitle('Non inventoriables', '', 'fa fa-times-circle paddingleft', $_SERVER["PHP_SELF"].'?viewmode=noninventoriable', '', 1, array('morecss'=>'reposition'.($viewmode == 'noninventoriable' ? ' btnTitleSelected' : ' imgforviewmode')));

$arrayofmassactions['predelete'] = "<span class='fa fa-trash paddingrightonly'></span>".$langs->trans("Delete");
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

if($resql)
{
	$num = $db->num_rows($resql);
}
//talm_print_barre_liste('Liste des sources', 0, $_SERVER["PHP_SELF"], '', '', '', $massactionbutton, $num, $nbtotalofrecords, $picto, 0, $morehtmlright, '', $limit, 0, 0, 1);
talm_print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, $picto, 0, $morehtmlright, '', $limit, 0, 0, 1);
// Gestion des actions de masse
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/source-massaction-pre.lib.php';

// Lines with input filters
print '<div class="div-table-responsive">';
print '<table class="tagtable liste">'."\n";
print '<tr class="liste_titre_filter">';


print '<td class="liste_titre maxwidthsearch">';
print $formpreinventaire->selectSources($search_ref, 'searchref');
print '</td>';
print '<td class="liste_titre center maxwidthsearch">';
print '</td>';

print '<td class="liste_titre center maxwidthsearch">';
print $form->selectarray('search_source_type', getSourceTypeArray(0), $search_source_type, 1);
print '</td>';

print '<td class="liste_titre center maxwidthsearch">';
print '</td>';

print '<td class="liste_titre center maxwidthsearch">';
print '</td>';

print '<td class="liste_titre center maxwidthsearch">';
print '</td>';

print '<td class="liste_titre center maxwidthsearch">';
print '</td>';

print '<td class="liste_titre center maxwidthsearch">';
$searchpicto = $form->showFilterButtons();
print $searchpicto;
print '</td>';

print '</tr>';


print '<tr class="liste_titre">';

print_liste_field_titre('Réf.', $_SERVER["PHP_SELF"], "s.ref", "", $param, "", $sortfield, $sortorder);
print_liste_field_titre('Contenu', $_SERVER["PHP_SELF"], "s.ref", "", $param, "", $sortfield, $sortorder);
print_liste_field_titre('Type de source', $_SERVER["PHP_SELF"], "s.fk_type_source", "", $param, 'align="center"', $sortfield, $sortorder);
print_liste_field_titre('Date effective', $_SERVER["PHP_SELF"], "s.datec", "", $param, "", $sortfield, $sortorder);
print_liste_field_titre('Montant indiqué', $_SERVER["PHP_SELF"], "s.total_ttc", "", $param, "", $sortfield, $sortorder); // A CHANGER (total_ttc pas le meme champs sur les tables recus fiscaux et emprunts)
print '<td>Valeur cumulée</td>';
print '<td class="center">État</td>';
print '<td></td>';
print '</tr>';


// Draft MyObject
if ($conf->materiel->enabled == 1)
{
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

				$source = new Source($db);
				$source->fetch($obj->rowid);
				$source->fetch_lines();

				// Filter for search_ref
				if ($search_ref != '' && $source->ref != $search_ref) {
					$i++;
					continue;
				}

				print '<tr class="oddeven">';

    			print '<td class="tdoverflowmax200">';
				print $source->getNomUrl(1);
    			print "</td>\n";

				print '<td class="tdoverflowmax200">';
				if($obj->fk_type_source == 2)
				{
					$detail = "SELECT * FROM ".MAIN_DB_PREFIX."recu_fiscal_det WHERE fk_recu_fiscal = ".$obj->fk_origine;
				}
				elseif($obj->fk_type_source == 3)
				{
					$detail = "SELECT * FROM ".MAIN_DB_PREFIX."emprunt_det WHERE fk_emprunt = ".$obj->fk_origine;
				}
				else
				{
					$detail = "SELECT * FROM ".MAIN_DB_PREFIX."facture_fourn_det WHERE fk_facture_fourn = ".$obj->fk_origine;
				}
				$resqlDetail = $db->query($detail);
					
				foreach($resqlDetail as $value)
				{
					print '- '.$value['description'].' (x'.$value['qty'].')'.'<br>';
				}
				
    			print "</td>\n";

    			print '<td class="tdoverflowmax200 center">';
				print '<span class="badgeneutral">';
				print $source->source_reference_type;
				print '</span>';
    			print "</td>\n";
    			print '<td class="tdoverflowmax200">';
				print date('d/m/Y', ($source->source_reference_type == 'Facture' ? $source->source_reference_object->date : ($source->source_reference_type == 'Reçu Fiscal' ? $source->source_reference_object->date_recu_fiscal : $source->source_reference_object->date_emprunt)));

    			print "</td>\n";
                
                print '<td class="tdoverflowmax200">';
                print price($source->source_reference_object->total_ttc, 1, '', 0, -1, -1, $conf->currency);
                print "</td>\n";

    			print '<td class="tdoverflowmax200">';
                print price($source->total_specified, 1, '', 0, -1, -1, $conf->currency);
    			print "</td>\n";

    			print '<td class="tdoverflowmax200 center">';
				print $source->LibStatus($source->fk_status, 6);
    			print "</td>\n";
				
				// Action
        		print '<td class="nowrap center">';
        		if ($usercandelete) {
        			$selected = 0;
        			if (in_array($source->id, $arrayofselected)) $selected = 1;
        			print '<input id="cb'.$source->id.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$source->id.'"'.($selected ? ' checked="checked"' : '').'>';
        		}
        		print '</td>';
    
				print '</tr>';
				$total++;
				$i++;
			}
			if (!$total) print '<tr class="oddeven"><td colspan="7" class="opacitymedium">Pas de source correspondante.</td></tr>';
		}
		else
		{

			print '<tr class="oddeven"><td colspan="7" class="opacitymedium">Pas de source correspondante.</td></tr>';
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