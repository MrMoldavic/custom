<?php
/*ini_set('display_errors', '1');
 ini_set('display_startup_errors', '1');
 error_reporting(E_ALL);*/
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
if (!$res && !empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
     $res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT'] . '/main.inc.php';
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
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . '/main.inc.php')) {
     $res = @include substr($tmp, 0, ($i + 1)) . '/main.inc.php';
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . '/main.inc.php')) {
     $res = @include dirname(substr($tmp, 0, ($i + 1))) . '/main.inc.php';
}
// Try main.inc.php using relative path
if (!$res && file_exists('../main.inc.php')) {
     $res = @include '../main.inc.php';
}
if (!$res && file_exists('../../main.inc.php')) {
     $res = @include '../../main.inc.php';
}
if (!$res && file_exists('../../../main.inc.php')) {
     $res = @include '../../../main.inc.php';
}
if (!$res) {
     die('Include of main fails');
}

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/materiel/core/lib/functions.lib.php';
//require_once DOL_DOCUMENT_ROOT . '/custom/viescolaire/scripts/loader.css';

dol_include_once('/viescolaire/class/appel.class.php');
dol_include_once('/scolarite/class/dispositif.class.php');
dol_include_once('/scolarite/class/etablissement.class.php');

dol_include_once('/viescolaire/lib/viescolaire_appel.lib.php');
//dol_include_once('/viescolaire/scripts/loader.css');

// Load translation files required by the page
$langs->loadLangs(array('viescolaire@viescolaire', 'other'));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'appelcard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid   = GETPOST('lineid', 'int');
$creneauid = GETPOST('creneauid', 'int');
$getHeureActuelle = (GETPOST('heureActuelle', 'alpha') ? : intval(strftime('%k')));

strlen($getHeureActuelle) > 2 ? $heureActuelle = substr($getHeureActuelle, 0, 2) : $heureActuelle = $getHeureActuelle;




$allCreaneaux = GETPOST('allCreaneaux', 'alpha') ? GETPOST('allCreaneaux', 'alpha') : false;




// Initialize technical objects
$object = new Appel($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->viescolaire->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('appelcard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST('search_all', 'alpha');
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
     $permissiontodelete = $user->rights->viescolaire->appel->delete;
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

/*
 * View
 *
 * Put here all code to build page
 */
print '<style>
.loader {
  border: 8px solid #f3f3f3;
  border-radius: 50%;
  border-top: 8px solid #3498db;
  width: 20px;
  height:  20px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

/* Safari */
@-webkit-keyframes spin {
	0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
	0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>';
$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans('Appel');
$help_url = '';
llxHeader('', $title, $help_url);



// Part to create
if ($action == 'confirmAppel') {

     $sql = 'SELECT e.nom, e.prenom,e.rowid FROM ' . MAIN_DB_PREFIX . 'souhait as s INNER JOIN ' . MAIN_DB_PREFIX . 'affectation as a ON a.fk_souhait = s.rowid INNER JOIN ' . MAIN_DB_PREFIX . 'eleve as e ON e.rowid = s.fk_eleve WHERE a.fk_creneau = ' . GETPOST('creneauid', 'int') . ' AND a.status = 4';
     $sqlEleves = $db->query($sql);



     $sqlProf = 'SELECT p.fk_prof_1, p.fk_prof_2, p.fk_prof_3 FROM ' . MAIN_DB_PREFIX . 'creneau as p WHERE p.rowid=' . $creneauid;
     $sqlProf = $db->query($sqlProf);
     $sqlProReal = $db->fetch_object($sqlProf);

	 $error = 0;
     if(GETPOST('day', 'alpha') != '')
     {
          print '<input type="hidden" name="day" value="'.GETPOST('day', 'alpha').'">';
          print '<input type="hidden" name="daymonth" value="'.GETPOST('daymonth', 'alpha').'">';
          print '<input type="hidden" name="month" value="'.GETPOST('month', 'alpha').'">';
          print '<input type="hidden" name="year" value="'.GETPOST('year', 'alpha').'">';
     }

     // Check if all eleve / profs are filled
     $check = true;
     $checkInjustifiee = true;

     // Si on détecte pas d'appel en POST pour chaque élève du créneau, on renvoie une erreur
     foreach ($sqlEleves as $val) {
          if (!GETPOST('presence' . $val['rowid'], 'alpha')) $check = false;
     }

     // Si on ne detecte pas de professeur dans l'appel en POST du créneau, on renvoie une erreur (Pour l'instant en pause)

     if (!$check) {
          setEventMessage('Veuillez renseigner tous les champs.', 'errors');
          $creneauid = GETPOST('creneauid', 'int');
     }
     else {
          // pour chaque élève, ajout de l'appel
          foreach ($sqlEleves as $val) {
               // requête qui va chercher un appel déjà présent pour ce créneau
               $sqlAppel = 'SELECT justification,status,rowid FROM ' . MAIN_DB_PREFIX . 'appel WHERE fk_eleve = ' . $val['rowid'];
               $sqlAppel .= ' AND fk_creneau = ' . GETPOST('creneauid', 'int');
               $sqlAppel .= ' AND treated = ' . 1;
			   if(GETPOST('daymonth', 'alpha'))
			   {
				   $sqlAppel .= " AND date_creation LIKE '".date('Y-m-d', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."%'";
			   }
			   else
			   {
				   $sqlAppel .=" AND date_creation LIKE '" . date('Y-m-d'). "%'";
			   }
               $sqlAppel .= ' ORDER BY rowid DESC LIMIT 1';
               $resqlEleves = $db->query($sqlAppel);
               // Si appel déjà présent, cela indique que l'appel en modification et qu'on à une entrée différente de celle en BDD, donc un va modifier l'appel existant
               if($resqlEleves->num_rows > 0)
               {
                    $resEleves = $db->fetch_object($resqlEleves);
					if($resEleves->status != GETPOST('presence' . $val['rowid'], 'alpha') || $resEleves->justification != str_replace("'",'`',GETPOST('infos' . $val['rowid'], 'alpha')))
					{
						// On remplace par le nouveau status
						$sqlUpdateEleve = 'UPDATE ' . MAIN_DB_PREFIX . "appel SET status = '".GETPOST('presence' . $val['rowid'], 'alpha')."',justification='" . str_replace("'",'`',GETPOST('infos' . $val['rowid'], 'alpha')) . "' WHERE rowid=".$resEleves->rowid;
						if(!$db->query($sqlUpdateEleve)) $error++;
					}

               }
               // Sinon, cela veut dire que c'est la première fois que l'appel est fait aujourd'hui
               else
               {
                    $sqlres = 'INSERT INTO ' . MAIN_DB_PREFIX . 'appel (fk_etablissement, fk_creneau, fk_eleve, justification, action_faite, date_creation, fk_user_creat, status, treated) VALUES (';
                    $sqlres .= GETPOST('etablissementid', 'int') . ',';
                    $sqlres .= GETPOST('creneauid', 'int') . ',';
                    $sqlres .= $val['rowid'] . ',';
                    $sqlres .= "'" . str_replace("'",'`',GETPOST('infos' . $val['rowid'], 'alpha')) . "',";
                    $sqlres .= 'NULL' . ',';
                    if(GETPOST('daymonth', 'alpha'))
                    {
                         $sqlres .= "'".date('Y-m-d H:i:s', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."',";
                    }
                    else
                    {
                         $sqlres .="'" . date('Y-m-d H:i:s'). "',";
                    }
                    $sqlres .= $user->id . ',';
                    $sqlres .= "'" . GETPOST('presence' . $val['rowid'], 'alpha') . "',";
                    $sqlres .= 1 . ')';

                  	if(!$db->query($sqlres)) $error++;
               }
          }

		  if($sqlProReal->fk_prof_1 != NULL) {

			 //Ajout de l'appel pour le professeur 1
			 $sqlAppelProf1 = 'SELECT justification,status,rowid FROM ' . MAIN_DB_PREFIX . 'appel WHERE fk_user = ' . $sqlProReal->fk_prof_1;
			 $sqlAppelProf1 .= ' AND fk_creneau = ' . GETPOST('creneauid', 'int');
			 $sqlAppelProf1 .= ' AND treated = ' . 1;
			  if(GETPOST('daymonth', 'alpha'))
			  {
				  $sqlAppelProf1 .= " AND date_creation LIKE '".date('Y-m-d', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."%'";
			  }
			  else
			  {
				  $sqlAppelProf1 .=" AND date_creation LIKE '" . date('Y-m-d'). "%'";
			  }
			 $sqlAppelProf1 .= ' ORDER BY rowid DESC limit 1';

			 $resqlProf1 = $db->query($sqlAppelProf1);

			 // Si appel déjà présent, cela indique que l'appel en modification et qu'on à une entrée diffénte de celle en BDD, donc un va modifier l'appel existant
			 if ($resqlProf1->num_rows != 0) {
				 $resProf = $db->fetch_object($resqlProf1);
				 if($resProf->status != GETPOST('prof' . $sqlProReal->fk_prof_1, 'alpha') || $resProf->justification != str_replace("'",'`',GETPOST('infos' . $sqlProReal->fk_prof_1, 'alpha'))){
					 // On remplace par le nouveau status
					 $sqlUpdateProf = 'UPDATE ' . MAIN_DB_PREFIX . "appel SET status = '" . GETPOST('prof' . $sqlProReal->fk_prof_1, 'alpha') . "',justification='" . str_replace("'",'`',GETPOST('infos' . $sqlProReal->fk_prof_1, 'alpha')) . "' WHERE rowid=" . $resProf->rowid;
					 if (!$db->query($sqlUpdateProf)) $error++;
				 }
			 } else {
				 $sqlResProf = 'INSERT INTO ' . MAIN_DB_PREFIX . 'appel (fk_etablissement, fk_creneau, fk_user, justification, action_faite, date_creation, fk_user_creat, status, treated) VALUES (';
				 $sqlResProf .= GETPOST('etablissementid', 'int') . ',';
				 $sqlResProf .= GETPOST('creneauid', 'int') . ',';
				 $sqlResProf .= $sqlProReal->fk_prof_1 . ',';
				 $sqlResProf .= "'" . str_replace("'",'`',GETPOST('infos' . $sqlProReal->fk_prof_1, 'alpha')) . "',";
				 $sqlResProf .= 'NULL' . ',';
				 if (GETPOST('daymonth', 'alpha')) {
					 $sqlResProf .= "'" . date('Y-m-d H:i:s', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha')))) . "',";
				 } else {
					 $sqlResProf .= "'" . date('Y-m-d H:i:s') . "',";
				 }
				 $sqlResProf .= $user->id . ',';
				 $sqlResProf .= "'" . GETPOST('prof' . $sqlProReal->fk_prof_1, 'alpha') . "',";
				 $sqlResProf .= 1 . ')';

				 if (!$db->query($sqlResProf)) $error++;
			 }
		  }

          //Ajout de l'appel pour le professeur 2
          if($sqlProReal->fk_prof_2 != NULL)
          {

               $sqlAppelProf2 = 'SELECT justification,status,rowid FROM ' . MAIN_DB_PREFIX . 'appel WHERE fk_user = ' . $sqlProReal->fk_prof_2;
               $sqlAppelProf2 .= ' AND fk_creneau = ' . GETPOST('creneauid', 'int');
               $sqlAppelProf2 .= ' AND treated = ' . 1;
			   if(GETPOST('daymonth', 'alpha'))
			   {
				   $sqlAppelProf2 .= " AND date_creation LIKE '".date('Y-m-d', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."%'";
			   }
			   else
			   {
				   $sqlAppelProf2 .=" AND date_creation LIKE '" . date('Y-m-d'). "%'";
			   }
               $sqlAppelProf2 .= ' ORDER BY rowid DESC limit 1';
               $resqlProf2 = $db->query($sqlAppelProf2);

               // Si appel déjà présent, cela indique que l'appel en modification et qu'on à une entrée diffénte de celle en BDD, donc un va modifier l'appel existant
               if($resqlProf2->num_rows != 0)
               {
                    $resProf2 = $db->fetch_object($resqlProf2);
                    // On remplace par le nouveau status
				   if($resProf2 != GETPOST('prof' . $sqlProReal->fk_prof_2, 'alpha') || $resProf2->justification != str_replace("'",'`',GETPOST('infos' . $sqlProReal->fk_prof_2, 'alpha'))){
					   $sqlUpdateProf = 'UPDATE ' . MAIN_DB_PREFIX . "appel SET status = '".GETPOST('prof' . $sqlProReal->fk_prof_2, 'alpha')."',justification='" . str_replace("'",'`',GETPOST('infos' . $sqlProReal->fk_prof_2, 'alpha')) . "' WHERE rowid=".$resProf2->rowid;
					   if(!$db->query($sqlUpdateProf)) $error++;
				   }
               }
               else
               {
                    $sqlResProf = 'INSERT INTO ' . MAIN_DB_PREFIX . 'appel (fk_etablissement, fk_creneau, fk_user, justification, action_faite, date_creation, fk_user_creat, status, treated) VALUES (';
                    $sqlResProf .= GETPOST('etablissementid', 'int') . ',';
                    $sqlResProf .= GETPOST('creneauid', 'int') . ',';
                    $sqlResProf .= $sqlProReal->fk_prof_2 . ',';
                    $sqlResProf .= "'" . str_replace("'",'`',GETPOST('infos' . $sqlProReal->fk_prof_2, 'alpha')) . "',";
                    $sqlResProf .= 'NULL' . ',';
                    if(GETPOST('daymonth', 'alpha'))
                    {
                         $sqlResProf .= "'".date('Y-m-d H:i:s', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."',";
                    }
                    else
                    {
                         $sqlResProf .= "'" . date('Y-m-d H:i:s'). "',";
                    }
                    $sqlResProf .= $user->id . ',';
                    $sqlResProf .= "'" . GETPOST('prof' . $sqlProReal->fk_prof_2, 'alpha') . "',";
                    $sqlResProf .= 1 . ')';

                    if(!$db->query($sqlResProf)) $error++;
               }

          }


          //Ajout de l'appel pour le professeur 3
		 if($sqlProReal->fk_prof_3 != NULL)
		 {

			 $sqlAppelProf3 = 'SELECT justification,status,rowid FROM ' . MAIN_DB_PREFIX . 'appel WHERE fk_user = ' . $sqlProReal->fk_prof_3;
			 $sqlAppelProf3 .= ' AND fk_creneau = ' . GETPOST('creneauid', 'int');
			 $sqlAppelProf3 .= ' AND treated = ' . 1;
			 if(GETPOST('daymonth', 'alpha'))
			 {
				 $sqlAppelProf3 .= " AND date_creation LIKE '".date('Y-m-d', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."%'";
			 }
			 else
			 {
				 $sqlAppelProf3 .=" AND date_creation LIKE '" . date('Y-m-d'). "%'";
			 }
			 $sqlAppelProf3 .= ' ORDER BY rowid DESC limit 1';
			 $resqlProf3 = $db->query($sqlAppelProf3);

			 // Si appel déjà présent, cela indique que l'appel en modification et qu'on à une entrée diffénte de celle en BDD, donc un va modifier l'appel existant
			 if($resqlProf3->num_rows != 0)
			 {
				 $resProf3 = $db->fetch_object($resqlProf3);
				 // On remplace par le nouveau status
				 if($resProf3 != GETPOST('prof' . $sqlProReal->fk_prof_3, 'alpha') || $resProf3->justification != str_replace("'",'`',GETPOST('infos' . $sqlProReal->fk_prof_3, 'alpha'))){
					 $sqlUpdateProf = 'UPDATE ' . MAIN_DB_PREFIX . "appel SET status = '".GETPOST('prof' . $sqlProReal->fk_prof_3, 'alpha')."',justification='" . str_replace("'",'`',GETPOST('infos' . $sqlProReal->fk_prof_3, 'alpha')) . "' WHERE rowid=".$resProf3->rowid;
					 if(!$db->query($sqlUpdateProf)) $error++;
				 }
			 }
			 else
			 {
				 $sqlResProf = 'INSERT INTO ' . MAIN_DB_PREFIX . 'appel (fk_etablissement, fk_creneau, fk_user, justification, action_faite, date_creation, fk_user_creat, status, treated) VALUES (';
				 $sqlResProf .= GETPOST('etablissementid', 'int') . ',';
				 $sqlResProf .= GETPOST('creneauid', 'int') . ',';
				 $sqlResProf .= $sqlProReal->fk_prof_3 . ',';
				 $sqlResProf .= "'" . str_replace("'",'`',GETPOST('infos' . $sqlProReal->fk_prof_3, 'alpha')) . "',";
				 $sqlResProf .= 'NULL' . ',';
				 if(GETPOST('daymonth', 'alpha'))
				 {
					 $sqlResProf .= "'".date('Y-m-d H:i:s', mktime(0, 0, 0, GETPOST('month', 'alpha'), GETPOST('daymonth', 'alpha'), date(GETPOST('year', 'alpha'))))."',";
				 }
				 else
				 {
					 $sqlResProf .= "'" . date('Y-m-d H:i:s'). "',";
				 }
				 $sqlResProf .= $user->id . ',';
				 $sqlResProf .= "'" . GETPOST('prof' . $sqlProReal->fk_prof_3, 'alpha') . "',";
				 $sqlResProf .= 1 . ')';

				 if(!$db->query($sqlResProf)) $error++;
			 }

		 }
		  if($error > 0) setEventMessage('Une erreur est survenue','errors');
		  else setEventMessage('Appel réalisé avec succès!');
     }

     if($creneauid && !$check) $action = 'returnFromError';
     else $action = 'create';

}
if ($action == 'create' && !GETPOST('etablissementid', 'int')) // SELECTION DU TYPE DE KIT
{
     //WYSIWYG Editor

	 $etablissementClass = new Etablissement($db);
	 $etablissementFetch = $etablissementClass->fetchBy(['nom','rowid'],0,'');
     $etablissements = [];

     foreach ($etablissementFetch as $val) {
          $etablissements[$val->rowid] = $val->nom;
     }

     print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
     print '<input type="hidden" name="action" value="create">';
     $titre = 'Nouvel Appel';
     print talm_load_fiche_titre($title, $linkback, $picto);
     dol_fiche_head('');
     print '<table class="border centpercent">';
     print '<tr>';
     print '</td></tr>';
     // Type de Kit
     print '<tr><td class="fieldrequired titlefieldcreate">Selectionnez votre établissement : </td><td>';
     print $form->selectarray('etablissementid', $etablissements);
     print ' <a href="' . DOL_URL_ROOT . '/custom/scolarite/etablissement_card.php?action=create">';
     print '</a>';
     print '</td>';
     print '</tr>';
     print '</table>';
	 dol_fiche_end();
     print '<div class="center">';
     print '<input type="submit" class="button" value="Suivant">';
     print '</div>';
     print '</form>';
}
if (($action == 'create' or $action == 'modifAppel' or $action == 'returnFromError') && GETPOST('etablissementid', 'int'))
{

     $picto = 'kit';
     $title = 'Nouvel appel';
     $linkback = '';
     print talm_load_fiche_titre($title, $linkback, $picto);
     dol_fiche_head('');

     print '<a href="' . DOL_URL_ROOT . '/custom/viescolaire/appel_card.php?etablissementid=' . GETPOST('etablissementid', 'int') . '&allCreaneaux='.(GETPOST('allCreaneaux','alpha') == true ? false : true).'&action=create">'.(GETPOST('allCreaneaux','alpha') == false ? 'Afficher tous les créneaux' : 'Afficher seulement les créneaux de l\'heure actuelle').'</a><br>';
	 // Form sélection de l'heure désirée
	print '<form method="POST" action="/custom/viescolaire/appel_card.php?etablissementid=' . GETPOST('etablissementid', 'int') . '&action=create" method="post">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print dol_get_fiche_head(array(), '');
	print '<table class="border centpercent ">'."\n";

	print '<div class="center">';
	print '<label>Selectionnez l\'heure désirée : </label>';

	print '<input type="time" name="heureActuelle" value="'.$heureActuelle.':00" >';
	print '</div>';
	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel('Valider');

	print '</form>';

     // Si on viens du sommaire on affiche d'un coups tout les créneaux
     if(GETPOST('day', 'alpha') != '')
     {
          $JourSemaine = GETPOST('day', 'alpha');
		  $minuteActuelle = 0;
     }
     else
     {
          $JourSemaine = intval(date('N'));
		  $minuteActuelle = intval(date('i'));
     }


     if(GETPOST('daymonth', 'alpha'))  $day = GETPOST('daymonth', 'alpha');
     else  $day = date('d');

     if(GETPOST('month', 'alpha')) $month = GETPOST('month', 'alpha');
     else $month = date('m');


     if(GETPOST('year', 'alpha')) $year = GETPOST('year', 'alpha');
     else $year = date('Y');


     // Requête qui va chercher tous les créneaux d'une heure donné, selon le dispositif sélectionné plus tôt
	$sql = 'SELECT c.rowid, c.nom_creneau, c.fk_dispositif, c.heure_debut
        FROM ' . MAIN_DB_PREFIX . 'creneau as c
        INNER JOIN ' . MAIN_DB_PREFIX . 'dispositif as d ON c.fk_dispositif = d.rowid
        WHERE d.fk_etablissement = ' . GETPOST('etablissementid', 'int') . '
            AND c.jour = ' . $JourSemaine . '
            AND (c.heure_debut ' . ($allCreaneaux ? '<=23' : '=' . $heureActuelle*3600) .
		' ' . ($minuteActuelle > 49 ? ' OR c.heure_debut=' . ($heureActuelle+1)*3600 : '') .')
            AND c.status = ' . 4 . '
        ORDER BY c.rowid DESC, c.fk_instrument_enseigne ASC';
	$resqlAffectation = $db->query($sql);

	var_dump($sql);

	if($resqlAffectation->num_rows == 0)
	{
		setEventMessage("Aucun cours à cette heure, changez d'horaire.",'errors');
	}

	$heureAffichage = 0;
	foreach ($resqlAffectation as $val)
	{
		$sqlheure = 'SELECT heure,rowid FROM ' .MAIN_DB_PREFIX. 'c_heure WHERE rowid= ' .$val['heure_debut'];
		$resqlheure = $db->query($sqlheure);
		$objHeure = $db->fetch_object($resqlheure);
		// Affichage de l'heure de chaque boucle afin de mieux identifier les créneaux
		if($heureAffichage != $objHeure->heure)
		{
			print '<div style="dislay:flex">';
			print '<h3 id="'.$objHeure->heure.'h">Créneau(x) de '.$objHeure->heure.'H⬇️</h3>';
			print '</div>';

			$heureAffichage = $objHeure->heure;
		}


		// requête qui va chercher tout les élèves d'un créneau à un horaire donné
		$sql1 = 'SELECT e.nom, e.prenom,e.rowid FROM ' . MAIN_DB_PREFIX . 'souhait as s INNER JOIN ' . MAIN_DB_PREFIX . 'affectation as a ON a.fk_souhait = s.rowid INNER JOIN ' . MAIN_DB_PREFIX . 'eleve as e ON e.rowid = s.fk_eleve WHERE a.fk_creneau = ' . $val['rowid'] . ' AND a.status = 4';
		$resql = $db->query($sql1);

		// variables nécessaires pour la validation
		$isComplete = true;
		$injustifiee = false;
		$treated = false;
		$countInj = 0;

		// boucle pour chaque élève
		foreach ($resql as $res) {
			// va chercher tout les appels présents pour savoir si l'appel est terminé ou non
			$sql = 'SELECT DISTINCT fk_creneau,status,treated,justification,rowid FROM ' . MAIN_DB_PREFIX . 'appel WHERE fk_eleve = ' . $res['rowid'] . ' AND YEAR(date_creation) = ' . $year;
			$sql .= ' AND MONTH(date_creation) = ' . $month;
			$sql .= ' AND DAY(date_creation) = ' . $day;
			$sql .= ' AND fk_creneau = ' . $val['rowid'];
			$sql .= ' AND treated = ' . 1;

			$resqlCountAppelEleve = $db->query($sql);
			// Si aucun résultat, l'appel l'appel n'as pas été fait
			if ($resqlCountAppelEleve->num_rows == 0) $isComplete = false;
			else{
				// Sinon, pour chaque appel d'élève trouvé, on regarde chaque résultat et check certaines conditions qui serviront ensuite à l'affichage
				foreach($resqlCountAppelEleve as $value)
				{
					if($value['status'] == 'absenceI' && $value['treated'] == 1){
						$injustifiee = true;
						$countInj++;
					}
					if($value['status'] == 'absenceI' && $value['justification'] == '' && $value['treated'] == 1) $treated = true;
				}
			}
		}


		// On va chercher les professeurs du créneau
		$sqlProf = 'SELECT p.fk_prof_1, p.fk_prof_2, p.fk_prof_3 FROM ' . MAIN_DB_PREFIX . 'creneau as p WHERE p.rowid=' . $val['rowid'];
		$sqlProf = $db->query($sqlProf);
		$sqlProReal = $db->fetch_object($sqlProf);

		// Ensuite
		if ($sqlProReal->fk_prof_1) {
			$checkProf = 'SELECT rowid,fk_user_creat FROM ' . MAIN_DB_PREFIX . 'appel WHERE fk_user = ' . $sqlProReal->fk_prof_1 . ' AND YEAR(date_creation) = ' . $year;
			$checkProf .= ' AND MONTH(date_creation) = ' . $month;
			$checkProf .= ' AND DAY(date_creation) = ' . $day;
			$checkProf .= ' AND fk_creneau = ' . $val['rowid'];

			$resqlCountprof = $db->query($checkProf);
			if($resqlCountprof->num_rows == 0) $isComplete = false;
		}

		if ($sqlProReal->fk_prof_2) {

			$checkProf = 'SELECT rowid,fk_user_creat FROM ' . MAIN_DB_PREFIX . 'appel WHERE fk_user = ' . $sqlProReal->fk_prof_2 . ' AND YEAR(date_creation) = ' . $year;
			$checkProf .= ' AND MONTH(date_creation) = ' . $month;
			$checkProf .= ' AND DAY(date_creation) = ' . $day;
			$checkProf .= ' AND fk_creneau = ' . $val['rowid'];

			$resqlCountprof = $db->query($checkProf);
			if($resqlCountprof->num_rows == 0) $isComplete = false;
		}

		if ($sqlProReal->fk_prof_3) {
			$checkProf = 'SELECT rowid,fk_user_creat FROM ' . MAIN_DB_PREFIX . 'appel WHERE fk_user = ' . $sqlProReal->fk_prof_3 . ' AND YEAR(date_creation) = ' . $year;
			$checkProf .= ' AND MONTH(date_creation) = ' . $month;
			$checkProf .= ' AND DAY(date_creation) = ' . $day;
			$checkProf .= ' AND fk_creneau = ' . $val['rowid'];

			$resqlCountprof = $db->query($checkProf);
			if($resqlCountprof->num_rows == 0) $isComplete = false;
		}


		// Div accordéon d'un cours
		print '<div class="appel-accordion'.(($action == 'modifAppel' || $action == 'returnFromError') && ($creneauid == $val['rowid']) ? '-opened' : '').'" id="appel-' . $val['rowid'] . '">';
		print '<h3>';

		if($isComplete)
		{
			$userCreatAppel = $db->fetch_object($resqlCountprof);

			$sqlAppelCreat = 'SELECT firstname,lastname,rowid FROM ' .MAIN_DB_PREFIX. 'user WHERE rowid= ' .$userCreatAppel->fk_user_creat;
			$resqlAppelCreat = $db->query($sqlAppelCreat);
			if($resqlAppelCreat->num_rows > 0)
			{
				$appelObjectCreat = $db->fetch_object($resqlAppelCreat);
			}
			else $errorFetchScola = "Une erreur est survenue";

		}

		if ($isComplete && ($action == 'modifAppel' && $creneauid == $val['rowid'])) {
			print '<span class="badge  badge-status10 badge-status" style="color:white;">Appel en cours de modification</span> ';
		} elseif (!$isComplete) {
			print '<span class="badge  badge-status2 badge-status" style="color:white;">Appel non Fait</span> ';
		} elseif ($isComplete && $injustifiee && !$treated) {
			print '<span class="badge  badge-status4 badge-status" style="color:white;">Appel Fait par '.($errorFetchScola ? : $appelObjectCreat->firstname.' '.$appelObjectCreat->lastname).'</span>&nbsp;&nbsp;&nbsp;<span class="badge  badge-status8 badge-status" style="color:white;">'.$countInj.' Absence Injustifiée(s)</span>&nbsp;&nbsp;&nbsp;</span><span class="badge  badge-status4 badge-status" style="color:white;">Traitées</span> ';
		} elseif ($isComplete && $injustifiee && $treated) {
			print '<span class="badge  badge-status4 badge-status" style="color:white;">Appel Fait par '.($errorFetchScola ? : $appelObjectCreat->firstname.' '.$appelObjectCreat->lastname).'</span>&nbsp;&nbsp;&nbsp;<span class="badge  badge-status8 badge-status" style="color:white;">'.$countInj.' Absence Injustifiée(s)</span>&nbsp;&nbsp;&nbsp;</span><span class="badge  badge-status1 badge-status" style="color:white;">Non traitée(s) </span> ';
		}
		else {
			print '<span class="badge  badge-status4 badge-status" style="color:white;">Appel Fait par '.($errorFetchScola ? : $appelObjectCreat->firstname.' '.$appelObjectCreat->lastname).'</span> ';
		}
		print $val['nom_creneau'];
		print '';

		print '</h3>';

		print '<div>';

		print '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		if (!$isComplete or ($action == 'modifAppel' && $creneauid == $val['rowid'])) {
			print '<input type="hidden" name="action" value="confirmAppel">';
		} else {
			print '<input type="hidden" name="action" value="modifAppel">';
		}
		if(GETPOST('day', 'alpha') != '')
		{
			print '<input type="hidden" name="day" value="'.GETPOST('day', 'alpha').'">';
			print '<input type="hidden" name="daymonth" value="'.GETPOST('daymonth', 'alpha').'">';
			print '<input type="hidden" name="month" value="'.GETPOST('month', 'alpha').'">';
			print '<input type="hidden" name="year" value="'.GETPOST('year', 'alpha').'">';
		}
		print '<input type="hidden" name="creneauid" value="' . $val['rowid'] . '">';
		print '<input type="hidden" name="etablissementid" value="' . GETPOST('etablissementid', 'id') . '">';
		print '<input type="hidden" name="heureActuelle" value="' . ($heureActuelle*3600) . '">';



		// requête qui va chercher les agents du créneau
		$sqlProf = 'SELECT u.nom,u.prenom,u.rowid FROM ' . MAIN_DB_PREFIX . 'creneau as c INNER JOIN ' . MAIN_DB_PREFIX . 'management_agent as u ON c.fk_prof_1 = u.rowid OR c.fk_prof_2 = u.rowid OR c.fk_prof_3 = u.rowid WHERE c.rowid = ' . $val['rowid'];
		$profCreneau = $db->query($sqlProf);
		$prof = $db->fetch_object($profCreneau);

		print '<table class="tagtable liste">';
		print '<h3>Professeur</h3>';

		foreach ($profCreneau as $value) {
			$sqlProfPresence = 'SELECT status,justification,rowid FROM ' . MAIN_DB_PREFIX . 'appel WHERE fk_user=' . $value['rowid'] . ' AND YEAR(date_creation) = ' . $year;
			$sqlProfPresence .= ' AND MONTH(date_creation) = ' . $month;
			$sqlProfPresence .= ' AND DAY(date_creation) = ' . $day;
			$sqlProfPresence .= ' AND fk_creneau = ' . $val['rowid'];
			$sqlProfPresence .= ' ORDER BY rowid DESC';


			$resqlProf = $db->query($sqlProfPresence);
			if($resqlProf->num_rows > 0)
			{
				$profInfo = $db->fetch_object($resqlProf);
			}


			print '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';
			print '<tr class="oddeven">';
			print '<td>';

			print '<a href="' . DOL_URL_ROOT . '/custom/management/agent_card.php?id=' . $value['rowid'] . '" target="_blank">' .'(Prof) '. $value['prenom'] . ' ' . $value['nom']. '</a>';
			print '</td>';
			print '<td>';
			print '<input type="radio" ' . (isset($profInfo) && $profInfo->status == 'present' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="present" name="prof' . $value['rowid'] . '" id="">&nbsp;<span class="badge  badge-status4 badge-status" style="color:white;">Présent</span>';
			print '</td>';
			print '<td>';
			print '<input type="radio"  ' . (isset($profInfo) && $profInfo->status == 'retard' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="retard" name="prof' . $value['rowid'] . '" id="">&nbsp;<span class="badge  badge-status1 badge-status" style="color:white;">Retard</span>';
			print '</td>';
			print '<td>';
			print '<input type="radio" ' . (isset($profInfo) && $profInfo->status == 'remplace' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="remplace" name="prof' . $value['rowid'] . '" id="">&nbsp;<span class="badge  badge-status5 badge-status" style="color:white;">Remplacé</span>';
			print '</td>';
			print '<td>';
			print '<input type="radio" ' . (isset($profInfo) && $profInfo->status == 'absent' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="absent" name="prof' . $value['rowid'] . '" id="">&nbsp;<span class="badge  badge-status8 badge-status" style="color:white;">Absent</span>';
			print '</td>';
			print '<td>';
			print 'Infos: <input type="text" ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' name="infos' . $value['rowid'] . '" value="' . (isset($profInfo) ? $profInfo->justification : '') . '" id=""/>';
			print '</td>';
			print '</tr>';

			unset($profInfo);
		}

		print '<div>';
		print '</table>';
		print '<table class="tagtable liste">';
		print '<h3>Élèves</h3>';
		print '</div>';

		foreach ($resql as $res) {

			// Verifie si l'eleve a une entrée dans la bdd pour ce jour
			$sql = 'SELECT status,justification FROM ' . MAIN_DB_PREFIX . 'appel WHERE fk_eleve = ' . $res['rowid'] . ' AND YEAR(date_creation) = ' . $year;
			$sql .= ' AND MONTH(date_creation) = ' . $month;
			$sql .= ' AND DAY(date_creation) = ' . $day;
			$sql .= ' AND fk_creneau = ' . $val['rowid'];
			$sql .= ' ORDER BY rowid DESC';

			$resqlEleve = $db->query($sql);
			if($resqlEleve->num_rows > 0)
			{
				$eleveInfo = $db->fetch_object($resqlEleve);
			}


			print '<tr class="oddeven">';
			print '<td>';
			print '<a href="' . DOL_URL_ROOT . '/custom/viescolaire/eleve_card.php?id=' . $res['rowid'] . '" target="_blank">' . $res['prenom'] . ' ' . $res['nom'] . '</a>';

			print '</td>';
			print '<td>';
			print '<input type="radio"  ' . (isset($eleveInfo) && $eleveInfo->status == 'present' ? 'checked' : '') . '  ' . ($isComplete == 1 && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="present" name="presence' . $res['rowid'] . '" id="">&nbsp;<span class="badge  badge-status4 badge-status" style="color:white;">Présent</span>';
			print '</td>';
			print '<td>';
			print '<input type="radio"   ' . (isset($eleveInfo) && $eleveInfo->status == 'retard' ? 'checked' : '') . ' ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="retard" name="presence' . $res['rowid'] . '" id="">&nbsp;<span class="badge  badge-status1 badge-status" style="color:white;">Retard</span>';
			print '</td>';
			print '<td>';
			print '<input type="radio" ' . (isset($eleveInfo) && $eleveInfo->status == 'absenceJ' ? 'checked' : '') . ' ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="absenceJ" name="presence' . $res['rowid'] . '" id="">&nbsp;<span class="badge  badge-status5 badge-status" style="color:white;">Abscence justifiée</span>';
			print '</td>';
			print '<td>';
			print '<input type="radio" ' . (isset($eleveInfo) && $eleveInfo->status == 'absenceI' ? 'checked' : '') . ' ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="absenceI" name="presence' . $res['rowid'] . '" id="">&nbsp;<span class="badge  badge-status8 badge-status" style="color:white;">Abscence injustifiée</span>';
			print '</td>';
			print '<td>';
			print 'Infos: <input type="text" ' . ($isComplete && $action == 'modifAppel' && $creneauid == $val['rowid'] ? '' : ($isComplete ? 'disabled' : '')) . ' value="' . (isset($eleveInfo) ? $eleveInfo->justification : '') . '" name="infos' . $res['rowid'] . '" id=""/>';
			print '</td>';
			print '</tr>';

			unset($eleveInfo);
		}
		print '</table>';
		if (!$isComplete or ($action == 'modifAppel' && $creneauid == $val['rowid'])) {
			print '<div class="center" style="display: flex; align-items: center; justify-content: center; flex-direction: column">';
			print '<div id="loader-'.$val['rowid'].'"></div>';
			print '<input type="submit" id="'.$val['rowid'].'" value="Valider l\'appel" class="button appelButton" style="background-color:lightslategray">';
			print '</div>';
		} else {
			print '<div class="center" style="display: flex; align-items: center; justify-content: center; flex-direction: column">';
			print '<div id="loader-'.$val['rowid'].'"></div>';
			print '<input type="submit" id="'.$val['rowid'].'" value="Modifier l\'appel" class="button appelButton" style="background-color:lightslategray">';
			print '</div>';
		}

		print '</form>';

		print '</div>';
		print '</div>';

	}

	print '<script>
		$( ".appelButton" ).on( "click", function() {
		  this.style.opacity = 0;
		 $( "#loader-"+this.id).addClass("loader");
		 $( "#loader-"+this.id).style.opacity = 1;
		});
		</script>';

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

//print '<script src="scripts/script_appel.js" defer></script>';

// End of page
llxFooter();
$db->close();
