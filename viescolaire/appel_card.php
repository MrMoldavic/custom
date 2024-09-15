<?php
/*ini_set('display_errors', '1');
 ini_set('display_startup_errors', '1');
 error_reporting(E_ALL);*/
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *   	\file       appel_card.php
 *		\ingroup    viescolaire
 *		\brief      Page to create/edit/view appel
 */
/*
 ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token).
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
if (!$res && !empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
     $res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT'] . '/main.inc.php';
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
     $i--;
     $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . '/main.inc.php')) {
     $res = @include substr($tmp, 0, ($i + 1)) . '/main.inc.php';
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . '/main.inc.php')) {
     $res = @include dirname(substr($tmp, 0, ($i + 1))) . '/main.inc.php';
}
// Try main.inc.php using relative path
if (!$res && file_exists('../main.inc.php')) {
     $res = @include '../main.inc.php';
}
if (!$res && file_exists('../../main.inc.php')) {
     $res = @include '../../main.inc.php';
}
if (!$res && file_exists('../../../main.inc.php')) {
     $res = @include '../../../main.inc.php';
}
if (!$res) {
     die('Include of main fails');
}

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/materiel/core/lib/functions.lib.php';
//require_once DOL_DOCUMENT_ROOT . '/custom/viescolaire/scripts/loader.css';

dol_include_once('/viescolaire/class/appel.class.php');
dol_include_once('/scolarite/class/dispositif.class.php');
dol_include_once('/scolarite/class/etablissement.class.php');

dol_include_once('/viescolaire/lib/viescolaire_appel.lib.php');
//dol_include_once('/viescolaire/scripts/loader.css');

// Load translation files required by the page
$langs->loadLangs(array('viescolaire@viescolaire', 'other'));

// Get parameters
$action = GETPOST('action', 'alpha');
$creneauid = GETPOST('creneauid', 'int');
$getHeureActuelle = (GETPOST('heureActuelle', 'alpha') ? : (int)strftime('%k'));
$allCreneaux = GETPOST('allCreneaux', 'alpha') ? : false;
$selectedDate = GETPOST('selectedDate', 'alpha') ? : date('Y-m-d');
$antenneId = GETPOST('antenneId','int');

$selectedDay = GETPOST('selectedDay','int') ? : date('w',strtotime(date('Y-m-d')));;

strlen($getHeureActuelle) > 2 ? $heureActuelle = substr($getHeureActuelle, 0, 2) : $heureActuelle = $getHeureActuelle;


// Initialize technical objects
$object = new Appel($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->viescolaire->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('appelcard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST('search_all', 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
     if (GETPOST('search_' . $key, 'alpha')) {
          $search[$key] = GETPOST('search_' . $key, 'alpha');
     }
}
// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
     $permissiontoread = $user->rights->viescolaire->appel->read;
     $permissiontoadd = $user->rights->viescolaire->appel->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
     $permissiontodelete = $user->rights->viescolaire->appel->delete;
     $permissionnote = $user->rights->viescolaire->appel->write; // Used by the include of actions_setnotes.inc.php
     $permissiondellink = $user->rights->viescolaire->appel->write; // Used by the include of actions_dellink.inc.php
} else {
     $permissiontoread = 1;
     $permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
     $permissiontodelete = 1;
     $permissionnote = 1;
     $permissiondellink = 1;
}

$upload_dir = $conf->viescolaire->multidir_output[isset($object->entity) ? $object->entity : 1] . '/appel';

/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans('Appel');
$help_url = '';
llxHeader('', $title, $help_url,'','','',array('https://kit.fontawesome.com/5ebaa97b0a.js'),array('custom/viescolaire/assets/css/styles.css'));

// include des actions, pour ne pas flooder le fichier (include et pas include_once)
include DOL_DOCUMENT_ROOT.'/custom/viescolaire/core/actions/actions_appel_viescolaire.inc.php';

if ($action == 'create' && !$antenneId)
{
	print '<hr>';
	print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="create">';

	print load_fiche_titre('Choisir son antenne', '', 'fa-school');

	print '<table class="border centpercent">';
	print '<tr>';
	print '</td></tr>';
	// Type de Kit
	print '<tr><td class="fieldrequired titlefieldcreate">Selectionnez votre établissement : </td><td>';

	$antenneClass = new Etablissement($db);
	$antenneArray = $antenneClass->returnAntenneNameArray();

	print $form->selectarray('antenneId', $antenneArray, $_SESSION['etablissementid']);
	print ' <a href="' . DOL_URL_ROOT . '/custom/scolarite/etablissement_card.php?action=create">';
	print '</a>';
	print '</td>';
	print '</tr>';
	print '</table>';

	print '<div class="center">';
	print '<input type="submit" class="button" value="Suivant">';
	print '</div>';
	print '</form>';
	print '<hr>';
}

// Affichage de la page principale d'appel
if (($action == 'create' || $action == 'modifAppel' || $action == 'returnFromError') && $antenneId)
{
	$antenneClass = new Etablissement($db);
	$creneauClass = new Creneau($db);

	$dateTime = DateTime::createFromFormat('Y-m-d', $selectedDate); // Crée un objet DateTime à partir de ta date
	$formattedDate = $dateTime->format('d/m/Y'); // Formate la date en JJ/MM/YYYY


	print load_fiche_titre('Appel du '.$formattedDate, '', 'fa-house');

	// Affichage du formulaire de changement d'heure
	print $object->printChangeHourFormAppel($antenneId, $heureActuelle, $selectedDay, $selectedDate);

	//Fetch de l'antenne actuelle pour récupérer son diminutif
	$antenneClass->fetch($antenneId);

	// Recherche de tout les créneau à un jour donné, heure donnée, antenne donnée
	$creneauCountList = $creneauClass->fetchAll('','',$action === 'modifAppel' ? 1 : 0,0, ['t.jour'=>$selectedDay,'customsql'=>($action == 'modifAppel' ? ' t.rowid=' .GETPOST('creneauid','int') : ($allCreneaux ? 't.heure_debut <=82800' : 't.heure_debut ='.($heureActuelle*3600)." AND a.diminutif='$antenneClass->diminutif'")),'t.status'=>Creneau::STATUS_VALIDATED],'AND',' INNER JOIN '.MAIN_DB_PREFIX.'dispositif as d ON t.fk_dispositif=d.rowid INNER JOIN '.MAIN_DB_PREFIX.'etablissement as a ON d.fk_etablissement=a.rowid');

	if(count($creneauCountList) === 0) {
		setEventMessage("Aucun cours à cette heure, changez d'horaire.", 'errors');
	}

	print dolGetButtonAction($langs->trans('Retour au sommaire'), '', '', dol_buildpath('/custom/viescolaire/appel_sommaire.php',1).'?antenneId='.$antenneId.'&action=create&appel='.$formattedDate , '');

	print dolGetButtonAction((!GETPOST('allCreneaux', 'alpha') ? 'Afficher tous les créneaux' : 'Afficher seulement les créneaux de l\'heure actuelle'), '', 'danger', dol_buildpath('/custom/viescolaire/appel_card.php',1) .'?antenneId='.$antenneId.'&selectedDay='.$selectedDay.'&selectedDate='.$selectedDate.'&heureActuelle='.$heureActuelle.(!GETPOST('allCreneaux', 'alpha') ? '&allCreneaux=true' : '').'&action=create&token=' . newToken(), '');

	$heureAffichage = 0;
	foreach ($creneauCountList as $val)
	{
		if($val->heure_debut !== $heureAffichage)
		{
			print '<div style="dislay:flex;">';
			print '<h3 id="'.($val->debut/3600).'h">Créneau(x) de '.($val->heure_debut/3600).'h <i class="fa-solid fa-chevron-down"></i></h3>';
			print '<div class="hourLoader" style="display: flex; flex-direction: row; align-items: center; margin-left: 2em">';
			print '<div class="loader"></div>';
			print '<p style="margin-left: 1em">Chargement de l\'appel, merci de patienter...</p>';
			print '</div>';
			print '</div>';

			$heureAffichage = $val->heure_debut;
		}

		print '<div class="appelDiv">';
		// Recherche des assignations pour ce créneau
		$agentClass = new Agent($db);
		$professeurs = $agentClass->fetchAll('','',0,0,array('a.fk_creneau'=>$val->id,'a.status'=>Assignation::STATUS_VALIDATED),'AND',' INNER JOIN '.MAIN_DB_PREFIX.'assignation as a ON a.fk_agent=t.rowid');

		// Recherche des élèves présents dans le créneau (via leurs souhaits)
		$eleveClass = new Eleve($db);
		$eleves = $eleveClass->fetchAll('','',0,0,array('a.fk_creneau'=>$val->id,'a.status'=>Affectation::STATUS_VALIDATED),'AND',' INNER JOIN ' .MAIN_DB_PREFIX. 'souhait as s ON s.fk_eleve = t.rowid INNER JOIN '.MAIN_DB_PREFIX.'affectation as a ON a.fk_souhait=s.rowid');

		list($isComplete,$injustifiee,$treated,$agentClass) = $object->returnAllAppelInfos($val->id,$selectedDate,$eleves, $professeurs);

		// Div accordéon d'un cours
		print '<div class="appel-accordion'.(($action == 'modifAppel' || $action == 'returnFromError') && ($creneauid == $val->id) ? '-opened' : '').'" id="appel-' . $val->id . '">';
		print '<h3>';

		if ($isComplete && ($action == 'modifAppel' && $creneauid == $val->id)) {
			print '<span class="badge  badge-status10 badge-status" style="color:white;">Appel en cours de modification</span> ';
		} elseif (!$isComplete) {
			print '<span class="badge  badge-status2 badge-status" style="color:white;">Appel non Fait</span> ';
		} elseif ($injustifiee > 0 && $treated) {
			print '<span class="badge  badge-status4 badge-status" style="color:white;">Appel Fait par '.($agentClass->id ? $agentClass->firstname.' '.$agentClass->lastname : 'Une erreur est survenue.').'</span>&nbsp;&nbsp;&nbsp;<span class="badge  badge-status8 badge-status" style="color:white;">'.(int)$injustifiee.' Absence Injustifiée(s)</span>&nbsp;&nbsp;&nbsp;</span><span class="badge  badge-status4 badge-status" style="color:white;">Traitée(s)</span> ';
		} elseif ($injustifiee > 0 && !$treated) {
			print '<span class="badge  badge-status4 badge-status" style="color:white;">Appel Fait par '.($agentClass->id ? $agentClass->firstname.' '.$agentClass->lastname : 'Une erreur est survenue.').'</span>&nbsp;&nbsp;&nbsp;<span class="badge  badge-status8 badge-status" style="color:white;">'.(int)$injustifiee.' Absence Injustifiée(s)</span>&nbsp;&nbsp;&nbsp;</span><span class="badge  badge-status1 badge-status" style="color:white;">Non traitée(s) </span> ';
		} else {
			print '<span class="badge  badge-status4 badge-status" style="color:white;">Appel Fait par '.($agentClass->id ? $agentClass->firstname.' '.$agentClass->lastname : 'Une erreur est survenue.').'</span> ';
		}
		print $val->nom_creneau;
		print '';

		print '</h3>';

		print '<div>';

		print '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		if (!$isComplete || ($action == 'modifAppel' && $creneauid == $val->id)) {
			print '<input type="hidden" name="action" value="confirmAppel">';
		} else {
			print '<input type="hidden" name="action" value="modifAppel">';
		}
		print '<input type="hidden" name="selectedDay" value="' . $selectedDay . '">';
		print '<input type="hidden" name="selectedDate" value="' . $selectedDate . '">';
		print '<input type="hidden" name="creneauid" value="' . $val->id . '">';
		print '<input type="hidden" name="antenneId" value="' . $antenneId . '">';
		print '<input type="hidden" name="heureActuelle" value="' . $heureActuelle . '">';


		print '<table class="tagtable liste">';
		print '<h3>Professeur(s)</h3>';

		foreach ($professeurs as $professeur) {

			// Recherche d'un appel existant pour ce professeur à cette date sur ce créneau
			$appelClassAgent = new Appel($db);
			$appelClassAgent->fetch('',''," AND fk_creneau=$val->id AND fk_user=$professeur->id AND date_creation LIKE '$selectedDate%'");

			print '<tr class="oddeven">';
			print '<td>';
			print $professeur->getNomUrl(1,'target="_blank"');
			print '</td>';
			print '<td>';
			print '<input type="radio" ' . (isset($appelClassAgent->id) && $appelClassAgent->status == 'present' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val->id ? '' : ($isComplete ? 'disabled' : '')) . ' value="present" name="prof' . $professeur->id . '" id="">&nbsp;<span class="badge  badge-status4 badge-status" style="color:white;">Présent</span>';
			print '</td>';
			print '<td>';
			print '<input type="radio"  ' . ($appelClassAgent->status == 'retard' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val->id ? '' : ($isComplete ? 'disabled' : '')) . ' value="retard" name="prof' . $professeur->id . '" id="">&nbsp;<span class="badge  badge-status1 badge-status" style="color:white;">Retard</span>';
			print '</td>';
			print '<td>';
			print '<input type="radio" ' . ($appelClassAgent->status == 'remplace' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val->id ? '' : ($isComplete ? 'disabled' : '')) . ' value="remplace" name="prof' . $professeur->id . '" id="">&nbsp;<span class="badge  badge-status5 badge-status" style="color:white;">Remplacé</span>';
			print '</td>';
			print '<td>';
			print '<input type="radio" ' . ($appelClassAgent->status == 'absent' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val->id ? '' : ($isComplete ? 'disabled' : '')) . ' value="absent" name="prof' . $professeur->id . '" id="">&nbsp;<span class="badge  badge-status8 badge-status" style="color:white;">Absent</span>';
			print '</td>';
			print '<td>';
			print 'Infos: <input type="text" ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val->id ? '' : ($isComplete ? 'disabled' : '')) . ' name="infos' . $professeur->id . '" value="' . ($appelClassAgent->justification ?  : '') . '" id=""/>';
			print '</td>';
			print '</tr>';
		}

		print '<div>';
		print '</table>';
		print '<table class="tagtable liste">';
		print '<h3>Élèves</h3>';
		print '</div>';


		foreach ($eleves as $eleve) {

			// Recherche d'un appel existant pour cet élève à cette date sur ce créneau
			$appelClassEleve = new Appel($db);
			$appelClassEleve->fetch('',''," AND fk_creneau=$val->id AND fk_eleve=$eleve->id AND date_creation LIKE '$selectedDate%'");

			print '<tr class="oddeven">';
			print '<td>';
			print $eleve->getNomUrl(1);
			print '</td>';
			print '<td>';
			print '<input type="radio"  ' . (isset($appelClassEleve) && $appelClassEleve->status == 'present' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val->id ? '' : ($isComplete ? 'disabled' : '')) . ' value="present" name="presence' . $eleve->id . '" id="">&nbsp;<span class="badge  badge-status4 badge-status" style="color:white;">Présent</span>';
			print '</td>';
			print '<td>';
			print '<input type="radio"   ' . (isset($appelClassEleve) && $appelClassEleve->status == 'retard' ? 'checked' : '') . ' ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val->id ? '' : ($isComplete ? 'disabled' : '')) . ' value="retard" name="presence' . $eleve->id . '" id="">&nbsp;<span class="badge  badge-status1 badge-status" style="color:white;">Retard</span>';
			print '</td>';
			print '<td>';
			print '<input type="radio" ' . (isset($appelClassEleve) && $appelClassEleve->status == 'absenceJ' ? 'checked' : '') . ' ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val->id ? '' : ($isComplete ? 'disabled' : '')) . ' value="absenceJ" name="presence' . $eleve->id . '" id="">&nbsp;<span class="badge  badge-status5 badge-status" style="color:white;">Abscence justifiée</span>';
			print '</td>';
			print '<td>';
			print '<input type="radio" ' . (isset($appelClassEleve) && $appelClassEleve->status == 'absenceI' ? 'checked' : '') . ' ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val->id ? '' : ($isComplete ? 'disabled' : '')) . ' value="absenceI" name="presence' . $eleve->id . '" id="">&nbsp;<span class="badge  badge-status8 badge-status" style="color:white;">Abscence injustifiée</span>';
			print '</td>';
			print '<td>';
			print 'Infos: <input type="text" ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val->id ? '' : ($isComplete ? 'disabled' : '')) . ' value="' . (isset($appelClassEleve) ? $appelClassEleve->justification : '') . '" name="infos' . $eleve->id . '" id=""/>';
			print '</td>';
			print '</tr>';
		}

		print '</table>';
		print '<div class="center" style="display: flex; align-items: center; justify-content: center; flex-direction: column">';
		print '<div id="loader-'.$val->id.'"></div>';
		print '<input type="submit" id="'.$val->id.'" value="'.(!$isComplete || ($action == 'modifAppel' && $creneauid == $val->id) ? 'Valider l\'appel' : 'Modifier l\'appel').'" class="button appelButton" style="background-color:lightslategray">';
		print '</div>';

		print '</form>';
		print '</div>';
		print '</div>';

		print '</div>';
	}
	print "<script src = 'assets/js/appel.js'></script>";
}

// End of page
llxFooter();
$db->close();
