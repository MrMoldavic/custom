<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
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
 *   	\file       appel_card.php
 *		\ingroup    viescolaire
 *		\brief      Page to create/edit/view appel
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
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
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
require_once DOL_DOCUMENT_ROOT . '/custom/materiel/core/lib/functions.lib.php';
dol_include_once('/viescolaire/class/appel.class.php');
dol_include_once('/scolarite/class/dispositif.class.php');

dol_include_once('/viescolaire/lib/viescolaire_appel.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("viescolaire@viescolaire", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09') ? GETPOST('action', 'aZ09') : 'create';
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'appelcard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid   = GETPOST('lineid', 'int');
$creneauid = GETPOST('creneauid', 'int');

// Initialize technical objects
$object = new Appel($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->viescolaire->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('appelcard', 'globalcard')); // Note that conf->hooks_modules contains array

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
     $permissionnote = $user->rights->viescolaire->eleve->write; // Used by the include of actions_setnotes.inc.php
     $permissiondellink = $user->rights->viescolaire->eleve->write; // Used by the include of actions_dellink.inc.php
} else {
     $permissiontoread = 1;
     $permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
     $permissiontodelete = 1;
     $permissionnote = 1;
     $permissiondellink = 1;
}

$upload_dir = $conf->viescolaire->multidir_output[isset($object->entity) ? $object->entity : 1] . '/appel';

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

     $backurlforlist = dol_buildpath('/viescolaire/appel_list.php', 1);

     if (empty($backtopage) || ($cancel && empty($id))) {
          if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
               if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
                    $backtopage = $backurlforlist;
               } else {
                    $backtopage = dol_buildpath('/viescolaire/appel_card.php', 1) . '?id=' . ((!empty($id) && $id > 0) ? $id : '__ID__');
               }
          }
     }

     $triggermodname = 'VIESCOLAIRE_APPEL_MODIFY'; // Name of trigger action code to execute when we modify record

     // Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_sethraft, confirm_reopen
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
     $triggersendname = 'VIESCOLAIRE_APPEL_SENTBYMAIL';
     $autocopy = 'MAIN_MAIL_AUTOCOPY_APPEL_TO';
     $trackid = 'appel' . $object->id;
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

$title = $langs->trans("Appel");
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


if ($action == 'create' && !GETPOST('etablissementid', 'int') && !GETPOST('appelday', 'alpha')) // SELECTION DU TYPE DE KIT
{

     $sql = "SELECT e.rowid,e.nom FROM " . MAIN_DB_PREFIX . "etablissement as e";
     $resql = $db->query($sql);
     $etablissements = [];

     foreach ($resql as $val) {
          $etablissements[$val['rowid']] = $val['nom'];
     }


     print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">';

     print '<input type="hidden" name="action" value="create">';
     $titre = "Nouvel Appel";
     print talm_load_fiche_titre($title, $linkback, $picto);
     dol_fiche_head('');
     print '<table class="border centpercent">';
     print '<tr>';
     print '</th></tr>';
     print '<tr><th class="fieldrequired titlefieldcreate">Selectionnez votre établissement : </th><th>';
     print $form->selectarray('etablissementid', $etablissements);
     print ' <a href="' . DOL_URL_ROOT . '/custom/scolarite/etablissement_card.php?action=create">';
     print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter un type de kit"></span>';
     print '</a>';
     print '</th>';
     print '</tr>';

     print '<tr><th class="fieldrequired titlefieldcreate">Selectionnez une date: </th><th>';
     print $form->selectDate('', 'appel');
     print '</th>';
     print '</tr>';

     print "</table>";
     dol_fiche_end();
     print '<div class="center">';
     print '<input type="submit" class="button" value="Suivant">';
     print '</div>';
     print '</form>';
}
if ($action == 'create' && GETPOST('etablissementid', 'int') && GETPOST('appelday', 'alpha'))  // Type de kit choisi -> création d'un nouveau kit
{
     $title = 'Sommaire';
     $linkback = "";
     print talm_load_fiche_titre($title, $linkback, $picto);

     setlocale(LC_TIME, "fr_FR");
     $Mois = strftime('%B');
     $heureActuelle = strftime('%k');

     $datedemerde =  date("Y-m-d H:i:s", mktime(0, 0, 0, GETPOST('appelmonth', 'alpha'), GETPOST('appelday', 'alpha'), GETPOST('appelyear', 'alpha')));
     $jour = ucfirst(strftime('%A', strtotime($datedemerde)));
     $JourSemaine = strftime('%u', strtotime($datedemerde));
     $daymonth = strftime('%e', strtotime($datedemerde));

     print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
     print '<input type="hidden" name="action" value="add">';
     print '<input type="hidden" name="etablissementid" value="' . GETPOST('etablissementid', 'int') . '">';


     print '<h2><a href="' . DOL_URL_ROOT . '/custom/viescolaire/appel_card.php?day=' . $JourSemaine . '&month=' . GETPOST('appelmonth', 'alpha') . '&year=' . GETPOST('appelyear', 'alpha') . '&action=create&daymonth=' . GETPOST('appelday', 'alpha') . '&etablissementid=' . GETPOST('etablissementid', 'int') . '">' . 'Date du jour : ' . $jour . ' ' . GETPOST('appelday', 'alpha') . ' ' . $Mois . ' ' . GETPOST('appelyear', 'alpha') . '</a></h2>';

     print '<div class="div-table-responsive">';
     print '<table class="tagtable liste">';
     print '<tbody>';

     print '<tr class="liste_titre">
		<th class="wrapcolumntitle liste_titre">Créneau</th>
		<th class="wrapcolumntitle liste_titre">Validés</th>
		<th class="wrapcolumntitle liste_titre">Elèves</th>
		<th class="wrapcolumntitle liste_titre">Profs</th>
		<th class="wrapcolumntitle liste_titre">Nb de cours à cette heure</th>
		<th class="wrapcolumntitle liste_titre">Statut</th>
		</tr>';
     // Boucle pour lister une ligne par heure, de 8h à 18h
     for ($i = 8; $i <= 20; $i++) {
          $sql = "SELECT COUNT(DISTINCT c.rowid) as total FROM " . MAIN_DB_PREFIX . "creneau as c INNER JOIN " . MAIN_DB_PREFIX . "dispositif as d ON c.fk_dispositif=d.rowid  WHERE nom_creneau LIKE '%" . $jour . '-' . $i . "h%' AND d.fk_etablissement=" . GETPOST("etablissementid", "int") . " AND c.status = 4";
          $resql = $db->query($sql);
          $res = $db->fetch_object($resql);

          $totalCreneauCount = $res->total;

          $sqlProf = "SELECT c.fk_prof_1,c.fk_prof_2,c.fk_prof_3 FROM " . MAIN_DB_PREFIX . "creneau as c INNER JOIN " . MAIN_DB_PREFIX . "dispositif as d ON c.fk_dispositif=d.rowid   WHERE nom_creneau LIKE '%" . $jour . '-' . $i .
               "h%' AND d.fk_etablissement=" . GETPOST("etablissementid", "int")." AND c.status = 4";
          $resqlProf = $db->query($sqlProf);
          $prof_count = 0;
          for ($j = 0; $j < $resqlProf->num_rows; $j++) {
               $profs = $resqlProf->fetch_object();
               if (!$profs) continue;
               if ($profs->fk_prof_1) $prof_count++;
               if ($profs->fk_prof_2) $prof_count++;
               if ($profs->fk_prof_3) $prof_count++;
          }

          $sqlProfPresent = "SELECT fk_user,fk_creneau FROM " . MAIN_DB_PREFIX . "appel as a INNER JOIN " . MAIN_DB_PREFIX . "creneau as c ON c.rowid=a.fk_creneau WHERE c.nom_creneau LIKE '%" . $jour . '-' . $i . "h%' AND a.status!='absent' AND a.fk_user IS NOT NULL AND YEAR(a.date_creation)=" . intval(GETPOST("appelyear", "alpha")) . " AND MONTH(a.date_creation)=" . intval(GETPOST("appelmonth", "alpha")) . " AND DAY(a.date_creation)=" . intval(GETPOST("appelday", "alpha"));
          $sqlProfPresent .= " AND a.fk_etablissement = " . GETPOST("etablissementid", "int")." AND c.status = 4";

          $resqlProfPresent = $db->query($sqlProfPresent);
          $prof_present_count = 0;
          $filter = [];
          for ($j = 0; $j < $resqlProfPresent->num_rows; $j++) {
               $prof = $resqlProfPresent->fetch_object();
               if (in_array([$prof->fk_user, $prof->fk_creneau], $filter)) continue;
               $filter[] = [$prof->fk_user, $prof->fk_creneau];
               $prof_present_count++;
          }

          // Select nombre d'eleve
          $sqlNombreEleve = "SELECT COUNT(DISTINCT a.rowid) as count FROM " . MAIN_DB_PREFIX . "affectation as a INNER JOIN " . MAIN_DB_PREFIX . "creneau as c ON c.rowid=a.fk_creneau INNER JOIN " . MAIN_DB_PREFIX . "dispositif as d ON d.rowid=c.fk_dispositif WHERE a.status=4 AND c.nom_creneau LIKE '%" . $jour . '-' . $i . "h%' ";
          $sqlNombreEleve .= " AND d.fk_etablissement = " . GETPOST("etablissementid", "int")." AND c.status = 4";

          $resqlNombreEleve = $db->query($sqlNombreEleve);
          $nombreEleve = $resqlNombreEleve->fetch_object();

          $sqlElevePresent = "SELECT fk_eleve,fk_creneau FROM " . MAIN_DB_PREFIX . "appel as a INNER JOIN " . MAIN_DB_PREFIX . "creneau as c ON c.rowid=a.fk_creneau WHERE c.nom_creneau LIKE '%" . $jour . '-' . $i . "h%' AND a.fk_eleve IS NOT NULL AND YEAR(a.date_creation)=" . intval(GETPOST("appelyear", "alpha")) . " AND MONTH(a.date_creation)=" . intval(GETPOST("appelmonth", "alpha")) . " AND DAY(a.date_creation)=" . intval(GETPOST("appelday", "alpha"));
          $sqlElevePresent .= " AND a.fk_etablissement = " . GETPOST("etablissementid", "int")." AND c.status = 4";

          $resqlElevePresent = $db->query($sqlElevePresent);
          //
          $eleve_present_count = 0;
          $filter = [];
          for ($j = 0; $j < $resqlElevePresent->num_rows; $j++) {
               $eleve = $resqlElevePresent->fetch_object();
               if (in_array([$eleve->fk_eleve, $eleve->fk_creneau], $filter)) continue;
               $filter[] = [$eleve->fk_eleve, $eleve->fk_creneau];
               $eleve_present_count++;
          }

          // Select nombre d'appels validés
          $completeAppelCount = 0;
          $sql = "SELECT c.rowid,c.nom_creneau,c.fk_dispositif FROM " . MAIN_DB_PREFIX . "creneau as c INNER JOIN " . MAIN_DB_PREFIX . "dispositif as d ON c.fk_dispositif = d.rowid INNER JOIN " . MAIN_DB_PREFIX . "c_heure as h ON c.heure_debut = h.rowid WHERE d.fk_etablissement =" . GETPOST('etablissementid', 'int') . " AND c.jour=" . $JourSemaine . " AND h.heure <= 23 AND c.nom_creneau LIKE '%" . $jour . '-' . $i . "h%' ";
          $resqlAffectation = $db->query($sql);

          foreach ($resqlAffectation as $val) {
               $sql1 = "SELECT e.nom, e.prenom,e.rowid FROM " . MAIN_DB_PREFIX . "souhait as s INNER JOIN " . MAIN_DB_PREFIX . "affectation as a ON a.fk_souhait = s.rowid INNER JOIN " . MAIN_DB_PREFIX . "eleve as e ON e.rowid = s.fk_eleve WHERE a.fk_creneau = " . $val['rowid'] . " AND a.status = 4";
               $resql = $db->query($sql1);

               $isComplete = true;

               // Compte des appels à cette date pour chaque élève
               foreach ($resql as $res) {
                    $sqll = "SELECT rowid FROM " . MAIN_DB_PREFIX . "appel WHERE fk_eleve = " . $res['rowid'] . " AND YEAR(date_creation) = " . strftime('%Y', strtotime($datedemerde));
                    $sqll .= " AND MONTH(date_creation) = " . strftime('%m', strtotime($datedemerde));
                    $sqll .= " AND DAY(date_creation) = " . $daymonth;
                    $sqll .= " AND fk_creneau = " . $val['rowid'];
                    $sqll .= " AND treated = " . 1;

                    $resqlCount = $db->query($sqll);
                    $numEleve = $db->num_rows($resqlCount);

                    if ($numEleve == 0) {
                         $isComplete = false;
                    }
               }

               // Check if prof are filled
               $sqlProf = "SELECT p.fk_prof_1, p.fk_prof_2, p.fk_prof_3 FROM " . MAIN_DB_PREFIX . "creneau as p WHERE p.rowid=" . $val['rowid'];
               $sqlProf = $db->query($sqlProf);
               $sqlProReal = $db->fetch_object($sqlProf);

               // Compte des appels à cette date pour le prof 1
               if ($sqlProReal->fk_prof_1) {
                    $checkProf = "SELECT rowid FROM " . MAIN_DB_PREFIX . "appel WHERE fk_user = " . $sqlProReal->fk_prof_1 . " AND YEAR(date_creation) = " . strftime('%Y', strtotime($datedemerde));
                    $checkProf .= " AND MONTH(date_creation) = " . strftime('%m', strtotime($datedemerde));
                    $checkProf .= " AND DAY(date_creation) = " . $daymonth;
                    $checkProf .= " AND fk_creneau = " . $val['rowid'];
                    $checkProf .= " AND fk_etablissement = " . GETPOST("etablissementid", "int");
                    $checkProf .= " AND treated = " . 1;

                    $resqlCountprof = $db->query($checkProf);
                    $numProf1 = $db->num_rows($resqlCountprof);
                    if ($numProf1 == 0) {
                         $isComplete = false;
                    }
               }

               // Compte des appels à cette date pour le prof 2
               if ($sqlProReal->fk_prof_2) {
                    $checkProf = "SELECT rowid FROM " . MAIN_DB_PREFIX . "appel WHERE fk_user = " . $sqlProReal->fk_prof_2 . " AND YEAR(date_creation) = " . strftime('%Y', strtotime($datedemerde));
                    $checkProf .= " AND MONTH(date_creation) = " . strftime('%m', strtotime($datedemerde));
                    $checkProf .= " AND DAY(date_creation) = " . $daymonth;
                    $checkProf .= " AND fk_creneau = " . $val['rowid'];
                    $checkProf .= " AND fk_etablissement = " . GETPOST("etablissementid", "int");
                    $checkProf .= " AND treated = " . 1;

                    $resqlCountprof = $db->query($checkProf);
                    $numProf2 = $db->num_rows($resqlCountprof);
                    if ($numProf2 == 0) {
                         $isComplete = false;
                    }
               }

               // Compte des appels à cette date pour le prof 3
               if ($sqlProReal->fk_prof_3) {
                    $checkProf = "SELECT rowid FROM " . MAIN_DB_PREFIX . "appel WHERE fk_user = " . $sqlProReal->fk_prof_3 . " AND YEAR(date_creation) = " . strftime('%Y', strtotime($datedemerde));
                    $checkProf .= " AND MONTH(date_creation) = " . strftime('%m', strtotime($datedemerde));
                    $checkProf .= " AND DAY(date_creation) = " . $daymonth;
                    $checkProf .= " AND fk_creneau = " . $val['rowid'];
                    $checkProf .= " AND fk_etablissement = " . GETPOST("etablissementid", "int");
                    $checkProf .= " AND treated = " . 1;

                    $resqlCountprof = $db->query($checkProf);
                    $numProf3 = $db->num_rows($resqlCountprof);
                    if ($numProf3 == 0) {
                         $isComplete = false;
                    }
               }

               if ($isComplete) $completeAppelCount += 1;
          }

          print '<tr class="oddeven">';

		  print '<td>'.($totalCreneauCount > 0 ? '<a href="' . DOL_URL_ROOT . '/custom/viescolaire/appel_card.php?day=' . $JourSemaine . '&month=' . GETPOST('appelmonth', 'alpha') . '&year=' . GETPOST('appelyear', 'alpha') . '&action=create&daymonth=' . GETPOST('appelday', 'alpha') . '&etablissementid=' . GETPOST('etablissementid', 'int') .'&heureActuelle='.$i.'">' : ''). $jour . ' ' . $i . 'h-' . ($i + 1) . 'h' .($totalCreneauCount > 0 ? '</a>' : '').'</td>';

		  print '</td>
		  <td>' . $completeAppelCount . '/' . $totalCreneauCount . '</td>
		  <td>' . $eleve_present_count . "/" . $nombreEleve->count . '</td>
		  <td>' . $prof_present_count . "/" . $prof_count . '</td>
		  <td>' . $totalCreneauCount . '</td>';

          if ($totalCreneauCount == 0) {
               print '<td><span class="badge  badge-status6 badge-status" style="color:white;">Pas de cours à cette heure</span></td>';
          } elseif (mktime($i, 0, 0, GETPOST('appelmonth'), GETPOST('appelday'), GETPOST('appelyear')) > time() && $completeAppelCount != $totalCreneauCount) {
               print '<td><span class="badge  badge-status5 badge-status" style="color:white;">Appel(s) à venir</span></td>';
          } elseif ($completeAppelCount == $totalCreneauCount) {
               print '<td><span class="badge  badge-status4 badge-status" style="color:white;">Appel(s) complet(s)</span></td>';
          } elseif ($completeAppelCount != $totalCreneauCount) {
               print '<td><span class="badge  badge-status8 badge-status" style="color:white;">Appel(s) incomplet(s)</span></td>';
          }
          print '</tr>';
     }

     print '</tbody>';
     print '</table>';
     print '</div>';
}
// End of page
llxFooter();
$db->close();
