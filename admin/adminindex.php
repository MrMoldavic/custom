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

dol_include_once('viescolaire/class/souhait.class.php');
dol_include_once('scolarite/class/dispositif.class.php');
dol_include_once('scolarite/class/etablissement.class.php');
dol_include_once('scolarite/class/classe.class.php');
dol_include_once('viescolaire/class/famille.class.php');
// Load translation files required by the page
$langs->loadLangs(array("admin@admin"));

$action = GETPOST('action', 'aZ09');
$anneeFromForm = GETPOST('annee', 'aZ09');
$etabFromForm = GETPOST('etablissement', 'aZ09');
$docName = str_replace(' ','_',GETPOST('doc_name', 'alpha'));


$max = 5;
$now = dol_now();

// Security check - Protection if external user
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}




/*
 * Actions
 */

// None


/*
 * View
 */



if($action == 'confirmExport')
{
	$souhait = new Souhait($db);
	//$sql = "SELECT DISTINCT e.nom, e.prenom,e.fk_famille,e.rowid,e.fk_etablissement,e.fk_classe_etablissement,e.genre,s.rowid COUNT(c.fk_dispositif) as total FROM " . MAIN_DB_PREFIX . "souhait as s INNER JOIN " . MAIN_DB_PREFIX . "eleve as e ON s.fk_eleve=e.rowid INNER JOIN " . MAIN_DB_PREFIX . "affectation as a ON a.fk_souhait=s.rowid INNER JOIN " . MAIN_DB_PREFIX . "creneau as c ON c.rowid=a.fk_creneau WHERE s.fk_annee_scolaire ={$anneeFromForm} AND ".($etabFromForm != 0 ? "e.fk_etablissement=$etabFromForm AND ": '')." s.status=".$souhait::STATUS_VALIDATED;
	$sql = "SELECT e.nom, e.prenom,e.fk_famille,e.rowid,e.fk_etablissement,e.fk_classe_etablissement,e.genre,e.geographie_prioritaire FROM " . MAIN_DB_PREFIX . "eleve as e INNER JOIN " . MAIN_DB_PREFIX . "souhait as s ON e.rowid=s.fk_eleve INNER JOIN " . MAIN_DB_PREFIX . "affectation as a ON s.rowid=a.fk_souhait INNER JOIN " . MAIN_DB_PREFIX . "creneau as c ON c.rowid=a.fk_creneau WHERE s.fk_annee_scolaire={$anneeFromForm} AND ".($etabFromForm != 0 ? "e.fk_etablissement=$etabFromForm AND ": '')." s.status=4 GROUP BY e.rowid ORDER BY e.prenom ASC";
	$resqlEleve = $db->query($sql);


	/*var_dump($sql);*/
	$dispositif = new Dispositif($db);
	$resqlDispositif = $dispositif->fetchBy(['rowid','nom'], 0, '');


	$customers_data = array();

	$count = 0;
	foreach($resqlEleve as $value)
	{
		$array['Prenom élève'] = $value['prenom'];
		$array['Nom élève'] = $value['nom'];

		$geo = "SELECT emplacement FROM " . MAIN_DB_PREFIX . "c_geographie_prioritaire WHERE rowid=" . $value['geographie_prioritaire'];
		$resqlGeo = $db->query($geo);
		if($resqlGeo) $objGeo = $db->fetch_object($resqlGeo);

		$array['Géographie prioritaire'] = ($objGeo->emplacement ? : 'Inconnue');

		$etablissementClass = new Etablissement($db);
		$antenne = $etablissementClass->fetchBy(['nom'], $value['fk_etablissement'], 'rowid');
		$array['Antenne'] = $antenne->nom;

		$classeClass = new Classe($db);
		$classe = $classeClass->fetchBy(['classe'], $value['fk_classe_etablissement'], 'rowid');
		$array['Classe'] = $classe->classe;

		switch ($value['genre']) {
			case 1:
				$array['Genre'] = "Masculin";
				break;
			case 2:
				$array['Genre'] = "Féminin";
				break;
			case 3:
				$array['Genre'] = "Autre";
				break;
		}

		foreach ($resqlDispositif as $val)
		{
			$souhaitClass = new Souhait($db);
			$sql = "SELECT COUNT(DISTINCT s.rowid) as total FROM ".MAIN_DB_PREFIX."souhait as s INNER JOIN " . MAIN_DB_PREFIX . "affectation as a ON s.rowid=a.fk_souhait INNER JOIN " . MAIN_DB_PREFIX . "creneau as c ON c.rowid=a.fk_creneau WHERE c.fk_dispositif=$val->rowid AND s.fk_annee_scolaire=$anneeFromForm AND s.fk_eleve={$value['rowid']} AND s.status=".$souhait::STATUS_VALIDATED;
			$resql = $db->query($sql);

			$obj = $db->fetch_object($resql);

			$array["Dispositif {$val->nom}"] = $obj->total;
		}

		/*		$array["Stage"] = "TEST";
				$array["Instrument prêté"] = "Aucun";*/

		if($value['fk_famille'])
		{
			$familleClass = new Famille($db);
			$famille = $familleClass->fetchBy(['nom_parent_1','prenom_parent_1','tel_parent_1','mail_parent_1','adresse_parent_1','csp_parent_1','nom_parent_2','prenom_parent_2','tel_parent_2','mail_parent_2'], $value['fk_famille'], 'rowid');

			$array['Nom parent 1'] = ($famille->nom_parent_1 ? : 'Inconnu');
			$array['Prénom parent 1'] = ($famille->prenom_parent_1 ? : 'Inconnu');
			$array['Adresse parent 1'] = ($famille->adresse_parent_1 ? : 'Inconnue');
			$array['Téléphone parent 1'] = ($famille->tel_parent_1 ? 0+$famille->tel_parent_1 : 'Inconnu');
			$array['Mail parent 1'] = ($famille->mail_parent_1 ? : 'Inconnu');
			$cspParent1 = "Inconnue";
			if($famille->csp_parent_1)
			{
				$csp = "SELECT categorie FROM " . MAIN_DB_PREFIX . "c_csp WHERE rowid=" . $famille->csp_parent_1;
				$resqlGeo = $db->query($csp);
				$objGeo = $db->fetch_object($resqlGeo);

				$cspParent1 = $objGeo->categorie;
			}
			$array['CSP parent 1'] = $cspParent1;
			$array['Nom parent 2'] = ($famille->nom_parent_2 ? : 'Inconnu');
			$array['Prénom parent 2'] = ($famille->prenom_parent_2 ? : 'Inconnu');
			$array['Adresse parent 2'] = ($famille->adresse_parent_2 ? : 'Inconnue');
			$array['Téléphone parent 2'] = ($famille->tel_parent_2 ? 0+$famille->tel_parent_2 : 'Inconnu');
			$array['Mail parent 2'] = ($famille->mail_parent_2 ? : 'Inconnu');
			$cspParent2 = "Inconnue";
			if($famille->csp_parent_2)
			{
				$csp = "SELECT categorie FROM " . MAIN_DB_PREFIX . "c_csp WHERE rowid=" . $famille->csp_parent_2;
				$resqlGeo = $db->query($csp);
				$objGeo = $db->fetch_object($resqlGeo);

				$cspParent2 = $objGeo->categorie;
			}
			$array['CSP parent 2'] = $cspParent2;

		}else $array['famille'] = "Aucune famille connue";


		array_push($customers_data, $array);
	}

	// Filter Customer Data
	function filterCustomerData(&$str) {
		$str = preg_replace("/\t/", "\\t", $str);
		$str = preg_replace("/\r?\n/", "\\n", $str);
		if (strstr($str, '"'))
			$str = '"' . str_replace('"', '""', $str) . '"';
	}

	// File Name & Content Header For Download
	header("Content-Disposition: attachment; filename=\"$docName\".xls");
	header("Content-Type: application/vnd.ms-excel");


	//To define column name in first row.
	$column_names = false;
	$count = 0;
	// run loop through each row in $customers_data
	foreach ($customers_data as $row) {
		if (!$column_names) {
			echo implode("\t", array_keys($row)) . "\n";
			$column_names = true;
		}
		// The array_walk() function runs each array element in a user-defined function.
		array_walk($row, 'filterCustomerData');
		echo implode("\t", array_values($row)) . "\n";
		$count++;

	}
	exit;
}

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
$souhait = new Souhait($db);
$object = new User($db);

llxHeader("", $langs->trans("Admin"));
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
if ($action == 'export') {

	print load_fiche_titre("Exporter des données", '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="confirmExport">';
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
	print '<div class="center">';
	print '<label>Nom du document après export (sans extension) : </label>';
	print '<input type="text" name="doc_name">';
	print '</div><br>';

	print '<div class="center">';
	print '<label>Selectionnez l\'année concernée : </label>';

	$annee = array();
	$annee_scolaire = "SELECT c.rowid, c.annee FROM ".MAIN_DB_PREFIX."c_annee_scolaire as c";
	$resqlAnneeScolaire = $db->query($annee_scolaire);
	$num = $db->num_rows($resqlAnneeScolaire);

	$i = 0;
	while ($i < $num)
	{
		$objAnneeScolaire = $db->fetch_object($resqlAnneeScolaire);
		$annee[$objAnneeScolaire->rowid] = $objAnneeScolaire->annee;
		$i++;
	}

	print $form->selectarray('annee',$annee);
	print '</div><br>';


	print '<div class="center">';
	print '<label>Selectionnez l\'établissement concerné : </label>';

	$etablissement = array(0=>"Tout les établissements");
	$sqlEtablissement = "SELECT e.rowid, e.nom FROM ".MAIN_DB_PREFIX."etablissement as e";
	$resqlEtablissement = $db->query($sqlEtablissement);
	$numEtablissement = $db->num_rows($resqlEtablissement);

	$i = 0;
	while ($i < $numEtablissement)
	{
		$objEtablissement = $db->fetch_object($resqlEtablissement);
		$etablissement[$objEtablissement->rowid] = $objEtablissement->nom;
		$i++;
	}

	print $form->selectarray('etablissement',$etablissement);
	print '</div><br>';
	print '</table>'."\n";

	print dol_get_fiche_end();
	print '<div class="center">';
	print '<div id="loader"></div>';
	print $form->buttonsSaveCancel("Valider",'',[],0,'validExport');
	print '</div>';

	print '</form>';

}
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
	print load_fiche_titre($langs->trans("Export"), '', 'object_'.$object->picto);

	print '<div style="display;flex">';
	print '<div id="loader"></div>';
	print '<a href="'.$_SERVER['PHP_SELF'].'?action=export" class="button validExport">Créer un export</a><br>';
	print '</div>';
	print '<hr>';




}
print '</div><div class="fichetwothirdright">';

print '</div></div>';

print '<script>
		$( ".validExport" ).on( "click", function() {
		  this.style.opacity = 0;
		 $( "#loader").addClass("loader");
		 $( "#loader").style.opacity = 1;
		});
		</script>';

// End of page
llxFooter();
$db->close();
