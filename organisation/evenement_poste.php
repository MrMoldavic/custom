<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *  \file       evenement_note.php
 *  \ingroup    organisation
 *  \brief      Tab for notes on Evenement
 */
//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
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

dol_include_once('/organisation/class/evenement.class.php');
dol_include_once('/organisation/lib/organisation_evenement.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("organisation@organisation", "companies"));

// Get parameters
$id = GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

$iteration = GETPOST('iteration', 'int');
$typePoste = GETPOST('typePoste', 'int');
$affectationPostes = GETPOST('affectationPostes','alpha');
$lineId = GETPOST('ligneId', 'alpha');
$validationPostes = GETPOST('validationPostes', 'alpha');


// Initialize technical objects
$object = new Evenement($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->organisation->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('evenementnote', 'globalcard')); // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals
if ($id > 0 || !empty($ref)) {
	$upload_dir = $conf->organisation->multidir_output[!empty($object->entity) ? $object->entity : $conf->entity]."/".$object->id;
}


// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 0;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->organisation->evenement->read;
	$permissiontoadd = $user->rights->organisation->evenement->write;
	$permissionnote = $user->rights->organisation->evenement->write; // Used by the include of actions_setnotes.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1;
	$permissionnote = 1;
}

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->organisation->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();



if(isset($_POST['typePoste']) && !$affectationPostes)
{

	for($i=0; $i<$iteration; $i++)
	{
		$poste = "INSERT INTO ".MAIN_DB_PREFIX."organisation_poste(`fk_evenement`,`fk_type_poste`, `fk_agent`, `fk_etat_convocation`, `presence`, `description`, `note_public`, `note_private`, `date_creation`, `tms`, `fk_user_creat`, `fk_user_modif`, `last_main_doc`, `import_key`, `model_pdf`, `status`) VALUES 
		(".$object->id.",".$typePoste .",NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,".$user->id.",NULL,NULL,NULL,NULL,4)";
		$resqlPoste = $db->query($poste);
	}

	setEventMessage('Poste créé avec succès');
}

if($affectationPostes)
{
	$count = count($lineId);

	for($i = 0; $i < $count; $i++)
	{

		if($affectationPostes[$i] != "0")
		{
			$sqlAgentExistant = "SELECT * FROM ".MAIN_DB_PREFIX."organisation_poste WHERE fk_agent =".$affectationPostes[$i]." AND fk_evenement=".$object->id;
			$resqlPosteExistant = $db->query($sqlAgentExistant);

			if($resqlPosteExistant->num_rows != 0)
			{
				setEventMessage('Cet utilisateur à déjà un rôle prévu à cet événement','errors');
			}
			else
			{
				$sql = "UPDATE ".MAIN_DB_PREFIX."organisation_poste SET fk_agent=".$affectationPostes[$i]." WHERE rowid=".$lineId[$i];
				$resql = $db->query($sql);

				setEventMessage('Affectation(s) créée(s) avec succès');
			}
			
		}
	}

	;
}


if ($action == 'deletePoste') {

	$sql = "DELETE FROM " . MAIN_DB_PREFIX . "organisation_poste WHERE rowid=".GETPOST('idPoste', 'int');
	$resql = $db->query($sql);

	setEventMessage('Engagement supprimé avec succès');
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
	include DOL_DOCUMENT_ROOT.'/core/actions_setnotes.inc.php'; // Must be include, not include_once
}


/*
 * View
 */

$form = new Form($db);

//$help_url='EN:Customers_Orders|FR:Commandes_Clients|ES:Pedidos de clientes';
$help_url = '';
$title = $langs->trans('Evenement').' - '.$langs->trans("Postes");
llxHeader('', $title, $help_url);

if ($id > 0 || !empty($ref)) {
	$object->fetch_thirdparty();

	$head = evenementPrepareHead($object);

	print dol_get_fiche_head($head, 'Postes', $langs->trans("Evenement"), -1, $object->picto);

	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/organisation/evenement_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	/*
	 // Ref customer
	 $morehtmlref.=$form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
	 $morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
	 // Thirdparty
	 $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
	 // Project
	 if (! empty($conf->project->enabled))
	 {
	 $langs->load("projects");
	 $morehtmlref.='<br>'.$langs->trans('Project') . ' ';
	 if ($permissiontoadd)
	 {
	 if ($action != 'classify')
	 //$morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&token='.newToken().'&id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> : ';
	 $morehtmlref.=' : ';
	 if ($action == 'classify') {
	 //$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
	 $morehtmlref.='<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
	 $morehtmlref.='<input type="hidden" name="action" value="classin">';
	 $morehtmlref.='<input type="hidden" name="token" value="'.newToken().'">';
	 $morehtmlref.=$formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
	 $morehtmlref.='<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
	 $morehtmlref.='</form>';
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


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'nom_evenement', $morehtmlref);

	
	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';

	print '<h2>Postes nécessaires pour l\'événement: </h2>';

	$sqlPoste = "SELECT poste, rowid FROM ".MAIN_DB_PREFIX."organisation_c_type_poste";
	$resqlPoste = $db->query($sqlPoste);	

	foreach($resqlPoste as $value)
	{
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."organisation_poste WHERE fk_evenement=".$object->id." AND fk_type_poste=".$value['rowid'];
		$resql = $db->query($sql);	

		print '<div class="poste-accordion'.(($typePoste == $value['rowid']) ? '-opened' : '').'">';
		print '<h3>'. $value['poste'].($resql->num_rows != 0 ? (' <span class="badge  badge-status4 badge-status"> (x'.$resql->num_rows.')</span>') : '').'</h3>';
	
		print '<div>';
		
		print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&typePoste='.$value['rowid'].'">';
		print '<input style="margin:1em" type="text" name="typePoste" value="'.$value['rowid'].'" hidden >';
		print 'Nombre de postes à ajouter : <input type="number" name="iteration" min="1" max="5" value="1">';
		print '<button type="submit">'.'➕'.'</button>';
		print '</form>';
		print '<hr>';


		if($resql->num_rows == 0)
		{
			print '<p>Aucune convocation prévue pour ce rôle</p>';
		}
		else
		{
			$sqlPostes = "SELECT * FROM ".MAIN_DB_PREFIX."organisation_poste WHERE fk_evenement=".$object->id." AND fk_type_poste=".$value['rowid'];
			$resqlPostes = $db->query($sqlPostes);	
			
			print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&affectationPoste&typePoste='.$value['rowid'].'">';
			print '<input style="margin:1em" type="text" name="typePoste[]" value="'.$value['rowid'].'" hidden >';


			print '<table class="border tableforfield">';
			print '<tbody>';
			print '<tr>';
			print '<td style="padding:0.5em" >Poste</td>';
			print '<td style="padding:0.5em" >utilisateur</td>';
			print '<td style="padding:0.5em" >Supprimer</td>';
			print '</tr>';

			$sqlAppetences = "SELECT fk_agent, fk_type_appetence, rowid FROM ".MAIN_DB_PREFIX."management_appetence WHERE fk_type_poste=".$value['rowid'];
			$resqlAppetence = $db->query($sqlAppetences);	
			
			for($i=1; $i <= $resql->num_rows; $i++)
			{
				$objLigne = $db->fetch_object($resql);
				$objPoste = $db->fetch_object($resqlPostes);

				print '<tr>';
				print '<td style="padding:0.5em; ">'.$value['poste'].' n°: '.$i.'</td>';
				print '<td style="padding:0.5em; ">';
				print '<input type="text" name="ligneId[]" value="'.$objLigne->rowid.'" hidden>';
				print '<select name="affectationPostes[]">';
				print '<option value="0">Aucun</option>';
				
				foreach($resqlAppetence as $val)
				{
					$objAppetence = $db->fetch_object($resqlAppetence);

					$sqlAgent = "SELECT prenom, nom, rowid FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid=".$val['fk_agent'];
					$resqlAgent= $db->query($sqlAgent);
					$objAgent = $db->fetch_object($resqlAgent);

					$sqlAppetencesFinal = "SELECT type FROM ".MAIN_DB_PREFIX."management_c_type_appetence WHERE rowid="."(SELECT fk_type_appetence FROM ".MAIN_DB_PREFIX."management_appetence WHERE fk_agent=".$val['fk_agent']." AND fk_type_poste=".$value['rowid'].")";
					$resqlAppetencesFinal= $db->query($sqlAppetencesFinal);
					$objAppetencesFinal = $db->fetch_object($resqlAppetencesFinal);

					print '<option value="'.$objAgent->rowid.'" '.($objPoste->fk_agent == $objAgent->rowid ? 'selected' : '').'>'.$objAgent->prenom.' '.$objAgent->nom.' - '.$objAppetencesFinal->type.'</option>';
				}
				
				print '</select>';
				print '</td>';
		
				print '<td style="padding:0.5em; "><a href="'.DOL_URL_ROOT.'/custom/organisation/evenement_poste.php?id='.$object->id.'&action=deletePoste&idPoste='.$objLigne->rowid.'&typePoste='.$value['rowid'].'">'.'❌'.'</a></td>';
				print '</tr>';
			}
			print '</tbody>';
			print '</table>';
			print '<button type="submit" style="margin:1em">Valider la sélection</button>';
			print '</form>';
		}



		print '</div>';
		print '</div>';
	}
	print '<hr>';
	print dolGetButtonAction($langs->trans('Terminer les postes'), '', 'default', DOL_URL_ROOT.'/custom/organisation/evenement_poste.php?id='.$object->id.'&action=create&validationPostes=1' , '', $permissiontoadd);


	if($validationPostes)
	{
		$sqlPoste2 = "SELECT poste, rowid FROM ".MAIN_DB_PREFIX."organisation_c_type_poste";
		$resqlPoste2 = $db->query($sqlPoste2);

		foreach($resqlPoste2 as $value)
		{
			print '<h3>'.$value['poste'].': </h3>';

			$sql2 = "SELECT * FROM ".MAIN_DB_PREFIX."organisation_poste WHERE fk_evenement=".$object->id." AND fk_type_poste=".$value['rowid'];
			$resql2 = $db->query($sql2);	
			
			if($resql2)
			{
				foreach($resql2 as $res)
				{
					$printPoste = "";
					$sqlAgent2 = "SELECT discord,prenom, nom, rowid FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid=".$res['fk_agent'];
					$resqlAgent2 = $db->query($sqlAgent2);
					
					if($resqlAgent2 != "")
					{
						$objAgent2 = $db->fetch_object($resqlAgent2);
						if($objAgent2->discord)
						{
							$printPoste .= "@".$objAgent2->discord.", ";
						}
						else
						{
							$printPoste .= $objAgent2->prenom." ".$objAgent2->nom.", ";
						}
					}
					
					print '<p>'.$printPoste.'</p>';
				}
			}
			
		}
	}

	print dol_get_fiche_end();

	print '<script>
		$( ".poste-accordion" ).accordion({
			collapsible: true,
			active: 2,
		});
		</script>';

     print '<script>
		$( ".poste-accordion-opened" ).accordion({
			collapsible: true,
		});
		</script>';
}

// End of page
llxFooter();
$db->close();
