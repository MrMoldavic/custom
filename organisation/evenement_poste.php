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

/*ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/
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
dol_include_once('/organisation/class/poste.class.php');
dol_include_once('/management/class/appetence.class.php');
dol_include_once('/management/class/agent.class.php');
dol_include_once('/viescolaire/class/dictionary.class.php');
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
$ligneId = GETPOST('ligneId', 'alpha');
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
$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->organisation->organisation->read;
	$permissiontoadd = $user->rights->organisation->organisation->write;
	$permissionnote = $user->rights->organisation->organisation->write; // Used by the include of actions_setnotes.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1;
	$permissionnote = 1;
}
if (empty($conf->organisation->enabled)) accessforbidden();
if (!$permissiontoadd) accessforbidden();

include DOL_DOCUMENT_ROOT.'/custom/organisation/core/actions/actions_evenement-poste_organisation.inc.php';

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
	$morehtmlref .= '</div>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'nom_evenement', $morehtmlref);

	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';

	print  load_fiche_titre('Postes nécessaires pour l\'événement', '', 'fa-briefcase');

	// On va chercher les types de postes
	$dictionaryClass = new Dictionary($db);
	$typePostes = $dictionaryClass->fetchByDictionary('organisation_c_type_poste', array('poste','rowid'));

	foreach($typePostes as $value)
	{
		// Pour chaque poste, on va chercher des postes éxistants
		$posteClass = new Poste($db);
		$existingPostes = $posteClass->fetchAll('','',0,0,array('fk_evenement'=>$object->id,'fk_type_poste'=>$value->rowid));

		print '<div class="poste-accordion'.(($typePoste == $value->rowid) ? '-opened' : '').'">';
		print '<h3>'. $value->poste.(count($existingPostes) != 0 ? (' <span class="badge  badge-status4 badge-status"> (x'.count($existingPostes).')</span>') : '').'</h3>';

		print '<div>';
		print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&typePoste='.$value->rowid.'">';
		print '<input style="margin:1em" type="text" name="typePoste" value="'.$value->rowid.'" hidden >';
		print '<input style="margin:1em" type="text" name="token" value="'.newToken().'" hidden >';
		print '<input type="text" name="action" value="addPostes" hidden>';
		print 'Nombre de postes à ajouter : <input type="number" name="iteration" min="1" max="5" value="1">';
		print '<button type="submit">'.'➕'.'</button>';
		print '</form>';
		print '<hr>';

		if(count($existingPostes) == 0) print '<p>Aucune convocation prévue pour ce rôle</p>';
		else
		{
			print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&affectationPoste&typePoste='.$value->rowid.'">';
			print '<input type="text" name="typePoste[]" value="'.$value->rowid.'" hidden >';
			print '<input type="text" name="token" value="'.newToken().'" hidden >';
			print '<input type="text" name="action" value="affectationPoste" hidden >';
			print '<table class="tagtable nobottomiftotal liste">';
			print '<tbody>';
			print '<tr>';
			print '<td style="padding:0.5em" >Poste</td>';
			print '<td style="padding:0.5em" >utilisateur</td>';
			print '<td style="padding:0.5em" >Supprimer</td>';
			print '</tr>';

			// Va chercher les appétences existantes (volontés de chaque agents)
			$appetenceClass = new Appetence($db);
			$appetences = $appetenceClass->fetchAll('','',0,0,array('fk_type_poste'=>$value->rowid));

			$count = 1;
			foreach ($existingPostes as $existingPoste)
			{
				print '<tr>';
				print '<td style="padding:0.5em; ">'.$value->poste.' n°: '.$count.'</td>';
				print '<td style="padding:0.5em; ">';
				print '<input type="text" name="ligneId[]" value="'.$existingPoste->id.'" hidden>';
				print '<input type="text" name="token" value="'.newToken().'" hidden>';
				print '<select name="affectationPostes[]">';
				print '<option value="0">Aucun</option>';

				// Boucle sur chaque appetence de la catégorie
				foreach($appetences as $appetence)
				{
					// On va chercher l'agent correspondant
					$agentClass = new Agent($db);
					$agentClass->fetch($appetence->fk_agent);
					// On va chercher le type d'appetence
					$dictionaryClass = new Dictionary($db);
					$posteType = $dictionaryClass->fetchByDictionary('management_c_type_appetence', array('type','rowid'),$appetence->fk_type_appetence,'rowid');
					// Affichage de l'option avec pré-selection si on est sur l'agent
					print '<option value='.$agentClass->id.' '.($agentClass->id == $existingPoste->fk_agent ? 'selected' : '').'>'.$agentClass->prenom.' '.$agentClass->nom.' - '.$posteType->type.'</option>';
				}
				print '</select>';
				print '</td>';

				print '<td style="padding:0.5em; "><a href="'.DOL_URL_ROOT.'/custom/organisation/evenement_poste.php?id='.$object->id.'&action=deletePoste&idPoste='.$existingPoste->id.'&typePoste='.$value->rowid.'">'.'❌'.'</a></td>';
				print '</tr>';
				$count++;
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


	// TODO Mettre ça dans une fonction
	if($validationPostes)
	{
		$sqlPoste2 = "SELECT poste, rowid FROM ".MAIN_DB_PREFIX."organisation_c_type_poste";
		$resqlPoste2 = $db->query($sqlPoste2);

		foreach($resqlPoste2 as $value)
		{
			print '<h3>'.$value['poste'].': </h3>';

			$sql2 = "SELECT * FROM ".MAIN_DB_PREFIX."organisation_poste WHERE fk_evenement=".$object->id." AND fk_type_poste=".$value->rowid;
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
