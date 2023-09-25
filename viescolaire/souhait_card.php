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

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


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

dol_include_once('viescolaire/class/souhait.class.php');
dol_include_once('viescolaire/lib/viescolaire_souhait.lib.php');

dol_include_once('viescolaire/class/affectation.class.php');
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
	$sql = "UPDATE " . MAIN_DB_PREFIX . "souhait SET status = " . $souhait::STATUS_CANCELED . " WHERE rowid=" . $id;
	$resql = $db->query($sql);

	setEventMessage('Souhait desactivé avec succès');
}

if ($action == 'activation') {

	$souhait = new Souhait($db);
	$sql = "UPDATE " . MAIN_DB_PREFIX . "souhait SET status = " . $souhait::STATUS_DRAFT . " WHERE rowid=" . $id;
	$resql = $db->query($sql);

	setEventMessage('Souhait activé avec succès.');
	
}

if ($action == 'confirm_setdraft') {
	$affectation = new Affectation($db);

	$sql = "UPDATE " . MAIN_DB_PREFIX . "affectation SET status = " . $affectation::STATUS_CANCELED . ", date_fin = CURDATE() WHERE fk_souhait=" . $id;
	$resql = $db->query($sql);
}


// Initialize technical objects
$object = new Souhait($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->viescolaire->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('souhaitcard', 'globalcard')); // Note that conf->hooks_modules contains array

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


	// Confirmation of action xxxx
	if ($action == 'xxx') {
		$formquestion = array();
		/*
		$forcecombo=0;
		if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
		$formquestion = array(
			// 'text' => $langs->trans("ConfirmClone"),
			// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
			// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
			// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
		);
		*/
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
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
	$sqlEtablissement = "SELECT nom FROM ".MAIN_DB_PREFIX."etablissement WHERE rowid=".("(SELECT fk_etablissement FROM ".MAIN_DB_PREFIX."eleve WHERE rowid=".$object->fk_eleve).")";
	$resqlEtablissement = $db->query($sqlEtablissement);
	$res = $db->fetch_object($resqlEtablissement);
	$morehtmlref.= $res->nom;

	/*
	 // Ref customer"
	 $morehtmlref.=$form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
	 $morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
	 // Thirdparty
	 $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
	 // Project
	 if (! empty($conf->projet->enabled)) {
	 $langs->load("projects");
	 $morehtmlref .= '<br>'.$langs->trans('Project') . ' ';
	 if ($permissiontoadd) {
	 //if ($action != 'classify') $morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&token='.newToken().'&id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> ';
	 $morehtmlref .= ' : ';
	 if ($action == 'classify') {
	 //$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
	 $morehtmlref .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
	 $morehtmlref .= '<input type="hidden" name="action" value="classin">';
	 $morehtmlref .= '<input type="hidden" name="token" value="'.newToken().'">';
	 $morehtmlref .= $formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
	 $morehtmlref .= '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
	 $morehtmlref .= '</form>';
	 } else {
	 $morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
	 }
	 } else {
	 if (! empty($object->fk_project)) {
	 $proj = new Project($db);
	 $proj->fetch($object->fk_project);
	 $morehtmlref .= ': '.$proj->getNomUrl();
	 } else {
	 $morehtmlref .= '';
	 }
	 }
	 }*/
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

	$sqlCreneauActuel = "SELECT c.jour,c.heure_debut,c.heure_fin,c.fk_prof_1,c.nom_creneau,c.rowid FROM ".MAIN_DB_PREFIX."creneau as c WHERE c.rowid =".("(SELECT e.fk_creneau FROM ".MAIN_DB_PREFIX."affectation as e WHERE e.fk_souhait =".$object->id." AND e.status = 4)");
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
			$professeurCreneauActuel = "SELECT * FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid = ".$objectCreneauActuel->fk_prof_1;
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
	print '<h3>Infos anciennes affectations pour ce souhait:</h3>';

	$sqlCountAffectation = "SELECT * FROM ".MAIN_DB_PREFIX."affectation WHERE fk_souhait =".$object->id." AND status = 8";
	$resqlAffectation = $db->query($sqlCountAffectation);
	

	print '<p>- Nombre d\'anciennes affectations : '.$resqlAffectation->num_rows.'</p>';
	
	if($resqlAffectation->num_rows > 0)
	{
		print '<table class="tagtable liste">';
		print '<tbody>';

		print '<tr class="liste_titre">
		<th class="wrapcolumntitle liste_titre">Jour</th>
		<th class="wrapcolumntitle liste_titre">Horaire</th>
		<th class="wrapcolumntitle liste_titre">Professeur</th>
		</tr>';
		foreach($resqlAffectation as $value)
		{
			$sqlOldAffectation = "SELECT * FROM ".MAIN_DB_PREFIX."creneau WHERE rowid =".$value['fk_creneau'];
			$resqlOldAffectation = $db->query($sqlOldAffectation);
			$objectCreneau = $db->fetch_object($resqlOldAffectation);


			if($objectCreneau)
			{
				$Jour = "SELECT jour, rowid FROM ".MAIN_DB_PREFIX."c_jour WHERE rowid =".$objectCreneau->jour;
				$resqlJour = $db->query($Jour);
				$objJour = $db->fetch_object($resqlJour);
		
				$heure = "SELECT heure, rowid FROM ".MAIN_DB_PREFIX."c_heure WHERE rowid =".$objectCreneau->heure_debut;
				$resqlheure = $db->query($heure);
				$objheure = $db->fetch_object($resqlheure);
		
				$heurefin = "SELECT heure, rowid FROM ".MAIN_DB_PREFIX."c_heure WHERE rowid =".$objectCreneau->heure_fin;
				$resqlheureFin = $db->query($heurefin);
				$objheureFin = $db->fetch_object($resqlheureFin);
		
				if($objectCreneau->fk_prof_1 != NULL)
				{
					$professeur = "SELECT * FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid = ".$objectCreneau->fk_prof_1;
					$resqlUser = $db->query($professeur);
					$objUser = $db->fetch_object($resqlUser);
				}
				

				print '<tr class="oddeven">';
				print '<td>'.$objJour->jour.'</td>';
				print '<td>'.$objheure->heure.'h / '.$objheureFin->heure.'h</td>';
				if($objectCreneau->fk_prof_1 != NULL) print '<td>'.$objUser->prenom.' '.$objUser->nom.'</td>';
				print '</tr>';
			}
		}
		print '</tbody>';
		print '</table>';
	}

	















































	

	print '</div>';
	print '</div>';
	

	print '<div class="clearboth"></div>';
	

	print dol_get_fiche_end();


	/*
	 * Lines
	 */

	if (!empty($object->table_element_line)) {
		// Show object lines
		$result = $object->getLinesArray();

		print '	<form name="addproduct" id="addproduct" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . (($action != 'editline') ? '' : '#line_' . GETPOST('lineid', 'int')) . '" method="POST">
		<input type="hidden" name="token" value="' . newToken() . '">
		<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline') . '">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="page_y" value="">
		<input type="hidden" name="id" value="' . $object->id . '">
		';

		if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
			include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
		}

		print '<div class="div-table-responsive-no-min">';
		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '<table id="tablelines" class="noborder noshadow" width="100%">';
		}

		if (!empty($object->lines)) {
			$object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
		}

		// Form to add new line
		if ($object->status == 0 && $permissiontoadd && $action != 'selectlines') {
			if ($action != 'editline') {
				// Add products/services form

				$parameters = array();
				$reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
				if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
				if (empty($reshook))
					$object->formAddObjectLine(1, $mysoc, $soc);
			}
		}

		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '</table>';
		}
		print '</div>';

		print "</form>\n";
	}

	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">' . "\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {
			// Send
			// if (empty($user->socid)) {
			// 	print dolGetButtonAction($langs->trans('SendMail'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=presend&mode=init&token='.newToken().'#formmailbeforetitle');
			// }

			// Back to draft
			print dolGetButtonAction($langs->trans('Faire appréciation de l\'élève'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=edit&token=' . newToken(), '', $permissionappreciation);
			

			if ($object->status == $object::STATUS_DRAFT) {
				print dolGetButtonAction($langs->trans('Modifier le souhait'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=edit&token=' . newToken(), '', $permissiontoadd);
			}

			// // Validate
			// if ($object->status == $object::STATUS_DRAFT) {
			// 	if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {
			// 		print dolGetButtonAction($langs->trans('Classer le souhait'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=confirm_validate&confirm=yes&token=' . newToken(), '', $permissiontoadd);
			// 	} else {
			// 		$langs->load("errors");
			// 		print dolGetButtonAction($langs->trans("ErrorAddAtLeastOneLineFirst"), $langs->trans("Validate"), 'default', '#', '', 0);
			// 	}
			// }

			// Clone
			if ($object->status == $object::STATUS_DRAFT) {
				print dolGetButtonAction($langs->trans('Cloner le souhait'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . (!empty($object->socid) ? '&socid=' . $object->socid : '') . '&action=clone&token=' . newToken(), '', $permissiontoadd);
				print dolGetButtonAction($langs->trans('Desactiver le souhait'), '', 'danger', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=desactivation&token=' . newToken(), '', $permissiontoadd);
			}

			if ($object->status == $object::STATUS_CANCELED) {
				print dolGetButtonAction($langs->trans('Activer le souhait'), '', 'danger', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=activation&token=' . newToken(), '', $permissiontoadd);
			}

			// if ($permissiontoadd) {
			// 	if ($object->status == $object::STATUS_ENABLED) {
			// 		print dolGetButtonAction($langs->trans('Disable'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=disable&token='.newToken(), '', $permissiontoadd);
			// 	} else {
			// 		print dolGetButtonAction($langs->trans('Enable'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=enable&token='.newToken(), '', $permissiontoadd);
			// 	}
			// }
			// if ($permissiontoadd) {
			// 	if ($object->status == $object::STATUS_VALIDATED) {
			// 		print dolGetButtonAction($langs->trans('Cancel'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=close&token='.newToken(), '', $permissiontoadd);
			// 	} else {
			// 		print dolGetButtonAction($langs->trans('Re-Open'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=reopen&token='.newToken(), '', $permissiontoadd);
			// 	}
			// }


			// Delete (need delete permission, or if draft, just need create/modify permission)
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

		// Show links to link elements
		// $linktoelem = $form->showLinkToObjectBlock($object, null, array('souhait'));
		// $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


		// print '</div><div class="fichehalfright">';
		$MAXEVENT = 10;

		// List of actions on element
		// include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
		// $formactions = new FormActions($db);
		// $somethingshown = $formactions->showactions($object, $object->element . '@' . $object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlcenter);

		if ($object->status == 0) {
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

			print dol_get_fiche_head(array(), '');

			// Set some default values
			$_POST['fk_souhait'] = $id;

			print '<table class="border centpercent tableforfieldcreate">' . "\n";

			// Common attributes
			include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

			// Other attributes
			include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

			print '</table>' . "\n";

			print dol_get_fiche_end();

			print $form->buttonsSaveCancel("Créer affectation");

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
}

// End of page
llxFooter();
$db->close();
