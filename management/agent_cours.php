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
 *  \file       agent_cours.php
 *  \ingroup    management
 *  \brief      Tab for notes on Agent
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

dol_include_once('/management/class/agent.class.php');
dol_include_once('/management/lib/management_agent.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("management@management", "companies"));

// Get parameters
$id = GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize technical objects
$object = new Agent($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->management->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('agentnote', 'globalcard')); // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals
if ($id > 0 || !empty($ref)) {
	$upload_dir = $conf->management->multidir_output[!empty($object->entity) ? $object->entity : $conf->entity]."/".$object->id;
}


// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 0;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->management->agent->read;
	$permissiontoadd = $user->rights->management->agent->write;
	$permissionnote = $user->rights->management->agent->write; // Used by the include of actions_setnotes.inc.php
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
if (empty($conf->management->enabled)) accessforbidden();
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
$title = $langs->trans('Agent').' - '.$langs->trans("Cours");
llxHeader('', $title, $help_url);

if ($id > 0 || !empty($ref)) {
	$object->fetch_thirdparty();

	$head = agentPrepareHead($object);

	print dol_get_fiche_head($head, 'Cours', $langs->trans("Agent"), -1, $object->picto);

	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/management/agent_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref  = '<div class="refidno">';
	$morehtmlref .= $object->prenom;


	
	$morehtmlref .= '</div>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'nom', $morehtmlref);

	print '<hr>';
	print '<h2>Vos cours: </h2>';
	print "<pre>( Vous avez ici accès aux mêmes informations que la scolarité, pour ce qui est des absences. <br> Si vous constatez un cours vide, rapprochez-vous de la scolarité pour plus d'informations. )</pre>";
	$Jour = "SELECT jour, rowid FROM ".MAIN_DB_PREFIX."c_jour WHERE active=1";
	$resqlJour = $db->query($Jour);

	foreach($resqlJour as $value)
	{
		$cours = "SELECT professeurs, nom_creneau, rowid FROM ".MAIN_DB_PREFIX."creneau WHERE status=4 AND jour=".$value['rowid']." AND (fk_prof_1=".$object->fk_user." OR fk_prof_2=".$object->fk_user." OR fk_prof_3=".$object->fk_user.") ORDER BY heure_debut ASC";
		$resqlCours = $db->query($cours);

		print '<h3>'.$value['jour'].($resqlCours->num_rows > 0 ? ('    <span class="badge  badge-status4 badge-status">  '.$resqlCours->num_rows.' Cours</span>') : ('      <span class="badge  badge-status8 badge-status">  Aucun cours à ce jour</span>')).'</h3>';

		print '<table class="tagtable liste">';
		print '<tbody>';
		

		if($resqlCours->num_rows != 0)
		{
			print '<tr class="liste_titre">
			<th class="wrapcolumntitle liste_titre">Créneau</th>
			<th class="wrapcolumntitle liste_titre">Professeurs</th>
			<th class="wrapcolumntitle liste_titre">Élèves</th>
			<th class="wrapcolumntitle liste_titre">Absences à venir</th>
			</tr>';
			foreach($resqlCours as $val)
			{
				print '<tr class="oddeven">';
				print '<td style="width:30%">'.$val['nom_creneau'].'</td>';
				print '<td>'.$val['professeurs'].'</td>';
	
				$affectation = "SELECT s.fk_souhait FROM ".MAIN_DB_PREFIX."affectation as s WHERE s.fk_creneau=".$val['rowid']." AND date_fin IS NULL";
				$resqlAffectation = $db->query($affectation);
				
				print '<td>';
				foreach($resqlAffectation as $v)
				{
					$eleve = "SELECT e.nom,e.prenom,e.rowid FROM ".MAIN_DB_PREFIX."eleve as e WHERE e.rowid=".("(SELECT s.fk_eleve FROM ".MAIN_DB_PREFIX."souhait as s WHERE s.rowid =".$v['fk_souhait'].")");
					$resqlEleve = $db->query($eleve);
					foreach($resqlEleve as $res)
					{
						print '<a href="' . DOL_URL_ROOT . '/custom/viescolaire/eleve_card.php?id=' . $res['rowid'] . '">' .'- '. $res['nom'].' '.$res['prenom'] . '</a>';
						print '<br>';
					}
				}
				print '</td>';
				print '<td>';
	

				$count = 0;
				foreach($resqlAffectation as $v)
				{
					$eleve = "SELECT e.nom,e.prenom,e.rowid FROM ".MAIN_DB_PREFIX."eleve as e WHERE e.rowid=".("(SELECT s.fk_eleve FROM ".MAIN_DB_PREFIX."souhait as s WHERE s.rowid =".$v['fk_souhait'].")");
					$resqlEleve = $db->query($eleve);
	
					foreach($resqlEleve as $res)
					{
						$date = date('Y-m-d H:i:s');
						$absence = "SELECT rowid, date_creation, justification  FROM ".MAIN_DB_PREFIX."appel WHERE fk_creneau=".$val['rowid']." AND fk_eleve=".$res['rowid']." AND date_creation >='".$date."'";
						$resqlAbsence = $db->query($absence);
	
						foreach($resqlAbsence as $r)
						{
							$count++;
							//print $res['prenom'].' '.$res['nom'].' '.date('d/m/Y', strtotime($r['date_creation'])).' - '.$r['justification'].'<br>';
						}
					}
				}
				
				print $count > 0 ? ('<a href="' . DOL_URL_ROOT . $_SERVER['PHP_SELF'].'?id=' . $object->id . '&idCours='.$val['rowid'].'&checkAbsences">' .$count.' absences futurs connues' . '</a>') : 'Aucune absences futurs connues à ce jour pour ces élèves.';
				print '</td>';
		
				print '</tr>';
			}
		}
		else
		print '<p></p>';
		
		print '</tbody>';
		print '</table>';
	}

	print dol_get_fiche_end();
}

// End of page
llxFooter();
$db->close();
