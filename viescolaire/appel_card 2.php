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
$action = GETPOST('action', 'aZ09');
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
$enablepermissioncheck = 0;
if ($enablepermissioncheck) {
     $permissiontoread = $user->rights->viescolaire->appel->read;
     $permissiontoadd = $user->rights->viescolaire->appel->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
     $permissiontodelete = $user->rights->viescolaire->appel->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
     $permissionnote = $user->rights->viescolaire->appel->write; // Used by the include of actions_setnotes.inc.php
     $permissiondellink = $user->rights->viescolaire->appel->write; // Used by the include of actions_dellink.inc.php
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


// Part to create
if ($action == 'confirmAppel') {

     $sql = "SELECT e.nom, e.prenom,e.rowid FROM " . MAIN_DB_PREFIX . "souhait as s INNER JOIN " . MAIN_DB_PREFIX . "affectation as a ON a.fk_souhait = s.rowid INNER JOIN " . MAIN_DB_PREFIX . "eleve as e ON e.rowid = s.fk_eleve WHERE a.fk_creneau = " . GETPOST('creneauid', 'int') . " AND a.status = 4";
     $sqlEleves = $db->query($sql);

     $sqlProf = "SELECT p.fk_prof_1, p.fk_prof_2, p.fk_prof_3 FROM " . MAIN_DB_PREFIX . "creneau as p WHERE p.rowid=" . $creneauid;
     $sqlProf = $db->query($sqlProf);
     $sqlProReal = $db->fetch_object($sqlProf);

     if(GETPOST('day', 'alpha'))
     {
          print '<input type="hidden" name="day" value="'.GETPOST('day', 'alpha').'">';
          print '<input type="hidden" name="daymonth" value="'.GETPOST('daymonth', 'alpha').'">';
          print '<input type="hidden" name="month" value="'.GETPOST('month', 'alpha').'">';
          print '<input type="hidden" name="year" value="'.GETPOST('year', 'alpha').'">';
     }

     // Check if all eleve / profs are filled
     $check = true;
     $checkInjustifiee = true;

     foreach ($sqlEleves as $val) {
          if (!GETPOST('presence' . $val['rowid'], 'alpha')) {
               $check = false;
          }

          // if (GETPOST('presence' . $val['rowid'], 'alpha') == "absenceI" && GETPOST('infos' . $val['rowid'], 'alpha') == "") {

          //      $checkInjustifiee = false;
          // }
     }

     if (!GETPOST('prof' . $sqlProReal->fk_prof_1, 'alpha')) $check = false;
     
     if (!$check) {
          setEventMessage("Veuillez renseigner tous les champs", 'errors');
     } 
     // elseif(!$checkInjustifiee)
     // {
     //      setEventMessage("Veuillez noter des infos pour l'absence injustifiée", 'errors');
     // }
     else {
          foreach ($sqlEleves as $val) {
               $sqlAppel = "SELECT * FROM " . MAIN_DB_PREFIX . "appel WHERE fk_eleve = " . $val['rowid'];
               $sqlAppel .= " AND fk_creneau = " . GETPOST('creneauid', 'int');
               $sqlAppel .= " AND treated = " . 1;
               $sqlAppel .= " ORDER BY rowid DESC";
         
               $resqlCountEleves = $db->query($sqlAppel);
               $res = $db->fetch_object($resqlCountEleves);
     
               if($res != null)
               {
                    $sql = "UPDATE " . MAIN_DB_PREFIX . "appel SET treated = " . 0 . " WHERE rowid=" . $res->rowid;
                    $resql = $db->query($sql);
               
                    $sqlres = "INSERT INTO " . MAIN_DB_PREFIX . "appel (fk_etablissement, fk_creneau, fk_eleve, justification, action_faite, date_creation, fk_user_creat, status, treated) VALUES (";
                    $sqlres .= GETPOST('etablissementid', 'int') . ",";
                    $sqlres .= GETPOST('creneauid', 'int') . ",";
                    $sqlres .= $val['rowid'] . ",";
                    $sqlres .= "'" . GETPOST('infos' . $val['rowid'], 'alpha') . "',";
                    $sqlres .= "NULL" . ",";
                    if(GETPOST('daymonth', 'alpha'))
                    {
                         $sqlres .= "'".date('Y-m-d H:i:s', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."',";
                    }
                    else
                    {
                         $sqlres .="'" . date('Y-m-d H:i:s'). "',";
                    }
                    $sqlres .= $user->id . ",";
                    $sqlres .= "'" . GETPOST('presence' . $val['rowid'], 'alpha') . "',";
                    $sqlres .= 1 .")";

                    $db->query($sqlres);
               }
               else
               {
                    $sqlres = "INSERT INTO " . MAIN_DB_PREFIX . "appel (fk_etablissement, fk_creneau, fk_eleve, justification, action_faite, date_creation, fk_user_creat, status, treated) VALUES (";
                    $sqlres .= GETPOST('etablissementid', 'int') . ",";
                    $sqlres .= GETPOST('creneauid', 'int') . ",";
                    $sqlres .= $val['rowid'] . ",";
                    $sqlres .= "'" . GETPOST('infos' . $val['rowid'], 'alpha') . "',";
                    $sqlres .= "NULL" . ",";
                    if(GETPOST('daymonth', 'alpha'))
                    {
                         $sqlres .= "'".date('Y-m-d H:i:s', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."',";
                    }
                    else
                    {
                         $sqlres .="'" . date('Y-m-d H:i:s'). "',";
                    }
                    $sqlres .= $user->id . ",";
                    $sqlres .= "'" . GETPOST('presence' . $val['rowid'], 'alpha') . "',";
                    $sqlres .= 1 .")";

                    $db->query($sqlres);
               }
          }


          $sqlAppelProf1 = "SELECT * FROM " . MAIN_DB_PREFIX . "appel WHERE fk_user = " . $sqlProReal->fk_prof_1;
          $sqlAppelProf1 .= " AND fk_creneau = " . GETPOST('creneauid', 'int');
          $sqlAppelProf1 .= " AND treated = " . 1;
          $sqlAppelProf1 .= " ORDER BY rowid DESC";

          $resqlProf1 = $db->query($sqlAppelProf1);
          $resProf1 = $db->fetch_object($resqlProf1);
          $numProf1 = $db->num_rows($resProf1);

          if($resProf1 != null)
          {
               $sqlP = "UPDATE " . MAIN_DB_PREFIX . "appel SET treated = " . 0 . " WHERE rowid=" . $resProf1->rowid;
               $resql = $db->query($sqlP);

               $sqlResProf = "INSERT INTO " . MAIN_DB_PREFIX . "appel (fk_etablissement, fk_creneau, fk_user, justification, action_faite, date_creation, fk_user_creat, status, treated) VALUES (";
               $sqlResProf .= GETPOST('etablissementid', 'int') . ",";
               $sqlResProf .= GETPOST('creneauid', 'int') . ",";
               $sqlResProf .= $sqlProReal->fk_prof_1 . ",";
               $sqlResProf .= "'" . GETPOST('infos' . $sqlProReal->fk_prof_1, 'alpha') . "',";
               $sqlResProf .= "NULL" . ",";
               if(GETPOST('daymonth', 'alpha'))
               {
                    $sqlResProf .= "'".date('Y-m-d H:i:s', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."',";
               }
               else
               {
                    $sqlResProf .= "'" . date('Y-m-d H:i:s'). "',";
               }
               $sqlResProf .= $user->id . ",";
               $sqlResProf .= "'" . GETPOST('prof' . $sqlProReal->fk_prof_1, 'alpha') . "',";
               $sqlResProf .= 1 .")";

               $db->query($sqlResProf);
          }
          else
          {
               $sqlResProf = "INSERT INTO " . MAIN_DB_PREFIX . "appel (fk_etablissement, fk_creneau, fk_user, justification, action_faite, date_creation, fk_user_creat, status, treated) VALUES (";
               $sqlResProf .= GETPOST('etablissementid', 'int') . ",";
               $sqlResProf .= GETPOST('creneauid', 'int') . ",";
               $sqlResProf .= $sqlProReal->fk_prof_1 . ",";
               $sqlResProf .= "'" . GETPOST('infos' . $sqlProReal->fk_prof_1, 'alpha') . "',";
               $sqlResProf .= "NULL" . ",";
               if(GETPOST('daymonth', 'alpha'))
               {
                    $sqlResProf .= "'".date('Y-m-d H:i:s', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."',";
               }
               else
               {
                    $sqlResProf .= "'" . date('Y-m-d H:i:s'). "',";
               }
               $sqlResProf .= $user->id . ",";
               $sqlResProf .= "'" . GETPOST('prof' . $sqlProReal->fk_prof_1, 'alpha') . "',";
               $sqlResProf .= 1 .")";
          
               $db->query($sqlResProf);
          }
          
          


          $sqlAppelProf2 = "SELECT * FROM " . MAIN_DB_PREFIX . "appel WHERE fk_user = " . $sqlProReal->fk_prof_2;
          $sqlAppelProf2 .= " AND fk_creneau = " . GETPOST('creneauid', 'int');
          $sqlAppelProf2 .= " AND treated = " . 1;
          $sqlAppelProf2 .= " ORDER BY rowid DESC";

          $resqlProf2 = $db->query($sqlAppelProf2);
          if($resqlProf2)
          {
               $resProf2 = $db->fetch_object($resqlProf2);
               $numProf2 = $db->num_rows($resProf2);
               if($resProf2 != null)
               {
                    $sqlP = "UPDATE " . MAIN_DB_PREFIX . "appel SET treated = " . 0 . " WHERE rowid=" . $resProf2->rowid;
                    $resql = $db->query($sqlP);
     
                    $sqlResProf = "INSERT INTO " . MAIN_DB_PREFIX . "appel (fk_etablissement, fk_creneau, fk_user, justification, action_faite, date_creation, fk_user_creat, status, treated) VALUES (";
                    $sqlResProf .= GETPOST('etablissementid', 'int') . ",";
                    $sqlResProf .= GETPOST('creneauid', 'int') . ",";
                    $sqlResProf .= $sqlProReal->fk_prof_2 . ",";
                    $sqlResProf .= "'" . GETPOST('infos' . $sqlProReal->fk_prof_2, 'alpha') . "',";
                    $sqlResProf .= "NULL" . ",";
                    if(GETPOST('daymonth', 'alpha'))
                    {
                         $sqlResProf .= "'".date('Y-m-d H:i:s', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."',";
                    }
                    else
                    {
                         $sqlResProf .= "'" . date('Y-m-d H:i:s'). "',";
                    }
                    $sqlResProf .= $user->id . ",";
                    $sqlResProf .= "'" . GETPOST('prof' . $sqlProReal->fk_prof_2, 'alpha') . "',";
                    $sqlResProf .= 1 .")";
     
                    $db->query($sqlResProf);
                    
               }
               else
               {
                    $sqlResProf = "INSERT INTO " . MAIN_DB_PREFIX . "appel (fk_etablissement, fk_creneau, fk_user, justification, action_faite, date_creation, fk_user_creat, status, treated) VALUES (";
                    $sqlResProf .= GETPOST('etablissementid', 'int') . ",";
                    $sqlResProf .= GETPOST('creneauid', 'int') . ",";
                    $sqlResProf .= $sqlProReal->fk_prof_2 . ",";
                    $sqlResProf .= "'" . GETPOST('infos' . $sqlProReal->fk_prof_2, 'alpha') . "',";
                    $sqlResProf .= "NULL" . ",";
                    if(GETPOST('daymonth', 'alpha'))
                    {
                         $sqlResProf .= "'".date('Y-m-d H:i:s', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."',";
                    }
                    else
                    {
                         $sqlResProf .= "'" . date('Y-m-d H:i:s'). "',";
                    }
                    $sqlResProf .= $user->id . ",";
                    $sqlResProf .= "'" . GETPOST('prof' . $sqlProReal->fk_prof_2, 'alpha') . "',";
                    $sqlResProf .= 1 .")";
     
                    $db->query($sqlResProf);
               
               }
          }

          $sqlAppelProf3 = "SELECT * FROM " . MAIN_DB_PREFIX . "appel WHERE fk_user = " . $sqlProReal->fk_prof_3;
          $sqlAppelProf3 .= " AND fk_creneau = " . GETPOST('creneauid', 'int');
          $sqlAppelProf3 .= " AND treated = " . 1;
          $sqlAppelProf3 .= " ORDER BY rowid DESC";

          $resqlProf3 = $db->query($sqlAppelProf3);
          if($resqlProf3)
          {
               $resProf3 = $db->fetch_object($resqlProf3);
               $numProf3 = $db->num_rows($resProf3);
               if($resProf3 != null)
               {
                    $sqlP = "UPDATE " . MAIN_DB_PREFIX . "appel SET treated = " . 0 . " WHERE rowid=" . $resProf3->rowid;
                    $resql = $db->query($sqlP);
     
                    $sqlResProf = "INSERT INTO " . MAIN_DB_PREFIX . "appel (fk_etablissement, fk_creneau, fk_user, justification, action_faite, date_creation, fk_user_creat, status, treated) VALUES (";
                    $sqlResProf .= GETPOST('etablissementid', 'int') . ",";
                    $sqlResProf .= GETPOST('creneauid', 'int') . ",";
                    $sqlResProf .= $sqlProReal->fk_prof_3 . ",";
                    $sqlResProf .= "'" . GETPOST('infos' . $sqlProReal->fk_prof_3, 'alpha') . "',";
                    $sqlResProf .= "NULL" . ",";
                    if(GETPOST('daymonth', 'alpha'))
                    {
                         $sqlResProf .= "'".date('Y-m-d H:i:s', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."',";
                    }
                    else
                    {
                         $sqlResProf .= "'" . date('Y-m-d H:i:s'). "',";
                    }
                    $sqlResProf .= $user->id . ",";
                    $sqlResProf .= "'" . GETPOST('prof' . $sqlProReal->fk_prof_3, 'alpha') . "',";
                    $sqlResProf .= 1 .")";
     
                    $db->query($sqlResProf);
                    
               }
               else
               {
                    $sqlResProf = "INSERT INTO " . MAIN_DB_PREFIX . "appel (fk_etablissement, fk_creneau, fk_user, justification, action_faite, date_creation, fk_user_creat, status, treated) VALUES (";
                    $sqlResProf .= GETPOST('etablissementid', 'int') . ",";
                    $sqlResProf .= GETPOST('creneauid', 'int') . ",";
                    $sqlResProf .= $sqlProReal->fk_prof_3 . ",";
                    $sqlResProf .= "'" . GETPOST('infos' . $sqlProReal->fk_prof_3, 'alpha') . "',";
                    $sqlResProf .= "NULL" . ",";
                    if(GETPOST('daymonth', 'alpha'))
                    {
                         $sqlResProf .= "'".date('Y-m-d H:i:s', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."',";
                    }
                    else
                    {
                         $sqlResProf .= "'" . date('Y-m-d H:i:s'). "',";
                    }
                    $sqlResProf .= $user->id . ",";
                    $sqlResProf .= "'" . GETPOST('prof' . $sqlProReal->fk_prof_3, 'alpha') . "',";
                    $sqlResProf .= 1 .")";
     
                    $db->query($sqlResProf);
               
               }
          }
         

     }






     $action = 'create';
}
if ($action == 'create' && !GETPOST('etablissementid', 'int')) // SELECTION DU TYPE DE KIT
{
     //WYSIWYG Editor

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
     print '</td></tr>';
     // Type de Kit
     print '<tr><td class="fieldrequired titlefieldcreate">Selectionnez votre établissement : </td><td>';
     print $form->selectarray('etablissementid', $etablissements);
     print ' <a href="' . DOL_URL_ROOT . '/custom/scolarite/etablissement_card.php?action=create">';
     print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter un type de kit"></span>';
     print '</a>';
     print '</td>';
     print '</tr>';
     print "</table>";
     dol_fiche_end();
     print '<div class="center">';
     print '<input type="submit" class="button" value="Suivant">';
     print '</div>';
     print '</form>';
}
if (($action == 'create' or $action == 'modifAppel') && GETPOST('etablissementid', 'int'))  // Type de kit choisi -> création d'un nouveau kit
{
     //WYSIWYG Editor
     
     print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
     print '<input type="hidden" name="action" value="add">';
     print '<input type="hidden" name="etablissementid" value="' . GETPOST('etablissementid', 'int') . '">';

     $picto = 'kit';
     $title = 'Nouvel appel';
     $linkback = "";
     print talm_load_fiche_titre($title, $linkback, $picto);
     dol_fiche_head('');

     if(GETPOST('day', 'alpha'))
     {
          $JourSemaine = GETPOST('day', 'alpha');
          $heureActuelle = "23";
     }
     else
     {
          $JourSemaine = strftime('%u');
          $heureActuelle = strftime('%k');
     }

     if(GETPOST('daymonth', 'alpha'))
     {
          $day = GETPOST('daymonth', 'alpha');
     }
     else
     {
          $day = date('d');
     }

     if(GETPOST('month', 'alpha'))
     {
          $month = GETPOST('month', 'alpha');
     }
     else
     {
          $month = date('m');
     }

     if(GETPOST('year', 'alpha'))
     {
          $year = GETPOST('year', 'alpha');
     }
     else
     {
          $year = date('Y');
     }


     $sql = "SELECT c.rowid,c.nom_creneau,c.fk_dispositif FROM " . MAIN_DB_PREFIX . "creneau as c INNER JOIN " . MAIN_DB_PREFIX . "dispositif as d ON c.fk_dispositif = d.rowid INNER JOIN " . MAIN_DB_PREFIX . "c_heure as h ON c.heure_debut = h.rowid WHERE d.fk_etablissement =" . GETPOST('etablissementid', 'int') . " AND c.jour=" . $JourSemaine . " AND h.heure <=" . $heureActuelle." AND c.status =" . 4 ." ORDER BY h.rowid DESC";
     $resqlAffectation = $db->query($sql);

     foreach ($resqlAffectation as $val) {
          $sql1 = "SELECT e.nom, e.prenom,e.rowid FROM " . MAIN_DB_PREFIX . "souhait as s INNER JOIN " . MAIN_DB_PREFIX . "affectation as a ON a.fk_souhait = s.rowid INNER JOIN " . MAIN_DB_PREFIX . "eleve as e ON e.rowid = s.fk_eleve WHERE a.fk_creneau = " . $val['rowid'] . " AND a.status = 4";
          $resql = $db->query($sql1);

          $isComplete = true;
          $injustifiee = false;
          $treated = false;
          $countInj = 0;

          foreach ($resql as $res) {
               $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "appel WHERE fk_eleve = " . $res['rowid'] . " AND YEAR(date_creation) = " . $year;
               $sql .= " AND MONTH(date_creation) = " . $month;
               $sql .= " AND DAY(date_creation) = " . $day;
               $sql .= " AND fk_creneau = " . $val['rowid'];

               $resqlCount = $db->query($sql);
               $num = $db->num_rows($resqlCount);

               if ($num == 0) {
                    $isComplete = false;
               }


               $sql2 = "SELECT * FROM " . MAIN_DB_PREFIX . "appel WHERE fk_eleve = " . $res['rowid'] . " AND YEAR(date_creation) = " . $year;
               $sql2 .= " AND MONTH(date_creation) = " . $month;
               $sql2 .= " AND DAY(date_creation) = " . $day;
               $sql2 .= " AND fk_creneau = " . $val['rowid'];
               $sql2 .= " AND status = " . "'absenceI'";
               $sql2 .= " AND treated = " . 1;
               $sql2 .= " ORDER BY rowid DESC";

               $resqlCount2 = $db->query($sql2);

               if($resqlCount2)
               {
                    $num2 = $db->num_rows($resqlCount2);
                    $sqlProReal = $db->fetch_object($resqlCount2);
               }
               

               if ($num2 != 0) {
                    $injustifiee = true;
               }

               $sql4 = "SELECT * FROM " . MAIN_DB_PREFIX . "appel WHERE fk_eleve = " . $res['rowid'] . " AND YEAR(date_creation) = " . $year;
               $sql4 .= " AND MONTH(date_creation) = " . $month;
               $sql4 .= " AND DAY(date_creation) = " . $day;
               $sql4 .= " AND fk_creneau = " . $val['rowid'];
               $sql4 .= " AND status = " . "'absenceI'";
               $sql4 .= " AND justification = " . '""';
               $sql4 .= " AND treated = " . 1;
               $sql4 .= " ORDER BY rowid DESC";

               
               $resqlCount4 = $db->query($sql4);

               if($resqlCount4)
               {
                    $num4 = $db->num_rows($resqlCount4);
                    $sqlProReal4 = $db->fetch_object($resqlCount4);
               }
               
              

               if ($num4 != 0) {
                    $treated = true;
               }

               $sql3 = "SELECT COUNT(*) as total FROM " . MAIN_DB_PREFIX . "appel WHERE fk_eleve = " . $res['rowid'] . " AND YEAR(date_creation) = " . $year;
               $sql3 .= " AND MONTH(date_creation) = " . $month;
               $sql3 .= " AND DAY(date_creation) = " . $day;
               $sql3 .= " AND fk_creneau = " . $val['rowid'];
               $sql3 .= " AND status = " . "'absenceI'";
               $sql3 .= " AND treated = " . 1;
               $sql3 .= " ORDER BY rowid DESC";

               $resqlCount3 = $db->query($sql3);

               if($resqlCount3)
               {
                    $sqlProReal3 = $db->fetch_object($resqlCount3);
               }
               

               if($sqlProReal3->total == "1")
               {
                    $countInj++;
               }
               
          }
         
          
          // Check if prof are filled
          $sqlProf = "SELECT p.fk_prof_1, p.fk_prof_2, p.fk_prof_3 FROM " . MAIN_DB_PREFIX . "creneau as p WHERE p.rowid=" . $val['rowid'];
          $sqlProf = $db->query($sqlProf);
          $sqlProReal = $db->fetch_object($sqlProf);

          if ($sqlProReal->fk_prof_1) {
               $checkProf = "SELECT * FROM " . MAIN_DB_PREFIX . "appel WHERE fk_user = " . $sqlProReal->fk_prof_1 . " AND YEAR(date_creation) = " . $year;
               $checkProf .= " AND MONTH(date_creation) = " . $month;
               $checkProf .= " AND DAY(date_creation) = " . $day;
               $checkProf .= " AND fk_creneau = " . $val['rowid'];

               $resqlCountprof = $db->query($checkProf);
               $num = $db->num_rows($resqlCountprof);
               if ($num == 0) {
                    $isComplete = false;
               }
          }

          if ($sqlProReal->fk_prof_2) {
               $checkProf = "SELECT * FROM " . MAIN_DB_PREFIX . "appel WHERE fk_user = " . $sqlProReal->fk_prof_2 . " AND YEAR(date_creation) = " . $year;
               $checkProf .= " AND MONTH(date_creation) = " . $month;
               $checkProf .= " AND DAY(date_creation) = " . $day;
               $checkProf .= " AND fk_creneau = " . $val['rowid'];

               $resqlCountprof = $db->query($checkProf);
               $num = $db->num_rows($resqlCountprof);
               if ($num == 0) {
                    $isComplete = false;
               }
          }

          if ($sqlProReal->fk_prof_3) {
               $checkProf = "SELECT * FROM " . MAIN_DB_PREFIX . "appel WHERE fk_user = " . $sqlProReal->fk_prof_3 . " AND YEAR(date_creation) = " . $year;
               $checkProf .= " AND MONTH(date_creation) = " . $month;
               $checkProf .= " AND DAY(date_creation) = " . $day;
               $checkProf .= " AND fk_creneau = " . $val['rowid'];

               $resqlCountprof = $db->query($checkProf);
               $num = $db->num_rows($resqlCountprof);
               if ($num == 0) {
                    $isComplete = false;
               }
          }

          // if($resqlCount && $isComplete)
          // {
          //      $sqlProRealCount = $db->fetch_object($resqlCount);
          //      $userCreate = "SELECT lastname,firstname,rowid FROM " . MAIN_DB_PREFIX . "user WHERE rowid = " . $sqlProRealCount->fk_user_creat;
          //      $userCreateId = $db->query($userCreate);
          //      $usercreate = $db->fetch_object($userCreateId);
     
          // }
         
          print '<div class="appel-accordion' . ($isComplete ? "" : "-opened") . '" id="appel-' . $val['rowid'] . '">';
          print '<h3>';
          if ($isComplete && ($action == 'modifAppel' && $creneauid == $val['rowid'])) {
               print '<span class="badge  badge-status10 badge-status" style="color:white;">Appel en cours de modification</span> ';
          } elseif (!$isComplete) {
               print '<span class="badge  badge-status2 badge-status" style="color:white;">Appel non Fait</span> ';
          } elseif ($isComplete && $injustifiee && !$treated) {
               print '<span class="badge  badge-status4 badge-status" style="color:white;">Appel Fait par (à venir) </span>&nbsp;&nbsp;&nbsp;<span class="badge  badge-status8 badge-status" style="color:white;">'.$countInj.' Absence Injustifiée(s)</span>&nbsp;&nbsp;&nbsp;</span><span class="badge  badge-status4 badge-status" style="color:white;">Traitées</span> ';
          } elseif ($isComplete && $injustifiee && $treated) {
               print '<span class="badge  badge-status4 badge-status" style="color:white;">Appel Fait par (à venir) </span>&nbsp;&nbsp;&nbsp;<span class="badge  badge-status8 badge-status" style="color:white;">'.$countInj.' Absence Injustifiée(s)</span>&nbsp;&nbsp;&nbsp;</span><span class="badge  badge-status1 badge-status" style="color:white;">Non traitée(s) </span> ';
          } 
          else {
               print '<span class="badge  badge-status4 badge-status" style="color:white;">Appel Fait par (à venir) </span> ';
          }
          print $val['nom_creneau'];
          print '';

          print '</h3>';

          print '<div>';



          print '<form action="">';
          print '<table class="tagtable liste">';
          if (!$isComplete or ($action == 'modifAppel' && $creneauid == $val['rowid'])) {
               print '<input type="hidden" name="action" value="confirmAppel">';
               
          } else {
               print '<input type="hidden" name="action" value="modifAppel">';
          }
          if(GETPOST('day', 'alpha'))
          {
               print '<input type="hidden" name="day" value="'.GETPOST('day', 'alpha').'">';
               print '<input type="hidden" name="daymonth" value="'.GETPOST('daymonth', 'alpha').'">';
               print '<input type="hidden" name="month" value="'.GETPOST('month', 'alpha').'">';
               print '<input type="hidden" name="year" value="'.GETPOST('year', 'alpha').'">';
          }
          print '<input type="hidden" name="creneauid" value="' . $val['rowid'] . '">';
          print '<input type="hidden" name="etablissementid" value="' . GETPOST('etablissementid', 'id') . '">';


          $sqlProf = "SELECT u.lastname,u.firstname,u.rowid FROM " . MAIN_DB_PREFIX . "creneau as c INNER JOIN " . MAIN_DB_PREFIX . "user as u ON c.fk_prof_1 = u.rowid OR c.fk_prof_2 = u.rowid OR c.fk_prof_3 = u.rowid WHERE c.rowid = " . $val['rowid'];
          $profCreneau = $db->query($sqlProf);
          $prof = $db->fetch_object($profCreneau);


          print '<h3>Professeur</h3>';

          foreach ($profCreneau as $value) {
               $sqlProfPresence = "SELECT * FROM " . MAIN_DB_PREFIX . "appel WHERE fk_user=" . $value['rowid'] . " AND YEAR(date_creation) = " . $year;
               $sqlProfPresence .= " AND MONTH(date_creation) = " . $month;
               $sqlProfPresence .= " AND DAY(date_creation) = " . $day;
               $sqlProfPresence .= " AND fk_creneau = " . $val['rowid'];
               $sqlProfPresence .= " ORDER BY rowid DESC";


               $resqlProf = $db->query($sqlProfPresence);
               $profInfo = $db->fetch_object($resqlProf);

               print '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';
               print '<tr class="oddeven">';
               print '<td>';

               print '<a href="' . DOL_URL_ROOT . '/user/card.php?id=' . $value['rowid'] . '" target="_blank">' .'(Prof) '. $value['firstname'] . ' ' . $value['lastname']. '</a>';
               print '</td>';
               print '<td>';
               print '<input type="radio" ' . ($profInfo->status == 'present' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="present" name="prof' . $value['rowid'] . '" id="">&nbsp;<span class="badge  badge-status4 badge-status" style="color:white;">Présent</span>';
               print '</td>';
               print '<td>';
               print '<input type="radio"  ' . ($profInfo->status == 'retard' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="retard" name="prof' . $value['rowid'] . '" id="">&nbsp;<span class="badge  badge-status1 badge-status" style="color:white;">Retard</span>';
               print '</td>';
               print '<td>';
               print '<input type="radio" ' . ($profInfo->status == 'remplace' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="remplace" name="prof' . $value['rowid'] . '" id="">&nbsp;<span class="badge  badge-status5 badge-status" style="color:white;">Remplacé</span>';
               print '</td>';
               print '<td>';
               print '<input type="radio" ' . ($profInfo->status == 'absent' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="absent" name="prof' . $value['rowid'] . '" id="">&nbsp;<span class="badge  badge-status8 badge-status" style="color:white;">Absent</span>';
               print '</td>';
               print '<td>';
               print 'Infos: <input type="text" ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' name="infos' . $value["rowid"] . '" value="' . ($profInfo->justification) . '" id=""/>';
               print '</td>';
               print '</tr>';
          }

          print '<div>';
          print '</table>';
          print '<table class="tagtable liste">';
          print '<h3>Élèves</h3>';
          print '</div>';

          foreach ($resql as $res) {

               // Verifie si l'eleve a une entrée dans la bdd pour ce jour
               $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "appel WHERE fk_eleve = " . $res['rowid'] . " AND YEAR(date_creation) = " . $year;
               $sql .= " AND MONTH(date_creation) = " . $month;
               $sql .= " AND DAY(date_creation) = " . $day;
               $sql .= " AND fk_creneau = " . $val['rowid'];
               $sql .= " ORDER BY rowid DESC";

               $resqlEleve = $db->query($sql);

               $eleveInfo = $db->fetch_object($resqlEleve);

               print '<tr class="oddeven">';
               print '<td>';
               print '<a href="' . DOL_URL_ROOT . '/custom/viescolaire/eleve_card.php?id=' . $res['rowid'] . '" target="_blank">' . $res['prenom'] . ' ' . $res['nom'] . '</a>';

               print '</td>';
               print '<td>';
               print '<input type="radio"  ' . ($eleveInfo->status == 'present' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="present" name="presence' . $res["rowid"] . '" id="">&nbsp;<span class="badge  badge-status4 badge-status" style="color:white;">Présent</span>';
               print '</td>';
               print '<td>';
               print '<input type="radio"   ' . ($eleveInfo->status == 'retard' ? 'checked' : '') . ' ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="retard" name="presence' . $res["rowid"] . '" id="">&nbsp;<span class="badge  badge-status1 badge-status" style="color:white;">Retard</span>';
               print '</td>';
               print '<td>';
               print '<input type="radio" ' . ($eleveInfo->status == 'absenceJ' ? 'checked' : '') . ' ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="absenceJ" name="presence' . $res["rowid"] . '" id="">&nbsp;<span class="badge  badge-status5 badge-status" style="color:white;">Abscence justifiée</span>';
               print '</td>';
               print '<td>';
               print '<input type="radio" ' . ($eleveInfo->status == 'absenceI' ? 'checked' : '') . ' ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="absenceI" name="presence' . $res["rowid"] . '" id="">&nbsp;<span class="badge  badge-status8 badge-status" style="color:white;">Abscence injustifiée</span>';
               print '</td>';
               print '<td>';
               print 'Infos: <input type="text" ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="' . ($eleveInfo->justification) . '" name="infos' . $res["rowid"] . '" id=""/>';
               print '</td>';
               print '</tr>';
          }

          print '</table>';
          if (!$isComplete or ($action == 'modifAppel' && $creneauid == $val['rowid'])) {
               print '<div class="center"><input type="submit" class="button" style="background-color:cadetblue"></div>';
          } else {
               print '<div class="center"><input type="submit" value="Modifier l\'appel" class="button" style="background-color:lightslategray"></div>';
          }

          print '</form>';

          print '</div>';

          print '</div>';
     }

     print '<script>
		$( ".appel-accordion" ).accordion({
			collapsible: true,
			active: 2,
		});
		</script>';

     print '<script>
		$( ".appel-accordion-opened" ).accordion({
			collapsible: true,
		});
		</script>';
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
     print load_fiche_titre($langs->trans("Appel"), '', 'object_' . $object->picto);

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

     $head = appelPrepareHead($object);
     print dol_get_fiche_head($head, 'card', $langs->trans("Appel"), -1, $object->picto);

     $formconfirm = '';

     // Confirmation to delete
     if ($action == 'delete') {
          $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteAppel'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
     }
     // Confirmation to delete line
     if ($action == 'deleteline') {
          $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . '&lineid=' . $lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
     }
     // Clone confirmation
     if ($action == 'clone') {
          // Create an array for form
          $formquestion = array();
          $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
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
          $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
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
     $linkback = '<a href="' . dol_buildpath('/viescolaire/appel_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

     $morehtmlref = '<div class="refidno">';
     /*
	 // Ref customer
	 $morehtmlref.=$form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
	 $morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
	 // Thirdparty
	 $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
	 // Project
	 if (! empty($conf->projet->enabled)) {
	 $langs->load("projects");
	 $morehtmlref .= '<br>'.$langs->trans('Project') . ' ';
	 if ($permissiontoadd) {
	 //if ($action != 'classify') $morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&token='.newToken().'&id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> ';
	 $morehtmlref .= ' : ';
	 if ($action == 'classify') {
	 //$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
	 $morehtmlref .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
	 $morehtmlref .= '<input type="hidden" name="action" value="classin">';
	 $morehtmlref .= '<input type="hidden" name="token" value="'.newToken().'">';
	 $morehtmlref .= $formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
	 $morehtmlref .= '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
	 $morehtmlref .= '</form>';
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

     dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

     print '<div class="fichecenter">';
     print '<div class="fichehalfleft">';
     print '<div class="underbanner clearboth"></div>';
     print '<table class="border centpercent tableforfield">' . "\n";

     // Common attributes
     //$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
     //unset($object->fields['fk_project']);				// Hide field already shown in banner
     //unset($object->fields['fk_soc']);					// Hide field already shown in banner
     include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';

     // Other attributes. Fields from hook formObjectOptions and Extrafields.
     include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

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
          $result = $object->getLinesArray();

          print '	<form name="addproduct" id="addproduct" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . (($action != 'editline') ? '' : '#line_' . GETPOST('lineid', 'int')) . '" method="POST">
		<input type="hidden" name="token" value="' . newToken() . '">
		<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline') . '">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="page_y" value="">
		<input type="hidden" name="id" value="' . $object->id . '">
		';

          if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
               include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
          }

          print '<div class="div-table-responsive-no-min">';
          if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
               print '<table id="tablelines" class="noborder noshadow" width="100%">';
          }

          if (!empty($object->lines)) {
               $object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
          }

          // Form to add new line
          if ($object->status == 0 && $permissiontoadd && $action != 'selectlines') {
               if ($action != 'editline') {
                    // Add products/services form

                    $parameters = array();
                    $reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
                    if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
                    if (empty($reshook))
                         $object->formAddObjectLine(1, $mysoc, $soc);
               }
          }

          if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
               print '</table>';
          }
          print '</div>';

          print "</form>\n";
     }


     // Buttons for actions

     if ($action != 'presend' && $action != 'editline') {
          print '<div class="tabsAction">' . "\n";
          $parameters = array();
          $reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
          if ($reshook < 0) {
               setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
          }

          if (empty($reshook)) {
               // Send
               if (empty($user->socid)) {
                    print dolGetButtonAction($langs->trans('SendMail'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init&token=' . newToken() . '#formmailbeforetitle');
               }

               // Back to draft
               if ($object->status == $object::STATUS_VALIDATED) {
                    print dolGetButtonAction($langs->trans('SetToDraft'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=confirm_setdraft&confirm=yes&token=' . newToken(), '', $permissiontoadd);
               }

               print dolGetButtonAction($langs->trans('Modify'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=edit&token=' . newToken(), '', $permissiontoadd);

               // Validate
               if ($object->status == $object::STATUS_DRAFT) {
                    if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {
                         print dolGetButtonAction($langs->trans('Validate'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=confirm_validate&confirm=yes&token=' . newToken(), '', $permissiontoadd);
                    } else {
                         $langs->load("errors");
                         print dolGetButtonAction($langs->trans("ErrorAddAtLeastOneLineFirst"), $langs->trans("Validate"), 'default', '#', '', 0);
                    }
               }

               // Clone
               print dolGetButtonAction($langs->trans('ToClone'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . (!empty($object->socid) ? '&socid=' . $object->socid : '') . '&action=clone&token=' . newToken(), '', $permissiontoadd);

               /*
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_ENABLED) {
					print dolGetButtonAction($langs->trans('Disable'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=disable&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction($langs->trans('Enable'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=enable&token='.newToken(), '', $permissiontoadd);
				}
			}
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_VALIDATED) {
					print dolGetButtonAction($langs->trans('Cancel'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=close&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction($langs->trans('Re-Open'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=reopen&token='.newToken(), '', $permissiontoadd);
				}
			}
			*/

               // Delete (need delete permission, or if draft, just need create/modify permission)
               print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=delete&token=' . newToken(), '', $permissiontodelete || ($object->status == $object::STATUS_DRAFT && $permissiontoadd));
          }
          print '</div>' . "\n";
     }


     // Select mail models is same action as presend
     if (GETPOST('modelselected')) {
          $action = 'presend';
     }

     if ($action != 'presend') {
          print '<div class="fichecenter"><div class="fichehalfleft">';
          print '<a name="builddoc"></a>'; // ancre

          $includedocgeneration = 1;

          // Documents
          if ($includedocgeneration) {
               $objref = dol_sanitizeFileName($object->ref);
               $relativepath = $objref . '/' . $objref . '.pdf';
               $filedir = $conf->viescolaire->dir_output . '/' . $object->element . '/' . $objref;
               $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
               $genallowed = $permissiontoread; // If you can read, you can build the PDF to read content
               $delallowed = $permissiontoadd; // If you can create/edit, you can remove a file on card
               print $formfile->showdocuments('viescolaire:Appel', $object->element . '/' . $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
          }

          // Show links to link elements
          $linktoelem = $form->showLinkToObjectBlock($object, null, array('appel'));
          $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


          print '</div><div class="fichehalfright">';

          $MAXEVENT = 10;

          $morehtmlcenter = dolGetButtonTitle($langs->trans('SeeAll'), '', 'fa fa-list-alt imgforviewmode', dol_buildpath('/viescolaire/appel_agenda.php', 1) . '?id=' . $object->id);

          // List of actions on element
          include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
          $formactions = new FormActions($db);
          $somethingshown = $formactions->showactions($object, $object->element . '@' . $object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlcenter);

          print '</div></div>';
     }

     //Select mail models is same action as presend
     if (GETPOST('modelselected')) {
          $action = 'presend';
     }

     // Presend form
     $modelmail = 'appel';
     $defaulttopic = 'InformationMessage';
     $diroutput = $conf->viescolaire->dir_output;
     $trackid = 'appel' . $object->id;

     include DOL_DOCUMENT_ROOT . '/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
