<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

@include_once "../../../../main.inc.php";

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/preinventaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/source.class.php';
// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));
$form = new Form($db);

/*
 * Data fetching
 */
$usercanread = ($user->rights->materiel->read);
$usercancreate = ($user->rights->materiel->create);
$usercandelete = ($user->rights->materiel->delete);

$source_type_array = getSourceTypeArray(1);

$action = GETPOST('action', 'alpha');
$id = GETPOST('id', 'int');
$preinventoriable = GETPOST('preinventoriable', 'int');

$massaction = GETPOST('massaction', 'alpha');
$toselect = GETPOST('toselect', 'array');
$confirm = GETPOST('confirm', 'alpha');
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');


$search_ref = GETPOST("search_ref", 'alpha');
$search_donateur = GETPOST("search_donateur", 'int');
$search_type = GETPOST("search_type", 'int');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'managesource'; // To manage different context of search


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



$sourcetypeid = GETPOST("sourcetypeid", 'alpha') ? GETPOST("sourcetypeid", 'alpha') : 1;

if (!$sortfield && $sourcetypeid != 3) $sortfield = "s.datec";
else $sortfield = "s.date_creation";
if (!$sortorder) $sortorder = "DESC";

if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers
{
    // Reset filters values
}
// Security check
if (!$usercanread) accessforbidden();
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0)
{
	$action = '';
	$socid = $user->socid;
}

// Add file containing action 
include_once('./actions.lib.php');
/*
 * Actions
 */

// Massactions

if ($action == 'add')
{
	if (!$id) {
		setEventMessages('Erreur lors du traitement de la source', null, 'errors');
		exit;
	} else {
		$source = new Source($db);
		$source->create_reference_object($sourcetypeid);
		$source->source_reference_object->fetch($id);
		$result = $source->add($preinventoriable);
		if (!$result) setEventMessages('Erreur lors du traitement de la source', null, 'errors');
		else setEventMessages('Source traitée avec succès', null);
	}

}


/*
 * View
 */

llxHeader("", 'Gestion / Traitement des sources');

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


// Fetch untreated sources from the database using the table specified by sourcetypeid
$sql = "SELECT DISTINCT s.rowid";
$sql.= " FROM " . MAIN_DB_PREFIX . $source_type_array[$sourcetypeid]['tablename'] . " as s ";
$sql.= " WHERE s.rowid NOT IN (SELECT fk_origine FROM ".MAIN_DB_PREFIX."source WHERE fk_type_source = ". $sourcetypeid;

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

$sql.= ")";


if($source_type_array[$sourcetypeid]['tablename'] == "emprunt")
{
	$sql.= " AND s.status != 0"; // This line is to not select drafts sources
	$sortfield = "s.date_creation";
	$title = 'Liste des Emprunts';
}
elseif($source_type_array[$sourcetypeid]['tablename'] == "facture_fourn" || $source_type_array[$sourcetypeid]['tablename'] == "recu_fiscal")
{
	$sql.= " AND s.fk_statut != 0"; // This line is to not select drafts sources
	$sortfield = "s.datec";
	$title = $source_type_array[$sourcetypeid]['tablename'] == "facture_fourn" ? 'Liste des factures' : 'Liste des reçus fiscaux';
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
print '<input type="hidden" name="sourcetypeid" value="'.$sourcetypeid.'">';


$arrayofselected = is_array($toselect) ? $toselect : array();

// Button to switch the source type to manage
$morehtmlright = dolGetButtonTitle('Factures', '', 'fa fa-file-invoice-dollar paddingleft', $_SERVER["PHP_SELF"].'?sourcetypeid=1', '', 1, array('morecss'=>'reposition'.($sourcetypeid == 1 ? ' btnTitleSelected' : ' imgforviewmode')));
$morehtmlright .= dolGetButtonTitle('Reçus fiscaux', '', 'fa fa-hand-holding-usd paddingleft', $_SERVER["PHP_SELF"].'?sourcetypeid=2', '', 1, array('morecss'=>'reposition'.($sourcetypeid == 2 ? ' btnTitleSelected' : ' imgforviewmode')));
$morehtmlright .= dolGetButtonTitle('Emprunts', '', 'fa fa-handshake paddingleft', $_SERVER["PHP_SELF"].'?sourcetypeid=3', '', 1, array('morecss'=>'reposition'.($sourcetypeid == 3 ? ' btnTitleSelected' : ' imgforviewmode')));


$arrayofmassactions['preaddtopreinventory'] = "<span class='fa fa-plus-square paddingrightonly'></span> Ajouter au pré-inventaire";
$arrayofmassactions['prenoninventoriable'] = "<span class='fa fa-minus-square paddingrightonly'></span> Définir comme non inventoriable";
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);
$picto = 'materiel';

if($resql)
{
	$num = $db->num_rows($resql);
}

//talm_print_barre_liste('Gestion / Traitement des sources', 0, $_SERVER["PHP_SELF"], '', '', '',$massactionbutton, $num, $nbtotalofrecords, $picto, 0, $morehtmlright, '', $limit, 0, 0, 1);
talm_print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, $picto, 0, $morehtmlright, '', $limit, 0, 0, 1);

// Gestion des actions de masse
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/source-massaction-pre.lib.php';


// Lines with input filters
print '<div class="div-table-responsive">';
print '<table class="tagtable liste">'."\n";
print '<tr class="liste_titre_filter">';



print '<td class="liste_titre center maxwidthsearch">';
print '</td>';

print '<td class="liste_titre center maxwidthsearch">';
print '</td>';

print '<td class="liste_titre center maxwidthsearch">';
print '</td>';
print '<td class="liste_titre center maxwidthsearch">';
print '</td>';
if($sourcetypeid == 1)
{
	print '<td class="liste_titre center maxwidthsearch">';
	print '</td>';

	print '<td class="liste_titre center maxwidthsearch">';
	print '</td>';
}

print '<td class="liste_titre center maxwidthsearch">';
$searchpicto = $form->showFilterButtons();
print $searchpicto;
print '</td>';

print '</tr>';



print '<tr class="liste_titre">';
$param = '&sourcetypeid='.$sourcetypeid;
print_liste_field_titre('Référence', $_SERVER["PHP_SELF"], "s.ref", "", $param, "", $sortfield, $sortorder);

if($sourcetypeid == 2)
{
	print_liste_field_titre('Contenu du don', $_SERVER["PHP_SELF"], "", "", $param, "", $sortfield, $sortorder);
	$intitule = "Date du Don";
}
elseif($sourcetypeid == 3)
{
	print_liste_field_titre('Contenu de l\'emprunt', $_SERVER["PHP_SELF"], "", "", $param, "", $sortfield, $sortorder);
	$intitule = "Date de l'emprunt";
}
else
{
	print_liste_field_titre('Contenu de la facture', $_SERVER["PHP_SELF"], "s.libelle", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre('Tiers', $_SERVER["PHP_SELF"], "s.fk_soc", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre('Montant', $_SERVER["PHP_SELF"], "s.total_ttc", "", $param, "", $sortfield, $sortorder); // A CHANGER (total_ttc pas le meme champs sur les tables recus fiscaux et emprunts)
	$intitule = "Date de la facture";
}
print_liste_field_titre($intitule, $_SERVER["PHP_SELF"], "s.datec", "", $param, "", $sortfield, $sortorder);
print '<td class="center">Gérer</td>';
print '<td class="nowrap"></td>';


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
				// Instantiate new Class using source factory function
				$source = new Source($db);
				$source->create_reference_object($sourcetypeid);
				$source->fetch_reference_object($obj->rowid);
				print '<tr class="oddeven">';
    			print '<td class="tdoverflowmax100">';
				print $source->source_reference_object->getNomUrl(1);
    			print "</td>\n";
				print '<td class="tdoverflowmax300">';
				if($sourcetypeid == 2)
				{
					$detail = "SELECT * FROM ".MAIN_DB_PREFIX."recu_fiscal_det WHERE fk_recu_fiscal = ".$obj->rowid;
				}
				elseif($sourcetypeid == 3)
				{
					$detail = "SELECT * FROM ".MAIN_DB_PREFIX."emprunt_det WHERE fk_emprunt = ".$obj->rowid;
				}
				else
				{
					$detail = "SELECT * FROM ".MAIN_DB_PREFIX."facture_fourn_det WHERE fk_facture_fourn = ".$obj->rowid;
				}
				$resqlDetail = $db->query($detail);
				foreach($resqlDetail as $value)
				{
					print '- '.$value['description'].' (x'.$value['qty'].')'.'<br>';
				}
    			print "</td>\n";
				
				if($sourcetypeid == 1)
				{
				print '<td class="tdoverflowmax100">';
				$societe = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid = ".$source->source_reference_object->fk_soc;
				$resqlSociete = $db->query($societe);
				$objSociete = $db->fetch_object($resqlSociete);
				print $objSociete->nom;
    			print "</td>\n";

    			print '<td class="tdoverflowmax100">';
				print number_format($source->source_reference_object->total_ttc, 2, ',', ' ') . ' €';
    			print "</td>\n";
				}
    			print '<td class="tdoverflowmax100">';
				print date('d/m/Y', ($sourcetypeid == 1 ? $source->source_reference_object->date : ($sourcetypeid == 2 ? $source->source_reference_object->date_recu_fiscal : $source->source_reference_object->date_emprunt)));
    			print "</td>\n";
				

    			print '<td class="tdoverflowmax100 center">';
				$url = $_SERVER["PHP_SELF"].'?sourcetypeid='.$sourcetypeid.'&action=add&id='.$source->source_reference_object->id;
				print dolGetButtonTitle('Ajouter au pré-inventaire', '', 'fa fa-plus-square', $url.'&preinventoriable=1', '', 1);
				print dolGetButtonTitle('Non inventoriable', '', 'fa fa-minus-square', $url.'&preinventoriable=0', '', 1);
    			print "</td>\n";

        		// Action
        		print '<td class="nowrap center">';
        		if ($usercandelete) {
        			$selected = 0;
        			if (in_array($obj->rowid, $arrayofselected)) $selected = 1;
        			print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected ? ' checked="checked"' : '').'>';
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