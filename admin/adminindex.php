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
if (!$res && !empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
	$res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT']. '/main.inc.php';
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)). '/main.inc.php')) {
	$res = @include substr($tmp, 0, ($i + 1)). '/main.inc.php';
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))). '/main.inc.php')) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))). '/main.inc.php';
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

dol_include_once('viescolaire/class/souhait.class.php');
dol_include_once('scolarite/class/dispositif.class.php');
dol_include_once('scolarite/class/etablissement.class.php');
dol_include_once('scolarite/class/classe.class.php');
dol_include_once('scolarite/class/creneau.class.php');
dol_include_once('scolarite/class/annee.class.php');
dol_include_once('viescolaire/class/famille.class.php');
dol_include_once('viescolaire/class/parents.class.php');

// Load translation files required by the page
$langs->loadLangs(array('admin@admin'));

$action = GETPOST('action', 'aZ09');
$anneeFromForm = GETPOST('annee', 'aZ09');
$etabFromForm = GETPOST('etablissement', 'aZ09');
$docName = str_replace(' ','_',GETPOST('doc_name', 'alpha'));
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');

$max = 5;
$now = dol_now();

// Security check - Protection if external user
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

// TODO: mettre dans une fonction propre
if($action == 'confirm_desactivate' || $action == 'confirm_activate' ) {

	$itemClass = GETPOST('item','alpha');

	$sql = 'UPDATE ' . MAIN_DB_PREFIX .strtolower(GETPOST('item','alpha')).' SET status = ' .($action == 'confirm_desactivate' ? $itemClass::STATUS_CANCELED : $itemClass::STATUS_VALIDATED). ' WHERE fk_annee_scolaire=' . $anneeFromForm;
	$resql = $db->query($sql);

	$anneeClass = new Annee($db);
	$anneeClass->fetch($anneeFromForm);

	if($resql > 0) {
		setEventMessage(GETPOST('item','alpha').' de l\'année scolaire '.$anneeClass->annee.($action == 'confirm_desactivate' ? 'désactivés' : 'activés').' avec succès!');
	} else {
		setEventMessage('Une erreur est survenue.');
	}
}

// TODO: mettre dans une fonction propre
if($action == 'confirmExport')
{
	$souhait = new Souhait($db);

	$sql = 'SELECT e.nom, e.prenom, e.fk_famille, e.rowid, e.fk_etablissement, e.fk_classe_etablissement, e.genre, e.geographie_prioritaire
            FROM '.MAIN_DB_PREFIX.'eleve as e
            INNER JOIN ' .MAIN_DB_PREFIX. 'souhait as s ON e.rowid = s.fk_eleve
            WHERE s.fk_annee_scolaire = '.$anneeFromForm.' AND s.status != '.Souhait::STATUS_CANCELED.'
            '.($etabFromForm != 0 ? " AND e.fk_etablissement=$etabFromForm ": ''). '
            GROUP BY e.rowid
            ORDER BY e.fk_etablissement, e.prenom ASC';
	$resqlEleve = $db->query($sql);

	$dispositif = new Dispositif($db);
	$resqlDispositif = $dispositif->fetchByd(['rowid','nom','fk_etablissement'], 0, '');

	$customers_data = array();

	$count = 0;
	foreach($resqlEleve as $value) {
		$array['Prenom élève'] = $value['prenom'];
		$array['Nom élève'] = $value['nom'];

		$geo = 'SELECT emplacement FROM ' . MAIN_DB_PREFIX . 'c_geographie_prioritaire WHERE rowid=' . $value['geographie_prioritaire'];
		$resqlGeo = $db->query($geo);
		if ($resqlGeo) $objGeo = $db->fetch_object($resqlGeo);

		$array['Géographie prioritaire'] = ($objGeo->emplacement ? : 'Inconnue');

		$etablissementClass = new Etablissement($db);
		$etablissementClass->fetch($value['fk_etablissement']);
		$array['Antenne'] = $etablissementClass->nom;

		$classeClass = new Classe($db);
		$classeClass->fetch($value['fk_classe_etablissement']);
		$array['Classe'] = $classeClass->classe;

		switch ($value['genre']) {
			case 1:
				$array['Genre'] = 'Masculin';
				break;
			case 2:
				$array['Genre'] = 'Féminin';
				break;
			case 3:
				$array['Genre'] = 'Autre';
				break;
			default :
				$array['Genre'] = 'Inconnu';
				break;
		}

		$existingSouhait = 0;
		$count = 0;
		foreach ($resqlDispositif as $val) {
			$sql = 'SELECT DISTINCT s.rowid
					FROM ' . MAIN_DB_PREFIX . 'souhait s
					INNER JOIN ' . MAIN_DB_PREFIX . 'affectation a ON s.rowid = a.fk_souhait
					INNER JOIN ' . MAIN_DB_PREFIX . 'creneau c ON c.rowid = a.fk_creneau
					INNER JOIN ' . MAIN_DB_PREFIX . 'eleve e ON s.fk_eleve = e.rowid
					INNER JOIN ' . MAIN_DB_PREFIX . 'etablissement t ON e.fk_etablissement = t.rowid
					INNER JOIN ' . MAIN_DB_PREFIX . 'dispositif d ON d.fk_etablissement = t.rowid
					WHERE  d.rowid = ' . $val->rowid . '
					AND s.fk_annee_scolaire = ' . $anneeFromForm;
			if ($count != 0) {
				$sql .= ' AND s.rowid NOT IN ( ' . $existingSouhait . ' )';
			}
			$sql .= ' AND s.fk_eleve = ' . $value['rowid'] . '
					AND s.status != ' . Souhait::STATUS_CANCELED;

			$resql = $db->query($sql);


			foreach ($resql as $res) {
				$existingSouhait .= ', ' . $res['rowid'];
			}

			$array["Dispositif {$val->nom}"] = $resql->num_rows;

			if ($count == $resql->num_rows) {
				$count = 0;
			}

			$count++;
		}
		$existingSouhait = '';

		$parentClass = new Parents($db);

		if (!empty($value['fk_famille'])) {
			$parents = $parentClass->fetchAll('', '', 0, 0, ['fk_famille' => $value['fk_famille']], 'AND');
			$loop = 0;
			foreach ($parents as $parent) {

				if ($loop == 0) {
					$array['Nom parent 1'] = $parent->lastname ? : 'Inconnu';
					$array['Prénom parent 1'] = ($parent->firstname ? : ($value['fk_famille'] == NULL ? 'Aucune famille connue' : 'Inconnu'));
					$array['Adresse parent 1'] = ($parent->address ? : ($value['fk_famille'] == NULL ? 'Aucune famille connue' : 'Inconnue'));
					$array['Téléphone parent 1'] = ($parent->phone ? 0+$parent->phone : ($value['fk_famille'] == NULL ? 'Aucune famille connue' : 'Inconnu'));
					$array['Mail parent 1'] = ($parent->mail ?: ($value['fk_famille'] == NULL ? 'Aucune famille connue' : 'Inconnu'));
					$cspParent1 = ($value['fk_famille'] == NULL ? 'Aucune famille connue' : 'Inconnu');
					if ($parent->csp) {
						$csp = 'SELECT categorie FROM ' . MAIN_DB_PREFIX . 'c_csp WHERE rowid=' . $parent->csp;
						$resqlGeo = $db->query($csp);
						$objGeo = $db->fetch_object($resqlGeo);

						$cspParent1 = $objGeo->categorie;
					}
					$array['CSP parent 1'] = $cspParent1;
				}


				if ($loop == 1) {
					$array['Nom parent 2'] = ($parent->lastname ?: ($value['fk_famille'] == NULL ? 'Aucune famille connue' : 'Inconnu'));
					$array['Prénom parent 2'] = ($parent->firstname ?: ($value['fk_famille'] == NULL ? 'Aucune famille connue' : 'Inconnu'));
					$array['Adresse parent 2'] = ($parent->address ?: ($value['fk_famille'] == NULL ? 'Aucune famille connue' : 'Inconnue'));
					$array['Téléphone parent 2'] = ($parent->phone ? 0 + $parent->phone : ($value['fk_famille'] == NULL ? 'Aucune famille connue' : 'Inconnu'));
					$array['Mail parent 2'] = ($parent->mail ?: ($value['fk_famille'] == NULL ? 'Aucune famille connue' : 'Inconnu'));
					$cspParent2 = ($value['fk_famille'] == NULL ? 'Aucune famille connue' : 'Inconnu');
					if ($parent->csp) {
						$csp = 'SELECT categorie FROM ' . MAIN_DB_PREFIX . 'c_csp WHERE rowid=' . $parent->csp;
						$resqlGeo = $db->query($csp);
						$objGeo = $db->fetch_object($resqlGeo);

						$cspParent2 = $objGeo->categorie;
					}
					$array['CSP parent 2'] = $cspParent2;
				}
				$loop++;
			}
		}

		array_push($customers_data, $array);
		unset($parents);
		unset($loop);
		unset($array);
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
	header('Content-Type: application/vnd.ms-excel');
	die;

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

llxHeader('', $langs->trans('Admin'));
print load_fiche_titre($langs->trans('Zone Admin'), '', $user->picto);

$formconfirm = '';

if ((GETPOST('subAction','alpha') == 'form_desactivate' || GETPOST('subAction','alpha') == 'form_activate')) {

	$className = GETPOST('item','alpha');

	if($className == '0') {
		setEventMessage('Merci de choisir un élément valide.','errors');
	} else {

		$itemClass = new $className($db);

		$items = $itemClass->fetchAll('','',0,0,array('t.fk_annee_scolaire'=>$anneeFromForm,'customsql'=>' t.status = '.(GETPOST('subAction','alpha') == 'form_desactivate' ? $className::STATUS_VALIDATED : $className::STATUS_CANCELED)));

		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'].'?id='.$object->id.'&annee='.$anneeFromForm.'&item='.GETPOST('item','alpha'), ((GETPOST('subAction','alpha') == 'form_desactivate' ? 'Désactiver '.strtolower(GETPOST('item','alpha')) : 'Activer '.strtolower(GETPOST('item','alpha'))) ), 'Voulez-vous vraiment ' . (GETPOST('subAction','alpha') == 'form_desactivate' ? 'désactiver' : 'activer')." l'intégralité des ".GETPOST('item','alpha'). ' de cette année scolaire? Nombre de lignes affectées : ' .count($items). '', (GETPOST('subAction','alpha') == 'form_desactivate' ? 'confirm_desactivate' : 'confirm_activate'), '', 0, 1, 0, 500, 0, ($action == 'activate_creneau' ? 'Désactiver' : 'Activer'), 'Annuler');
	}
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

	print load_fiche_titre((GETPOST('subAction','alpha') == 'activate' ? 'Activer' : 'Désactiver').' un élément', '', 'object_'.$object->picto);
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="subAction" value="form_'.GETPOST('subAction','alpha').'">';

	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="/custom/admin/adminindex.php?action=cardDesactivate">';
	}

	print dol_get_fiche_head(array(''), '');

	print '<table class="border centpercent tableforfieldcreate">'."\n";
	print '<div class="center">';
	print '<label>Selectionnez l\'année concernée : </label>';

	$anneeClass = new Annee($db);
	print $form->selectarray('annee',$anneeClass->returnAnneeNameArray());

	print '</div><br>';
	print '<div class="center">';
	print '<label>Selectionnez l\'élément concerné : </label>';

	$item = [0=>'','Souhait'=>'Souhait','Creneau'=>'Creneau','Agent'=>'Agent'];
	print $form->selectarray('item',$item);

	print '</div>';
	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel('Valider');

	print '</form>';

}

if ($action == 'export') {

	print load_fiche_titre('Exporter des données', '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="confirmExport">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head(array(), '');

	print '<table class="border centpercent tableforfieldcreate">'."\n";
	print '<div class="center">';
	print '<label>Nom du document après export (sans extension) : </label>';
	print '<input type="text" name="doc_name">';
	print '</div><br>';

	print '<div class="center">';
	print '<label>Selectionnez l\'année concernée : </label>';

	$anneeClass = new Annee($db);
	print $form->selectarray('annee',$anneeClass->returnAnneeNameArray());

	print '</div><br>';

	print '<div class="center">';
	print '<label>Selectionnez l\'antenne concerné : </label>';

	$etablissementClass = new Etablissement($db);
	print $form->selectarray('etablissement',$etablissementClass->returnAntenneNameArray());
	print '</div><br>';
	print '</table>'."\n";

	print dol_get_fiche_end();
	print '<div class="center">';
	print '<div id="loader"></div>';
	print $form->buttonsSaveCancel('Valider','',[],0,'validExport');
	print '</div>';

	print '</form>';

}
else
{
	print load_fiche_titre($langs->trans('Zone de danger !'), '', 'object_'.$object->picto);
	print '<div style="display;flex">';
	print '<a href="'.$_SERVER['PHP_SELF'].'?action=cardDesactivate&subAction=activate&token='.newToken().'" class="button">Activer tout les éléments d\'une année</a><br>';
	print '<a href="'.$_SERVER['PHP_SELF'].'?action=cardDesactivate&subAction=desactivate&token='.newToken().'" class="button">Désactiver tout les éléments d\'une année</a><br>';
	print '</div>';

	print load_fiche_titre($langs->trans('Export'), '', 'object_'.$object->picto);
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
