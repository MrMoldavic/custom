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
 *   	\file       famille_card.php
 *		\ingroup    viescolaire
 *		\brief      Page to create/edit/view famille
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

dol_include_once('/viescolaire/class/famille.class.php');
dol_include_once('/viescolaire/class/dictionary.class.php');
dol_include_once('/viescolaire/class/parents.class.php');
dol_include_once('/viescolaire/class/contribution.class.php');
dol_include_once('/viescolaire/lib/viescolaire_famille.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("viescolaire@viescolaire", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'famillecard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid   = GETPOST('lineid', 'int');
$parentId = GETPOST('id_parent','int');

if( $action == 'stateModify')
{
	$famille = new Famille($db);
	$sql = "UPDATE " . MAIN_DB_PREFIX . "famille SET status = " . GETPOST('stateInscription', 'int') . " WHERE rowid=" . $id;
	$resql = $db->query($sql);

	setEventMessage('Etat modifié avec succès!');
}


if ($action == 'deleteParent') {

	$sql = "DELETE FROM " . MAIN_DB_PREFIX . "parents WHERE rowid=".$parentId;
	$resql = $db->query($sql);

	if($resql)
	{
		setEventMessage('Parent supprimé avec succès');
	}else setEventMessage('Une erreur est survenue.', 'errors');


}


// Initialize technical objects
$object = new Famille($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->viescolaire->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('famillecard', 'globalcard')); // Note that conf->hooks_modules contains array

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

$upload_dir = $conf->viescolaire->multidir_output[isset($object->entity) ? $object->entity : 1].'/famille';

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

	$backurlforlist = dol_buildpath('/viescolaire/famille_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/viescolaire/famille_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'VIESCOLAIRE_FAMILLE_MODIFY'; // Name of trigger action code to execute when we modify record

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
	$triggersendname = 'VIESCOLAIRE_FAMILLE_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_FAMILLE_TO';
	$trackid = 'famille'.$object->id;
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

$title = $langs->trans("Famille");
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

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Famille")), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	/*if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}*/

	print dol_get_fiche_head(array(), '');

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

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
	print load_fiche_titre($langs->trans("Famille"), '', 'object_'.$object->picto);

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

	$head = famillePrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("Famille"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteFamille'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
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
	$linkback = '<a href="'.dol_buildpath('/viescolaire/famille_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';


	$morehtmlref = '<div class="refidno">';
	$morehtmlref .= '</div>';



	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'identifiant_famille', $morehtmlref);


	print '<div class="fichecenter">';

	print '<div class="fichehalfleft">';

	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';


	print '<form action="/custom/viescolaire/famille_card.php?id='.$object->id.'&action=stateModify" method="post">';
	print '<input type="hidden" name="id_eleve" value='.$object->id.'>';
	print '<input type="hidden" name="token" value='.newToken().'>';
	print load_fiche_titre("Etat du paiement", '', 'fa-euro');
	print '<table class="border centpercent">'."\n";

	print '<div class="center">';

	$infos=array(0 => "Non budgétisé", 1 => "Budgétisé", 7 => "En cours de paiement", 4 => "Payé", 8 => "Problème");

	print $form->selectarray('stateInscription',$infos,$object->status);
	print dol_get_fiche_end()."<br>";

	if($permissiontoadd) {
		print $form->buttonsSaveCancel("Valider état");
	}
	print '</div>';
	print '</table>'."\n";


	print '</form>';

	print '<hr>';


	print '</div>';

	print '</div>';

	//Listing des parents dans le card
	$parentsClass = new Parents($db);
	$result = $parentsClass->fetchBy(['lastname','firstname','phone','mail','address','town','zipcode','description','contact_preferentiel','fk_type_parent','rowid'],$object->id,'fk_famille');

	print load_fiche_titre("Liste des membres de la famille", '', 'fa-user');

	if($result != null)
	{
		print '<table class="noborder allwidth">';
		print '<tbody>';

		print '<tr class="liste_titre">
					<th class="wrapcolumntitle liste_titre">Prenom</th>
					<th class="wrapcolumntitle liste_titre">Nom</th>
					<th class="wrapcolumntitle liste_titre">Type de parent</th>
					<th class="wrapcolumntitle liste_titre">Contact préférentiel</th>
					<th class="wrapcolumntitle liste_titre">Téléphone</th>
					<th class="wrapcolumntitle liste_titre">Mail</th>
					<th class="wrapcolumntitle liste_titre">Address</th>
					<th class="wrapcolumntitle liste_titre">Précisions</th>
					<th class="wrapcolumntitle liste_titre">Supprimer</th>
					</tr>';
		foreach($result as $val)
		{
			$dictionaryClass = new Dictionary($db);
			if($val->fk_type_parent != null)
			{
				$typeParent = $dictionaryClass->fetchByDictionary('c_type_parent',['type','rowid'],$val->fk_type_parent,'rowid');
			}

			print '<tr class="oddeven">';
			print '<td><a href=' . DOL_URL_ROOT . '/custom/viescolaire/parents_card.php?id=' . $val->rowid . '&action=edit'. '>' .$val->firstname. '</td>';
			print "<td>".$val->lastname."</td>";
			print "<td>".($typeParent != null ? $typeParent->type : 'Type Inconnu')."</td>";
			print "<td><span class='badge  badge-status".($val->contact_preferentiel == 1 ? '4': '8')." badge-status'>".($val->contact_preferentiel == 1 ? 'Oui': 'Non').'</td>';
			print "<td>".$val->phone."</td>";
			print "<td>".$val->mail."</td>";
			print "<td>$val->address $val->zipcode $val->town</td>";
			print "<td>$val->description</td>";
			print "<td><a href=".$_SERVER['PHP_SELF']."?id=".$object->id."&id_parent=".$val->rowid."&action=deleteParent&token=".newToken().">Supprimer</a></td>";
			print '</tr>';
		}
		print '</tbody>';
		print '</table>';
	} else print "Aucun membre connu";

	print dolGetButtonAction($langs->trans('Ajouter un parent'), '', 'default', '/custom/viescolaire/parents_card.php?action=create&fk_famille='.$object->id.'&token='.newToken(), '', $permissiontoadd);


	$enfants = "SELECT * FROM ".MAIN_DB_PREFIX."eleve WHERE fk_famille = ".$object->id;
	$resqlEnfants = $db->query($enfants);
	$objEnfants = $db->fetch_object($resqlEnfants);


	print load_fiche_titre("Information cours des enfants", '', 'fa-school');

	print '<table class="noborder allwidth">'."\n";
	print '<tbody>';

	print '<tr>';
	print '<td><h4>Enfant<h4></td>';
	print '<td><h4>Souhait(s)<h4></td>';
	print '<td><h4>Affectation(s)<h4></td>';
	print '</tr>';

	foreach($resqlEnfants as $value)
	{
		print '<tr>';
		print '<td>'.'<a href="' . DOL_URL_ROOT . '/custom/viescolaire/eleve_card.php?id=' . $value['rowid'] . '">'.$value['nom'].' '.$value['prenom'].'</a>'.'<br>';

		print '<td>';

		$souhaits = "SELECT * FROM ".MAIN_DB_PREFIX."souhait WHERE fk_eleve = ".$value['rowid'];
		$resqlSouhait = $db->query($souhaits);
		$objSouhait = $db->fetch_object($resqlSouhait);
		$souhait = "";
		foreach($resqlSouhait as $value)
		{
			$souhait .= '<a href="' . DOL_URL_ROOT . '/custom/viescolaire/souhait_card.php?id=' . $value['rowid'] . '">' . $value['nom_souhait'] . '</a>'.'<br>';
		}

		print $souhait;

		print '</td>';
		print '<td>';
		$etat = "";
		foreach($resqlSouhait as $res)
		{
			if($res['status'] == 4)
			{
				$etat .= "&#9989;	Affecté"."<br>";
			}
			elseif($res['status'] == 9)
			{
				$etat .= "&#9209;&#65039;	Souhait désactivé"."<br>";
			}
			elseif($res['status'] == 0)
			{
				$etat .= "&#9888;&#65039;	Affectation manquante"."<br>";
			}
		}

		print $etat;

		print '</td>';
		print '</tr>';
	}
	print '</tbody>';
	print '</table>';

	//Listing des parents dans le card
	$contributionClass = new Contribution($db);
	$result = $contributionClass->fetchBy(['ref','fk_antenne','fk_annee_scolaire','montant_total','rowid'],$object->id,'fk_famille');


	print load_fiche_titre("Liste des cotisations", '', 'fa-euro');

	if($result != NULL)
	{
		print '<table class="noborder allwidth">';
		print '<tbody>';

		print '<tr class="liste_titre">
					<th class="wrapcolumntitle liste_titre">Référence de la contribution</th>
					<th class="wrapcolumntitle liste_titre">Antenne concernée</th>
					<th class="wrapcolumntitle liste_titre">Année scolaire concernée</th>
					<th class="wrapcolumntitle liste_titre">Montant</th>
					</tr>';
		foreach($result as $val)
		{
			print '<tr class="oddeven">';
			print '<td><a href=' . DOL_URL_ROOT . '/custom/viescolaire/contribution_card.php?id=' . $val->rowid . '&action=edit'. '>' .$val->ref. '</td>';
			print "<td>".$val->fk_antenne."</td>";
			print "<td>".$val->fk_annee_scolaire."</td>";
			print "<td>".$val->montant_total."</td>";

			print '</tr>';


		}
		print '</tbody>';
		print '</table>';
	}else print "Aucune contribution connue.";
	print dolGetButtonAction($langs->trans('Créer une contribution'), '', 'default', '/custom/viescolaire/contribution_card.php?action=create&fk_famille='.$object->id.'&token='.newToken(), '', $permissiontoadd);









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

			print dolGetButtonAction($langs->trans('Modifier la famille'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);
			// Delete (need delete permission, or if draft, just need create/modify permission)
			print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=delete&token='.newToken(), '', $permissiontodelete );
		}
		print '</div>'."\n";
	}


	// Select mail models is same action as presend
	/*if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	if ($action != 'presend') {
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		$includedocgeneration = 0;

		// Documents
		if ($includedocgeneration) {
			$objref = dol_sanitizeFileName($object->ref);
			$relativepath = $objref.'/'.$objref.'.pdf';
			$filedir = $conf->viescolaire->dir_output.'/'.$object->element.'/'.$objref;
			$urlsource = $_SERVER["PHP_SELF"]."?id=".$object->id;
			$genallowed = $permissiontoread; // If you can read, you can build the PDF to read content
			$delallowed = $permissiontoadd; // If you can create/edit, you can remove a file on card
			print $formfile->showdocuments('viescolaire:Famille', $object->element.'/'.$objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
		}

		// Show links to link elements
		$linktoelem = $form->showLinkToObjectBlock($object, null, array('famille'));
		$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


		print '</div><div class="fichehalfright">';

		$MAXEVENT = 10;

		$morehtmlcenter = dolGetButtonTitle($langs->trans('SeeAll'), '', 'fa fa-list-alt imgforviewmode', dol_buildpath('/viescolaire/famille_agenda.php', 1).'?id='.$object->id);

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($object, $object->element.'@'.$object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlcenter);

		print '</div></div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'famille';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->viescolaire->dir_output;
	$trackid = 'famille'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';*/
}

// End of page
llxFooter();
$db->close();
