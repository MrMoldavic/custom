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
 *	\file       admin/adminindex.php
 *	\ingroup    admin
 *	\brief      Home page of admin top menu
 */

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

dol_include_once('viescolaire/class/souhait.class.php');
// Load translation files required by the page
$langs->loadLangs(array("admin@admin"));

$action = GETPOST('action', 'aZ09');
$anneeFromForm = GETPOST('annee', 'aZ09');

$max = 5;
$now = dol_now();

// Security check - Protection if external user
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//if (!isModEnabled('admin')) {
//	accessforbidden('Module not enabled');
//}
//if (! $user->hasRight('admin', 'myobject', 'read')) {
//	accessforbidden();
//}
//restrictedArea($user, 'admin', 0, 'admin_myobject', 'myobject', '', 'rowid');
//if (empty($user->admin)) {
//	accessforbidden('Must be admin');
//}

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


/*
 * Actions
 */

// None


/*
 * View
 */



$form = new Form($db);
$formfile = new FormFile($db);
$souhait = new Souhait($db);

llxHeader("", $langs->trans("Admin"));
print "<h1>Version Git-1 : 0fe3b85</h1>";
print load_fiche_titre($langs->trans("Zone Admin"), '', $user->picto);

$formconfirm = '';
if ($action == 'desactivate_souhait') {

	$sqlSouhaitPreUpdate = "SELECT COUNT(*) as total FROM " . MAIN_DB_PREFIX . "souhait WHERE status !=" . $souhait::STATUS_CANCELED." AND fk_annee_scolaire=".$anneeFromForm;
	$resqlSouhaitPreUpdate = $db->query($sqlSouhaitPreUpdate);
	$objSouhaitPreUpdate = $db->fetch_object($resqlSouhaitPreUpdate);

	$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&annee='.$anneeFromForm, $langs->trans('Désactiver souhaits'), "Voulez-vous vraiment désactiver l'intégralité des souhaits de cette année scolaire? Nombre de lignes affectées : ".$objSouhaitPreUpdate->total."", 'confirm_desactivate_souhait', '', 0, 1, 0, 500, 0, "Désactiver", "Annuler");
}


if($action == "confirm_desactivate_souhait")
{
	$sql = "UPDATE " . MAIN_DB_PREFIX . "souhait SET status = " . $souhait::STATUS_CANCELED . " WHERE fk_annee_scolaire=" . $anneeFromForm;
	$resql = $db->query($sql);

	$sqlAnnee = "SELECT annee FROM " . MAIN_DB_PREFIX . "c_annee_scolaire WHERE rowid=" . $anneeFromForm;
	$resqlAnnee = $db->query($sqlAnnee);
	$objAnnee = $db->fetch_object($resqlAnnee);

	setEventMessage('Souhait de l\'année scolaire '.$objAnnee->annee.' désactivés avec succès');

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

print '<div class="fichecenter"><div class="fichethirdleft">';

if ($action == 'cardDesactivate') {

	if(GETPOSTISSET('s','alpha'))
	{
		print load_fiche_titre($langs->trans("Désactiver les souhaits"), '', 'object_'.$object->picto);
	}
	elseif(GETPOSTISSET('c','alpha'))
	{
		print load_fiche_titre($langs->trans("Désactiver les créneaux"), '', 'object_'.$object->picto);

	}

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';

	if(GETPOSTISSET('s','alpha'))
	{
		print '<input type="hidden" name="action" value="desactivate_souhait">';
	}
	elseif(GETPOSTISSET('c','alpha'))
	{
		print '<input type="hidden" name="action" value="desactivate_creneau">';
	}

	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head(array(), '');

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	$annee = array();
	$annee_scolaire = "SELECT c.rowid, c.annee FROM ".MAIN_DB_PREFIX."c_annee_scolaire as c";
	$resqlAnneeScolaire = $db->query($annee_scolaire);
	$num = $db->num_rows($resqlAnneeScolaire);

	print '<div class="center">';
	print '<label>Selectionnez l\'année concernée : </label>';

	$i = 0;
	while ($i < $num)
	{
		$objAnneeScolaire = $db->fetch_object($resqlAnneeScolaire);
		$annee[$objAnneeScolaire->rowid] = $objAnneeScolaire->annee;
		$i++;
	}

	print $form->selectarray('annee',$annee);
	print '</div>';
	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Valider");

	print '</form>';

}

// if($action == "export")
// {
// 	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
// 	print '<input type="hidden" name="token" value="'.newToken().'">';

// 	if(GETPOSTISSET('s','alpha'))
// 	{
// 		print '<input type="hidden" name="action" value="desactivate_souhait">';
// 	}
// 	elseif(GETPOSTISSET('c','alpha'))
// 	{
// 		print '<input type="hidden" name="action" value="desactivate_creneau">';
// 	}

// 	if ($backtopage) print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
// 	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';

// 	print dol_get_fiche_head(array(), '');
// 	print '<table class="border centpercent tableforfieldcreate">'."\n";

// 	print '<div class="center">';
// 	print '<label>Selectionnez ce que vous voulez récupérer: </label>';

// 	$item = ["Mail"=>"Mail","Téléphone"=>"Téléphone"];

// 	print $form->selectarray('item',$item);
// 	print '</div>';
// 	print '</table>'."\n";

// 	print dol_get_fiche_end();
// 	print $form->buttonsSaveCancel("Valider");
// 	print '</form>';
// }
else
{
	print load_fiche_titre($langs->trans("Souhaits"), '', 'object_'.$object->picto);
	print '<div style="display;flex">';
	print '<a href="'.$_SERVER['PHP_SELF'].'?action=cardDesactivate&s&desactivate&token='.newToken().'" class="button">Désactiver tout les souhaits d\'une année</a><br>';
	print '<a href="'.$_SERVER['PHP_SELF'].'?action=cardDesactivate&s&activate&token='.newToken().'" class="button">Activer tout les souhaits d\'une année</a><br>';
	print '</div>';
	print '<hr>';
	print load_fiche_titre($langs->trans("Créneaux"), '', 'object_'.$object->picto);
	print '<div style="display;flex">';
	print '<a href="'.$_SERVER['PHP_SELF'].'?action=cardDesactivate&c&token='.newToken().'" class="button">Désactiver tout les créneaux d\'une année</a><br>';
	print '<a href="'.$_SERVER['PHP_SELF'].'?action=cardDesactivate&s&token='.newToken().'" class="button">Activer tout les souhaits d\'une année</a><br>';
	print '</div>';
	print '<hr>';
	print load_fiche_titre($langs->trans("Agents"), '', 'object_'.$object->picto);
	print '<div style="display;flex">';
	print '<a href="'.$_SERVER['PHP_SELF'].'?action=cardDesactivate&c&token='.newToken().'" class="button">Désactiver tout les agents d\'une année</a><br>';
	print '<a href="'.$_SERVER['PHP_SELF'].'?action=cardDesactivate&s&token='.newToken().'" class="button">Activer tout les agents d\'une année</a><br>';
	print '</div>';
	print '<hr>';
}




print '</div><div class="fichetwothirdright">';





print '</div></div>';

// End of page
llxFooter();
$db->close();
