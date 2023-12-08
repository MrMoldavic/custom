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
 *   	\file       contribution_card.php
 *		\ingroup    viescolaire
 *		\brief      Page to create/edit/view contribution
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
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("MAIN_SECURITY_FORCECSP"))   define('MAIN_SECURITY_FORCECSP', 'none');	// Disable all Content Security Policies
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
require_once DOL_DOCUMENT_ROOT.'/don/class/don.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/subscription.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

dol_include_once('/viescolaire/class/dictionary.class.php');
dol_include_once('/viescolaire/class/contribution.class.php');
dol_include_once('/viescolaire/lib/viescolaire_contribution.lib.php');

include_once (DOL_DOCUMENT_ROOT.'/adherents/class/subscription.class.php');

// Load translation files required by the page
$langs->loadLangs(array("viescolaire@viescolaire", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$lineid   = GETPOST('lineid', 'int');
$montant   = GETPOST('montant', 'int');
$fk_famille = GETPOST('fk_famille', 'int');
$fk_adherent = GETPOST('fk_adherent', 'int');
$parentId = GETPOST('parentId', 'int');

$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : str_replace('_', '', basename(dirname(__FILE__)).basename(__FILE__, '.php')); // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');

$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');

// Initialize technical objects
$object = new Contribution($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->viescolaire->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('contributioncard', 'globalcard')); // Note that conf->hooks_modules contains array

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
	$permissiontodelete = $user->rights->viescolaire->eleve->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
	$permissionnote = $user->rights->viescolaire->eleve->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->viescolaire->eleve->write; // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}


$upload_dir = $conf->viescolaire->multidir_output[isset($object->entity) ? $object->entity : 1].'/contribution';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->module, $object->id, $object->table_element, $object->element, 'fk_soc', 'rowid', $isdraft);
if (!isModEnabled("viescolaire")) {
	accessforbidden();
}
if (!$permissiontoread) {
	accessforbidden();
}


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

	$backurlforlist = dol_buildpath('/viescolaire/contribution_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/viescolaire/contribution_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'VIESCOLAIRE_CONTRIBUTION_MODIFY'; // Name of trigger action code to execute when we modify record

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
	$triggersendname = 'VIESCOLAIRE_CONTRIBUTION_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_CONTRIBUTION_TO';
	$trackid = 'contribution'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}

if ($action == 'updateline')
{
	$error = 0;
	if($montant > $object->montant_total)
	{
		setEventMessages('Le montant reseigné est supérieur au montant total' , null, 'errors');
		$error++;
	}

	$contributionContentClass = new ContributionContent($db);

	$allLines = $contributionContentClass->fetchAll('ASC','rowid','','',['customsql'=>"fk_contribution = $object->id AND rowid != $lineid AND montant != 0" ]);


	$countTotal = 0;
	$count =0;
	foreach ($allLines as $oneLine)
	{
		$countTotal += $oneLine->montant;
		$count++;
	}


	if(intval($countTotal) + intval($montant) > intval($object->montant_total))
	{
		setEventMessages('Le montant renseigné est supérieur au montant restant possible' , null, 'errors');
		$error++;
	}

	if($error == 0)
	{
		$contributionContentClass = new ContributionContent($db);
		$contributionContentClass->fetch($lineid);
		$contributionContentClass->montant = $montant;
		$result = $contributionContentClass->update($user);
	}


	if ($result && $error == 0) {
		setEventMessages('Ligne de produit mise à jour' , null);
	}
}

if ($action == 'addLine')
{
	$errors = new stdClass();
	$errors->message = [];
	$contributionContentClass = new ContributionContent($db);
	$res = $contributionContentClass->fetchAll('ASC','rowid','','',['fk_contribution'=>$object->id,"fk_adherent"=>$fk_adherent,'fk_type_contribution_content'=>GETPOST('fk_type_contribution_content','int')]);

	/*foreach($res as $val)
	{
		$errors->nb++;
		$errors->message[] .= 'Un Don/facture existe déjà pour cet adhérent. Veuillez éditer celui existant';
	};*/

	if ($errors->nb == 0)
	{
		$contributionContentClass->fk_contribution = $object->id;
		$contributionContentClass->fk_type_contribution_content = GETPOST('fk_type_contribution_content','int');
		$contributionContentClass->montant = 0;
		$contributionContentClass->fk_adherent = $fk_adherent;
		$result = $contributionContentClass->create($user);

		if($result > 0)
		{
			setEventMessages('Ligne ajoutée' , null);
		}
		else $errors->message[] .= 'Une erreur est survenue lors de l\'ajout en base.';
	}
	print setEventMessages($errors->message , null, 'errors');
}

if($action == "addSubscription")
{
	$dateActuelle = new DateTime();
	// Vérifiez si nous sommes avant le 1er septembre
	if ($dateActuelle->format('n') < 9) {
		// Si oui, ajustez l'année à l'année précédente
		$dateAdhesion = new DateTime('01/09/' . ($dateActuelle->format('Y') - 1));
	} else {
		// Sinon, utilisez simplement l'année actuelle
		$dateAdhesion = new DateTime('01/09/' . $dateActuelle->format('Y'));
	}

	if($montant == 0)
	{
		$contributionContentClass = new ContributionContent($db);
		//$contributionContentClass->fetch($lineid);

		$existingSubscription = $contributionContentClass->fetchExistingSubscriptionForContributionContent($fk_adherent,$lineid,$montant);
		if(!$existingSubscription)
		{
			$subscriptionClass = new Subscription($db);
			$subscriptionClass->fk_adherent = $fk_adherent;
			$subscriptionClass->subscription = $montant;
			$subscriptionClass->fk_contribution_content = $lineid;
			$subscriptionClass->fk_bank = null;
			$subscriptionClass->dateh = $dateAdhesion->format('Y-d-m');
			$subscriptionClass->datef = $dateAdhesion->format(($dateActuelle->format('Y') + 1).'-08-31');

			$subscriptionClass->datec = date('Y-m-d H:i:s');
			$subscriptionClass->note = "";

			$result = $subscriptionClass->create($user);

			if($result > 0)
			{
				$adherentClass = new Adherent($db);
				$adherentClass->fetch($fk_adherent);
				$adherentClass->datefin = $subscriptionClass->datef;
				$adherentClass->update($user);

				setEventMessages('Cotisation payée avec succès!' , null);
			} else setEventMessages('Une erreur est survenue', null, 'errors');
		}

	}
	else{


		$action = "newSubscription";
		header('Location: ../../adherents/subscription.php?rowid='.$fk_adherent.'&action=addsubscription&subscription='.$montant.'&paymentsave=bankdirect&fk_contribution_content='.$lineid.'&contribution_id='.$id.'&fromContribution=1&reday=01&remonth=09&reyear='.$dateAdhesion->format('Y').'&endday=31&endmonth=08&endyear='.($dateAdhesion->format('Y')+1));
		die;
	}
}

if($action == 'addParents'){

	// On va chercher tout les parents actuels dans la famille
	$parentsClass = new Parents($db);
	$allParents = $parentsClass->fetchAll('rowid','rowid',0,0,['fk_famille'=>$object->fk_famille],'AND');

	$error = 0;
	foreach ($allParents as $parentUnique)
	{
		if(!$parentUnique->fk_adherent)
		{
			// On lui crée un adhérent
			$parentUnique->fetch($parentUnique->rowid);

			$familleClass= new Famille($db);
			$familleClass->fetch($parentUnique->fk_famille);

			$etablissementClass = new Etablissement($db);
			$etablissementClass->fetch($familleClass->fk_antenne);

			$adherentClass = new Adherent($db);
			$adherentClass->ref = $parentUnique->rowid.'-(Ref Provisoire)';
			$adherentClass->typeid = ($etablissementClass->fk_type_adherent ? : null);
			$adherentClass->firstname = $parentUnique->firstname;
			$adherentClass->lastname = $parentUnique->lastname;
			$adherentClass->morphy = "phy";
			$adherentClass->statut = 1;
			$result = $adherentClass->create($user);

			if($result <= 0) $error++;
			else{
				$parentUnique->fk_adherent = $result;
				$parentUnique->update($user);
			}
		}

		$contributionContentClass = new ContributionContent($db);
		// On va chercher toute ses contributions
		$existingContributionContent = $contributionContentClass->fetchAll('rowid','rowid',0,0,['fk_adherent'=>$parentUnique->fk_adherent,'fk_contribution'=>$id,'fk_type_contribution_content'=>0]);

		if(count($existingContributionContent) == 0)
		{
			$contributionContentClass = new ContributionContent($db);
			$contributionContentClass->fk_contribution = $id;
			$contributionContentClass->fk_type_contribution_content = "Adhésion";
			$contributionContentClass->montant = 0;
			$contributionContentClass->fk_type_adherent = 1;
			$contributionContentClass->fk_subscription = null;
			$contributionContentClass->fk_adherent = $parentUnique->fk_adherent;
			$resContribution = $contributionContentClass->create($user);

			if($resContribution <= 0) $error++;

		}
	}

	if($error == 0) setEventMessage('Ajout du parent et de sa contribution avec succès!');
	else setEventMessage("$error erreur(s) sont survenue(s)",'errors');
}


if($action == 'confirmCreateFacture'){

	// On va chercher tout les parents actuels dans la famille

	$parentsClass = new Parents($db);
	$etablissementClass = new Etablissement($db);
	$familleClass = new Famille($db);

	$parentsClass->fetch($parentId);
	$familleClass->fetch($parentsClass->fk_famille);
	$etablissementClass->fetch($familleClass->fk_antenne);

	// Si le parent n'a ps de tiers lié
	if(!$parentsClass->fk_tiers)
	{
		$societe = new Societe($db);
		$societe->nom = "$parentsClass->firstname $parentsClass->lastname";
		$societe->fk_pays = 1;
		$societe->client = 1;
		$societe->code_client = -1;
		$resultTiers = $societe->create($user);

		if($resultTiers > 0)
		{
			$societe = new Societe($db);
			$societe->fetch($resultTiers);
			$societe->client = 1;
			$res = $societe->update(0, $user);

			if($res > 0)
			{
				$parentsClass->fk_tiers = $resultTiers;
				$parentsClass->update($user);
			}
		}
	}
	$contributionContentClass = new ContributionContent($db);
	$contributionContentClass->fetch($lineid);

	// On crée la facture
	$factureClass = new Facture($db);
	$factureClass->socid = $parentsClass->fk_tiers;

	$factureClass->date = date('Y-m-d');
	$res = $factureClass->create($user);

	// On importe la description du produit
	$productClass = new Product($db);
	$productClass->fetch($etablissementClass->fk_service);

	// On crée la ligne de la facture avec ses infos
	$factureDetClass = new FactureLigne($db);
	$factureDetClass->fk_facture = $res;
	$factureDetClass->fk_product = $productClass->rowid;
	$factureDetClass->remise_percent = 0;
	$factureDetClass->total_tva = 0;
	$factureDetClass->total_ht = $contributionContentClass->montant;
	$factureDetClass->total_ttc = $contributionContentClass->montant;
	$factureDetClass->desc = "Contribution - $productClass->label";
	$factureDetClass->qty = 1;
	$resFacture = $factureDetClass->insert();

	// Si aucune erreur, on update avec le montant HT et TTC (pas ajoutable avec create)
	if($res > 0 && $resFacture > 0)
	{
		$factureClass->fetch($res);
		$factureClass->total_ht = $contributionContentClass->montant;
		$factureClass->total_ttc = $contributionContentClass->montant;
		$factureClass->array_options= array('options_contribution_content'=>$contributionContentClass->id,'options_fk_object'=>$resFacture);
		$factureClass->update($user);
		$factureClass->insertExtraFields();

		header('Location: ../../compta/facture/card.php?facid='.$res.'&contributionId='.$id);
	}else setEventMessage("Une erreur est survenue",'errors');

}


/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);
$dictionaryClass = new Dictionary($db);

$title = $langs->trans("Contribution");
$help_url = '';
llxHeader('', $title, $help_url);


// Part to create
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden('NotEnoughPermissions', 0, 1);
	}
	$backtopageforcancel = $_SERVER['HTTP_HOST']."/custom/viescolaire/famille_card.php?id=".GETPOST('fk_famille','int');

	print load_fiche_titre('Nouvelle contribution', '', 'fa-euro');

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
	if (! GETPOSTISSET('fk_famille')) $_POST['fk_famille'] = GETPOST('fk_famille');
	$actualSchoolYear = $dictionaryClass->fetchByDictionary('c_annee_scolaire', ['annee','rowid'], 1, 'annee_actuelle');

	$_POST['fk_annee_scolaire'] = $actualSchoolYear->rowid;
	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';

	//dol_set_focus('input[name="fk_annee_scolaire"]');
}


// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("Contribution"), '', 'object_'.$object->picto);

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
	$head = contributionPrepareHead($object);

	print dol_get_fiche_head($head, 'card', $langs->trans("Contribution"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteContribution'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}

	if($action == 'createFacture')
	{
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid.'&parentId='.$parentId, 'Créer une facture', 'Voulez-vous créer une facture pour cette ligne?', 'confirmCreateFacture', '', 0, 1);
	}

	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// Confirmation of action xxxx (You can use it for xxx = 'close', xxx = 'reopen', ...)
	if ($action == 'xxx') {
		$text = $langs->trans('ConfirmActionContribution', $object->ref);
		/*if (isModEnabled('notification'))
		{
			require_once DOL_DOCUMENT_ROOT . '/core/class/notify.class.php';
			$notify = new Notify($db);
			$text .= '<br>';
			$text .= $notify->confirmMessage('CONTRIBUTION_CLOSE', $object->socid, $object);
		}*/

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
	$linkback = '<a href="'.dol_buildpath('/viescolaire/contribution_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	/*
		// Ref customer
		$morehtmlref .= $form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, $usercancreate, 'string', '', 0, 1);
		$morehtmlref .= $form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, $usercancreate, 'string'.(isset($conf->global->THIRDPARTY_REF_INPUT_SIZE) ? ':'.$conf->global->THIRDPARTY_REF_INPUT_SIZE : ''), '', null, null, '', 1);
		// Thirdparty
		$morehtmlref .= '<br>'.$object->thirdparty->getNomUrl(1, 'customer');
		if (empty($conf->global->MAIN_DISABLE_OTHER_LINK) && $object->thirdparty->id > 0) {
			$morehtmlref .= ' (<a href="'.DOL_URL_ROOT.'/commande/list.php?socid='.$object->thirdparty->id.'&search_societe='.urlencode($object->thirdparty->name).'">'.$langs->trans("OtherOrders").'</a>)';
		}
		// Project
		if (isModEnabled('project')) {
			$langs->load("projects");
			$morehtmlref .= '<br>';
			if ($permissiontoadd) {
				$morehtmlref .= img_picto($langs->trans("Project"), 'project', 'class="pictofixedwidth"');
				if ($action != 'classify') {
					$morehtmlref .= '<a class="editfielda" href="'.$_SERVER['PHP_SELF'].'?action=classify&token='.newToken().'&id='.$object->id.'">'.img_edit($langs->transnoentitiesnoconv('SetProject')).'</a> ';
				}
				$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'].'?id='.$object->id, $object->socid, $object->fk_project, ($action == 'classify' ? 'projectid' : 'none'), 0, 0, 0, 1, '', 'maxwidth300');
			} else {
				if (!empty($object->fk_project)) {
					$proj = new Project($db);
					$proj->fetch($object->fk_project);
					$morehtmlref .= $proj->getNomUrl(1);
					if ($proj->title) {
						$morehtmlref .= '<span class="opacitymedium"> - '.dol_escape_htmltag($proj->title).'</span>';
					}
				}
			}
		}
	*/
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


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

	print '<tr><td class="titlefield">';
	print "Montant total";
	print '</td><td colspan="3" class="amountpaymentcomplete">';


	/*$allLines = $object->getLinesArray();

	$countTotal = 0;
	foreach ($allLines as $oneLine)
	{
		$countTotal += $oneLine->montant;
	}*/
	print '<span style="color : '.($object->getTotalAmountOfContent() == $object->montant_total ? "green" : "darkgrey").'">'.price($object->getTotalAmountOfContent(), 1, '', 0, -1, -1, $conf->currency).' / '.price($object->montant_total, 1, '', 0, -1, -1, $conf->currency).'</span>';
	print '</td></tr>';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';

	print dol_get_fiche_end();


	/*
	 * Lines
	 */
	if (!empty($object->table_element_line)) {
		// Show object lines
		print '<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action != 'editline') ? '#addline' : '#line_'.GETPOST('lineid')).'" method="POST">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="'.(($action != 'editline') ? 'addline' : 'updateline').'">';
		print '<input type="hidden" name="mode" value="">';
		print '<input type="hidden" name="id" value="'.$object->id.'">';

		print '<div class="div-table-responsive-no-min">';
		print '<table id="tablelines" class="noborder noshadow" width="100%">';
		print '<tr class="liste_titre">';
		print '<td>Adhérent</td>';
		print '<td>Type d\'adhérent</td>';
		print '<td>Type de contribution</td>';
		print '<td>Valeur totale</td>';
		print '<td></td>'; // Empty column for edit and remove button
		print '<td></td>'; // Empty column for edit and remove button
		print '</tr>';

		$ret = $object->printObjectLines($action, $lineid);

		print '</table>';
		print '</form>';

		dol_fiche_end();
	}

	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {
			// Send

			// Back to draftss
			if ($object->status == $object::STATUS_VALIDATED) {
				print dolGetButtonAction('', $langs->trans('SetToDraft'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes&token='.newToken(), '', $permissiontoadd);
			}

			print dolGetButtonAction('', "Ajouter les parents manquants", 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=addParents&token='.newToken(), '', $permissiontoadd);

			print dolGetButtonAction('', $langs->trans('Modify'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);

			// Validate
			if ($object->status == $object::STATUS_DRAFT && $object->getTotalAmountOfContent() == $object->montant_total) {
				if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {
					print dolGetButtonAction('', $langs->trans('Validate'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes&token='.newToken(), '', $permissiontoadd);
				} else {
					$langs->load("errors");
					print dolGetButtonAction($langs->trans("ErrorAddAtLeastOneLineFirst"), $langs->trans("Validate"), 'default', '#', '', 0);
				}
			}

			// Delete
			$params = array();
			print dolGetButtonAction('', $langs->trans("Delete"), 'delete', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=delete&token='.newToken(), 'delete', $permissiontodelete, $params);
		}
		print '</div>'."\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}



	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'contribution';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->viescolaire->dir_output;
	$trackid = 'contribution'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
