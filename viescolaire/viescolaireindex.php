<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
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
 *	\file       viescolaire/viescolaireindex.php
 *	\ingroup    viescolaire
 *	\brief      Home page of viescolaire top menu
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/viescolaire/class/eleve.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/scolarite/class/etablissement.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/scolarite/class/creneau.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/viescolaire/class/appel.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';


// Load translation files required by the page
$langs->loadLangs(array("viescolaire@viescolaire"));
$hookmanager->initHooks(array('index'));

$action = GETPOST('action', 'aZ09');

// Changement de la valeur de session que quand on valide le formulaire
if ($action == 'changeEtablissement') {
	$etablissementClass = new Etablissement($db);
	$etablissementClass->checkSetCookieEtablissement(GETPOST('etablissementid', 'int'));
}


// Security check
// if (! $user->rights->viescolaire->myobject->read) {
// 	accessforbidden();
// }
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$eleve = new Eleve($db);

llxHeader("", "Module Vie scolaire");

print load_fiche_titre("Accueil Vie Scolaire", '', 'fa-school');
print '<hr>';

include DOL_DOCUMENT_ROOT.'/custom/viescolaire/homeStats.php';
print '<hr>';
print '<div class="fichecenter"><div style="width:40%" class="fichethirdleft">';

// Appel de la fonction qui permet l'affichage des statistiques du nombre d'élève
$appelClass = new Appel($db);
$appelClass->printStatsIndex();

print '</div><div style="width:55%" class="fichetwothirdright "><div class="ficheaddleft ">';

// Ajout du formulaire qui permet de changer son établissement de prédilection
$etablissementClass = new Etablissement($db);
$etablissementsList = $etablissementClass->fetchAll('', '', 0, 0, [], 'AND');
$etablissements = [0 => 'Tous'];

foreach ($etablissementsList as $val) {
	$etablissements[$val->id] = $val->nom;
}
print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">';
print '<input type="hidden" tyname="sortfield" value="' . $sortfield . '">';
print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
print '<input type="hidden" name="action" value="changeEtablissement">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<table class="border centpercent">';
print '<tr>';
print '</td></tr>';
print '<tr><td class="fieldrequired titlefieldcreate">Selectionnez votre établissement: </td><td>';
print $form->selectarray('etablissementid', $etablissements, $_SESSION['etablissementid']);
print '</td>';
print '</tr>';
print '<td></td>';
print '<td>';
print '<input type="submit" class="button" value="Valider">';
print '</td>';
print '</table>';
print '</form>';
print '<hr>';

// Appel de la fonction qui permet l'affichage des absences par etablissement
$appelClass->printAbsencesIndex();

print '</div></div>';

// End of page
llxFooter();
$db->close();
