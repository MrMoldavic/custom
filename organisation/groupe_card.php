<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *   	\file       groupe_card.php
 *		\ingroup    organisation
 *		\brief      Page to create/edit/view groupe
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

dol_include_once('/organisation/class/groupe.class.php');
dol_include_once('/organisation/class/evenement.class.php');
dol_include_once('/organisation/class/proposition.class.php');
dol_include_once('/organisation/class/programmation.class.php');
dol_include_once('/organisation/class/interpretation.class.php');
dol_include_once('/organisation/class/morceau.class.php');
dol_include_once('/viescolaire/class/affectation.class.php');
dol_include_once('/viescolaire/class/eleve.class.php');
dol_include_once('/management/class/agent.class.php');
dol_include_once('/organisation/class/instrument.class.php');
dol_include_once('/organisation/class/liaisoninstrument.class.php');
dol_include_once('/organisation/class/engagement.class.php');
dol_include_once('/organisation/lib/organisation_groupe.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("organisation@organisation", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$lineid   = GETPOST('lineid', 'int');

$action = GETPOST('action', 'aZ09');
$creneau = GETPOST('creneau', 'int');

$instru = GETPOST('Instru','alpha');

$engagement = GETPOST('engagement','alpha');

$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : str_replace('_', '', basename(dirname(__FILE__)).basename(__FILE__, '.php')); // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');
$idEngagement = GETPOST('idEngagement','int');

// Initialize technical objects
$object = new Groupe($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->organisation->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('groupecard', 'globalcard')); // Note that conf->hooks_modules contains array

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
	$permissiontoread = $user->rights->organisation->organisation->read;
	$permissiontoadd = $user->rights->organisation->organisation->write || $user->rights->organisation->programmation->writeProgrammation;
	$permissiontoaddProposition = $user->rights->organisation->programmation->writeProgrammation;

	// Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->organisation->organisation->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
	$permissionnote = $user->rights->organisation->organisation->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->organisation->organisation->write; // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}

$upload_dir = $conf->organisation->multidir_output[isset($object->entity) ? $object->entity : 1].'/groupe';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->organisation->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();

// include des actions, pour ne pas flooder le fichier
include DOL_DOCUMENT_ROOT.'/custom/organisation/core/actions/actions_groupe_organisation.inc.php';


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

	$backurlforlist = dol_buildpath('/organisation/groupe_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'addvar' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/organisation/groupe_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'ORGANISATION_GROUPE_MODIFY'; // Name of trigger action code to execute when we modify record

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
	$triggersendname = 'ORGANISATION_GROUPE_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_GROUPE_TO';
	$trackid = 'groupe'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}


if (GETPOST('res', 'aZ09') == "success") {
	setEventMessage('Engagement créé avec succès');
}

/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Groupe");
$help_url = '';
llxHeader('', $title, $help_url);


// Part to create
if ($action == 'create') {
	if (empty($permissiontoaddProposition)) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Groupe")), '', 'object_'.$object->picto);

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

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("Groupe"), '', 'object_'.$object->picto);

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

	$head = groupePrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("Groupe"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteGroupe'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}

	// Confirmation to delete
	if ($action == 'deleteEngagement') {
		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id.'&idEngagement='.GETPOST('idEngagement','int'), 'Supprimer un engagement', "Voulez-vous supprimer cet engagement du groupe? Il peut être rajouté par la suite si besoin", 'confirm_deleteEngagement', '', 0, 1);
	}

	// Confirmation to delete
	if ($action == 'deleteLiaison') {
		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id . '&idLiaisonInstru=' . GETPOST('idLiaisonInstru', 'int'), 'Supprimer un instrument', 'Voulez-vous supprimer cet instrument de l\'agent/élève? Il peut être rajouté par la suite si besoin', 'confirm_deleteInstru', '', 0, 1);
	}

	// Confirmation to delete
	if ($action == 'endEngagement') {
		$engagementClass = new Engagement($db);
		$engagementClass->fetch(GETPOST('idEngagement', 'int'));

		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id . '&idEngagement=' . $engagementClass->id, ($engagementClass->date_fin_engagement ? 'Ré-ouvrir' : 'Terminer')." un engagement", 'Voulez-vous '.($engagementClass->date_fin_engagement ? 'ré-ouvrir' : 'Terminer').' cet engagement?', 'confirm_endEngagement', '', 0, 1);
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
	$linkback = '<a href="'.dol_buildpath('/organisation/groupe_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'nom_groupe', $morehtmlref);


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

	// Fonction qui affiche les morceaux liés à cet artiste
	print $object->printPassageConcertGroupe();

	print '</div>';

	print '</div>';

	print '<div class="clearboth"></div>';

	print load_fiche_titre("Liste des engagements", '', 'fa-sign');

	// Appel de la fonction qui affiche le tableau des engagements
	$engagementClass = new Engagement($db);
	print $engagementClass->printListeEngagement($object->id);

	print '<span style="margin: 0.5em" class="badge  badge-status9 badge-status"><a href="'.DOL_URL_ROOT.'/custom/organisation/engagement_card.php?action=create&fk_groupe='.$object->id.'">+ Ajouter un membre</a></span><br>';
	if($object->fk_creneau)
	{
		print '<span style="margin: 0.5em" class="badge  badge-status9 badge-status"><a href="'.DOL_URL_ROOT.'/custom/organisation/groupe_card.php?id='.$object->id.'&creneau='.$object->fk_creneau.'&action=importAll">+ Importer tout les élèves</a></span>';
	}

	print dol_get_fiche_end();

	print '<hr>';
	// Buttons for actions
	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {
			if ($object->status == $object::STATUS_DRAFT) {

				print dolGetButtonAction($langs->trans('Groupe actif'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=confirm_validate&confirm=yes&token=' . newToken(), '', $permissiontoaddProposition);
				print dolGetButtonAction($langs->trans('Groupe à la retraite'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=desactivation&token='.newToken(), '', $permissiontoaddProposition);
			}

			if ($object->status == $object::STATUS_VALIDATED) {
				print dolGetButtonAction($langs->trans('Groupe en attente'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=confirm_setdraft&confirm=yes&token=' . newToken(), '', $permissiontoaddProposition);
				print dolGetButtonAction($langs->trans('Proposer à un concert'), '', 'default', DOL_URL_ROOT . '/custom/organisation/proposition_card.php?fk_groupe=' . $object->id . '&action=create&token=' . newToken(), '', $permissiontoaddProposition);
			}

			if ($object->status == $object::STATUS_CANCELED) {
				print dolGetButtonAction($langs->trans('Groupe en attente'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes&token='.newToken(), '', $permissiontoaddProposition);
			}

			if($object->status == $object::STATUS_DRAFT)
			{
				print dolGetButtonAction($langs->trans('Modifier'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=edit&token=' . newToken(), '', $permissiontoaddProposition);
			}

			print dolGetButtonAction($langs->trans('Supprimer le groupe'), '', 'delete', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=delete&token='.newToken(), '', $permissiontodelete || ($object->status == $object::STATUS_DRAFT && $permissiontoaddProposition));
		}
		print '</div>'."\n";
	}
}

// End of page
llxFooter();
$db->close();
