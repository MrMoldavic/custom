<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 *  \file       eleve_absence.php
 *  \ingroup    viescolaire
 */

 ini_set('display_errors', '1');
 ini_set('display_startup_errors', '1');
 error_reporting(E_ALL);

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification

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

dol_include_once('/viescolaire/class/eleve.class.php');
dol_include_once('/scolarite/class/creneau.class.php');
dol_include_once('/scolarite/class/etablissement.class.php');
dol_include_once('/viescolaire/class/appel.class.php');
dol_include_once('/viescolaire/lib/viescolaire_eleve.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("viescolaire@viescolaire", "companies"));

// Get parameters
$id = GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize technical objects
$object = new Eleve($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->viescolaire->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('elevenote', 'globalcard')); // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals
if ($id > 0 || !empty($ref)) {
	$upload_dir = $conf->viescolaire->multidir_output[!empty($object->entity) ? $object->entity : $conf->entity]."/".$object->id;
}


// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->viescolaire->eleve->read;
	$permissiontoadd = $user->rights->viescolaire->eleve->write;
	$permissionnote = $user->rights->viescolaire->eleve->write; // Used by the include of actions_setnotes.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1;
	$permissionnote = 1;
}

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->viescolaire->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}
if (empty($reshook)) {
	include DOL_DOCUMENT_ROOT.'/core/actions_setnotes.inc.php'; // Must be include, not include_once
}

if ($action == 'deleteAbsence') {

	$appelClass = new Appel($db);
	$appelClass->fetch(GETPOST('idAppel', 'int'));
	$result = $appelClass->delete($user);

	if($result > 0) setEventMessage('Absence supprimée avec succès !');
	else setEventMessage("Une erreur est survenue : $appelClass->error",'errors');
}


/*
 * View
 */

$form = new Form($db);

//$help_url='EN:Customers_Orders|FR:Commandes_Clients|ES:Pedidos de clientes';
$help_url = '';
llxHeader('', $langs->trans('Eleve'), $help_url);

if ($id > 0 || !empty($ref)) {
	$object->fetch_thirdparty();

	$head = elevePrepareHead($object);

	// Ligne qui affiche les entêtes en haut de la page (Fiche, Notes, Fichiers joints, Evenements)
	print dol_get_fiche_head($head, 'Absence', $langs->trans("Eleve"), -1, $object->picto);

	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/viescolaire/eleve_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	$morehtmlref .= $object->prenom;
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'nom', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';

	print '<h3>Absences de l\'élève:</h3>';


	$materiels = array();

	$sql = "SELECT COUNT(DISTINCT a.rowid) as total, status";
	$sql .= " FROM ".MAIN_DB_PREFIX."appel as a";
	$sql .= " WHERE a.treated = 1";
	$sql .= ' AND a.fk_eleve = '.$object->id;
	$sql .= " GROUP BY a.status";
	$result = $db->query($sql);

	while ($objp = $db->fetch_object($result))
	{
		switch ($objp->status) {
			case 'retard':
				$status = 'Retard';
				break;
			case 'absenceJ':
				$status = 'Absence Justifiée';
				break;
			case 'absenceI':
				$status = 'Absence Injustifiée';
				break;
			case 'present':
				$status = 'Présent';
				break;
		}
		$materiels[$status] = $objp->total;
	}

	if ($conf->use_javascript_ajax)
	{
		print '<div class="div-table-responsive-no-min">';
		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre"><th>Bilan global des absences de '.$object->prenom.'</th></tr>';
		print '<tr><td class="center nopaddingleftimp nopaddingrightimp">';

		$total = 0;
		$dataval = array();
		$datalabels = array();
		$dataseries = array();
		$i = 0;

		foreach ($materiels as $type=>$appel_count)
		{
			$total+=$appel_count;
			$dataseries[] = array($type, $appel_count);
		}
		include_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';
		$dolgraph = new DolGraph();
		$dolgraph->SetData($dataseries);
		$dolgraph->setShowLegend(2);
		$dolgraph->setShowPercent(1);
		$dolgraph->SetType(array('pie'));
		$dolgraph->setHeight('200');
		$dolgraph->draw('idgraphstatus');
		print $dolgraph->show($total ? 0 : 1);

		print '</td></tr>';
		print '</table>';
		print '</div>';
	}

	

	$anneScolaire = "SELECT annee,annee_actuelle,rowid FROM ".MAIN_DB_PREFIX."c_annee_scolaire WHERE active = 1 AND annee_actuelle = 1 ORDER BY rowid DESC";
	$resqlAnneeScolaire = $db->query($anneScolaire);
	$objAnneScolaire = $db->fetch_object($resqlAnneeScolaire);

	$abscence = "SELECT fk_etablissement,fk_creneau,date_creation,justification,status,rowid FROM ".MAIN_DB_PREFIX."appel as a WHERE a.fk_eleve = ".$object->id." AND a.treated= 1 ORDER BY a.date_creation DESC";
	$resql = $db->query($abscence);
	$num = $db->num_rows($resql);

	print '<table class="border centpercent tableforfield">';
	print '<tbody>';
	print '<tr>';
	print '<td>Etablissement</td>';
	print '<td>Creneau</td>';
	print '<td>Professeur</td>';
	print '<td>Justification</td>';
	print '<td>Date de l\'absence</td>';
	print '<td>Statut</td>';
	print '<td>Action</td>';
	print '</tr>';
	foreach($resql as $value)
	{
		$etablissementClass = new Etablissement($db);
		$etablissementClass->fetch($value['fk_etablissement']);

		$creneauClass = new Creneau($db);
		$creneauClass->fetch($value['fk_creneau']);


		$time = strtotime($value['date_creation']);


		$sqlProf1 = "SELECT prenom,nom,rowid FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid= ".$creneauClass->fk_prof_1;
		if($creneauClass->fk_prof_1 != null) $resqlProf1 = $db->query($sqlProf1);

		$sqlProf2 = "SELECT prenom,nom,rowid FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid= ".$creneauClass->fk_prof_2;
		if($creneauClass->fk_prof_2 != null) $resqlProf2 = $db->query($sqlProf2);

		$sqlProf3 = "SELECT prenom,nom,rowid FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid= ".$creneauClass->fk_prof_3;
		if($creneauClass->fk_prof_3 != null) $resqlProf3 = $db->query($sqlProf3);

		print '<tr '.($objAnneScolaire->rowid != $creneauClass->fk_annee_scolaire ? 'style="background-color: #BBBBBB"' : '').'">';
		print '<td>'.$etablissementClass->diminutif.'</td>';
		print '<td>'.($creneauClass->nom_creneau != "" ? '<a href="'.DOL_URL_ROOT.'/custom/scolarite/creneau_card.php?id='.$creneauClass->rowid.' " target="_blank">'.$creneauClass->nom_creneau.'</a>' : '<span class="badge  badge-status8 badge-status" style="color:white;">Erreur créneau</span>').'</td>';
		print '<td>';

		if($resqlProf1)
		{
			$profObject1 = $db->fetch_object($resqlProf1);
			print '<a href="' . DOL_URL_ROOT . '/custom/management/agent_card.php?id=' .  $profObject1->rowid . '" target="_blank">' .$profObject1->prenom.' '.$profObject1->nom. '</a><br>';
		}
		if($resqlProf2)
		{
			$profObject2 = $db->fetch_object($resqlProf2);
			print '<a href="' . DOL_URL_ROOT . '/custom/management/agent_card.php?id=' .  $profObject2->rowid . '" target="_blank">' .$profObject2->prenom.' '.$profObject2->nom. '</a><br>';

		}
		if($resqlProf3)
		{
			$profObject3 = $db->fetch_object($resqlProf3);
			print '<a href="' . DOL_URL_ROOT . '/custom/management/agent_card.php?id=' .  $profObject3->rowid . '" target="_blank">' .$profObject3->prenom.' '.$profObject3->nom. '</a><br>';
		}

		print '</td>';
		if($value['justification'] == null) print '<td>Aucune</td>';
		else print "<td style='overflow-wrap: normal; max-width: 30em'>{$value['justification']}</td>";

		print '<td><span class="badge  badge-status'.($objAnneScolaire->rowid != $creneauClass->fk_annee_scolaire ? '9' : '4').' badge-status" style="color:white;">'.date('d/m/Y', $time).($objAnneScolaire->rowid != $creneauClass->fk_annee_scolaire ? ' / Année précédente' : ' / Année actuelle').'</span></td>';
		print '<td>'.'<span class="badge  badge-status'.($value['status'] == 'retard' ? '1' : ($value['status'] == 'absenceJ' ? '7' : ($value['status'] == 'present' ? '4' : '8'))).' badge-status" style="color:white;">'.$value['status'].'</span>'.'</td>';
		print '<td style="padding:1em; "><a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&idAppel='.$value['rowid'].'&action=deleteAbsence">'.'❌'.'</a></td>';

		print '</tr>';
	}
	print '</tbody>';
	print '</table>';


	$cssclass = "titlefield";

	print '</div>';
	print dol_get_fiche_end();
}

// End of page
llxFooter();
$db->close();
