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
 *   	\file       eleve_card.php
 *		\ingroup    viescolaire
 *		\brief      Page to create/edit/view eleve
 */

//  ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

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
dol_include_once('/viescolaire/class/eleve.class.php');
dol_include_once('/viescolaire/lib/viescolaire_eleve.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("viescolaire@viescolaire", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'elevecard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid   = GETPOST('lineid', 'int');


if ($action == 'annulationInscription') {

	$eleve = new Eleve($db);
	$sql = "UPDATE " . MAIN_DB_PREFIX . "eleve SET status = " . $eleve::STATUS_CANCELED . " WHERE rowid=" . $id;
	$resql = $db->query($sql);

	setEventMessage('Inscription annulée avec succès');
}



if( $action == 'stateModify')
{
	$object = new Eleve($db);
	$souhait = "SELECT * FROM ".MAIN_DB_PREFIX."souhait as c WHERE c.fk_eleve = ".$id;
	$resqlSouhait = $db->query($souhait);
	
	$count = 0;
	foreach($resqlSouhait as $val)
	{
		if($val['status'] == 4)
		{
			$sql = "SELECT c.nom_creneau,c.rowid FROM ".MAIN_DB_PREFIX."creneau as c WHERE c.rowid =".("(SELECT e.fk_creneau FROM ".MAIN_DB_PREFIX."affectation as e WHERE e.fk_souhait =".$val['rowid']." AND e.status = 4)");
			$resql = $db->query($sql);
			$objectCreneau = $db->fetch_object($resql);
			$count++;
		}
		elseif($val['status'] == 0)
		{
			$count++;
		}
	}

	if($count != 0 && GETPOST('stateInscription', 'int') == 9)
	{
		setEventMessage('Abandon impossible si des souhaits non désactivés existent.','errors');
	}
	else
	{
		$eleve = new Eleve($db);
		$sql = "UPDATE " . MAIN_DB_PREFIX . "eleve SET status = " . GETPOST('stateInscription', 'int') . " WHERE rowid=" . $id;
		$resql = $db->query($sql);

		setEventMessage('Status modifié avec succès!');
	}
	
}

// Initialize technical objects
$object = new Eleve($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->viescolaire->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('elevecard', 'globalcard')); // Note that conf->hooks_modules contains array

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

$upload_dir = $conf->viescolaire->multidir_output[isset($object->entity) ? $object->entity : 1].'/eleve';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->viescolaire->enabled)) accessforbidden();

// if (!$permissiontoread) accessforbidden();


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

	$backurlforlist = dol_buildpath('/viescolaire/eleve_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/viescolaire/eleve_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'VIESCOLAIRE_ELEVE_MODIFY'; // Name of trigger action code to execute when we modify record

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
	$triggersendname = 'VIESCOLAIRE_ELEVE_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_ELEVE_TO';
	$trackid = 'eleve'.$object->id;
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

$title = $langs->trans("Eleve");
$help_url = '';
llxHeader('', $title, $help_url);

// Example : Adding jquery code
// print '<script type="text/javascript">
// jQuery(document).ready(function() {
// 	function init_myfunc()
// 	{
// 		jQuery("#myid").removeAttr(\'disabled\');
// 		jQuery("#myid").attr(\'disabled\',\'disabled\');
// 	}
// 	init_myfunc();
// 	jQuery("#mybutton").click(function() {
// 		init_myfunc();
// 	});
// });
// </script>';


// Part to create
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Eleve")), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head(array(), '');

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';
	// print $form->selectArrayAjax('test','intranet.tousalamusique.com/custom/scolarite/ajax/getClasseSelect.php?etablissementid=1');

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("Eleve"), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';


	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	$head = elevePrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("Eleve"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteEleve'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
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
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
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
	$linkback = '<a href="'.dol_buildpath('/viescolaire/eleve_list.php', 1).'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	$morehtmlref .= $object->prenom;
	/*
	 // Ref customer
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

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'nom', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	

	print '<div class="underbanner clearboth"></div>';
	

	print '<table class="border centpercent tableforfield">'."\n";
	
	// Common attributes
	//$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
	//unset($object->fields['fk_project']);				// Hide field already shown in banner
	//unset($object->fields['fk_soc']);					// Hide field already shown in banner
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '<h2><u>Suivi de l\'élève:</u></h2>';

	$abscenceInj = "SELECT * FROM ".MAIN_DB_PREFIX."appel as a WHERE a.fk_eleve = ".$object->id." AND a.status= 'absenceI' AND a.treated = 1";
	$resqlInj = $db->query($abscenceInj);
	$numInj = $db->num_rows($resqlInj);

	print '<p>Nombre d\'absence <span class="badge  badge-status8 badge-status" style="color:white;">Injustifiées</span> totales : '.'<a href="' . DOL_URL_ROOT . '/custom/viescolaire/eleve_absence.php?id=' . $object->id . '&absenceI">' . $numInj . '</a>'.'</p>';
	
	$abscenceJus = "SELECT * FROM ".MAIN_DB_PREFIX."appel as a WHERE a.fk_eleve = ".$object->id." AND a.status= 'absenceJ' AND a.treated= 1";
	$resqlJus = $db->query($abscenceJus);
	$numJus = $db->num_rows($resqlJus);

	print '<p>Nombre d\'absence <span class="badge  badge-status4 badge-status" style="color:white;">Justifiées</span> totales : '.$numJus.'</p>';

	$retards = "SELECT * FROM ".MAIN_DB_PREFIX."appel as a WHERE a.fk_eleve = ".$object->id." AND a.status= 'retard' AND a.treated= 1";
	$retards = $db->query($retards);
	$numRetards = $db->num_rows($retards);

	print '<p>Nombre de <span class="badge  badge-status1 badge-status" style="color:white;">retards</span> totaux: '.$numRetards.'</p>';
	print '<h2><u>Etat de l\'inscription: </u></h2>';
	print '<form action="/custom/viescolaire/eleve_card.php?id='.$object->id.'&action=stateModify" method="post">';
	print '<input type="hidden" name="id_eleve" value='.$object->id.'>';
	print '<select name="stateInscription" id="">
			<option value="9" '.($object->status == '9' ? 'selected' : '').' >Abandon</option>
   			<option value="1" '.($object->status == '1' ? 'selected' : '').' >Ancien à remotiver</option>
   			<option value="3" '.($object->status == '3' ? 'selected' : '').' >Venu pour informations</option>
			<option value="7" '.($object->status == '7' ? 'selected' : '').' >Placé (paiement incomplet)</option>
   			<option value="4" '.($object->status == '4' ? 'selected' : '').' >Inscription terminée (payée) </option>
			<option value="2" '.($object->status == '2' ? 'selected' : '').' >Budgétisé</option>
			<option value="8" '.($object->status == '8' ? 'selected' : '').' >Problème</option>
		</select>';	

	if($permissiontoadd == 1)
	{
		print '<button type="submit">Valider</button>';
	}
	else
	{
		print '<button type="submit" disabled>Valider</button>';
	}
	
	
	print '</form>';

	print '</div>';
	$famille = "SELECT * FROM ".MAIN_DB_PREFIX."famille WHERE rowid = ".$object->fk_famille;
	$resqlFamille = $db->query($famille);
	$objFamille = $db->fetch_object($resqlFamille);

	print '<table class="border tableforfield">';
	print '<tbody>';
	print '<tr>';
	print '<td>Téléphone parent 1('.$objFamille->prenom_parent_1.' '.$objFamille->nom_parent_1.'):</td>';
	print '<td>'.$objFamille->tel_parent_1.'</td>';
	print '</tr>';
	print '<tr>';
	print '<td>Téléphone parent 2('.$objFamille->prenom_parent_2.' '.$objFamille->nom_parent_2.'):</td>';
	print '<td>'.$objFamille->tel_parent_2.'</td>';
	print '</tr>';	
	print '<tr>';
	if(!empty($objFamille->mail_parent_1))
	{
		print '<td>Mail parent 1('.$objFamille->prenom_parent_1.' '.$objFamille->nom_parent_1.'):</td>';
		print '<td>'.$objFamille->mail_parent_1.'</td>';
	}
	print '</tr>';
	print '<tr>';
	if(!empty($objFamille->mail_parent_2))
	{
		print '<td>Mail parent 2('.$objFamille->prenom_parent_2.' '.$objFamille->nom_parent_2.'):</td>';
		print '<td>'.$objFamille->mail_parent_2.'</td>';
	}
	print '</tr>';
	print '</tbody>';
	print '</table>';




	print '</div>';
	
	// print '<h2><u>Instruments prêtés: </u></h2>';

	// print '<p>'.dolGetButtonAction('Prêter un instrument', '', 'default', '/custom/exploitation/card.php'.'?action=create&token='.newToken().'&idtypeexploitation=1&idexploitant='.$object->id, '', $permissiontoadd).'</p>';


	// $sqlInstrumentsPretes = "SELECT fk_materiel FROM ".MAIN_DB_PREFIX."kit_content WHERE fk_kit =(SELECT fk_kit FROM ".MAIN_DB_PREFIX."exploitation_content WHERE fk_exploitation = (SELECT rowid FROM ".MAIN_DB_PREFIX."exploitation WHERE fk_exploitant = ".$object->id." AND fk_type_exploitation= 1 AND active = 1))";
	// $resqlInstruments = $db->query($sqlInstrumentsPretes);

	// if(!$resqlInstruments)
	// {
	// 	print '<p>Aucun prêt en cours.</p>';
	// }
	// else
	// {
	// 	print '<table class="tagtable liste">';
	// 	print '<tbody>';
	
	// 	print '<tr class="liste_titre">
	// 		<th class="wrapcolumntitle liste_titre">Marque</th>
	// 		<th class="wrapcolumntitle liste_titre">Modele</th>
	// 		<th class="wrapcolumntitle liste_titre">Infos</th>
	// 		<th class="wrapcolumntitle liste_titre">Etat de l\'instrument</th>
	// 		<th class="wrapcolumntitle liste_titre">Début/Fin de contrat</th>
	// 		<th class="wrapcolumntitle liste_titre">Précisions</th>
	// 		<th class="wrapcolumntitle liste_titre">Origine</th>
	// 		<th class="wrapcolumntitle liste_titre">Action</th>
	// 	</tr>';
	
	// 	foreach($resqlInstruments as $value)
	// 	{
	// 		$sqlInstruments = "SELECT * FROM ".MAIN_DB_PREFIX."materiel WHERE rowid=".$value['fk_materiel'];
	// 		$resqlInstrumentListe = $db->query($sqlInstruments);
	// 		$objInstrument = $db->fetch_object($resqlInstrumentListe);
	
	// 		$sqlMarque = "SELECT marque FROM ".MAIN_DB_PREFIX."c_marque WHERE rowid=".$objInstrument->fk_marque;
	// 		$resqlMarque = $db->query($sqlMarque);
	// 		$objMarque= $db->fetch_object($resqlMarque);
	
	// 		$sqlEtat = "SELECT etat, badge_status_code FROM ".MAIN_DB_PREFIX."c_etat_materiel WHERE rowid=".$objInstrument->fk_etat;
	// 		$resqlEtat = $db->query($sqlEtat);
	// 		$objEtat= $db->fetch_object($resqlEtat);
	
	// 		$sqlOrigine = "SELECT origine FROM ".MAIN_DB_PREFIX."c_origine_materiel WHERE rowid=".$objInstrument->fk_origine;
	// 		$resqlOrigine = $db->query($sqlOrigine);
	// 		$objOrigine= $db->fetch_object($resqlOrigine);

	// 		$sqlDates = "SELECT date_debut,date_fin,rowid FROM ".MAIN_DB_PREFIX."exploitation WHERE fk_exploitant = ".$object->id." AND fk_type_exploitation= 1 AND active = 1";
	// 		$resqlDates = $db->query($sqlDates);
	// 		$objDates= $db->fetch_object($resqlDates);
			

		
	// 		print '<tr class="oddeven">';
	// 		print '<td>'.$objMarque->marque.'</td>';
	// 		print '<td>'.$objInstrument->modele.'</td>';
	// 		print '<td>'.$objInstrument->notes_supplementaires.'</td>';
	// 		print '<td><span class="badge  badge-status'.$objEtat->badge_status_code.' badge-status" style="color:white;">'.$objEtat->etat.'</span></td>';
	// 		print '<td>'.date('d-m-Y',strtotime($objDates->date_debut)).' / '.date('d-m-Y',strtotime($objDates->date_fin)).'</td>';
	// 		print '<td>'.$objInstrument->precision_type.'</td>';
	// 		print '<td>'.$objOrigine->origine.'</td>';
	// 		print '<td>'.dolGetButtonAction('Voir l\'exploitation', '', 'default', '/custom/exploitation/card.php'.'?action=view&token='.newToken().'&id='.$objDates->rowid, '', $permissiontoadd).dolGetButtonAction('Voir le matériel', '', 'default', '/custom/materiel/card.php'.'?action=view&token='.newToken().'&id='.$objInstrument->rowid, '', $permissiontoadd).'</td>';
	// 		print '</tr>';
	// 	}

	// 	print '</tbody>';
	// 	print '</table>';
	// }

	
	$nom = '\'%'.$object->nom.'-'.$object->prenom.'%\'';

	$cours = "SELECT * FROM ".MAIN_DB_PREFIX."souhait as c WHERE c.fk_eleve = ".$object->id." AND c.status= 4";
	$resql = $db->query($cours);
	$obj = $db->fetch_row($resql);
	$num = $db->num_rows($resql);
	
	$souhait = "SELECT * FROM ".MAIN_DB_PREFIX."souhait as c WHERE c.fk_eleve = ".$object->id;
	$resqlSouhait = $db->query($souhait);
	$obj = $db->fetch_object($resqlSouhait);
	$numSouhait = $db->num_rows($resqlSouhait);
	

	print '<div class="clearboth"></div>';

	
	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {

			print dolGetButtonAction($langs->trans('Engager dans un groupe'), '', 'default', DOL_URL_ROOT.'/custom/organisation/engagement_card.php?fk_eleve='.$object->id.'&action=create' , '', $permissiontoadd);

			print dolGetButtonAction($langs->trans('Modifier l\'élève'), '', 'default', ''.'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);
			print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=delete&token='.newToken(), '', $permissiontodelete);
			// // Send
			// if (empty($user->socid)) {
			// 	print dolGetButtonAction($langs->trans('SendMail'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=presend&mode=init&token='.newToken().'#formmailbeforetitle');
			// }

			
			// if ($object->status == $object::STATUS_VALIDATED) {
			// 	print dolGetButtonAction($langs->trans('Inscription en brouillon'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes&token='.newToken(), '', $permissiontoadd);
			// 	print dolGetButtonAction($langs->trans('Annuler l\'inscription'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=annulationInscription&token=' . newToken(), '', $permissiontoadd);
			// }
		
			// if($object->status == $object::STATUS_DRAFT)
			// {
			// 	print dolGetButtonAction($langs->trans('Modifier l\'élève'), '', 'default', ''.'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);
			// 	print dolGetButtonAction($langs->trans('Annuler l\'inscription'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=annulationInscription&token=' . newToken(), '', $permissiontoadd);
			// 	print dolGetButtonAction($langs->trans('Valider l\'inscription'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes&token='.newToken(), '', $permissiontoadd);
			// }

			// if($object->status == $object::STATUS_VALIDATED)
			// {
			// 	print dolGetButtonAction($langs->trans('Modifier l\'élève'), '', 'default', ''.'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);
			// 	print dolGetButtonAction($langs->trans('Annuler l\'inscription'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=annulationInscription&token=' . newToken(), '', $permissiontoadd);
			// 	print dolGetButtonAction($langs->trans('Inscription en brouillon'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes&token='.newToken(), '', $permissiontoadd);
			// }

			// if($object->status == $object::STATUS_CANCELED)
			// {
			// 	print dolGetButtonAction($langs->trans('Valider l\'inscription'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes&token='.newToken(), '', $permissiontoadd);
			// 	print dolGetButtonAction($langs->trans('Inscription en brouillon'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes&token='.newToken(), '', $permissiontoadd);

			// }
		
			// Delete (need delete permission, or if draft, just need create/modify permission)
		}
		print '</div>'."\n";
	}
	
	// print '<p>Nombre de souhaits de l\'élève: '.$numSouhait.'</p>';
	// print '<hr>';
	// print '<p>Nombre de cours effectifs de l\'élève: '.$num.'</p>';
	
	print '<hr>';
	print '<h2><u>Liste des souhaits de l\'élève:</u></h2>';

	print '<p>'.dolGetButtonAction('Ajouter un souhait', '', 'default', '/custom/viescolaire/souhait_card.php'.'?action=create&fk_eleve='.$object->id, '', $permissiontoadd).'</p>';



	if($obj == NULL)
	{
		print '<p>Aucun cours</p>';
	}
	else
	{
		print '<table class="tagtable liste">';
		print '<tbody>';
	
		print '<tr class="liste_titre">
			<th class="wrapcolumntitle liste_titre">Souhait</th>
			<th class="wrapcolumntitle liste_titre">Etat</th>
			<th class="wrapcolumntitle liste_titre">Créneau</th>
			</tr>';
		foreach($resqlSouhait as $val)
		{
			print '<tr class="oddeven">';
			print '<td><a href="' . DOL_URL_ROOT . '/custom/viescolaire/souhait_card.php?id=' . $val['rowid']. '">' .'- ' . $val['nom_souhait'].'</a> <span class="badge  badge-status'.($val['details'] != "" ? "4" : "8").' badge-status" style="color:white;">'.($val['details'] != "" ? "Appréciation Faite" : "Appréciation manquante").'</span></td>';
			if($val['status'] == 4)
			{
				$sql = "SELECT c.nom_creneau,c.rowid FROM ".MAIN_DB_PREFIX."creneau as c WHERE c.rowid =".("(SELECT e.fk_creneau FROM ".MAIN_DB_PREFIX."affectation as e WHERE e.fk_souhait =".$val['rowid']." AND e.status = 4)");
				$resql = $db->query($sql);
				$objectCreneau = $db->fetch_object($resql);
				
				print '<td><span class="badge  badge-status4 badge-status" style="color:white;">Affecté</span></td>';
				print '<td><a href="'.DOL_URL_ROOT.'/custom/scolarite/creneau_card.php?id='.$objectCreneau->rowid.'">'.$objectCreneau->nom_creneau.'</a></td>';
			}
			elseif($val['status'] == 9)
			{
				// $sql = "SELECT c.nom_creneau,c.rowid FROM ".MAIN_DB_PREFIX."creneau as c WHERE c.rowid =".("(SELECT e.fk_creneau FROM ".MAIN_DB_PREFIX."affectation as e WHERE e.fk_souhait =".$val['rowid'].")");
				// $resql = $db->query($sql);
				// $objectCreneau = $db->fetch_object($resql);

				print '<td><span class="badge  badge-status8 badge-status" style="color:white;">Souhait désactivé</span></td>';
				print '<td><a href="'.DOL_URL_ROOT.'/custom/scolarite/creneau_card.php?id='.$objectCreneau->rowid.'">'.$objectCreneau->nom_creneau.'</a></td>';
			}
			else
			{
				print '<td><span class="badge  badge-status1 badge-status" style="color:white;">En attente d\'affectation</span></td>';
				print '<td>Aucun créneau</td>';
			}
			print '</tr>';
			
		}
		print '</tbody>';
		print '</table>';
	}

	print dol_get_fiche_end();

}

// End of page
llxFooter();
$db->close();
