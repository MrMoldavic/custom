<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2023 Baptiste Diodati <baptiste.diodati@gmail.com>
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
 *  \file       evenement_organisation.php
 *  \ingroup    organisation
 *  \brief      Tab for notes on Evenement
 */


/* ini_set('display_errors', '1');
 ini_set('display_startup_errors', '1');
 error_reporting(E_ALL);*/

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

dol_include_once('/organisation/class/evenement.class.php');
dol_include_once('/management/class/agent.class.php');
dol_include_once('/viescolaire/class/eleve.class.php');
dol_include_once('/organisation/class/engagement.class.php');
dol_include_once('/organisation/class/morceau.class.php');
dol_include_once('/organisation/class/artiste.class.php');
dol_include_once('/organisation/class/interpretation.class.php');
dol_include_once('/organisation/class/proposition.class.php');
dol_include_once('/organisation/class/programmation.class.php');
dol_include_once('/organisation/class/groupe.class.php');
dol_include_once('/organisation/class/liaisoninstrument.class.php');
dol_include_once('/organisation/class/instrument.class.php');
dol_include_once('/organisation/lib/organisation_evenement.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("organisation@organisation", "companies"));

// Get parameters
$id = GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$selectedProposition = GETPOST('fk_proposition', 'alpha');
$selectedInterpretation = GETPOST('fk_interpretation', 'alpha');
$programmationId = GETPOST('fk_programmation','int');

// Initialize technical objects
$object = new Evenement($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->organisation->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('evenementnote', 'globalcard')); // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals
if ($id > 0 || !empty($ref)) {
	$upload_dir = $conf->organisation->multidir_output[!empty($object->entity) ? $object->entity : $conf->entity]."/".$object->id;
}

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->organisation->organisation->read;
	$permissiontoadd = $user->rights->organisation->organisation->write;
	$permissionnote = $user->rights->organisation->organisation->write; // Used by the include of actions_setnotes.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1;
	$permissionnote = 1;
}

if (empty($conf->organisation->enabled)) accessforbidden();
if (!$permissiontoadd) accessforbidden();


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

// include des actions, pour ne pas flooder le fichier
include DOL_DOCUMENT_ROOT.'/custom/organisation/core/actions/actions_evenement_organisation.inc.php';

/*
 * View
 */

$form = new Form($db);

$help_url = '';
$title = $langs->trans('Evenement').' - '.$langs->trans("Notes");
llxHeader('', $title, $help_url);

if ($id > 0 || !empty($ref)) {
	$object->fetch_thirdparty();

	$head = evenementPrepareHead($object);

	print dol_get_fiche_head($head, 'Organisation', $langs->trans("Evenement"), -1, $object->picto);

	$formconfirm = '';
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&fk_proposition='.$selectedProposition, $langs->trans('DeleteProgrammation'), "Êtes-vous sûr de vouloir suppimer ce groupe de la conduite?", 'confirm_delete', '', 0, 1);
	}

	if ($action == 'deleteInterpretation') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&fk_programmation='.$programmationId, 'Suppression d\'un morceau de la conduite', "Êtes-vous sûr de vouloir supprimer ce morceau de la conduite?", 'confirm_deleteProgrammation', '', 0, 1);
	}

	if ($action == 'exportConduite') {
		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('Exporter la conduite'), 'Êtes-vous sûr de vouloir exporter la conduite? ', 'confirm_export_conduite', '', 0, 1);
	}

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) {
		$formconfirm .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;

	// Object card
	$linkback = '<a href="'.dol_buildpath('/organisation/evenement_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refid">';
	$morehtmlref .= '</div>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'nom_evenement', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';

	$propositionClass = new Proposition($db);
	$propositions = $propositionClass->fetchAll('ASC','position',0,0,array('customsql'=>"fk_evenement=$object->id AND (status=".Proposition::STATUS_VALIDATED.' OR status='.Proposition::STATUS_PROGRAMMED.')'));

	print '<table class="tagtable nobottomiftotal liste">';
	print '<tbody>';
	print '<tr>';
	print '<td style="padding:2em">Groupes Proposés</td>';
	print '<td style="padding:2em">Morceaux proposés</td>';
	print '<td style="padding:2em">Position</td>';
	print '<td style="padding:2em">Actions</td>';
	print '</tr>';

	$positions = [];
	$loop = 0;
	foreach($propositions as $value)
	{
		if($loop != 0)
		{
			print '<tr>';
			print '<td colspan="4" style="background-color:grey; color:white">Changement plateau (+5min)</td>';
			print '</tr>';
			$object->tempsTotal += 5;
		}

		// fetch du groupe
		$groupeClass = new Groupe($db);
		$groupeClass->fetch($value->fk_groupe);
		// fetch des programmations à ce concert où se trouve les propositions
		$programmationClass = new Programmation($db);
		$programmations = $programmationClass->fetchAll('ASC','position',0,'',array('fk_proposition'=>$value->id,'fk_evenement'=>$object->id));

		print '<tr>';
		print '<td '.($value->status == Proposition::STATUS_PROGRAMMED ? 'style="background-color: #E9ffd7;"' : '').'><a href="groupe_card.php?id='.$groupeClass->id.'"><span class="badge  badge-status4 badge-status" style="color:white;">'.$groupeClass->nom_groupe.'</span></a><br><br>';

		print $groupeClass->printEngagements();

		print '</td>';
		print '<td style="padding:1em '.($value->status == Proposition::STATUS_PROGRAMMED ? ';background-color: #E9ffd7' : '').'">';
		if($value->status != Proposition::STATUS_CANCELED) {
			if (count($programmations) > 0) {
				print '<table class="table table-striped table-dark" style="background-color: lightgray">';
				print '<tbody>';
				print '<tr>';
				print '<td style="padding:0.8em">Titre et artiste</td>';
				print '<td style="padding:0.8em">Durée</td>';
				print '<td style="padding:0.8em">Position</td>';
				print '<td style="padding:0.8em"></td>';
				print '</tr>';

				foreach ($programmations as $programmation) {
					// Appel de la fonction qui affiche les lines de programmation
					print $object->printProgrammationLines((object)$programmation);
				}

				print '</tbody>';
				print '</table>';
			} else print 'Aucune programmation connue pour ce groupe!<br>';

			print '<a href="/custom/organisation/groupe_interpretation.php?id='.$value->fk_groupe.'&evenementid='.$object->id.'">'.img_picto('rotate','fa-plus').'</a>';
		}
		print '</td>';

		// print du formulaire de changement de position
		print $object->printPositionUpdateForm($id, $value);

		print '<td '.($value->status == Proposition::STATUS_PROGRAMMED ? 'style="background-color: #E9ffd7;"' : '').'>';
		if($value->status != Proposition::STATUS_PROGRAMMED)
		{
			print dolGetButtonAction('Mettre en attente', '', 'delete', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=handleProposition&token=' . newToken().'&propositionId='.$value->id.'&typeAction=desactivateProposition', '', $permissiontoadd).'<br><br>';
			print dolGetButtonAction('Reprogrammer le passage', '', 'delete',  'proposition_card.php?id=' . $value->id . '&action=edit&token=' . newToken().'&backtopage='.$_SERVER['PHP_SELF'].'?id='.$object->id.'&backtopageforcancel='.$_SERVER['PHP_SELF'].'?id='.$object->id.'&reprogrammation=true', '', $permissiontoadd).'<br><br>';
		}

		print dolGetButtonAction(($value->status == Proposition::STATUS_PROGRAMMED ? 'Invalider le passage' : 'Valider le passage'), '', 'delete', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=handle_validation_proposition&token=' . newToken().'&fk_proposition='.$value->id.'&subAction='.($value->status == Proposition::STATUS_PROGRAMMED ? 'deprogramProposition' : 'programProposition'), '', $permissiontoadd);

		print '</td>';
		print '</tr>';
		$loop++;
	}

	print "<h3 style='text-align: center'>Durée du concert: {$object->tempsTotal}min</h3>";
	print '</tbody>';
	print '</table>';
	print '</div>';

	print dolGetButtonAction($langs->trans('Mettre à jour les positions'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=updateAllPositions&token=' . newToken(), '', $permissiontoadd);
	print dolGetButtonAction($langs->trans('Exporter la conduite'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=exportConduite&token=' . newToken(), '', $permissiontoadd);
	print dolGetButtonAction($langs->trans('Programmer un groupe'), '', 'default', 'proposition_card.php?fk_evenement=' . $object->id . '&action=create&token=' . newToken().'&backtopage='.$_SERVER['PHP_SELF'].'?id='.$object->id, '', $permissiontoadd);

	print '<hr>';
	print load_fiche_titre("Liste des propositions en attente", '', 'fa-hourglass-start');

	print '<table class="tagtable nobottomiftotal liste">';
	print '<tbody>';
	print '<tr>';
	print '<td style="padding:2em; width: 20%">Groupes Proposés</td>';
	print '<td style="padding:2em; width: 40%">Morceaux proposés</td>';
	print '<td style="padding:2em; width: 30%">Actions</td>';
	print '</tr>';

	$waitingPropositions = $propositionClass->fetchAll('ASC','position',0,0,array('fk_evenement'=>$object->id,'status'=>Proposition::STATUS_DRAFT));


	$positions = [];
	$loop = 0;
	if(count($waitingPropositions) > 0)
	{
		foreach($waitingPropositions as $value)
		{
			if($loop != 0)
			{
				print '<tr>';
				print '<td colspan="4" style="background-color:grey; color:white">Changement plateau (+5min)</td>';
				print '</tr>';
				$object->tempsTotal += 5;
			}

			// fetch du groupe
			$groupeClass = new Groupe($db);
			$groupeClass->fetch($value->fk_groupe);
			// fetch des programmations à ce concert où se trouve les propositions
			$programmationClass = new Programmation($db);
			$programmations = $programmationClass->fetchAll('','',0,'',array('fk_proposition'=>$value->id,'fk_evenement'=>$object->id));

			print '<tr>';
			print '<td '.($value->status == Proposition::STATUS_DRAFT ? 'style="background-color: #EBEBE4;"' : '').'><a href="groupe_card.php?id='.$groupeClass->id.'"><span class="badge  badge-status4 badge-status" style="color:white;">'.$groupeClass->nom_groupe.'</span></a><br><br>';
			print $groupeClass->printEngagements();
			print '</td>';
			print '<td style="padding:1em '.($value->status == Proposition::STATUS_DRAFT ? ';background-color: #EBEBE4' : '').'">';
			if (count($programmations) > 0) {
				print '<table class="tagtable nobottomiftotal liste" style="background-color: lightgray">';
				print '<tbody>';
				print '<tr>';
				print '<td style="padding:0.8em">Titre et artiste</td>';
				print '<td style="padding:0.8em">Durée</td>';
				print '</tr>';

				foreach ($programmations as $programmation) {
					// Appel de la fonction qui affiche les lines de programmation
					print $object->printProgrammationLines((object)$programmation);
				}

				print '</tbody>';
				print '</table>';
			} else print 'Aucune programmation connue pour ce groupe!<br>';

			print '</td>';

			print '<td '.($value->status == Proposition::STATUS_DRAFT ? 'style="background-color: #EBEBE4;"' : '').'>';
			print dolGetButtonAction('Inclure à la conduite','', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=handleProposition&token=' . newToken().'&propositionId='.$value->id.'&typeAction=activateProposition', '', $permissiontoadd).'<br><br>';
			print dolGetButtonAction('Reprogrammer le passage à une autre date', '', 'default',  'proposition_card.php?id=' . $value->id . '&action=edit&token=' . newToken().'&backtopage='.$_SERVER['PHP_SELF'].'?id='.$object->id.'&backtopageforcancel='.$_SERVER['PHP_SELF'].'?id='.$object->id.'&reprogrammation=true', '', $permissiontoadd);

			print '</td>';
			print '</tr>';
			$loop++;
		}
	} else {
		print '<tr><td colspan="3">Toutes les passages ont étés traités!</td></tr>';
	}

	print '</tbody>';
	print '</table>';
	print '</div>';

	print dol_get_fiche_end();
}

// End of page
llxFooter();
$db->close();
