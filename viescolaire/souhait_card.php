<?php
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
 *   	\file       souhait_card.php
 *		\ingroup    viescolaire
 *		\brief      Page to create/edit/view souhait
 */

/*ini_set('display_errors', '1');
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
//if (! defined('NOSESSION'))     		     define('NOSESSION', '1');				    // Disable session

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
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
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
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

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT .  '/user/class/user.class.php';

dol_include_once('viescolaire/class/souhait.class.php');
dol_include_once('viescolaire/lib/viescolaire_souhait.lib.php');

dol_include_once('viescolaire/class/affectation.class.php');
dol_include_once('viescolaire/class/dictionary.class.php');
dol_include_once('management/class/agent.class.php');
dol_include_once('scolarite/class/creneau.class.php');
// dol_include_once('viescolaire/class/eleve.class.php');




// Load translation files required by the page
$langs->loadLangs(array("viescolaire@viescolaire", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'souhaitcard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid   = GETPOST('lineid', 'int');

if ($action == 'confirm_clone') {
	setEventMessage('Vous êtes bien sur le nouveau souhait.');
}
if ($action == 'newaffectation') {

	if(GETPOST('fk_creneau', 'int') == '-1')
	{
		setEventMessage('Veuillez selectionner un creneau valide','errors');
	}
	elseif(dol_mktime(12, 0, 0, GETPOST('date_debutmonth', 'int'), GETPOST('date_debutday', 'int'), GETPOST('date_debutyear', 'int')) == "")
	{
		setEventMessage('Veuillez selectionner un date de début valide','errors');
	}
	else
	{
		$sql = "SELECT status FROM " . MAIN_DB_PREFIX . "souhait WHERE rowid=" . $id;
		$resql = $db->query($sql);

		$statutSouhait = $db->fetch_object($resql);

		if ($statutSouhait->status == 0) {
			$affectation = new Affectation($db);
			$affectation->fk_souhait = GETPOST('fk_souhait', 'int');
			$affectation->fk_creneau = GETPOST('fk_creneau', 'int');
			$affectation->fk_souhait = GETPOST('fk_souhait', 'int');
			$affectation->date_debut = dol_mktime(12, 0, 0, GETPOST('date_debutmonth', 'int'), GETPOST('date_debutday', 'int'), GETPOST('date_debutyear', 'int'));
			$affectation->date_fin = dol_mktime(12, 0, 0, GETPOST('date_finmonth', 'int'), GETPOST('date_finday', 'int'), GETPOST('date_finyear', 'int'));
			$affectation->description = GETPOST('description', 'text');

			if ($affectation->create($user) < 0) {
				setEventMessage('Une erreur est survenue', 'error');
			}
		}
	}
}

if ($action == 'desactivation') {

	$souhait = new Souhait($db);
	$souhait->fetch($id);
	$souhait->status = $souhait::STATUS_CANCELED;
	$res = $souhait->update($user);

	if($res > 0) setEventMessage('Souhait desactivé avec succès!');
	else setEventMessage('Une erreur est survenue', 'errors');
}

if ($action == 'activation') {

	$souhait = new Souhait($db);
	$souhait->fetch($id);
	$souhait->status = $souhait::STATUS_DRAFT;
	$res = $souhait->update($user);

	if($res > 0) setEventMessage('Souhait activé avec succès!');
	else setEventMessage('Une erreur est survenue', 'errors');

}

if ($action == 'confirm_setdraft') {
	$affectation = new Affectation($db);

	$sql = "UPDATE " . MAIN_DB_PREFIX . "affectation SET status = " . $affectation::STATUS_CANCELED . ", date_fin = CURDATE() WHERE fk_souhait=" . $id;
	$resql = $db->query($sql);
}


// Initialize technical objects
$object = new Souhait($db);
$extrafields = new ExtraFields($db);
$dictionaryClass = new Dictionary($db);
$diroutputmassaction = $conf->viescolaire->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('souhaitcard', 'globalcard')); // Note that conf->hooks_modules contains array

$instruEnseigne = $object->fk_instru_enseigne;
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_' . $key, 'alpha')) {
		$search[$key] = GETPOST('search_' . $key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->viescolaire->eleve->read;
	$permissiontoadd = $user->rights->viescolaire->eleve->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->viescolaire->eleve->delete;
	$permissionappreciation = $user->rights->viescolaire->eleve->appreciation;
	$permissionnote = $user->rights->viescolaire->eleve->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->viescolaire->eleve->write; // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}

$upload_dir = $conf->viescolaire->multidir_output[isset($object->entity) ? $object->entity : 1] . '/souhait';

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

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/viescolaire/souhait_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/viescolaire/souhait_card.php', 1) . '?id=' . ((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'VIESCOLAIRE_SOUHAIT_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT . '/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT . '/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT . '/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT . '/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// Actions to send emails
	$triggersendname = 'VIESCOLAIRE_SOUHAIT_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_SOUHAIT_TO';
	$trackid = 'souhait' . $object->id;
	include DOL_DOCUMENT_ROOT . '/core/actions_sendmails.inc.php';
}




/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);
@
$title = $langs->trans("Souhait");
$help_url = '';
llxHeader('', $title, $help_url);


// Part to create
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Souhait")), '', 'object_' . $object->picto);

	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
	}

	print dol_get_fiche_head(array(), '');

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

	print '<table class="border centpercent tableforfieldcreate">' . "\n";

	$anneScolaire = "SELECT annee,annee_actuelle,rowid FROM ".MAIN_DB_PREFIX."c_annee_scolaire WHERE active = 1 AND annee_actuelle = 1";
	$resqlAnneeScolaire = $db->query($anneScolaire);
	$objAnneScolaire = $db->fetch_object($resqlAnneeScolaire);

	if (!GETPOSTISSET('fk_type_classe')) $_POST['fk_type_classe'] = 1;
	if (!GETPOSTISSET('fk_niveau')) $_POST['fk_niveau'] = 1;
	if (!GETPOSTISSET('fk_annee_scolaire')) $_POST['fk_annee_scolaire'] = $objAnneScolaire->rowid;
	// Common attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';


	print '</table>' . "\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("Souhait"), '', 'object_' . $object->picto);

	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="' . $object->id . '">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
	}

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">' . "\n";

	// Common attributes

	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	$head = souhaitPrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("Souhait"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteSouhait'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . '&lineid=' . $lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	if ($action == 'setdraft') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ToClone'), 'Si vous repassez le souhait en brouillon, cela va le désaffecter de son créneau actuel. Continuer?', 'confirm_setdraft', $formquestion, 'yes', 1);
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
	// ------------------------------------------------------------
	$linkback = '<a href="' . dol_buildpath('/viescolaire/souhait_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref = '<div class="refidno">';
	$sqlEtablissement = "SELECT nom,diminutif FROM ".MAIN_DB_PREFIX."etablissement WHERE rowid=".("(SELECT fk_etablissement FROM ".MAIN_DB_PREFIX."eleve WHERE rowid=".$object->fk_eleve).")";
	$resqlEtablissement = $db->query($sqlEtablissement);
	$resEtablissement = $db->fetch_object($resqlEtablissement);
	$morehtmlref.= $resEtablissement->nom;
	$morehtmlref .= '</div>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'nom_souhait', $morehtmlref);


	print '<div class="fichecenter">';

	print '<div class="fichehalfleft">';

	print '<div class="underbanner clearboth"></div>';

	print '<table class="border centpercent tableforfield">' . "\n";

	// Common attributes
	//$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
	//unset($object->fields['fk_project']);				// Hide field already shown in banner
	//unset($object->fields['fk_soc']);					// Hide field already shown in banner
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';
	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '<h3>Créneau actuel:</h3>';

	//$sqlCreneauActuel = "SELECT c.jour,c.heure_debut,c.heure_fin,c.fk_prof_1,c.nom_creneau,c.rowid FROM ".MAIN_DB_PREFIX."creneau as c WHERE c.rowid =".("(SELECT e.fk_creneau FROM ".MAIN_DB_PREFIX."affectation as e WHERE e.fk_souhait =".$object->id." AND e.status = 4)");
	$sqlCreneauActuel = 'SELECT c.jour, c.heure_debut, c.heure_fin, c.fk_prof_1, c.nom_creneau, c.rowid
						FROM ' .MAIN_DB_PREFIX. 'creneau as c
						INNER JOIN ' .MAIN_DB_PREFIX."affectation as e ON c.rowid = e.fk_creneau
						WHERE e.fk_souhait = $object->id AND e.status = 4";

	$resqlCreneauActuel = $db->query($sqlCreneauActuel);
	$objectCreneauActuel = $db->fetch_object($resqlCreneauActuel);

	if(!$objectCreneauActuel && $object->status != 9)
	{
		print '<span class="badge  badge-status8 badge-status" style="color:white;">Non affecté</span>';
	}
	elseif($object->status == 9)
	{
		print '<span class="badge  badge-status9 badge-status" style="color:white;">Souhait Désactivé</span>';
	}
	else
	{
		$JourCreneauActuel = "SELECT jour, rowid FROM ".MAIN_DB_PREFIX."c_jour WHERE rowid =".$objectCreneauActuel->jour;
		$resqlJourCreneauActuel = $db->query($JourCreneauActuel);
		$objJourCreneauActuel = $db->fetch_object($resqlJourCreneauActuel);

		$heureDebutCreneauActuel = "SELECT heure, rowid FROM ".MAIN_DB_PREFIX."c_heure WHERE rowid =".$objectCreneauActuel->heure_debut;
		$resqlheureDebutCreneauActuel = $db->query($heureDebutCreneauActuel);
		$objheureDebutCreneauActuel = $db->fetch_object($resqlheureDebutCreneauActuel);

		$heureFinCreneauActuel = "SELECT heure, rowid FROM ".MAIN_DB_PREFIX."c_heure WHERE rowid =".$objectCreneauActuel->heure_fin;
		$resqlheureFinCreneauActuel = $db->query($heureFinCreneauActuel);
		$objheureFinCreneauActuel = $db->fetch_object($resqlheureFinCreneauActuel);

		if($objectCreneauActuel->fk_prof_1 != NULL)
		{
			$professeurCreneauActuel = "SELECT prenom,nom FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid = ".$objectCreneauActuel->fk_prof_1;
			$resqlUserCreneauActuel = $db->query($professeurCreneauActuel);
			$objUserCreneauActuel = $db->fetch_object($resqlUserCreneauActuel);
		}


		print '<div style="background-color: #f2f2f2;border-radius: 25px;padding:1em;max-width:60%;margin:1em">';
		print 'Jour : <span class="badge  badge-status8 badge-status" style="color:white;">'.$objJourCreneauActuel->jour.'</span><br>';
		print 'Heure : '.$objheureDebutCreneauActuel->heure.'h / '.$objheureFinCreneauActuel->heure."h<br>";
		print 'Professeur : '.($objUserCreneauActuel->prenom != NULL ? ($objUserCreneauActuel->prenom.' '.$objUserCreneauActuel->nom) : 'Aucun professeur pour l\'instant!')."<br>";
		print 'Lien :'.'<a href="'.DOL_URL_ROOT.'/custom/scolarite/creneau_card.php?id='.$objectCreneauActuel->rowid.'">'.$objectCreneauActuel->nom_creneau.'</a>';
		print '</div>';

	}

	if ($object->status == $object::STATUS_VALIDATED) {
		print dolGetButtonAction($langs->trans('Désaffecter'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=setdraft&token=' . newToken(), '', $permissiontoadd);
	}
	print '<h3>Infos anciens créneaux pour ce souhait:</h3>';

		$anneScolaire = "SELECT annee,annee_actuelle,rowid FROM ".MAIN_DB_PREFIX."c_annee_scolaire WHERE active = 1 ORDER BY annee_actuelle DESC, rowid ASC";
		$resqlAnneeScolaire = $db->query($anneScolaire);
		$objAnneScolaire = $db->fetch_object($resqlAnneeScolaire);


		foreach($resqlAnneeScolaire as $val)
		{
			$sqlCountAffectation = "SELECT a.fk_creneau,a.date_creation,a.fk_user_creat FROM ".MAIN_DB_PREFIX."affectation as a INNER JOIN ".MAIN_DB_PREFIX."souhait as s ON a.fk_souhait=s.rowid WHERE a.fk_souhait =".$object->id." AND a.status = 8 AND s.fk_annee_scolaire=".$val['rowid'];
			$resqlAffectation = $db->query($sqlCountAffectation);

			if($resqlAffectation->num_rows > 0)
			{
				print '<div class="annee-accordion'.($val['annee_actuelle'] == 1 ? '-opened' : '').'">';
				print '<h3><span class="badge badge-status4 badge-status">'.$val['annee'].($val['annee_actuelle'] == 1 ? ' - (année actuelle)' : '(année précédente)').'</span></h3>';

				print '<table class="tagtable liste">';
				print '<tbody>';

				print '<tr class="liste_titre">
					<th class="wrapcolumntitle liste_titre">Jour</th>
					<th class="wrapcolumntitle liste_titre">Horaire</th>
					<th class="wrapcolumntitle liste_titre">Professeur</th>
					<th class="wrapcolumntitle liste_titre">Créé par, le</th>
					</tr>';
				foreach($resqlAffectation as $value)
				{
					$creneauClass = new Creneau($db);
					$resultCreneau = $creneauClass->fetchBy(['jour','heure_debut','heure_fin','fk_prof_1','rowid'],$value['fk_creneau'],'rowid');

					if($resultCreneau)
					{
						$resultJour = $dictionaryClass->fetchByDictionary('c_jour',['jour','rowid'],$resultCreneau->jour,'rowid');
						$resultHeureDebut = $dictionaryClass->fetchByDictionary('c_heure',['heure','rowid'],$resultCreneau->heure_debut,'rowid');
						$resultHeureFin = $dictionaryClass->fetchByDictionary('c_heure',['heure','rowid'],$resultCreneau->heure_fin,'rowid');

						if($resultCreneau->fk_prof_1 != NULL)
						{
							$agentClass = new Agent($db);
							$agent = $agentClass->fetchBy(['prenom','nom','rowid'],$resultCreneau->fk_prof_1,'rowid');
						}

						print '<tr class="oddeven">';
						print "<td>$resultJour->jour</td>";
						print '<td>'.$resultHeureDebut->heure.'h / '.$resultHeureFin->heure.'h</td>';
						if($resultCreneau->fk_prof_1 != NULL) print '<td>'.$agent->prenom.' '.$agent->nom.'</td>';

						$userClass = new User($db);
						$userClass->fetch($value['fk_user_creat']);

						print '<td>'.$userClass->firstname.' '.$userClass->lastname.' le '.date('d/m/Y',strtotime($value['date_creation'])).'</td>';
						print '</tr>';
					}
				}
				print '</tbody>';
				print '</table>';
				print '</div>';
			}

		}
	print '</div>';
	print '</div>';


	print '<div class="clearboth"></div>';


	print dol_get_fiche_end();

	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">' . "\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {

			print dolGetButtonAction($langs->trans('Faire appréciation de l\'élève'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=edit&token=' . newToken(), '', $permissionappreciation);

			print dolGetButtonAction($langs->trans('Modifier le souhait'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=edit&token=' . newToken(), '', $permissiontoadd);

			if ($object->status == $object::STATUS_DRAFT) {
				print dolGetButtonAction($langs->trans('Cloner le souhait'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . (!empty($object->socid) ? '&socid=' . $object->socid : '') . '&action=clone&token=' . newToken(), '', $permissiontoadd);
				print dolGetButtonAction($langs->trans('Desactiver le souhait'), '', 'danger', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=desactivation&token=' . newToken(), '', $permissiontoadd);
			}

			if ($object->status == $object::STATUS_CANCELED) {
				print dolGetButtonAction($langs->trans('Activer le souhait'), '', 'danger', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=activation&token=' . newToken(), '', $permissiontoadd);
			}

			print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=delete&token=' . newToken(), '', $permissiontodelete);
		}
		print '</div>' . "\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	if ($action != 'presend') {
		print '<div class="fichecenter">';
		print '<a name="builddoc"></a>'; // ancre

		$includedocgeneration = 0;

		// Documents
		if ($includedocgeneration) {
			$objref = dol_sanitizeFileName($object->ref);
			$relativepath = $objref . '/' . $objref . '.pdf';
			$filedir = $conf->viescolaire->dir_output . '/' . $object->element . '/' . $objref;
			$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
			$genallowed = $permissiontoread; // If you can read, you can build the PDF to read content
			$delallowed = $permissiontoadd; // If you can create/edit, you can remove a file on card
			print $formfile->showdocuments('viescolaire:Souhait', $object->element . '/' . $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
		}

		$MAXEVENT = 10;

		if ($object->status == 0) {
			$instruEnseigne = $object->fk_instru_enseigne;
			$typeClasse = $object->fk_type_classe;
			$object = new Affectation($db);

			$object->fk_souhait = $id;
			print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Affectation")), '', 'object_' . $object->picto);

			print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '">';
			print '<input type="hidden" name="token" value="' . newToken() . '">';
			print '<input type="hidden" name="action" value="newaffectation">';
			if ($backtopage) {
				print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
			}
			if ($backtopageforcancel) {
				print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
			}
			//var_dump($object->fields);
			print dol_get_fiche_head(array(), '');


			// Set some default values
			$_POST['fk_souhait'] = $id;
			$_POST['date_debutmonth'] = date('m', time());
			$_POST['date_debutday'] = date('d', time());
			$_POST['date_debutyear'] = date('Y', time());

			print '<table class="border centpercent tableforfieldcreate">' . "\n";

			// Copie d'un code existant (commonfields_add.tpl.php) afin de modifier la clause qui nous intéresse
			foreach ($object->fields as $key => $val) {
				// Discard if field is a hidden field on form
				if (abs($val['visible']) != 1 && abs($val['visible']) != 3) {
					continue;
				}

				if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) {
					continue; // We don't want this field
				}

				print '<tr class="field_'.$key.'">';
				print '<td';
				print ' class="titlefieldcreate';
				if (isset($val['notnull']) && $val['notnull'] > 0) {
					print ' fieldrequired';
				}
				if ($val['type'] == 'text' || $val['type'] == 'html') {
					print ' tdtop';
				}
				print '"';
				print '>';
				if (!empty($val['help'])) {
					print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
				} else {
					print $langs->trans($val['label']);
				}
				print '</td>';
				print '<td class="valuefieldcreate">';
				if (!empty($val['picto'])) {
					print img_picto('', $val['picto'], '', false, 0, 0, '', 'pictofixedwidth');
				}
				if (in_array($val['type'], array('int', 'integer'))) {
					$value = GETPOST($key, 'int');
				} elseif ($val['type'] == 'double') {
					$value = price2num(GETPOST($key, 'alphanohtml'));
				} elseif ($val['type'] == 'text' || $val['type'] == 'html') {
					$value = GETPOST($key, 'restricthtml');
				} elseif ($val['type'] == 'date') {
					$value = dol_mktime(12, 0, 0, GETPOST($key.'month', 'int'), GETPOST($key.'day', 'int'), GETPOST($key.'year', 'int'));
				} elseif ($val['type'] == 'datetime') {
					$value = dol_mktime(GETPOST($key.'hour', 'int'), GETPOST($key.'min', 'int'), 0, GETPOST($key.'month', 'int'), GETPOST($key.'day', 'int'), GETPOST($key.'year', 'int'));
				} elseif ($val['type'] == 'boolean') {
					$value = (GETPOST($key) == 'on' ? 1 : 0);
				} elseif ($val['type'] == 'price') {
					$value = price2num(GETPOST($key));
				} elseif ($key == 'lang') {
					$value = GETPOST($key, 'aZ09');
				} else {
					$value = GETPOST($key, 'alphanohtml');
				}
				if (!empty($val['noteditable'])) {
					print $object->showOutputField($val, $key, $value, '', '', '', 0);
				} else {
					if ($key == 'lang') {
						print img_picto('', 'language', 'class="pictofixedwidth"');
						print $formadmin->select_language($value, $key, 0, null, 1, 0, 0, 'minwidth300', 2);
					} else {
						// Si on est sur l'input fk_creneau, on rentre dans la boucle
						if($key == 'fk_creneau') {
							// On supprime le champ Type de fk_creneau
							unset($object->fields['fk_creneau']['type']);
							// On ajoute le notre, avec nos conditions
							$object->fields['fk_creneau']['type'] = "integer:Creneau:custom/scolarite/class/creneau.class.php:1:(t.nombre_places>(SELECT COUNT(*) FROM llx_affectation as c WHERE c.fk_creneau=t.rowid AND c.status = 4 AND DATE(NOW()) >= DATE(c.date_debut) AND (DATE(NOW()) <= DATE(c.date_fin) OR ISNULL(c.date_fin))) AND t.status = 4 AND (t.nom_creneau LIKE '%".substr($resEtablissement->diminutif, 0,2)."%') ".($typeClasse == 2 ? "AND t.fk_type_classe=".$typeClasse : "AND t.fk_instrument_enseigne = ".$instruEnseigne).") ";
						}
						print $object->showInputField($val, $key, $value, '', '', '', 0);
					}
				}
				print '</td>';
				print '</tr>';
			}

			// Common attributes
			//include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

			// Other attributes
			include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

			print '</table>' . "\n";

			print dol_get_fiche_end();

			print '<div id="div-creneau">';
			print $form->buttonsSaveCancel("Créer affectation");
			print '</div>';

			print '</form>';

			print '</div>';
		}
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'souhait';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->viescolaire->dir_output;
	$trackid = 'souhait' . $object->id;

	include DOL_DOCUMENT_ROOT . '/core/tpl/card_presend.tpl.php';
	print '<script src="/custom/viescolaire/scripts/selectCreneaux.js" defer ></script>';
}


print '<script>
    $( ".annee-accordion" ).accordion({
        collapsible: true,
        active: 2,
    });
    </script>';

print '<script>
    $( ".annee-accordion-opened" ).accordion({
        collapsible: true,
    });
    </script>';

// End of page
llxFooter();
$db->close();
