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
dol_include_once('scolarite/class/classe.class.php');
dol_include_once('viescolaire/lib/viescolaire_souhait.lib.php');

dol_include_once('viescolaire/class/affectation.class.php');
dol_include_once('viescolaire/class/dictionary.class.php');
dol_include_once('management/class/agent.class.php');
dol_include_once('scolarite/class/creneau.class.php');
dol_include_once('scolarite/class/annee.class.php');
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
$affectationid = GETPOST('affectationid','int');

// include des actions, pour ne pas flooder le fichier (include et pas include_once)
include DOL_DOCUMENT_ROOT.'/custom/viescolaire/core/actions/actions_souhait_viescolaire.inc.php';

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

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Souhait")), '', 'fa-heart');

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


	print '<table class="border centpercent tableforfieldcreate">' . "\n";

	$anneeScolaire = new Annee($db);
	$anneeScolaire->fetch('','',' AND active=1 AND annee_actuelle=1');

	if (!GETPOSTISSET('fk_type_classe')) $_POST['fk_type_classe'] = 1;
	if (!GETPOSTISSET('fk_niveau')) $_POST['fk_niveau'] = 1;
	if (!GETPOSTISSET('fk_annee_scolaire')) $_POST['fk_annee_scolaire'] = $anneeScolaire->id;
	// Common attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';


	print '</table>' . "\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("Souhait"), '', 'fa-heart');

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

	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	if ($action == 'setdraft') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, 'Désaffecter le souhait', 'Cette action va désaffecter le souhait de son créneau actuel. Continuer?', 'confirm_setdraft', $formquestion, 'yes', 1);
	}

	if ($action == 'deleteAffectation') {
		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id . '&affectationid=' . GETPOST('affectationid', 'int'), 'Suppression d\'une affectation', 'Voulez-vous supprimer cette affectation? Ceci est irréversible.', 'confirm_delete_affectation', '', 0, 1);
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

	$morehtmlref = '<div class="refid">';
	$morehtmlref.= $object->returnFullNameSouhait();

	$etablissementClass = new Etablissement($db);
	$etablissement = $etablissementClass->fetchAll('','',1,0,['e.rowid'=>$object->fk_eleve],'',' INNER JOIN '.MAIN_DB_PREFIX.'classe as c ON t.rowid=c.fk_college INNER JOIN '.MAIN_DB_PREFIX.'eleve as e ON e.fk_classe_etablissement=c.rowid');

	$morehtmlref.= '<div class="refidno">';
	$morehtmlref.= $etablissement->nom;
	$morehtmlref .= '</div>';
	$morehtmlref .= '</div>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'none', $morehtmlref);


	print '<div class="fichecenter">';

	print '<div class="fichehalfleft">';

	print '<div class="underbanner clearboth"></div>';

	print '<table class="border centpercent tableforfield">' . "\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';
	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

	print '</table>';

	// partie pour afficher les infos du créneau actuel
	print load_fiche_titre('Créneau actuel', '', 'fa-sign');

	$creneauClass = new Creneau($db);
	$affectationClass = new Affectation($db);

	// Récupération des créneaux associés à l'objet en cours
	$actualCreneau = $creneauClass->fetchAll('', '', 1, 0, array('a.fk_souhait' => $object->id, 'a.status' => Affectation::STATUS_VALIDATED), 'AND', ' INNER JOIN ' . MAIN_DB_PREFIX . $affectationClass->table_element . ' as a ON t.rowid = a.fk_creneau');

	print '<div style="background-color: #FFFFFF; border: 1px solid #ddd;border-radius: 12px; padding: 20px; max-width: 60%;box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); ">';
	// Vérification si aucun créneau n'est affecté et le statut n'est pas annulé
	if (empty($actualCreneau) && $object->status !== Souhait::STATUS_CANCELED) {
		print '<span class="badge badge-status8 badge-status" style="color:white;">Aucun créneau affecté à ce souhait.</span>';
	} elseif ($object->status === Souhait::STATUS_CANCELED) {
		// Affichage pour un souhait annulé
		print '<span class="badge badge-status9 badge-status" style="color:white;">Souhait Désactivé</span>';
	} else {
		// Affichage des créneaux affectés
		foreach ($actualCreneau as $valueCreneau) {
			// Affichage du nom du créneau
			print 'Intitulé du cours : <h3 style="margin-left: 20px">' . htmlspecialchars($valueCreneau->nom_creneau) . '</h3>';

			// Affichage des professeurs associés au créneau
			print 'Professeur(s) :<br>';
			print $valueCreneau->printProfesseursFromCreneau($valueCreneau->id);

			print '<br>';
			// Bouton pour accéder à la page du cours
			print dolGetButtonAction('Page du cours', '', 'default', '/custom/scolarite/creneau_card.php?id=' . $valueCreneau->id, '', $permissiontoread);

			// Bouton pour désaffecter le souhait si le statut est validé
			if ($object->status === Souhait::STATUS_VALIDATED) {
				print dolGetButtonAction($langs->trans('Désaffecter'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=setdraft&token=' . newToken(), '', $permissiontoadd
				);
			}
		}
	}
	print '</div>';

	// partie pour afficher les infos d'anciens créneaux
	$anneeScolaire = new Annee($db);
	$anneeScolaires = $anneeScolaire->fetchAll('DESC,ASC','annee_actuelle,rowid',0,0,array('active'=>1));
	print '<hr>';

	print load_fiche_titre('Infos anciens créneaux pour ce souhait', '', 'fa-table');
	foreach($anneeScolaires as $val)
	{
		$affectationClass = new Affectation($db);
		$affectations = $affectationClass->fetchAll('','',0,0,array('t.fk_souhait'=>$object->id,'t.status'=>Affectation::STATUS_CANCELED,'s.fk_annee_scolaire'=>$val->id),'AND',' INNER JOIN '.MAIN_DB_PREFIX.$object->table_element.' as s ON t.fk_souhait=s.rowid');


		if(empty($affectations)) {
			print '<div style="background-color: #FFFFFF; border: 1px solid #ddd;border-radius: 12px; padding: 20px; max-width: 60%;box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); ">';
			print '<span class="badge badge-status8 badge-status" style="color:white;">Aucune affectation antérieure connue.</span>';
			print '</div>';
			break;
		} else {
			print '<div class="annee-accordion'.((int) $val->annee_actuelle === 1 ? '-opened' : '').'">';
			print '<h3><span class="badge badge-status4 badge-status">'.$val->annee.((int) $val->annee_actuelle === 1 ? ' - (année actuelle)' : '(année précédente)').'</span></h3>';

			print '<table class="tagtable liste table">';
			print '<tbody>';

			print '<tr class="liste_titre">
				<th class="wrapcolumntitle liste_titre">Jour</th>
				<th class="wrapcolumntitle liste_titre">Horaire</th>
				<th class="wrapcolumntitle liste_titre">Professeur</th>
				<th class="wrapcolumntitle liste_titre">Créé par, le</th>
				<th colspan="2"></th>
				</tr>';
			foreach($affectations as $value)
			{
				$creneauClass = new Creneau($db);
				$creneauClass->fetch($value->fk_creneau,'');

				if($creneauClass->id)
				{
					$resultJour = $dictionaryClass->fetchByDictionary('c_jour',['jour','rowid'],$creneauClass->jour,'rowid');

					print '<tr class="oddeven">';
					print "<td>$resultJour->jour</td>";
					print '<td>'.date('H:i',$creneauClass->heure_debut-3600).'h / '.date('H:i',$creneauClass->heure_fin-3600).'h</td>';
					print '<td>'.($creneauClass->printProfesseursFromCreneau($creneauClass->id) ? : 'Erreur professeurs').'</td>';

					$userClass = new User($db);
					if($value->fk_user_creat) {
						$userClass->fetch($value->fk_user_creat);
					}

					print '<td>'.($userClass->id ? $userClass->firstname.' '.$userClass->lastname.' le '.date('d/m/Y',$value->date_creation) : 'Erreur créateur affectation').'</td>';
					print '<td><a title="Voir le créneau" href="' . dol_buildpath('/custom/scolarite/creneau_card.php',1).'?id=' . $creneauClass->id .'">'.img_picto('', 'fa-eye', 'class="valignmiddle"').'</a></td>';
					print '<td><a  title="Supprimer l\'affectation" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&affectationid=' . $value->id . '&action=deleteAffectation">'.img_picto('', 'fa-trash', 'class="valignmiddle pictotitle"', $pictoisfullpath).'</a></td>';
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

			if((int)$conf->global->TIME_FOR_APPRECIATION)
			{
				print dolGetButtonAction($langs->trans('Faire appréciation de l\'élève'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=edit&token=' . newToken(), '', $permissionappreciation);
			}

			print dolGetButtonAction($langs->trans('Modifier le souhait'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=edit&token=' . newToken(), '', $permissiontoadd);

			if ($object->status === Souhait::STATUS_DRAFT) {
				print dolGetButtonAction($langs->trans('Cloner le souhait'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . (!empty($object->socid) ? '&socid=' . $object->socid : '') . '&action=clone&token=' . newToken(), '', $permissiontoadd);
				print dolGetButtonAction($langs->trans('Desactiver le souhait'), '', 'danger', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=desactivation&token=' . newToken(), '', $permissiontoadd);
			}

			if ($object->status === Souhait::STATUS_CANCELED) {
				print dolGetButtonAction($langs->trans('Activer le souhait'), '', 'danger', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=activation&token=' . newToken(), '', $permissiontoadd);
			}

			print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=delete&token=' . newToken(), '', $permissiontodelete);
		}
		print '</div>' . "\n";
	}

	if ($object->status === Souhait::STATUS_DRAFT) {

		$instruEnseigne = (int) $object->fk_instru_enseigne;
		$typeClasse = (int) $object->fk_type_classe;
		$etablissementDiminutif = substr($etablissement->diminutif, 0,2);

		$object = new Affectation($db, $etablissementDiminutif,$typeClasse, $instruEnseigne);

		print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '">';
		print '<input type="hidden" name="fk_souhait" value="' . $id . '">';
		print '<input type="hidden" name="token" value="' . newToken() . '">';
		print '<input type="hidden" name="action" value="newaffectation">';
		if ($backtopage) {
			print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
		}
		if ($backtopageforcancel) {
			print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
		}

		// Set some default values
		$_POST['fk_souhait'] = $id;
		$_POST['date_debutmonth'] = date('m', time());
		$_POST['date_debutday'] = date('d', time());
		$_POST['date_debutyear'] = date('Y', time());

		print dol_get_fiche_head(array(), '');


		print '<table class="border centpercent tableforfieldcreate">' . "\n";

		// Common attributes
		include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

		// Other attributes
		include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

		print '</table>' . "\n";

		print dol_get_fiche_end();

		print '<div id="div-creneau">';
		print $form->buttonsSaveCancel('Créer affectation');
		print '</div>';

		print '</form>';
	}


	print '<script src="../viescolaire/scripts/selectCreneaux.js" defer ></script>';
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
