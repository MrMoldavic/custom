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
dol_include_once('/viescolaire/class/souhait.class.php');
dol_include_once('/viescolaire/class/parents.class.php');
dol_include_once('/scolarite/class/creneau.class.php');
dol_include_once('/viescolaire/class/dictionary.class.php');
dol_include_once('/viescolaire/class/inscription.class.php');
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
$inscriptionid = GETPOST('inscriptionid','int');


// include des actions, pour ne pas flooder le fichier (include et pas include_once)
include DOL_DOCUMENT_ROOT.'/custom/viescolaire/core/actions/actions_eleve_viescolaire.inc.php';

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
if (empty($conf->viescolaire->enabled)) accessforbidden();

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

// Action pour confirmer la suppression d'une inscription
include DOL_DOCUMENT_ROOT.'/custom/viescolaire/core/actions/actions_eleveInscription_viescolaire.inc.php';

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
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, 'Suppréssion d\'un élève', 'Voulez-vous supprimer cet élève? Cette action est irréversible.', 'confirm_delete', '', 0, 1);
	}
	if ($action == 'desactivation') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, 'Désactivation d\'un élève', 'Voulez-vous désactiver cet élève? Cette action est réversible sur le card de l\'élève.', 'confirm_desactivation', '', 0, 1);
	}
	if ($action == 'activation') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, 'Activation d\'un élève', 'Voulez-vous activer cet élève? Cette action est réversible sur le card de l\'élève.', 'confirm_activation', '', 0, 1);
	}
	if ($action == 'deleteInscription') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&inscriptionid='.GETPOST('inscriptionid','int'), 'Suppression d\'une inscription', 'Voulez-vous supprimer cette inscription? Ceci est irréversible.', 'confirm_delete_inscription', '', 0, 1);
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
	$morehtmlref .= '</div>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'nom', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';


	print '<div class="underbanner clearboth"></div>';


	print '<table class="border centpercent tableforfield">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';

	// Partie qui affiche les informations des inscriptions d'un élève
	if($object->status !== Eleve::STATUS_CANCELED)
	{
		print load_fiche_titre('Etats des inscriptions', '', 'fa-pen');

		$inscriptionClass = new Inscription($db);

		$dictionaryClass = new Dictionary($db);
		$resqlAnneeScolaire = $dictionaryClass->fetchByDictionary('c_annee_scolaire', ['rowid', 'annee', 'annee_actuelle'], 0, '', ' active = 1 ORDER BY rowid DESC');

		foreach ($resqlAnneeScolaire as $value) {
			$results = $inscriptionClass->fetchAll('', '', 0, '', ['fk_eleve' => $object->id, 'fk_annee_scolaire' => $value->rowid]);

			print '<div class="annee-accordion' . ((int) $value->annee_actuelle === 1 ? '-opened' : '') . '">';
			print '<h3><span class="badge badge-status4 badge-status">Année ' . $value->annee . ((int) $value->annee_actuelle === 1 ? ' (année actuelle)' : ' (année précédente)') . '</span></h3>';

			if (count($results) > 0) {
				print '<table class="tagtable liste">';
				print '<tbody>';

				print '<tr class="liste_titre">
					<th class="wrapcolumntitle liste_titre">Année scolaire</th>
					<th class="wrapcolumntitle liste_titre">Etat</th>
					<th class="wrapcolumntitle liste_titre">Crée par</th>
					<th class="wrapcolumntitle liste_titre">Le</th>
					<th class="wrapcolumntitle liste_titre"></th>
					</tr>';

				print '</tbody>';
				foreach ($results as $result) {
					print '<tr class="oddeven">';
					print '<td>' . $dictionaryClass->fetchByDictionary('c_annee_scolaire', ['annee'], $result->fk_annee_scolaire, 'rowid')->annee . '</td>';
					print '<td><span class="badge badge-status' . $result->status . ' badge-status">' . $inscriptionClass->LibStatut($result->status) . '</span></td>';

					$userClass = new User($db);
					$userClass->fetch($result->fk_user_creat);

					print "<td>$userClass->firstname $userClass->lastname</td>";
					print '<td>' . date('d/m/Y', $result->date_creation) . '</td>';
					print '<td>';
					print '<a href="../viescolaire/inscription_card.php?id=' . $result->id . '&action=edit">' . img_picto('', 'fa-pen', 'class="valignmiddle"', $pictoisfullpath) . '️</a> ';
					print '<a href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&inscriptionid=' . $result->id . '&action=deleteInscription">' . img_picto('', 'fa-trash', 'class="valignmiddle"', $pictoisfullpath) . '</a>';
					print '</td>';
					print '</tr>';
				}
				unset($result);
				print '</table>';
			} else {
				print '<p>Aucune inscription connue pour cette année scolaire.</p>';
			}

			print '</div>';
		}
	}

	print '</div>';

	print '</div>';


	print '<div class="clearboth"></div>';
	print '<hr>';
	print load_fiche_titre('Liste des parents', '', 'fa-user');

	// Partie affichant les informations de la famille d'un élève
	if($object->fk_famille)
	{
		$dictionaryClass = new Dictionary($db);

		$parentsClass = new Parents($db);
		$parents = $parentsClass->fetchAll('','',0,0,['fk_famille'=>$object->fk_famille],'AND');

		print '<table class="noborder allwidth">';
		print '<tbody>';

		print '<tr class="liste_titre">
					<th class="wrapcolumntitle liste_titre">Prénom</th>
					<th class="wrapcolumntitle liste_titre">Nom</th>
					<th class="wrapcolumntitle liste_titre">Type de parent</th>
					<th class="wrapcolumntitle liste_titre">Contact préférentiel ?</th>
					<th class="wrapcolumntitle liste_titre">Téléphone</th>
					<th class="wrapcolumntitle liste_titre">Mail</th>
					<th class="wrapcolumntitle liste_titre">Adresse</th>
					<th class="wrapcolumntitle liste_titre">Précisions</th>
					</tr>';

		foreach ($parents as $parent)
		{
			if ($parent->fk_type_parent) {
				$typeParent = $dictionaryClass->fetchByDictionary('c_type_parent', ['type', 'rowid'], $parent->fk_type_parent, 'rowid');
			}

			print '<tr class="oddeven">';
			print '<td><a href=' . dol_buildpath('/custom/viescolaire/parents_card.php',1) . '?id=' . $parent->id . '&action=edit' . '>' . $parent->firstname . '</td>';
			print '<td>' . $parent->lastname . '</td>';
			print '<td>' . ($typeParent && $parent->fk_type_parent  ? $typeParent->type : 'Type Inconnu') . '</td>';
			print "<td><span class='badge  badge-status" . ($parent->contact_preferentiel ? 4 : 8) . " badge-status'>" . ($parent->contact_preferentiel ? 'Oui' : 'Non') . '</td>';
			print '<td>' . $parent->phone . '</td>';
			print '<td>' . $parent->mail . '</td>';
			print "<td>$parent->address $parent->zipcode $parent->town</td>";
			print "<td>$parent->description</td>";
			print '</tr>';
		}

		print '</tbody>';
		print '</table>';
	} else {
		print '<h2><span class="badge badge-danger">Sans Famille liée '.img_picto('', 'fa-warning', '').'</span></h2>';
	}

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {

			if($object->status !== Eleve::STATUS_CANCELED)
			{
				print dolGetButtonAction($langs->trans('Engager dans un groupe'), '', '', dol_buildpath('/custom/organisation/engagement_card.php',1).'?fk_eleve='.$object->id.'&action=create' , '', $permissiontoadd);
				print dolGetButtonAction($langs->trans('Modifier l\'élève'), '', '', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);
			}
			print '<br>';

			print dolGetButtonAction($langs->trans('Ajouter une inscription'), '', 'delete', dol_buildpath('/custom/viescolaire/inscription_card.php',1).'?fk_eleve='.$object->id.'&action=create&token=' . newToken(), '', $permissiontoadd);

			if($object->status === Eleve::STATUS_CANCELED)
			{
				print dolGetButtonAction($langs->trans('Activer l\'élève'), '', 'delete', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=activation&token=' . newToken(), '', $permissiontoadd);
			}
			else print dolGetButtonAction($langs->trans('Desactiver l\'élève'), '', 'delete', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=desactivation&token=' . newToken(), '', $permissiontoadd);

			print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=delete&token='.newToken(), '', $permissiontodelete);

		}
		print '</div>'."\n";
	}

	// Patie qui affiche les souhaits et leurs états par années scolaires
	if($object->status !== Eleve::STATUS_CANCELED && $object->status !== Eleve::STATUS_ABANDON)
	{
		print '<hr>';
		print load_fiche_titre("Liste des souhait de l'élève", '', 'fa-school');

		print '<div style="margin: 20px">'.dolGetButtonAction('Ajouter un souhait', '', 'default', '/custom/viescolaire/souhait_card.php'.'?action=create&fk_eleve='.$object->id, '', $permissiontoadd).'</div>';

		$souhaitClass = new Souhait($db);
		$souhaitsEleve = $souhaitClass->fetchAll('','','','',array('fk_eleve'=>$object->id));

		if(count($souhaitsEleve) === 0)
		{
			print '<p>Aucun souhaits connus pour cette année scolaire.</p>';
		}
		else
		{
			$anneeClass = new Annee($db);
			$annees = $anneeClass->fetchAll('DESC,ASC','annee_actuelle,rowid',0,0,['active'=>1],'AND');

			foreach($annees as $value)
			{
				$souhaitClass = new Souhait($db);
				$souhaits = $souhaitClass->fetchAll('ASC','status',0,0,['fk_eleve'=>$object->id,'fk_annee_scolaire'=>$value->id],'AND');

				print '<div class="annee-accordion'.($value->annee_actuelle === 1 ? '-opened' : '').'">';
				print '<h3><span class="badge badge-status4 badge-status">'.$value->annee.($value->annee_actuelle !== 1 ? ' (année précédente)' : ' (année actuelle)').'</span></h3>';

				if(count($souhaits) > 0)
				{
					print '<table class="tagtable liste">';
					print '<tbody>';

					print '<tr class="liste_titre">
					<th class="wrapcolumntitle liste_titre">Souhait</th>
					<th class="wrapcolumntitle liste_titre">Etat</th>
					<th class="wrapcolumntitle liste_titre">Créneau</th>
					<th class="wrapcolumntitle liste_titre">Affecté par, le</th>
					</tr>';

					foreach($souhaits as $souhait)
					{
						print '<tr class="oddeven">';
						print '<td><a href="' . dol_buildpath('/custom/viescolaire/souhait_card.php',1) . '?id=' . $souhait->id. '">' .'- ' . $souhait->returnFullNameSouhait().'</a>'.($value->annee_actuelle !== 1 ? ' <span class="badge  badge-status'.($souhait->details !== ''  ? 4 : 8).' badge-status" style="color:white;">'.($souhait->details !== '' && getDolGlobalString('TIME_FOR_APPRECIATION', '') ? 'Appréciation Faite' : 'Appréciation manquante') : '').'</span></td>';

						if($souhait->status === Souhait::STATUS_VALIDATED)
						{
							$creneauClass = new Creneau($db);
							$objectCreneau = $creneauClass->getCreneauxFromSouhaits($souhait->id,$souhait->status);

							print '<td><span class="badge  badge-status4 badge-status" style="color:white;">Affecté</span></td>';
							print '<td><a href="'.dol_buildpath('/custom/scolarite/creneau_card.php',1).'?id='.$objectCreneau->rowid.'">'.$objectCreneau->nom_creneau.'</a></td>';

							$userClass = new User($db);
							$userClass->fetch($objectCreneau->fk_user_creat);

							$date = new DateTime($objectCreneau->date_creation);
							$dateFormat = $date->format('d/m/Y');

							print "<td>$userClass->firstname $userClass->lastname, ".$dateFormat.'</td>';
						}
						else
						{
							print '<td><span class="badge badge-status'.($souhait->status === Souhait::STATUS_CANCELED ? '8' : '1').' badge-status" style="color:white;">'.($souhait->status === Souhait::STATUS_CANCELED ? 'Souhait désactivé' : 'En attente d\'affectation').'</span></td>';
							print '<td>Aucun créneau</td>';
							print '<td>Aucune info</td>';
						}
						print '</tr>';
					}
					print '</tbody>';
					print '</table>';
				}
				else
				{
					print '<p>Aucun souhaits connus pour cette année scolaire.</p>';
				}

				print '</div>';
			}
		}
	}


	print dol_get_fiche_end();

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

}

// End of page
llxFooter();
$db->close();
