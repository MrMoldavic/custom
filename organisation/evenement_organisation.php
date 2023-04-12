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
 *  \file       evenement_organisation.php
 *  \ingroup    organisation
 *  \brief      Tab for notes on Evenement
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
$title = $langs->trans('Evenement').' - '.$langs->trans("Notes");
llxHeader('', $title, $help_url);

if ($id > 0 || !empty($ref)) {
	$object->fetch_thirdparty();

	$head = evenementPrepareHead($object);

	print dol_get_fiche_head($head, 'Organisation', $langs->trans("Evenement"), -1, $object->picto);

	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/organisation/evenement_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'nom_evenement', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';


	$propisition = "SELECT rowid,fk_groupe,description,date_proposition,fk_user_creat FROM ".MAIN_DB_PREFIX."organisation_proposition WHERE fk_evenement =".$object->id;
	$resqlPropisition = $db->query($propisition);

	
	print '<table class="border tableforfield">';
	print '<tbody>';
	print '<tr>';
	print '<td style="padding:2em">Groupes Proposés</td>';
	print '<td style="padding:2em">Morceaux proposés</td>';
	print '<td style="padding:2em">Position</td>';
	print '<td style="padding:2em">Actions</td>';
	print '</tr>';
			print '<tr id="goebbels">';
			print '</tr>';

	foreach($resqlPropisition as $value)
	{

		$groupe = "SELECT nom_groupe,rowid FROM ".MAIN_DB_PREFIX."organisation_groupe WHERE rowid = ".$value['fk_groupe'];
		$resqlGroupe = $db->query($groupe);
		$objGroupe = $db->fetch_object($resqlGroupe);

		$userCreat = "SELECT lastname, firstname, rowid FROM ".MAIN_DB_PREFIX."user WHERE rowid = ".$value['fk_user_creat'];
		$resqlUserCreat = $db->query($userCreat);
		$objuser = $db->fetch_object($resqlUserCreat);

		$programmation = "SELECT fk_interpretation, rowid FROM ".MAIN_DB_PREFIX."organisation_programmation WHERE fk_proposition = ".$value['rowid'].' AND fk_evenement = '.$object->id;
		$resqlProgrammation = $db->query($programmation);

	

		print '<tr>';
		print '<td>'.$objGroupe->nom_groupe.' ( proposé par '.$objuser->firstname.' '.$objuser->lastname.' )'.'</td>';
		print '<td style="padding:1em">';
		if($resqlProgrammation->num_rows > 0)
		{
			print '<table class="border tableforfield" style="background-color:#E0DBD9">';
			print '<tbody>';
			print '<tr>';
			print '<td style="padding:0.8em">Titre</td>';
			print '<td style="padding:0.8em">Artiste</td>';
			print '<td style="padding:0.8em">Position</td>';
			print '<td style="padding:0.8em; display:flex">Actions</td>';
			print '</tr>';

			foreach($resqlProgrammation as $val)
			{
				$interpretation = "SELECT fk_morceau,rowid FROM ".MAIN_DB_PREFIX."organisation_interpretation WHERE rowid = ".$val['fk_interpretation'];
				$resqlInterpretation = $db->query($interpretation);
				$objInterpretation = $db->fetch_object($resqlInterpretation);

				$morceau = "SELECT titre,fk_artiste,rowid FROM ".MAIN_DB_PREFIX."organisation_morceau WHERE rowid = ".$objInterpretation->fk_morceau;
				$resqlMorceau = $db->query($morceau);
				$objMorceau = $db->fetch_object($resqlMorceau);

				$artiste = "SELECT artiste,rowid FROM ".MAIN_DB_PREFIX."organisation_artiste WHERE rowid = ".$objMorceau->fk_artiste;
				$resqlArtiste = $db->query($artiste);
				$objArtiste = $db->fetch_object($resqlArtiste);

				print '<tr>';
				print '<td style="padding:0.8em">'.$objMorceau->titre.'</td>';
				print '<td style="padding:0.8em">'.$objArtiste->artiste.'</td>';
				print '<td style="padding:0.8em"></td>';
				print '<td style="padding:0.8em"></td>';
				print '</tr>';
			
			}
			print '</tbody>';
			print '</table>';
			print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=deleteInterpretation&interpretation='.$value['rowid'].'">'.'Ajouter une interprétation ➕'.'</a>';
			//print '<td style="padding:1em"><a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=deleteInterpretation&interpretation='.$value['rowid'].'">'.'➕'.'</a></td>';
		}
		else
		{
			print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=deleteInterpretation&interpretation='.$value['rowid'].'">'.'➕'.'</a>';

		}
		print '</td>';
		print '<td>Position</td>';
		
		// ACTIONS
		print '<td>';
 print '<div><div class="fas fa-angle-up mat-sort-up" style="cursor:pointer; position:absolute;"></div><div class="fas fa-angle-down mat-sort-down" style="cursor:pointer; margin-top:20px;"></div> </div></td><td style="vertical-align:middle; padding-left:20px;"><span class="fas fa-trash delete-select"></span>';
		print '</td>';
		print '<td style="text-align:right; display: flex;"></td>';
		print '</tr>';

	}

			print '<tr id="poutine">';
			print '</tr>';
	print '</tbody>';
	print '</table>';



	print "<script>$(document).ready(function(){
		". (true ? '$(\'[name="materiel[]"]\').find("option").removeAttr("disabled"),$(\'[name="materiel[]"]\').each(function(e){var t=$(this).next(".select2-container").find(".select2-selection__rendered").text();t.trim()||(t=0),console.log(t),$(\'[name="materiel[]"]\').not($(this)).find(\'option:contains("\'+t+\'")\').attr("disabled","disabled"),$.ajax({url:"http://test-dolibarr.tousalamusique.com/custom/kit/ajax/materiel_status.php?id="+$(this).find(\'option:contains("\'+t+\'")\').attr("value"),context:this,beforeSend:function(){$(this).next(".select2-container").nextAll("span").remove(),$(this).next(".select2-container").after(\'<span id="loader" class="lds-dual-ring"></span>\')},success:function(e){var t=e;$(this).next(".select2-container").nextAll("span").remove(),$(this).next(".select2-container").after(t)}})});' : '')."
		
		$(document).on(\"click\", \".mat-sort-up,.mat-sort-down\", function () {
			var row = $(this).parents(\"tr:first\");
			console.log(row.prev());
			console.log(row.next().attr(\"id\"));
			if ($(this).is(\".mat-sort-up\")) {
				console.log('zeub');
				if (row.prev().attr(\"id\") != $(\"#goebbels\").attr(\"id\")){
					console.log('ok');
					row.insertBefore(row.prev());
				}
			} else {
				console.log('merdde(')
				if (row.next().attr(\"id\") != $(\"#poutine\").attr(\"id\")){
					console.log('ok');
					row.insertAfter(row.next());
				}
			}
		});
	});</script>";




	print '</div>';

	print dol_get_fiche_end();
}

// End of page
llxFooter();
$db->close();
