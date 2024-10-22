<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2022 Baptiste Diodati
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
 *   	\file       cles_card.php
 *		\ingroup    scolarite
 *		\brief      Page to create/edit/view cles
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

dol_include_once('/scolarite/class/cles.class.php');
dol_include_once('/scolarite/class/attribution.class.php');
dol_include_once('/scolarite/class/annee.class.php');
dol_include_once('/scolarite/lib/scolarite_cles.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("scolarite@scolarite", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$lineid   = GETPOST('lineid', 'int');

$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : str_replace('_', '', basename(dirname(__FILE__)).basename(__FILE__, '.php')); // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');
$closeOrNot = GETPOST('closeOrNot','alpha');

// Initialize technical objects
$object = new Cles($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->scolarite->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('clescard', 'globalcard')); // Note that conf->hooks_modules contains array

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
	$permissiontoread = $user->rights->scolarite->scolarite->read;
	$permissiontoadd = $user->rights->scolarite->scolarite->create; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->scolarite->scolarite->delete;
	$permissionnote = $user->rights->scolarite->scolarite->create; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->scolarite->scolarite->create; // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}

$upload_dir = $conf->scolarite->multidir_output[isset($object->entity) ? $object->entity : 1].'/cles';

// Security check (enable the most restrictive one)
if (empty($conf->scolarite->enabled)) accessforbidden();
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

	$backurlforlist = dol_buildpath('/scolarite/cles_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/scolarite/cles_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'SCOLARITE_CLES_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// Actions to send emails
	$triggersendname = 'SCOLARITE_CLES_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_CLES_TO';
	$trackid = 'cles'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';


}

// Action avant Object car modifie des infos de l'object
include DOL_DOCUMENT_ROOT.'/custom/scolarite/scripts/actions/actions_cles_scolarite.inc.php';


/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Cles");
$help_url = '';
llxHeader('', $title, $help_url);


// Part to create
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Cles")), '', 'object_'.$object->picto);

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

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("Cles"), '', 'object_'.$object->picto);

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

	$head = clesPrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("Cles"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteCles'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
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

	if ($action == 'lostKey') {

		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'].'?id='.$object->id, 'Déclarer la clé perdue', 'Voulez-vous déclarer la clé comme perdue? Ceci va figer le prêt et rendre la clé indisponible.', 'confirm_key_lost', $formquestion, 0, 1);
	}

	if ($action == 'keyFound') {
		$formquestion = array();

		$attributionClass = new Attribution($db);
		$attributionClass->fetch('','',' AND fk_cle='.$id.' AND status ='.Attribution::STATUS_PROBLEME.' ORDER BY rowid DESC');

		if($attributionClass->id)
		{
			// Affiche une checkbox dans le formConfirm
			$formquestion = array(
				array('type' => 'checkbox', 'name' => 'closeOrNot', 'label' => "Clotûrer l'attribution actuelle?", 'value' => 0),
			);
		}

		// Définition des infos dans le formConfirm
		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'].'?id='.$object->id, 'Déclarer la clé retrouvée', 'Voulez-vous déclarer la clé comme retrouvée? Choisissez si le contrat doit être clos ou non.', 'confirm_key_found', $formquestion, 0, 1);
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
	$linkback = '<a href="'.dol_buildpath('/scolarite/cles_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	$morehtmlref .= '(numéro de clé)';
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'numero_cle', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';

	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';

	print load_fiche_titre('Attributions par année', '', 'fa-key');

	// Appel de la fonction qui affiche les attributions par années
	$attributionClass = new Attribution($db);
	/*$attributionClass->attributionsPerYear($object->id);*/

	// Récupération des années scolaires actives
	$anneeClass = new Annee($db);
	$resqlAnneeScolaire = $anneeClass->fetchAll('','',0,0,array('active'=>1));


	// Boucle sur chaque année scolaire récupérée
	foreach ($resqlAnneeScolaire as $value) {

		// Récupération des attributions pour l'année scolaire courante et la clé spécifiée
		$results = $attributionClass->fetchAll('DESC', 'rowid', 0, 0, ['fk_cle' => $object->id, 'fk_annee_scolaire' => $value->id]);
		// Ouverture du conteneur pour l'année scolaire
		print '<div class="annee-accordion' . ($value->annee_actuelle == 1 ? '-opened' : '') . '">';
		print '<h3><span class="badge badge-status4 badge-status">Année ' . htmlspecialchars($value->annee) . ($value->annee_actuelle != 1 ? ' (année précédente)' : '') . '</span></h3>';

		// Vérification de la présence des résultats
		if (!empty($results)) {
			// Début du tableau d'attributions
			print '<table class="tagtable liste">';
			print '<thead>';
			print '<tr class="liste_titre">
                <th class="wrapcolumntitle liste_titre">Prêtée à</th>
					<th class="wrapcolumntitle liste_titre">Etat</th>
					<th class="wrapcolumntitle liste_titre">Début</th>
					<th class="wrapcolumntitle liste_titre">Fin</th>
              </tr>';
			print '</thead>';
			print '<tbody>';

			// Boucle sur chaque attribution trouvée
			foreach ($results as $result) {
				$agentClass = new Agent($db);
				$agentClass->fetch($result->fk_user_pret);

				// Affichage des détails de chaque attribution
				print '<tr class="oddeven">';
				print '<td><a href="' . dol_buildpath('/custom/scolarite/attribution_card.php?id=' . $result->id, 1) . '">' . htmlspecialchars($agentClass->prenom . ' ' . $agentClass->nom) . '</a></td>';
				print '<td><span class="badge badge-status' . htmlspecialchars($result->status) . ' badge-status">' . htmlspecialchars($attributionClass->LibStatut($object->status)) . '</span></td>';
				print '<td>' . dol_print_date($result->date_debut_pret, 'day') . '</td>';
				print '<td>' . (!empty($result->date_fin_pret) ? dol_print_date($result->date_fin_pret, 'day') : 'Aucune date connue') . '</td>';
				print '</tr>';
			}
			print '</tbody>';
			print '</table>';
		} else {
			print '<p>Aucune attribution connue pour cette année scolaire.</p>';
		}

		// Fermeture du conteneur de l'année scolaire
		print '</div>';
	}


	print '</div>';

	// Si la clé est considérée comme perdue, on affiche sa date de perte
	if ($object->status == Cles::STATUS_PERDUE) {
		print '<div style="width: 50%">';
		print load_fiche_titre('Perte de la clé', '', 'fa-question');
		print '<h4 class="center">Clé perdue le : ' . date('d/m/Y', $object->tms) . '</h4>';
		print '<hr>';
		print '</div>';
	}

	print '</div>';

	print '<div class="clearboth"></div>';

	print dol_get_fiche_end();

	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {
			// Si le status de la clé n'est pas perdue, on affiche le bouton qui permet de la déclarée comme telle
			if($object->status != Cles::STATUS_PERDUE)
			{
				print dolGetButtonAction($langs->trans('Clé perdue'), '', 'danger', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=lostKey&token=' . newToken(), '', $permissiontoadd);
			}
			// Si le status de la clé n'est pas retrouvée, on affiche le bouton qui permet de la déclarée comme telle
			if($object->status == Cles::STATUS_PERDUE)
			{
				print dolGetButtonAction($langs->trans('Clé retrouvée'), '', 'danger', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=keyFound&token=' . newToken(), '', $permissiontoadd);
			}

			// Si la clé n'est ni perdue ni en cours de prêt, on permet la modif et l'attribution
			if($object->status != Cles::STATUS_VALIDATED && $object->status != Cles::STATUS_PERDUE)
			{
				print dolGetButtonAction($langs->trans('Attribuer la clé'), '', 'danger','/custom/scolarite/attribution_card.php?fk_cle=' . $object->id . '&action=create&token=' . newToken(), '', $permissiontoadd);
				print dolGetButtonAction($langs->trans('Modifier la clé'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=edit&token=' . newToken(), '', $permissiontoadd);
			}

			// Validate
			if ($object->status == Cles::STATUS_DRAFT) {
				if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {
					print dolGetButtonAction($langs->trans('Validate'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes&token='.newToken(), '', $permissiontoadd);
				} else {
					$langs->load("errors");
					print dolGetButtonAction($langs->trans("ErrorAddAtLeastOneLineFirst"), $langs->trans("Validate"), 'default', '#', '', 0);
				}
			}

			// Delete (need delete permission, or if draft, just need create/modify permission)
			print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=delete&token='.newToken(), '', $permissiontodelete);
		}
		print '</div>'."\n";
	}
}

// Script qui permet l'affichage des menus déroulants
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
