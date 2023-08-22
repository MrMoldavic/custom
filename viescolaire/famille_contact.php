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
 *  \file       salle_recherche.php
 *  \ingroup    scolarite
 *  \brief      Tab for contacts linked to salle
 */


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);





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
dol_include_once('/scolarite/lib/scolarite_salle.lib.php');
dol_include_once('/scolarite/class/creneau.class.php');
// Load translation files required by the page
$langs->loadLangs(array("scolarite@scolarite", "companies", "other", "mails"));

$id     = (GETPOST('id') ?GETPOST('id', 'int') : GETPOST('facid', 'int')); // For backward compatibility
$ref    = GETPOST('ref', 'alpha');
$lineid = GETPOST('lineid', 'int');
$socid  = GETPOST('socid', 'int');
$action = GETPOST('action', 'aZ09');

// Initialize technical objects
// $object = new Salle($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->scolarite->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('sallesallex', 'globalcard')); // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'create';
}

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
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->scolarite->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();


/*
 * View
 */

$title = $langs->trans('Rechercher une salle');
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


if ($action == 'create') // SELECTION DU TYPE DE KIT
{
	// $sql = "SELECT * FROM ".MAIN_DB_PREFIX."salles";
	// $resql = $db->query($sql);

	// $sqlEtablissement = "SELECT rowid,nom FROM ".MAIN_DB_PREFIX."etablissement";
	// $resqlEtablissement = $db->query($sqlEtablissement);

	// $etablissements = [];

	// foreach($resqlEtablissement as $value)
	// {
	// 	$etablissements[$value['rowid']] = $value['nom'];
	// }

	// $equipement = [''=>'Aucun', 'guitareE'=>'Guitares électriques (Amplis)','MAO'=>'MAO','Piano'=>'Piano','Batterie'=>'Batterie'];

	// $date = date('Y-m-d H:i:s');
	// //WYSIWYG Editor
	// print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
  
	// print '<input type="hidden" name="action" value="create">';
	// $titre = "Nouvel Appel";
	// print talm_load_fiche_titre($title, $linkback, $picto);
	// dol_fiche_head('');
	// print '<table class="border centpercent">';
	// print '<tr>';
	// print '</td></tr>';
	// // Type de Kit
	// print '<tr><td class="fieldrequired titlefieldcreate">Sélectionnez une heure: </td><td>';
	// print $form->selectDate($date,'date_evenement',1,1,0,"",1,1,0,'','','','',1,'','','auto');
	// print ' <a href="'.DOL_URL_ROOT.'/custom/viescolaire/eleve_card.php?action=create">';
	// print '</a>';
	// print '</td>';
	// print '</tr>';

	// print '<tr><td class="fieldrequired titlefieldcreate">Sélectionnez un établissement : </td><td>';
	// print $form->selectarray('etablissement', $etablissements);
	// print ' <a href="'.DOL_URL_ROOT.'/custom/viescolaire/eleve_card.php?action=create">';
	// print '</a>';
	// print '</td>';
	// print '</tr>';

	// print '<tr><td class="fieldrequired titlefieldcreate">Sélectionnez un type d\'équipement : </td><td>';
	// print $form->selectarray('equipement', $equipement);
	// print ' <a href="'.DOL_URL_ROOT.'/custom/viescolaire/eleve_card.php?action=create">';
	// print '</a>';
	// print '</td>';
	// print '</tr>';

	// print "</table>"; 
	// dol_fiche_end();
	// print '<div class="center">';
	// print '<input type="submit" class="button" value="Confirmer">';
	// print '</div>';
	// print '</form>';

	$object = new Creneau($db);
	// $object->fk_souhait = $id;
	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Affectation")), '', 'object_' . $object->picto);

	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="newaffectation">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
	}

	print dol_get_fiche_head(array(), '');

	// Set some default values
	// $_POST['fk_souhait'] = $id;

	print '<table class="border centpercent tableforfieldcreate">' . "\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

	print '</table>' . "\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Créer affectation");

	print '</form>';

	print '</div>';

	
	if(GETPOSTISSET('etablissement', 'int'))
	{
		$date = GETPOST('date_evenement','varchar');
		$dt = DateTime::createFromFormat('d/m/Y', $date);

		$sqlJour = "SELECT * FROM ".MAIN_DB_PREFIX."c_jour WHERE rowid=".date('w',strtotime($dt->format('Y-m-d')));
		$resqlJour = $db->query($sqlJour);
		$jourObj = $db->fetch_object($resqlJour);

		if(GETPOST('equipement', 'varchar') == '')
		{
			$sqlEquip = "SELECT s.rowid,s.salle FROM ". MAIN_DB_PREFIX . "salles as s WHERE fk_college=".GETPOST('etablissement', 'varchar')." AND equipement='Aucun'";
			$resqlEquip = $db->query($sqlEquip);
		}
		else
		{
			$sqlEquip = "SELECT s.rowid,s.salle FROM ". MAIN_DB_PREFIX . "salles as s WHERE fk_college=".GETPOST('etablissement', 'varchar')." AND equipement="."'".GETPOST('equipement', 'varchar')."'";
			$resqlEquip = $db->query($sqlEquip);
		}
	
		
	
		$salleDispos = [];
		foreach($resqlEquip as $salle)
		{
			$sql = "SELECT COUNT(*) as total FROM " . MAIN_DB_PREFIX . "creneau as c INNER JOIN " . MAIN_DB_PREFIX . "dispositif as d ON c.fk_dispositif = d.rowid INNER JOIN " . MAIN_DB_PREFIX . "c_heure as h ON c.heure_debut = h.rowid WHERE d.fk_etablissement =" . GETPOST('etablissement', 'int') . " AND c.jour=" . strftime('%u') . " AND h.heure =" .GETPOST('date_evenementhour', 'varchar')." AND c.fk_salle=".$salle['rowid']." AND c.status =" . 4 ." ORDER BY h.rowid DESC";
			$resqlSalle= $db->query($sql);
			$objSalle = $db->fetch_object($resqlSalle);
		
			if($objSalle->total == 0)
			{
				$salleDispos[] .= $salle['salle'];
			}
	
		}
		print '<h3>';
	
		if(GETPOST('equipement', 'varchar') == "")
		{
			print 'Liste des salles disponibles à '.GETPOST('date_evenementhour', 'varchar').'h le '.GETPOST('date_evenement', 'varchar').':';
		}
		else
		{
			print 'Liste des salles de '.GETPOST('equipement', 'varchar').' disponibles à '.GETPOST('date_evenementhour', 'varchar').'h le '.GETPOST('date_evenement', 'varchar').':';
		}
		print '</h3>';
		print '<ul>';
		if(empty($salleDispos))
		{
			print 'Désolé, aucune salle n\'est disponible à cet horaire avec cet équipement.';
		}
		else
		{
			foreach($salleDispos as $value)
			{
				print '<li>'.$value.'</li>';
			}
		}
		
		print '</ul>';
	}

	
}

// End of page
llxFooter();
$db->close();
