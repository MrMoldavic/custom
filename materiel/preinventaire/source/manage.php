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



if (!$sortfield) $sortfield = "s.datec";

if (!$sortorder) $sortorder = "DESC";



$sourcetypeid = GETPOST("sourcetypeid", 'alpha') ? GETPOST("sourcetypeid", 'alpha') : 1;





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

// Fetch untreated sources from the database using the table specified by sourcetypeid

$sql = "SELECT DISTINCT s.rowid";

$sql.= " FROM " . MAIN_DB_PREFIX . $source_type_array[$sourcetypeid]['tablename'] . " as s ";

$sql.= " WHERE s.rowid NOT IN (SELECT fk_source FROM ".MAIN_DB_PREFIX."source WHERE fk_type_source = ". $sourcetypeid .")";

$sql.= " AND s.fk_statut != 0"; // This line is to not select drafts sources

$sql .= $db->order($sortfield, $sortorder);

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



talm_print_barre_liste('Gestion / Traitement des sources', 0, $_SERVER["PHP_SELF"], '', '', '',$massactionbutton, $num, $nbtotalofrecords, $picto, 0, $morehtmlright, '', $limit, 0, 0, 1);



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

if($sourcetypeid != 2)
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
}
elseif($sourcetypeid == 3)
{
	print_liste_field_titre('Contenu de l\'emprunt', $_SERVER["PHP_SELF"], "", "", $param, "", $sortfield, $sortorder);
}
else
{
	print_liste_field_titre('Contenu de la facture', $_SERVER["PHP_SELF"], "s.libelle", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre('Tiers', $_SERVER["PHP_SELF"], "s.fk_soc", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre('Montant', $_SERVER["PHP_SELF"], "s.total_ttc", "", $param, "", $sortfield, $sortorder); // A CHANGER (total_ttc pas le meme champs sur les tables recus fiscaux et emprunts)
}

print_liste_field_titre('Date de création', $_SERVER["PHP_SELF"], "s.datec", "", $param, "", $sortfield, $sortorder);

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
    			print '<td class="tdoverflowmax200">';

				print $source->source_reference_object->getNomUrl(1);

    			print "</td>\n";


				print '<td class="tdoverflowmax200">';

				if($sourcetypeid == 2)
				{
					$detail = "SELECT * FROM ".MAIN_DB_PREFIX."recu_fiscal_det WHERE fk_recu_fiscal = ".$obj->rowid;
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

				if($sourcetypeid != 2)
				{
				print '<td class="tdoverflowmax200">';

				$societe = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid = ".$source->source_reference_object->fk_soc;
				$resqlSociete = $db->query($societe);
				$objSociete = $db->fetch_object($resqlSociete);

				print $objSociete->nom;

    			print "</td>\n";



    			print '<td class="tdoverflowmax200">';

				print number_format($source->source_reference_object->total_ttc, 2, ',', ' ') . ' €';

    			print "</td>\n";

				}
    			print '<td class="tdoverflowmax200">';

				print dol_print_date($source->source_reference_object->datec, '%e %B %Y');

    			print "</td>\n";
				


    			print '<td class="tdoverflowmax200 center">';

				$url = $_SERVER["PHP_SELF"].'?sourcetypeid='.$sourcetypeid.'&action=add&id='.$source->source_reference_object->id;

				print dolGetButtonTitle('Ajouter au pré-inventaire', '', 'fa fa-plus-square paddingleft', $url.'&preinventoriable=1', '', 1);

				print '&nbsp;';

				print dolGetButtonTitle('Non inventoriable', '', 'fa fa-minus-square paddingleft', $url.'&preinventoriable=0', '', 1);

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