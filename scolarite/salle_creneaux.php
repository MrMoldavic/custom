<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *  \file       salle_creneaux.php
 *  \ingroup    scolarite
 *  \brief      Tab for contacts linked to salle
 */

/*ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/

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

require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
dol_include_once('/scolarite/class/salle.class.php');
dol_include_once('/scolarite/class/creneau.class.php');
dol_include_once('/scolarite/lib/scolarite_salle.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("scolarite@scolarite", "companies", "other", "mails"));

$id     = (GETPOST('id') ?GETPOST('id', 'int') : GETPOST('facid', 'int')); // For backward compatibility
$ref    = GETPOST('ref', 'alpha');
$lineid = GETPOST('lineid', 'int');
$socid  = GETPOST('socid', 'int');
$action = GETPOST('action', 'aZ09');

// Initialize technical objects
$object = new Salle($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->scolarite->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('sallesallex', 'globalcard')); // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->scolarite->scolarite->read;
	$permission = $user->rights->scolarite->scolarite->create;
} else {
	$permissiontoread = 1;
	$permission = 1;
}

// Security check (enable the most restrictive one)
if (empty($conf->scolarite->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();


/*
 * View
 */

$title = $langs->trans('Créneaux Salle');
$help_url = '';
//$help_url='EN:Module_Third_Parties|FR:Module_Tiers|ES:Empresas';
llxHeader('', $title, $help_url);

$form = new Form($db);
$formcompany = new FormCompany($db);
$contactstatic = new Contact($db);
$userstatic = new User($db);


/* *************************************************************************** */
/*                                                                             */
/* View and edit mode                                                         */
/*                                                                             */
/* *************************************************************************** */

if ($object->id) {
	/*
	 * Show tabs
	 */
	$head = sallePrepareHead($object);

	print dol_get_fiche_head($head, 'salle', $langs->trans("salle"), -1, $object->picto);

	$linkback = '<a href="'.dol_buildpath('/scolarite/salle_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	$morehtmlref .= '</div>';

	print "<h2>Liste des cours dans cette salle : $object->nom_complet</h2>";

	print '<table class="border centpercent tableforfield">';
	print '<tbody>';
	print '<tr>';
	print '<td>Jour</td>';
	print '<td>Creneau</td>';
	print '<td>Éleves</td>';
	print '<td>Nombres de places occupées</td>';
	print '</tr>';

	$dictionaryClass = new Dictionary($db);
	$creneauClass = new Creneau($db);
	$creneaux = $creneauClass->fetchAll('ASC, ASC','jour, heure_debut',0,0,array('fk_salle'=>$object->id,'status'=>4));

	if(!empty($creneaux)) {
		foreach($creneaux as $value)
		{
			$jourObj = $dictionaryClass->fetchByDictionary('c_jour', ['jour','rowid'],$value->jour,'rowid');

			print '<tr>';
			print '<td>'.$jourObj->jour.'</td>';
			print '<td>'.$value->nom_creneau.'</td>';
			print '<td>';
			print $value->printElevesFromCreneau($value->id)[0];
			print '</td>';
			print '<td>'.$value->printElevesFromCreneau($value->id)[1].'/'.$value->nombre_places.'</td>';
			print '</tr>';
		}
	} else {
		print '<tr>';
		print '<td colspan="4"><strong>Aucun cours connu dans cette salle.</strong></td>';
		print '</tr>';
	}

	print '</tbody>';
	print '</table>';

}

// End of page
llxFooter();
$db->close();
