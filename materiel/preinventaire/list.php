<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

@include "../../../main.inc.php";

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/preinventaireline.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/source.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formpreinventaire.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));

$action = GETPOST('action', 'alpha');

$confirm = GETPOST('confirm', 'alpha');
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');


// Search fields values
$search_source = GETPOST("search_source", 'alpha');
$search_description = GETPOST('search_description', 'alpha');
$search_inventoriable = GETPOST('search_inventoriable', 'int');
$search_amortissable = GETPOST('search_amortissable', 'int');
$search_etat = GETPOST('search_etat', 'int');

if (!$sortfield) $sortfield = "p.rowid";
if (!$sortorder) $sortorder = "DESC";

if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers
{
	$search_source = '-1';
	$search_description = '';
	$search_inventoriable = '';
	$search_amortissable = '';
	$search_etat = '';
}

$form = new Form($db);

$usercanread = ($user->rights->materiel->read);
$usercancreate = ($user->rights->materiel->create);
$usercandelete = ($user->rights->materiel->delete);

$preinventaireline = new PreinventaireLine($db);
$formpreinventaire = new FormPreinventaire($db);

// Security check
if (!$usercanread) accessforbidden();

$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0)
{
	$action = '';
	$socid = $user->socid;
}


/*
 * View
 */
llxHeader("", $langs->trans("Pré-inventaire"));

print '<div class="fichecenter">';

$sql = "SELECT p.rowid";
$sql .= " FROM ".MAIN_DB_PREFIX."preinventaire as p ";
$sql .= " WHERE 1=1";
if ($search_description != '' and $search_description) $sql .= natural_search('p.description', $search_description);
if ($search_inventoriable != '-1' and $search_inventoriable != '') $sql .= natural_search('p.inventoriable', $search_inventoriable);
if ($search_amortissable != '-1' and $search_amortissable != '') $sql .= natural_search('p.amortissable', $search_amortissable);
$sql .= $db->order($sortfield, $sortorder);
$resql = $db->query($sql);

// Definition of fields for lists
$arrayfields = array(
	'p.rowid'=>array('label'=>'ID', 'checked'=>1),
	'p.fk_source'=>array('label'=>'Source', 'checked'=>1, 'position'=>10),
	'p.description'=>array('label'=>'Description', 'checked'=>1, 'position'=>11),
	'p.valeur'=>array('label'=>'Valeur', 'checked'=>1, 'position'=>12),
	'p.inventoriable'=>array('label'=>'Inventoriable', 'checked'=>1, 'position'=>12),
	'p.amortissable'=>array('label'=>'Amortissable', 'checked'=>1, 'position'=>13));


print '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="formulaire">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
	print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

    $picto = 'materiel';
	talm_print_barre_liste('Pré-inventaire', 0, $_SERVER["PHP_SELF"], '', '', '','', $num, $nbtotalofrecords, $picto, 0, '', '', $limit, 0, 0, 1);

print '<div class="div-table-responsive">';
	print '<table class="tagtable liste">'."\n";

	print '<tr class="liste_titre_filter">';
	$array = array(0=>'Non', 1=>'Oui');
	$array_etat = array(PreinventaireLine::STATUS_NON_INVENTORIE=>PreinventaireLine::NON_INVENTORIE_LABEL, 
						PreinventaireLine::STATUS_INVENTORIE=>PreinventaireLine::INVENTORIE_LABEL, 
						PreinventaireLine::STATUS_NON_INVENTORIABLE=>PreinventaireLine::NON_INVENTORIABLE_LABEL);


	print '<td class="liste_titre maxwidthsearch">';
	print $formpreinventaire->selectSources($search_source, 'search_source', 1, 0, '', '');
	print '</td>';
	
	print '<td class="liste_titre maxwidthsearch">';
	print '<input type="text" name="search_description" value="'.$search_description.'"/>';
	print '</td>';
	
	print '<td class="liste_titre center maxwidthsearch">';
	print '</td>';
	
	print '<td class="liste_titre center maxwidthsearch">';
	print $form->selectarray('search_inventoriable', $array, $search_inventoriable, 1);
	print '</td>';
	
	print '<td class="liste_titre center maxwidthsearch">';
	print $form->selectarray('search_amortissable', $array, $search_amortissable, 1);
	print '</td>';
	
	print '<td class="liste_titre center maxwidthsearch">';
	print $form->selectarray('search_etat', $array_etat, $search_etat, 1);
	print '</td>';
	
	print '<td class="liste_titre center maxwidthsearch">';
	$searchpicto = $form->showFilterButtons();
	print $searchpicto;
	print '</td>';
	
	print '</tr>';


print '<tr class="liste_titre">';

print_liste_field_titre($arrayfields['p.fk_source']['label'], $_SERVER["PHP_SELF"], "p.fk_source", "", $param, "", $sortfield, $sortorder);
print_liste_field_titre($arrayfields['p.description']['label'], $_SERVER["PHP_SELF"], "p.description", "", $param, "", $sortfield, $sortorder);
print_liste_field_titre($arrayfields['p.valeur']['label'], $_SERVER["PHP_SELF"], "p.valeur", "", $param, "", $sortfield, $sortorder);
print_liste_field_titre($arrayfields['p.inventoriable']['label'], $_SERVER["PHP_SELF"], "p.inventoriable", "", $param, "align='center'", $sortfield, $sortorder);
print_liste_field_titre($arrayfields['p.amortissable']['label'], $_SERVER["PHP_SELF"], "p.amortissable", "", $param, "align='center'", $sortfield, $sortorder);
print '<td class="center">État</td>';

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
                $preinventaireline->fetch($obj->rowid);
				$source_tmp = new Source($db);
				$source_tmp->fetch($preinventaireline->fk_source);

				if ($search_source != -1 && $search_source && $source_tmp->ref != $search_source) {
					$i++;
					continue;
				}	

				if ($search_etat != '-1' && $search_etat != '' && $preinventaireline->status != $search_etat) {
					$i++;
					continue;
				}	

				print '<tr class="oddeven">';

				print '<td class="tdoverflowmax200">';
				print $source_tmp->getNomUrl();
				print "</td>\n";

				print '<td class="nowrap">';
				print $preinventaireline->description;
				print "</td>\n";

				print '<td class="tdoverflowmax200">';
				print price($preinventaireline->valeur, 1, '', 0, -1, -1, $conf->currency);
				print "</td>\n";

				print '<td class="tdoverflowmax200 center">';
				$status = ($preinventaireline->inventoriable ? 'status4' : 'status5');
				$label = ($preinventaireline->inventoriable ? Source::LINE_INVENTORIABLE_LABEL : Source::LINE_NON_INVENTORIABLE_LABEL);
				print dolGetStatus($label, '', '', $status, 3);
				print "</td>\n";

				print '<td class="tdoverflowmax200 center">';
				$status = ($preinventaireline->amortissable ? 'status4' : 'status5');
				$label = ($preinventaireline->amortissable ? Source::LINE_AMORTISSABLE_LABEL : Source::LINE_NON_AMORTISSABLE_LABEL);
				print dolGetStatus($label, '', '', $status, 3);
				print "</td>\n";

				print '<td class="tdoverflowmax200 center">';
				print $preinventaireline->LibStatus($preinventaireline->status, 4);
				print "</td>\n";

				print '<td >';
				print "</td>\n";


				print '</tr>';
				
				$total++;
				$i++;
			}
			if (!$total) print '<tr class="oddeven"><td colspan="9" class="opacitymedium">Pas de materiel correspondant.</td></tr>';
		}
		else
		{

			print '<tr class="oddeven"><td colspan="9" class="opacitymedium">Pas de materiel correspondant.</td></tr>';
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