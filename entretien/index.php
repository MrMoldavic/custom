<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// Load Dolibarr environment

@include "../../main.inc.php";





// require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';



require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/entretien.class.php';

// require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/exploitation.class.php';

// require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';

// require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';

// require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formkit.class.php';

// require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';



// Load translation files required by the page

$langs->loadLangs(array("materiel@materiel"));





// Security check

$socid = GETPOST('socid', 'int');

if (isset($user->socid) && $user->socid > 0) $socid = $user->socid;



$usercanread = ($user->rights->exploitation->read);

$usercancreate = ($user->rights->exploitation->create);

$usercandelete = ($user->rights->exploitation->delete);



if (!$usercanread) accessforbidden();







/*

 * View

 */

llxHeader("", $langs->trans("Entretien"));



print talm_load_fiche_titre('Espace entretien', '', 'entretien');



print '<div class="fichecenter"><div class="fichethirdleft">';





/*

 * Statistiques sur les entretiens

 */



$entretiens = array();



// Fetch number of finished entretien

$sql = "SELECT COUNT(e.rowid) as total_finished";

$sql .= " FROM ".MAIN_DB_PREFIX."entretien as e";

$sql .= " WHERE e.active = 0";

$result = $db->query($sql);

$result_obj = $db->fetch_object($result);

$total_finished = $result_obj->total_finished;



// Fetch number of ongoing entretien

$sql = "SELECT COUNT(e.rowid) as total_ongoing";

$sql .= " FROM ".MAIN_DB_PREFIX."entretien as e";

$sql .= " WHERE e.active = 1 AND (deadline_timestamp > CURRENT_TIMESTAMP OR deadline_timestamp IS NULL)";

$result = $db->query($sql);

$result_obj = $db->fetch_object($result);

$total_ongoing = $result_obj->total_ongoing;



// Fetch number of delayed entretien (deadline < current time) 

$sql = "SELECT COUNT(e.rowid) as total_finished";

$sql .= " FROM ".MAIN_DB_PREFIX."entretien as e";

$sql .= " WHERE e.active = 1 AND deadline_timestamp < CURRENT_TIMESTAMP";

$result = $db->query($sql);

$result_obj = $db->fetch_object($result);

$total_delayed = $result_obj->total_finished;



$total = $total_delayed + $total_finished + $total_ongoing;



if ($conf->use_javascript_ajax)

{

	print '<div class="div-table-responsive-no-min">';

	print '<table class="noborder centpercent">';

    print '<tr class="liste_titre"><th>Statistiques - Entretiens</th></tr>';

	print '<tr><td class="center nopaddingleftimp nopaddingrightimp">';



	$dataval = array();

	$datalabels = array();

	$dataseries = array();

	$i = 0;



	$dataseries[] = array('En cours', $total_ongoing);

	$dataseries[] = array('Retardés', $total_delayed);

	$dataseries[] = array('Clôturés', $total_finished);



	include_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';

	$dolgraph = new DolGraph();

	$dolgraph->SetData($dataseries);

	$dolgraph->setShowLegend(2);

	$dolgraph->setShowPercent(0);

	$dolgraph->SetType(array('pie'));

	$dolgraph->setHeight('200');

	$dolgraph->draw('idgraphstatus');

	print $dolgraph->show($total ? 0 : 1);



	print '</td></tr>';

	print '</table>';

	print '</div>';

}









print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';





/*

 * Entretiens récent

 */

if ($conf->entretien->enabled)

{

	$max = 15;

	$sql = "SELECT e.rowid, e.creation_timestamp as datem";

	$sql .= " FROM ".MAIN_DB_PREFIX."entretien as e";

    $sql .= " WHERE e.active = 1";

	$sql .= $db->order("e.creation_timestamp", "DESC");

	$sql .= $db->plimit($max, 0);



	$result = $db->query($sql);

	if ($result)

	{

		$num = $db->num_rows($result);



		$i = 0;

        $colnb = 5;



        print '<div class="div-table-responsive-no-min">';

        print '<table class="noborder centpercent">';



        $transRecordedType = 'Entretiens récents';

        print '<tr class="liste_titre"><th colspan="2">'.$transRecordedType.'</th>';

        print '<th class="right" colspan="1"><a href="'.DOL_URL_ROOT.'/custom/entretien/list.php">'.$langs->trans("FullList").'</td>';

        print '</tr>';



		if ($num > 0)

		{



			while ($i < $num)

			{

				$objp = $db->fetch_object($result);

				$entretien_tmp = new Entretien($db);

                $entretien_tmp->fetch($objp->rowid);





				print '<tr class="oddeven">';

				print '<td class="nowrap">';

				print $entretien_tmp->getNomUrl();

				print "</td>\n";

				print '<td class="nowrap">';

				print $entretien_tmp->description;

				print "</td>\n";

				print "<td class='right'>";

				print dol_print_date($db->jdate($objp->datem), 'day');

				print "</td>";

				print "</tr>\n";

				$i++;

			}



			$db->free($result);

		} else {

            print '<tr class="oddeven">';

            print '<td class="nowrap" colspan="3">';

            print '<i>Pas d\'entretien récent</i>';

            print "</td>\n";

            print "</tr>\n";

        }



        print "</table>";

        print '</div>';

        print '<br>';

	}

	else

	{

		dol_print_error($db);

	}

}

print '</div></div></div>';



dol_fiche_end();



// End of page

llxFooter();

$db->close();