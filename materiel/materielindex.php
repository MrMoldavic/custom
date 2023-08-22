<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       materiel/materielindex.php
 *	\ingroup    materiel
 *	\brief      Home page of materiel top menu
 */

//  ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/entretien.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formkit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));

$action = GETPOST('action', 'alpha');

// Security check
//if (! $user->rights->materiel->myobject->read) accessforbidden();
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0)
{
	$action = '';
	$socid = $user->socid;
}
$usercanread = ($user->rights->materiel->read);
$usercancreate = ($user->rights->materiel->create);
$usercandelete = ($user->rights->materiel->delete);


$max = 5;
$now = dol_now();


/*
 * Actions
 */

// None


/*
 * View
 */
llxHeader("", $langs->trans("Materiel"));

print talm_load_fiche_titre('Espace matériel', '', 'materiel');

print '<div class="fichecenter"><div class="fichethirdleft">';


/*
 * Statistiques sur les types de matériels
 */

$materiels = array();

$sql = "SELECT COUNT(m.rowid) as total, tm.type";
$sql .= " FROM ".MAIN_DB_PREFIX."materiel as m";
$sql.=" INNER JOIN ".MAIN_DB_PREFIX."c_type_materiel as tm ON m.fk_type_materiel=tm.rowid ";
$sql .= " WHERE m.active = 1";
$sql .= " GROUP BY tm.type";
$result = $db->query($sql);
while ($objp = $db->fetch_object($result))
{
	$materiels[$objp->type] = $objp->total;
}

if ($conf->use_javascript_ajax)
{
	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre"><th>'.$langs->trans("Statistics").'</th></tr>';
	print '<tr><td class="center nopaddingleftimp nopaddingrightimp">';

	$total = 0;
	$dataval = array();
	$datalabels = array();
	$dataseries = array();
	$i = 0;

	foreach ($materiels as $type=>$mat_count)
	{
	    $total+=$mat_count;
	    $dataseries[] = array($type, $mat_count);
	}
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


if ($conf->materiel->enabled && $user->rights->materiel->read)
{
	$max = 10;
	$sql = "SELECT m.rowid,m.fk_materiel,m.description, m.creation_timestamp as datem";
	$sql .= " FROM ".MAIN_DB_PREFIX."entretien as m";
    $sql .= " WHERE m.active = 1";
	$sql .= $db->order("m.creation_timestamp", "DESC");
	$sql .= $db->plimit($max, 0);

	//print $sql;
	$result = $db->query($sql);
	if ($result)
	{
		$num = $db->num_rows($result);

		$i = 0;

		if ($num > 0)
		{
			$transRecordedType = 'Dernier entretiens ajoutés/modifiés';

			print '<div class="div-table-responsive-no-min">';
			print '<table class="noborder centpercent">';

			$colnb = 5;

			print '<tr class="liste_titre"><th colspan="3">'.$transRecordedType.'</th>';
			print '<th class="right" colspan="2"><a href="'.DOL_URL_ROOT.'/custom/entretien/list.php">'.$langs->trans("FullList").'</td>';
			print '</tr>';

			foreach($result as $value)
			{
				$objEntretien = new Entretien($db);
				$objEntretien->fetch($value['rowid']);

				
				
                // $materiel_tmp->fetch($objp->fk_materiel);

				print '<tr class="oddeven">';
				print '<td class="nowrap">';
				print $objEntretien->getNomUrl();
				print "</td>\n";
				print '<td>'.$value['description'].'</td>';
				// print '<td>'.($materiel_tmp->modele ? $materiel_tmp->modele : '<i>Pas de modèle</i>').'</td>';
				// print "<td>";
				// print dol_print_date($db->jdate($objp->datem), 'day');
				// print "</td>";
				// print '<td class="right nowrap width25"><span class="badge  badge-status'.$materiel_tmp->etat_badge_code.' badge-status">'.$materiel_tmp->etat.'</span></td>';
				print "</tr>\n";
			}
				

			$db->free($result);

			print "</table>";
			print '</div>';
			print '<br>';
		}
	}
	else
	{
		dol_print_error($db);
	}
}

$NBMAX = 3;
$max = 3;





print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


/*
 * Latest modified products
 */
if ($conf->materiel->enabled && $user->rights->materiel->read)
{
	$max = 15;
	$sql = "SELECT m.rowid, m.tms as datem";
	$sql .= " FROM ".MAIN_DB_PREFIX."materiel as m";
    $sql .= " WHERE m.active = 1";
	$sql .= $db->order("m.tms", "DESC");
	$sql .= $db->plimit($max, 0);

	//print $sql;
	$result = $db->query($sql);
	if ($result)
	{
		$num = $db->num_rows($result);

		$i = 0;

		if ($num > 0)
		{
			$transRecordedType = 'Dernier matériels ajoutés/modifiés';

			print '<div class="div-table-responsive-no-min">';
			print '<table class="noborder centpercent">';

			$colnb = 5;

			print '<tr class="liste_titre"><th colspan="3">'.$transRecordedType.'</th>';
			print '<th class="right" colspan="2"><a href="'.DOL_URL_ROOT.'/custom/materiel/list.php">'.$langs->trans("FullList").'</td>';
			print '</tr>';

			while ($i < $num)
			{
				$objp = $db->fetch_object($result);
				$materiel_tmp = new Materiel($db);
                $materiel_tmp->fetch($objp->rowid);


				print '<tr class="oddeven">';
				print '<td class="nowrap">';
				print $materiel_tmp->getNomUrl();
				print "</td>\n";
				print '<td>'.($materiel_tmp->marque ? $materiel_tmp->marque : '<i>Pas de marque</i>').'</td>';
				print '<td>'.($materiel_tmp->modele ? $materiel_tmp->modele : '<i>Pas de modèle</i>').'</td>';
				print "<td>";
				print dol_print_date($db->jdate($objp->datem), 'day');
				print "</td>";
				print '<td class="right nowrap width25"><span class="badge  badge-status'.$materiel_tmp->etat_badge_code.' badge-status">'.$materiel_tmp->etat.'</span></td>';
				print "</tr>\n";
				$i++;
			}

			$db->free($result);

			print "</table>";
			print '</div>';
			print '<br>';
		}
	}
	else
	{
		dol_print_error($db);
	}
}

$NBMAX = 3;
$max = 3;

print '</div></div></div>';



dol_fiche_end();


// End of page
llxFooter();
$db->close();
