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


/*ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/

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
dol_include_once('/scolarite/class/annee.class.php');
dol_include_once('/scolarite/class/creneau.class.php');

dol_include_once('/viescolaire/class/dictionary.class.php');
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
$enablepermissioncheck = 1;
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
if (empty($conf->management->enabled)) accessforbidden();
if (!$object->id) accessforbidden();

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

	$creneauClass = new Creneau($db);
	$eleveClass = new Eleve($db);
	$appelClass = new Appel($db);
	$dictionaryClass = new Dictionary($db);

	$dateDuJour = date('d/m/Y');
	$jours = $dictionaryClass->fetchByDictionary('c_jour', ['rowid','jour'],'','');

	foreach($jours as $jour)
	{
		$creneaux = $creneauClass->fetchAll('ASC','t.heure_debut',0,0,['t.status'=>Creneau::STATUS_VALIDATED,'t.jour'=>$jour->rowid],'AND',' INNER JOIN '.MAIN_DB_PREFIX.'assignation as a ON a.fk_creneau=t.rowid');

		print '<h3>'.$jour->jour.(count($creneaux) > 0 ? ('    <span class="badge  badge-status4 badge-status">  '.count($creneaux).' Cours</span>') : ('      <span class="badge  badge-status8 badge-status">  Aucun cours à ce jour</span>')).'</h3>';

		print '<table class="tagtable liste">';
		print '<tbody>';


		if(count($creneaux))
		{
			print '<tr class="liste_titre">
			<th class="wrapcolumntitle liste_titre">Créneau</th>
			<th class="wrapcolumntitle liste_titre">Professeurs</th>
			<th class="wrapcolumntitle liste_titre">Élèves</th>
			<th class="wrapcolumntitle liste_titre">Absences à venir</th>
			</tr>';
			foreach($creneaux as $creneau)
			{
				print '<tr class="oddeven">';
				print '<td>'.$creneau->getNomUrl(1).'</td>';
				print '<td>'.$creneau->printProfesseursFromCreneau($creneau->id).'</td>';

				print '<td>';
				print $creneau->printElevesFromCreneau($creneau->id)[0];
				print '</td>';

				print '<td>';

				$results = $appelClass->fetchAll(
					'', // Pas de colonne spécifique à sélectionner
					'', // Pas de tri spécifique
					0,  // Limite
					0,  // Offset
					[
						'fk_creneau' => $creneau->id,
						'customsql' => '
						t.date_creation >= "' . $date . '"
						AND t.status != "present"
						AND EXISTS (
							SELECT 1
							FROM ' . MAIN_DB_PREFIX . 'affectation AS affectation
							INNER JOIN ' . MAIN_DB_PREFIX . 'souhait AS souhait ON affectation.fk_souhait = souhait.rowid
							INNER JOIN ' . MAIN_DB_PREFIX . 'eleve AS eleve ON souhait.fk_eleve = eleve.rowid
							WHERE
								affectation.fk_creneau = ' . $creneau->id . '
								AND affectation.date_fin IS NULL
								AND eleve.rowid = t.fk_eleve
						)'
					],
					'AND',
					' INNER JOIN ' . MAIN_DB_PREFIX . 'eleve AS eleve ON t.fk_eleve = eleve.rowid'
				);

				$count = 0;

				// Affichage des résultats
				foreach ($results as $result) {

					$raison = ($result->status === 'absenceI' || $result->status === 'absenceJ') ? 'absent(e)' : 'en retard';
					// Instanciation de l'objet Eleve
					$eleveClass->fetch($result->fk_eleve); // fetch l'objet Eleve par son id

					if ($eleveClass->id) {
						$count++;
						echo '- ' . $eleveClass->getNomUrl(1) . ' sera '.$raison.' le: ' . date('d/m/Y', $result->date_creation) .
							(date('d/m/Y', $result->date_creation) === $dateDuJour ? ' <span class="badge badge-status4 badge-status">Aujourd\'hui</span>' : '') .
							'<br>Justification : ' . $result->justification . '<br><hr>';
					}
				}
				if($count === 0) {
					print 'Aucune absences futurs connues à ce jour pour ces élèves.';
				}
				print '</td>';

				print '</tr>';
			}
		}

		print '</tbody>';
		print '</table>';
	}

	print dol_get_fiche_end();
}

// End of page
llxFooter();
$db->close();
