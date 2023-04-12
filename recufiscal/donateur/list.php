<?php



// ini_set('display_errors', '1');

// ini_set('display_startup_errors', '1');

// error_reporting(E_ALL);



@include "../../../main.inc.php";



require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/recufiscal.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formrecufiscal.class.php';



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

$search_donateur = GETPOST("search_donateur", 'int');

$search_societe = GETPOST('search_societe', 'alpha');



if (!$sortfield) $sortfield = "d.rowid";

if (!$sortorder) $sortorder = "ASC";



if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers

{

	$search_donateur = '-1';

	$search_societe = '';

}



$form = new Form($db);

$formrecufiscal = new FormRecuFiscal($db);



$usercanread = ($user->rights->materiel->read);

$usercancreate = ($user->rights->materiel->create);

$usercandelete = ($user->rights->materiel->delete);



$donateur = new Donateur($db);



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

if (!$error && ($massaction == 'delete' || ($action == 'delete' && $confirm == 'yes')) && $usercandelete)

{

	foreach ($toselect as $toselectid)

	{		

		$donateur_tmp = new Donateur($db);

		$donateur_tmp->fetch($toselectid);

		$result = $donateur_tmp->delete();

		if (!$result) $error++;

	}

		$toselect = array();

		if (!$error) setEventMessages('Donateur(s) supprimé(s) avec succès !', null);

		else setEventMessages('Une erreur est survenue lors la suppression d\'un ou plusieurs donateurs.' , null, 'errors');

}







/*

 * View

 */

llxHeader("", $langs->trans("Donateur"));



print '<div class="fichecenter">';



$sql = "SELECT d.rowid";

$sql .= " FROM ".MAIN_DB_PREFIX."donateur as d ";

$sql .= " WHERE active=1";

if ($search_societe != '' and $search_societe) $sql .= natural_search('d.societe', $search_societe);

$sql .= $db->order($sortfield, $sortorder);

$resql = $db->query($sql);



// Definition of fields for lists

$arrayfields = array(

	'd.rowid'=>array('label'=>'ID', 'checked'=>1),

	'd.nom'=>array('label'=>'Nom', 'checked'=>1, 'position'=>10),

	'd.prenom'=>array('label'=>'Identifiant', 'checked'=>1, 'position'=>11),

	'd.societe'=>array('label'=>'Société', 'checked'=>1, 'position'=>12),

	'd.address'=>array('label'=>'Adresse', 'checked'=>1, 'position'=>12),

	'd.zipcode'=>array('label'=>'Code postal', 'checked'=>1, 'position'=>13),

	'd.town'=>array('label'=>'Ville', 'checked'=>1, 'position'=>13),

	'd.phone'=>array('label'=>'Téléphone', 'checked'=>1, 'position'=>13),

	'd.email'=>array('label'=>'E-mail', 'checked'=>1, 'position'=>13)

);





print '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="formulaire">';

print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';

print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';



$arrayofselected = is_array($toselect) ? $toselect : array();



// Button to create new donor

$morehtmlright = dolGetButtonTitle('Nouveau donateur', '', 'fa fa-plus-circle paddingleft', DOL_URL_ROOT.'/custom/recufiscal/donateur/card.php', '', 1, array('morecss'=>'reposition'));

$arrayofmassactions['predelete'] = "<span class='fa fa-trash paddingrightonly'></span>".$langs->trans("Delete");

$massactionbutton = $form->selectMassAction('', $arrayofmassactions);



$picto = 'donateur';



talm_print_barre_liste('Donateurs', 0, $_SERVER["PHP_SELF"], '', '', '', $massactionbutton, $num, $nbtotalofrecords, $picto, 0, $morehtmlright, '', $limit, 0, 0, 1);



// Gestion des actions de masse

require_once DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';



print '<div class="div-table-responsive">';

	print '<table class="tagtable liste">'."\n";



	print '<tr class="liste_titre_filter">';





	print '<td class="liste_titre maxwidthsearch">';

    print $form->selectarray('search_donateur', $formrecufiscal->getDonateurArray(), $search_donateur, 1, 0, 0, 'style="min-width:200px;"', 0, 0, 0, '', '', 1);

	print '</td>';

	

	print '<td class="liste_titre maxwidthsearch">';

	print '<input type="text" name="search_societe" value="'.$search_societe.'"/>';

	print '</td>';

	

	print '<td class="liste_titre center maxwidthsearch">';

	print '</td>';

	

	print '<td class="liste_titre center maxwidthsearch">';

	// print $form->selectarray('search_inventoriable', $array, $search_inventoriable, 1);

	print '</td>';

	

	print '<td class="liste_titre center maxwidthsearch">';

	// print $form->selectarray('search_amortissable', $array, $search_amortissable, 1);

	print '</td>';

	

	print '<td class="liste_titre center maxwidthsearch">';

	$searchpicto = $form->showFilterButtons();

	print $searchpicto;

	print '</td>';

	

	print '</tr>';





print '<tr class="liste_titre">';



print_liste_field_titre($arrayfields['d.prenom']['label'], $_SERVER["PHP_SELF"], "d.prenom", "", $param, "", $sortfield, $sortorder);

print_liste_field_titre($arrayfields['d.societe']['label'], $_SERVER["PHP_SELF"], "d.societe", "", $param, "", $sortfield, $sortorder);

print_liste_field_titre($arrayfields['d.address']['label'], $_SERVER["PHP_SELF"], "d.address", "", $param, "align='center'", $sortfield, $sortorder);

print '<td class="liste_titre center">Téléphone</td>';

print_liste_field_titre($arrayfields['d.email']['label'], $_SERVER["PHP_SELF"], "d.email", "", $param, "align='center'", $sortfield, $sortorder);



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

                $donateur->fetch($obj->rowid);



				if ($search_donateur != -1 && $search_donateur && $donateur->id != $search_donateur) {

					$i++;

					continue;

				}	





				print '<tr class="oddeven">';



				print '<td class="tdoverflowmax200">';

				print $donateur->getNomUrl();

				print "</td>\n";



				print '<td class="nowrap">';

				if (empty($donateur->societe)) print '-';

				else print $donateur->societe;

				print "</td>\n";





                $coords = $donateur->getFullAddress(1, ', ', $conf->global->MAIN_SHOW_REGION_IN_STATE_SELECT);

				$namecoords = $donateur->ref.'<br>'.$coords;

                if (!empty($conf->use_javascript_ajax))

                {

                    // Add picto with tooltip on map

                    // hideonsmatphone because copyToClipboard call jquery dialog that does not work with jmobile

                    $out = '<a href="#" class="hideonsmartphone" onclick="return copyToClipboard(\''.dol_escape_js($namecoords).'\',\''.dol_escape_js($langs->trans("HelpCopyToClipboard")).'\');">';

                    $out .= img_picto($langs->trans("Address"), 'map-marker-alt');

                    $out .= '</a> ';

                }

                $out .= dol_print_address($coords, 'address_'.$htmlkey.'_'.$donateur->id, $donateur->element, $donateur->id, 1, ', '); $outdone++;

				print '<td class="tdoverflowmax200">';

                print $out;

				print "</td>\n";



				print '<td class="tdoverflowmax200 center">';

				if (empty($donateur->phone)) print '-';

                else print dol_print_phone($donateur->phone, 'FR', 0, 0, 'AC_TEL', "&nbsp;", 'phone');

				print "</td>\n";



				print '<td class="tdoverflowmax200 center">';

				if (empty($donateur->email)) print '-';

                else print dol_print_email($donateur->email, 0, 0, 'AGENDA_ADDACTIONFOREMAIL', 64, 1, 'object_email');

				print "</td>\n";



				// Action

        		print '<td class="nowrap center">';

        		if ($usercandelete) {

        			$selected = 0;

        			if (in_array($donateur->id, $arrayofselected)) $selected = 1;

        			print '<input id="cb'.$donateur->id.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$donateur->id.'"'.($selected ? ' checked="checked"' : '').'>';

        		}

        		print '</td>';





				print '</tr>';

				

				$total++;

				$i++;

			}

			if (!$total) print '<tr class="oddeven"><td colspan="9" class="opacitymedium">Pas de donateur correspondant.</td></tr>';

		}

		else

		{



			print '<tr class="oddeven"><td colspan="9" class="opacitymedium">Pas de donateur correspondant.</td></tr>';

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