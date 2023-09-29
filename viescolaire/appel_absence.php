<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
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
//if (! defined('NOSESSION'))     		     define('NOSESSION', '1');				    // Disable session
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
dol_include_once('/viescolaire/class/appel.class.php');
dol_include_once('/scolarite/class/dispositif.class.php');

dol_include_once('/viescolaire/lib/viescolaire_appel.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("viescolaire@viescolaire", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'appelcard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid   = GETPOST('lineid', 'int');

// Initialize technical objects
$object = new Appel($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->viescolaire->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('appelcard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->viescolaire->eleve->read;
	$permissiontoadd = $user->rights->viescolaire->eleve->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->viescolaire->eleve->delete;
	$permissionnote = $user->rights->viescolaire->eleve->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->viescolaire->eleve->write; // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}

$upload_dir = $conf->viescolaire->multidir_output[isset($object->entity) ? $object->entity : 1].'/appel';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->viescolaire->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */
if($action == 'addAbsence')
{
	function dateToMySQL($date){
        $tabDate = explode('/' , $date);
        $dateToAdd = $tabDate[2].'-'.$tabDate[0].'-'.$tabDate[1];
        //$dates = date('Y-m-d H:i:s', strtotime($dates));
        return $dateToAdd;
    }

	$error = 0;
	if((strtotime(dateToMySQL(GETPOST('date_fin_evenement'))) != "") && (strtotime(dateToMySQL(GETPOST('date_fin_evenement'))) < strtotime(dateToMySQL(GETPOST('date_evenement')))))
	{
		setEventMessage('Veuillez choisir des dates valides.','errors');
		$action = 'create';
		$error++;
	}


	$creneau = "SELECT * FROM ".MAIN_DB_PREFIX."creneau WHERE rowid = ".GETPOST('creneauid', 'int');
	$resqlCreneau = $db->query($creneau);
	$objCreneau = $db->fetch_object($resqlCreneau);

	$dateJour = date('N', strtotime(dateToMySQL(GETPOST('date_evenement'))));


	if($objCreneau->jour != $dateJour)
	{
		setEventMessage("Le jour sélectioné ne correspond pas au jour du créneau.",'errors');
		$action = 'create';
		$error++;
	}



	$newDate = strtotime(dateToMySQL(GETPOST('date_fin_evenement'))) - strtotime(dateToMySQL(GETPOST('date_evenement')));

	$diffJours = floor($newDate / 86400);

	if($diffJours >= 7)
	{
		$result = ($diffJours/7)+1;
	}
	else
	{
		$result = 1;
	}

	$dateToAdd = dateToMySQL(GETPOST('date_evenement'));

	//$date = date('Y-m-d H:i:s', strtotime(GETPOST('date_evenement')));
	//var_dump($dateToAdd);
	for($i=0; $i<$result; $i++)
	{
		if($error == 0)
		{
			$sql = "SELECT fk_etablissement FROM ".MAIN_DB_PREFIX."creneau as c INNER JOIN ".MAIN_DB_PREFIX."dispositif as d ON c.fk_dispositif=d.rowid WHERE c.rowid=".GETPOST('creneauid','int');
			$resql = $db->query($sql);

			$resultat = $db->fetch_object($resql);

			$existingAbsence = "SELECT * FROM ".MAIN_DB_PREFIX."appel WHERE fk_eleve =".GETPOST('elevesid', 'int').' AND fk_creneau='.GETPOST('creneauid', 'int').' AND date_creation="'.$date.'"';
			$resqlAbsence = $db->query($existingAbsence);
		
		
			if($resqlAbsence->num_rows == 0)
			{
				$sqlres = "INSERT INTO ".MAIN_DB_PREFIX."appel (fk_etablissement, fk_creneau, fk_eleve, justification, date_creation,fk_user_creat, status, treated) VALUES (";
				$sqlres .= $resultat->fk_etablissement.",";
				$sqlres .= GETPOST('creneauid', 'int').",";
				$sqlres .= GETPOST('elevesid', 'int').",";
				$sqlres .=  "'".addslashes(GETPOST('infos_evenement', 'alpha'))."',";
				$sqlres .=  "'".$dateToAdd."',";
				$sqlres .= $user->id.",";
				$sqlres .="'".addslashes(GETPOST('evenementid', 'alpha'))."',";
				$sqlres .= 1 .")";
				$resql = $db->query($sqlres);

			}
			else
			{
				setEventMessage("Au moins une absence éxiste déjà avec ces informations",'errors');
				$action = 'create';
				$error++;
			}
		}

		$dateToAdd = date('Y-m-d H:i:s', strtotime($dateToAdd. ' + 7 days'));
		
	}

	if($error == 0)
	{
		header('Location: '.DOL_URL_ROOT.'/custom/viescolaire/appel_absence_list.php?action=create&etablissementid='.$resultat->fk_etablissement);
	}
}


$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/viescolaire/appel_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/viescolaire/appel_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'VIESCOLAIRE_APPEL_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// Actions to send emails
	$triggersendname = 'VIESCOLAIRE_APPEL_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_APPEL_TO';
	$trackid = 'appel'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}




/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Justifier une absence");
$help_url = '';
llxHeader('', $title, $help_url);

// Part to create
	if (($action == 'create' && !GETPOST('elevesid', 'int')) || ($action == 'fromValidation')) // SELECTION DU TYPE DE KIT
    {
		
		$sql = "SELECT e.rowid,e.nom,e.prenom FROM ".MAIN_DB_PREFIX."eleve as e";
		$resql = $db->query($sql);
		$eleves = [];

		foreach($resql as $val)
		{
			 $eleves[$val['rowid']]=$val['prenom'].' '.$val['nom'];

		}
        //WYSIWYG Editor
		print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
      
        print '<input type="hidden" name="action" value="create">';
		$titre = "Nouvel Appel";
        print talm_load_fiche_titre($title, $linkback, $picto);
        dol_fiche_head('');
        print '<table class="border centpercent">';
        print '<tr>';
        print '</td></tr>';
        // Type de Kit
        print '<tr><td class="fieldrequired titlefieldcreate">Selectionnez l\'élève concerné : </td><td>';
		print $form->selectarray('elevesid', $eleves);
        print ' <a href="'.DOL_URL_ROOT.'/custom/viescolaire/eleve_card.php?action=create">';
        print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter 1 Élie Kaks"></span>';
        print '</a>';
        print '</td>';
        print '</tr>';

        print "</table>";
		dol_fiche_end();
		print '<div class="center">';
		print '<input type="submit" class="button" value="Confirmer">';
		print '</div>';
		print '</form>';
		
    }
	elseif($action == 'create' && GETPOST('elevesid', 'int'))
	{
		
		$anneScolaire = "SELECT rowid FROM ".MAIN_DB_PREFIX."c_annee_scolaire WHERE active = 1 AND annee_actuelle = 1";
		$resqlAnneeScolaire = $db->query($anneScolaire);
		$objAnneScolaire = $db->fetch_object($resqlAnneeScolaire);

        $Creneau = "SELECT c.nom_creneau,c.rowid FROM ".MAIN_DB_PREFIX."souhait as s INNER JOIN ".MAIN_DB_PREFIX."affectation as a ON a.fk_souhait = s.rowid INNER JOIN ".MAIN_DB_PREFIX."creneau as c ON a.fk_creneau = c.rowid WHERE s.fk_eleve=".GETPOST('elevesid', 'int')." AND a.status=4 AND c.fk_annee_scolaire=".$objAnneScolaire->rowid;
		$resqlCreneau = $db->query($Creneau);
		$creneau = [];

		foreach($resqlCreneau as $valCreneau)
		{
			 $creneau[$valCreneau['rowid']]=$valCreneau['nom_creneau'];
		}
		
		if($creneau == [])
		{
			setEventMessage("Aucun créneau connu pour cet élève",'errors');
			$action = 'fromValidation';
		}
		else
		{
			$evenements = [
				'absenceJ' => "Absence Justifiée",
				'retard' => "Retard"
			];
			
			print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		  
			print '<input type="hidden" name="action" value="addAbsence">';
			print '<input type="hidden" name="elevesid" value="'.GETPOST('elevesid', 'int').'">';
			$titre = "Nouvel Appel";
			print talm_load_fiche_titre($title, $linkback, $picto);
			dol_fiche_head('');
			print '<table class="border centpercent">';
			print '<tr><td class="fieldrequired titlefieldcreate">Créneau: </td><td>';
			print $form->selectarray('creneauid', $creneau);
			print '</td>';
			print '</tr>';
	
			print '<tr><td class="fieldrequired titlefieldcreate">Type d\'absence: </td><td>';
			print $form->selectarray('evenementid', $evenements);
			print '</td>';
			print '</tr>';
	
			print '<tr><td class="fieldrequired titlefieldcreate">Date de l\'absence <br>(Premier cours concerné): </td><td>';
			print $form->selectDate('', 'date_evenement', '', '', '', '', 1, 1);
			print '</td>';
			print '</tr>';

			print '<tr><td class="titlefieldcreate">Fin de l\'absence<br> (facultatif): </td><td>';
			print $form->selectDate(-1, 'date_fin_evenement', '', '', '', '', 1, 1);
			print '</td>';
			print '</tr>';
	
			print '<tr><td class="fieldrequired titlefieldcreate">Infos: </td><td>';
			print '<textarea name="infos_evenement" id="infos_evenement" cols="100" rows="10"></textarea>';
			print '</td>';
			print '</tr>';
	
			print "</table>";
			dol_fiche_end();
			print '<div class="center">';
			print '<input type="submit" class="button" value="Confirmer">';
			print '</div>';
			print '</form>';
		}
	}

	
// End of page
llxFooter();
$db->close();
