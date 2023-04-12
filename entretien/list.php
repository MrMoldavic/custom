<?php





// Load Dolibarr environment

@include "../../main.inc.php";



require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/entretien.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formentretien.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';



// Load translation files required by the page

$langs->loadLangs(array("materiel@materiel"));



$viewmode = GETPOST("viewmode", 'alpha') ? GETPOST("viewmode", 'alpha') : 'active';



$action = GETPOST('action', 'alpha');

$massaction = GETPOST('massaction', 'alpha');

$toselect = GETPOST('toselect', 'array');



$confirm = GETPOST('confirm', 'alpha');

$sortfield = GETPOST("sortfield", 'alpha');

$sortorder = GETPOST("sortorder", 'alpha');



$search_ref = GETPOST("search_ref", 'alpha');

$search_materiel = GETPOST("idmateriel", 'int');

$search_materiel_replacement = GETPOST("idmaterielreplacement", 'int');

$search_description = GETPOST("search_description", 'alpha');

$search_deadline = (GETPOST("search_deadline", 'none') ? date('Y-m-d', strtotime(str_replace('/', '-', GETPOST("search_deadline", 'none')))) : '');



if (!$sortfield) $sortfield = "e.rowid";

if (!$sortorder) $sortorder = "ASC";



if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers

{

	$search_ref = '';

	$search_materiel = '';

	$search_materiel_replacement = '';

	$search_description = '';

	$search_deadline = '';

}



$usercanread = ($user->rights->entretien->read);

$usercancreate = ($user->rights->entretien->create);

$usercandelete = ($user->rights->entretien->delete);





$form = new Form($db);

$formmateriel = new FormMateriel($db);

$entretien = new Entretien($db);

$formentretien = new FormEntretien($db);



/*

 *  Traitement des données et vérifications de sécurité

 */

if (!empty($user->socid)) $socid = $user->socid;


if ($cancel) $action = '';



if (!$usercanread) accessforbidden();



$max = 5;

$now = dol_now();





/*

 * Actions

 */



$sql = "SELECT e.rowid";

$sql.= " FROM ".MAIN_DB_PREFIX."entretien as e";

$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."exploitation_replacement as er ON er.fk_entretien = e.rowid";

$sql.= ($viewmode == 'active' ? " WHERE e.active=1" :  " WHERE e.active=0");



if ($search_ref) {

	$sql .= natural_search('e.rowid', $search_ref);

}

if ($search_deadline) {

	$sql .= natural_search('e.deadline_timestamp', $search_deadline);

}

if ($search_description) {

	$sql .= natural_search('e.description', $search_description);

}

if ($search_materiel && $search_materiel != '-1') {

    $sql .= natural_search('e.fk_materiel', $search_materiel);

}

if ($search_materiel_replacement && $search_materiel_replacement != '-1') {

    $sql .= natural_search('er.fk_replacement_materiel', $search_materiel_replacement);

}



$sql .= $db->order($sortfield, $sortorder);

$resql = $db->query($sql);





/*

 * View

 */

llxHeader("", $langs->trans("Entretien - liste"));



print '<div class="fichecenter">';



print '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="formulaire">';

print '<input type="hidden" name="token" value="'.newToken().'">';

print '<input type="hidden" name="action" value="list">';

print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';

print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

print '<input type="hidden" name="viewmode" value="'.$viewmode.'">';



    $picto = 'entretien';



	$morehtmlright = dolGetButtonTitle('En cours', '', 'fa fa-hourglass-half paddingleft imgforviewmode', $_SERVER["PHP_SELF"].'?viewmode=active', '', 1, array('morecss'=>'reposition'.($viewmode == 'active' ? ' btnTitleSelected' : '')));

	$morehtmlright .= dolGetButtonTitle('Terminés', '', 'fa fa-hourglass-end paddingleft imgforviewmode', $_SERVER["PHP_SELF"].'?viewmode=ended', '', 1, array('morecss'=>'reposition'.($viewmode == 'ended' ? ' btnTitleSelected' : '')));

	

    if ($usercancreate) $morehtmlright .= dolGetButtonTitle('Nouvel entretien', '', 'fa fa-plus-circle', DOL_URL_ROOT.'/custom/entretien/card.php?action=create', '', 1);

	talm_print_barre_liste('Entretiens', 0, $_SERVER["PHP_SELF"], '', '', '','', $num, $nbtotalofrecords, $picto, 0, $morehtmlright, '', $limit, 0, 0, 1);

	include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';



print '<div class="div-table-responsive">';

	print '<table class="tagtable liste">'."\n";



	// Lines with input filters

	print '<tr class="liste_titre_filter">';



	print '<td class="liste_titre left">';

		print '<input class="flat" type="text" name="search_ref" size="8" value="'.dol_escape_htmltag($search_ref).'">';

	print '</td>';



	print '<td class="liste_titre left">';

		print $formmateriel->selectMateriels($search_materiel, 'idmateriel', '', 1);

	print '</td>';



	if ($viewmode == 'active') {

		print '<td class="liste_titre left">';

		print $formmateriel->selectMateriels($search_materiel_replacement, 'idmaterielreplacement', '', 1);

		print '</td>';

	}



	print '<td class="liste_titre left">';

		print '<input class="flat" type="text" name="search_description" size="8" value="'.dol_escape_htmltag($search_description).'">';

	print '</td>';



	print '<td class="liste_titre left">';

	print $form->selectDate(($search_deadline ? $search_deadline : -1), 'search_deadline', '', '', '', "add", 1);

	print '</td>';



	print '<td class="liste_titre center">';

	print '</td>';



	// Display the deactivation date in ended viewmode

	if ($viewmode == 'ended') {

		// Separation div

		print '<td class="liste_titre center">';

		print '</td>';

	}



	print '<td class="liste_titre center maxwidthsearch">';

	print $form->showFilterButtons();

	print '</td>';



	print '</tr>';





print '<tr class="liste_titre">';

$param = '&viewmode='.$viewmode;

print_liste_field_titre('Réf.', $_SERVER["PHP_SELF"], "e.rowid", "", $param, "", $sortfield, $sortorder);

print_liste_field_titre('Matériel', $_SERVER["PHP_SELF"], "e.fk_materiel", "", $param, "", $sortfield, $sortorder);

if ($viewmode == 'active') {

	print_liste_field_titre('Remplacement', $_SERVER["PHP_SELF"], "e.fk_materiel", "", $param, "", $sortfield, $sortorder);

}

print_liste_field_titre('Description', $_SERVER["PHP_SELF"], "e.description", "", $param, "", $sortfield, $sortorder);

print_liste_field_titre('Deadline', $_SERVER["PHP_SELF"], "e.deadline_timestamp", "", $param, "", $sortfield, $sortorder);



// Display the deactivation date in ended viewmode

if ($viewmode == 'ended') {

	print '<td class="liste_titre">Date de clôture</td>';

}

print '<td class="liste_titre center">État</td>';



print '<th></th>';





// Draft MyObject

if ($conf->entretien->enabled == 1)

{

	if ($resql)

	{

		$total = 0;

		$num = $db->num_rows($resql);

		if ($num > 0)

		{

			$i = 0;

			while ($i < $num)

			{

				$obj = $db->fetch_object($resql);

                $entretien->fetch($obj->rowid);



				print '<tr class="oddeven">';



    			print '<td class="tdoverflowmax200">';

    			print $entretien->getNomUrl();

    			print "</td>\n";



				print '<td class="nowrap">'.$entretien->materiel_object->getNomUrl().'</td>';



				if ($viewmode == 'active') {

					print '<td class="nowrap">';

					print ($entretien->replacement_materiel_id ? $entretien->replacement_materiel_object->getNomUrl() : '<i>Pas de remplacement</i>');

					print '</td>';

				}



    			print '<td class="tdoverflowmax200">';

    			print $entretien->description;

    			print "</td>\n";



    			print '<td class="tdoverflowmax200">';

    			print $entretien->deadline_timestamp ? dol_print_date($entretien->deadline_timestamp, '%e %B %Y') : '<i>Pas de deadline</i>';

    			print "</td>\n";



				// Display the deactivation date in ended viewmode

				if ($viewmode == 'ended') {

					print '<td class="tdoverflowmax200">';

					print dol_print_date($entretien->suppression_timestamp, '%e %B %Y');

					print "</td>\n";

				}



    			print '<td class="tdoverflowmax200 center">';

    			print '<span class="badge badge-status'.$entretien->etat_array[$entretien->fk_etat]['badge_code'].' classfortooltip badge-status" title="'.$entretien->etat_array[$entretien->fk_etat]['label'].'">'.$entretien->etat_array[$entretien->fk_etat]['label'].'</span>';

    			print "</td>\n";



    			print '<td class="tdoverflowmax200">';

    			print "</td>\n";



				print '</tr>';

				$i++;

			}

		}

		else

		{

			print '<tr class="oddeven"><td colspan="9" class="opacitymedium">Pas d\'entretien correspondant.</td></tr>';

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

