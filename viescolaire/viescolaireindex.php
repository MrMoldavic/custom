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
 *	\file       viescolaire/viescolaireindex.php
 *	\ingroup    viescolaire
 *	\brief      Home page of viescolaire top menu
 */

/*ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/viescolaire/class/eleve.class.php';


// Load translation files required by the page
$langs->loadLangs(array("viescolaire@viescolaire"));

$action = GETPOST('action', 'aZ09');


// Security check
// if (! $user->rights->viescolaire->myobject->read) {
// 	accessforbidden();
// }
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$eleve = new Eleve($db);

llxHeader("", "Module Vie scolaire");

print load_fiche_titre("Module Vie scolaire", '', 'viescolaire.png@viescolaire');

print '<div class="fichecenter"><div style="width:40%" class="fichethirdleft">';
// Compte des élèves de chaques antennes

// camenbaire
if ($conf->use_javascript_ajax)
{
	$totalEleves = 0;
	$dataseries = array();
	$sql = "SELECT rowid,nom FROM ".MAIN_DB_PREFIX."etablissement";
	$resql = $db->query($sql);

	foreach($resql as $value){
		$sql = "SELECT COUNT(*) as total FROM ".MAIN_DB_PREFIX."eleve WHERE fk_etablissement = ". $value['rowid']." AND status !=".$eleve::STATUS_CANCELED;
		$resql = $db->query($sql);
		$obj = $db->fetch_object($resql);

		$totalEleves .= $obj->total;
		array_push($dataseries, [$value['nom'],$obj->total]);

	}

	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre"><th>Statistiques nombre d\'élèves</th></tr>';
	print '<tr><td class="center nopaddingleftimp nopaddingrightimp">';


	$dataval = array();
	$datalabels = array();
	$i = 0;

	include_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';
	$dolgraph = new DolGraph();
	$dolgraph->SetData($dataseries);
	$dolgraph->setShowLegend(2);
	$dolgraph->setShowPercent(1);
	$dolgraph->SetType(array('pie'));
	$dolgraph->setHeight('200');
	$dolgraph->draw('idgraphstatus');
	print $dolgraph->show($totalEleves ? 0 : 1);

	print '</td></tr>';
	print '</table>';
	print '</div>';
}


print '</div><div style="width:55%" class="fichetwothirdright"><div class="ficheaddleft">';


$date = date('Y-m-d');
$absence = "SELECT DISTINCT rowid, justification,fk_eleve,fk_creneau FROM ".MAIN_DB_PREFIX."appel WHERE date_creation > '".$date."' AND treated=1 AND fk_eleve != '' AND status !='present' ORDER BY date_creation ASC";
$resqlAbsenceDuJour = $db->query($absence);

if($resqlAbsenceDuJour->num_rows > 0){
	print load_fiche_titre("Absences connues aujourd'hui <span class='badge badge-status4 badge-status'>{$resqlAbsenceDuJour->num_rows}</span>", '', 'fa-warning');
		print '<table class="tagtable liste">';
		print '<tbody>';
		print '<tr class="liste_titre">
			<th class="wrapcolumntitle liste_titre">Élève</th>
			<th class="wrapcolumntitle liste_titre">Justification</th>
			<th class="wrapcolumntitle liste_titre">Créneau</th>
			</tr>';
		foreach ($resqlAbsenceDuJour as $value)
		{
			$eleve = "SELECT e.nom,e.prenom,e.rowid FROM ".MAIN_DB_PREFIX."eleve as e WHERE e.rowid={$value['fk_eleve']}";
			$resqlEleve = $db->query($eleve);
			$objectNiveau = $db->fetch_object($resqlEleve);

			$creneau = "SELECT c.rowid,c.nom_creneau FROM ".MAIN_DB_PREFIX."creneau as c WHERE c.rowid={$value['fk_creneau']}";
			$resqlCreneau = $db->query($creneau);
			$objectCreneau = $db->fetch_object($resqlCreneau);

			print "<tr class='oddeven'>";
			print "<td style='width:20%'>{$objectNiveau->prenom} {$objectNiveau->nom}</td>";
			print "<td style='width:20%'>{$value['justification']}</td>";
			print "<td style='width:45%'><a href=".DOL_URL_ROOT."/custom/scolarite/creneau_card.php?id={$objectCreneau->rowid}>{$objectCreneau->nom_creneau}</a></td>";
			print '</tr>';
		}
		print '</tbody>';
		print '</table>';
}
else
{
	print load_fiche_titre("Absences connues aujourd'hui <span class='badge badge-status4 badge-status'>0</span>", '', 'fa-warning');
	print "Aucune absence connue pour aujourd'hui!";
}







print '</div></div>';


print load_fiche_titre("Cours de l'agent", '', 'fa-user');

print "<pre>( Vous avez ici accès aux mêmes informations que la scolarité, pour ce qui est des absences. <br> Si vous constatez un cours vide, rapprochez-vous de la scolarité pour plus d'informations. )</pre>";
$Jour = "SELECT jour, rowid FROM ".MAIN_DB_PREFIX."c_jour WHERE active=1";
$resqlJour = $db->query($Jour);

foreach($resqlJour as $value)
{
	$cours = "SELECT professeurs, nom_creneau, rowid FROM ".MAIN_DB_PREFIX."creneau WHERE status=4 AND jour=".$value['rowid']." AND (fk_prof_1={$user->id} OR fk_prof_2={$user->id} OR fk_prof_3={$user->id}) ORDER BY heure_debut ASC";
	$resqlCours = $db->query($cours);

	print '<h3>'.$value['jour'].($resqlCours->num_rows > 0 ? ('    <span class="badge  badge-status4 badge-status">  '.$resqlCours->num_rows.' Cours</span>') : ('      <span class="badge  badge-status8 badge-status">  Aucun cours à ce jour</span>')).'</h3>';

	print '<table class="tagtable liste">';
	print '<tbody>';


	if($resqlCours->num_rows != 0)
	{
		print '<tr class="liste_titre">
			<th class="wrapcolumntitle liste_titre">Créneau</th>
			<th class="wrapcolumntitle liste_titre">Professeurs</th>
			<th class="wrapcolumntitle liste_titre">Élèves</th>
			<th class="wrapcolumntitle liste_titre">Absences à venir</th>
			</tr>';
		foreach($resqlCours as $val)
		{
			print '<tr class="oddeven">';
			print '<td style="width:30%"><a href="' . DOL_URL_ROOT . '/custom/scolarite/creneau_card.php?id=' . $val['rowid'] . '">'.$val['nom_creneau'].'</a></td>';
			print '<td>'.$val['professeurs'].'</td>';

			$affectation = "SELECT s.fk_souhait FROM ".MAIN_DB_PREFIX."affectation as s WHERE s.fk_creneau=".$val['rowid']." AND s.date_fin IS NULL";
			$resqlAffectation = $db->query($affectation);

			print '<td>';
			foreach($resqlAffectation as $v)
			{
				$eleve = "SELECT e.nom,e.prenom,e.rowid FROM ".MAIN_DB_PREFIX."eleve as e WHERE e.rowid=".("(SELECT s.fk_eleve FROM ".MAIN_DB_PREFIX."souhait as s WHERE s.rowid =".$v['fk_souhait'].")");
				$resqlEleve = $db->query($eleve);
				foreach($resqlEleve as $res)
				{
					$niveau = "SELECT n.niveau FROM ".MAIN_DB_PREFIX."c_niveaux as n INNER JOIN ".MAIN_DB_PREFIX."souhait as s WHERE s.rowid=".$v['fk_souhait']." AND s.fk_niveau = n.rowid";
					$resqlNiveau = $db->query($niveau);
					$objectNiveau = $db->fetch_object($resqlNiveau);


					print '<a href="' . DOL_URL_ROOT . '/custom/viescolaire/eleve_card.php?id=' . $res['rowid'] . '">' .'- '. $res['prenom'].' '.$res['nom'] . ' / '.$objectNiveau->niveau.'</a>';
					print '<br>';
				}
			}
			print '</td>';
			print '<td>';


			$count = 0;
			foreach($resqlAffectation as $v)
			{
				$eleve = "SELECT e.nom,e.prenom,e.rowid FROM ".MAIN_DB_PREFIX."eleve as e WHERE e.rowid=".("(SELECT s.fk_eleve FROM ".MAIN_DB_PREFIX."souhait as s WHERE s.rowid =".$v['fk_souhait'].")");
				$resqlEleve = $db->query($eleve);

				foreach($resqlEleve as $res)
				{
					$date = date('Y-m-d H:i:s');
					$absence = "SELECT rowid, date_creation, justification  FROM ".MAIN_DB_PREFIX."appel WHERE fk_creneau=".$val['rowid']." AND fk_eleve=".$res['rowid']." AND date_creation >='".$date."'";
					$resqlAbsence = $db->query($absence);

					foreach($resqlAbsence as $r)
					{
						$dateDuJour = date('d/m/Y');
						$count++;
						print '- '.$res['prenom'].' '.$res['nom'].' sera absente le: '.date('d/m/Y', strtotime($r['date_creation'])).(date('d/m/Y', strtotime($r['date_creation'])) == $dateDuJour ? ' <span class="badge  badge-status4 badge-status">Aujourd\'hui</span>' : '').'<br>Justification : '.$r['justification'].'<br><hr>';
					}
				}
			}

			 if($count == 0) print 'Aucune absences futurs connues à ce jour pour ces élèves.';
			print '</td>';

			print '</tr>';
		}
	}
	else
		print '<p></p>';

	print '</tbody>';
	print '</table>';
}










/*print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';

print '<p>Nombre d\'élèves Inscrits au Collège Clemenceau : '.$objCZ->total.'</p>';
print '<hr>';
print '<p>Nombre d\'élèves Inscrits au Collège Jean de Verazzane : '.$nombreEleveVm.'</p>';
print '<hr>';
print '<p>Nombre d\'élèves Inscrits à l\'école des Dahlias : '.$nombreEleveDahlias.'</p>';
print '<hr>';
print '<div class="fichecenter"><div class="fichethirdleft">';
print '</div></div></div>';


print '<div class="fichecenter"><div class="ficheaddleft">';*/




// End of page
llxFooter();
$db->close();
