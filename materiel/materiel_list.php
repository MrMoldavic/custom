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
 *	\file       materiel/list.php
 *	\ingroup    materiel
 *	\brief      Home page of materiel top menu
 */

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/materiel.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formkit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formpreinventaire.class.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));

$action = GETPOST('action', 'alpha');
$massaction = GETPOST('massaction', 'alpha');
$toselect = GETPOST('toselect', 'array');

$showdeleted = GETPOST('button_showdeleted_x', 'alpha');
if(!empty(GETPOST('showdeleted', 'alpha'))){
    $showdeleted = GETPOST('showdeleted', 'alpha');
}



$confirm = GETPOST('confirm', 'alpha');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'materiellist'; // To manage different context of search

$search_ref = GETPOST("search_ref", 'alpha');
$search_source = GETPOST("search_source", 'alpha');
$search_state = GETPOST("search_state", 'alpha');
$search_etat_etiquette = GETPOST("search_etat_etiquette", 'alpha');
$search_exploitabilite = GETPOST("search_exploitabilite", 'alpha');
$search_marque_modele = GETPOST("search_marque_modele", 'alpha');
$search_entrepot = GETPOST("search_entrepot", 'alpha');
$search_proprietaire = GETPOST("search_proprietaire", 'alpha');
$search_kit = GETPOST("search_kit", 'alpha');

$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
	// If $page is not defined, or '' or -1 or if we click on clear filters
	$page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;


if (!$sortfield) $sortfield = "m.rowid";
if (!$sortorder) $sortorder = "DESC";

if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers
	{
		$search_ref = "";
		$search_source = "";
		$search_state = "";
		$search_exploitabilite = "";
        $search_marque_modele = "";
        $search_entrepot = "";
        $search_proprietaire = "";
        $showdeleted = "";
	}

$form = new Form($db);
$materiel = new Materiel($db);
$kit = new Kit($db);
$formproduct = new FormProduct($db);
$formmateriel = new FormMateriel($db);
$formpreinventaire = new FormPreinventaire($db);

// Security check
//if (! $user->rights->materiel->myobject->read) accessforbidden();
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0)
{
	$action = '';
	$socid = $user->socid;
}

$usercanread = ($user->rights->materiel->read);
$usercancreate = ($user->rights->materiel->create);
$usercandelete = ($user->rights->materiel->delete);

if (!$usercanread) {
    accessforbidden();
}


$max = 5;
$now = dol_now();

$search_all = GETPOST('search_all', 'alphanohtml');
$search = array();
foreach ($materiel->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha') !== '') {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
	if (preg_match('/^(date|timestamp|datetime)/', $val['type'])) {
		$search[$key.'_dtstart'] = dol_mktime(0, 0, 0, GETPOST('search_'.$key.'_dtstartmonth', 'int'), GETPOST('search_'.$key.'_dtstartday', 'int'), GETPOST('search_'.$key.'_dtstartyear', 'int'));
		$search[$key.'_dtend'] = dol_mktime(23, 59, 59, GETPOST('search_'.$key.'_dtendmonth', 'int'), GETPOST('search_'.$key.'_dtendday', 'int'), GETPOST('search_'.$key.'_dtendyear', 'int'));
	}
}


/*
 * Actions
 */

if (GETPOST('cancel', 'alpha')) { $action = 'list'; $massaction = ''; }
if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') { $massaction = ''; }
if (!$error && ($massaction == 'deactivate' || ($action == 'deactivate' && $confirm == 'yes')) && $usercandelete)
{
    $kit_affected_ref = array();
    $error = 0;
	foreach ($toselect as $toselectid)
	{
	    $tmp_materiel = new Materiel($db);
	    $tmp_materiel->fetch($toselectid);

	    // On vérifie d'abords si le materiel est dans un kit
	    if($tmp_materiel->fk_kit) {
	        $kit_tmp = new Kit($db);
	        $kit_tmp->fetch($tmp_materiel->fk_kit);
	        if ($kit_tmp->fk_exploitation) { // Si le kit est en exploitation on annule la désactivation
            	setEventMessages('Ce materiel est inclus dans une exploitation (réf. '. $kit_tmp->exploitation_ref .'). Il ne peut pas être désactivé avant la fin de l\'exploitation.', null, 'errors');
            	$error++;
	        }
	    } else {
    	    if ($tmp_materiel->fk_kit) $kit_affected_ref[] = $tmp_materiel->kit_ind . '-'. $tmp_materiel->kit_cote;
    	    if (!$tmp_materiel->deactivate($user)) $error ++;
	    }
	}

	$kit_affected_ref = array_unique($kit_affected_ref);

	if ($error > 0) setEventMessages('Erreur lors de la désactivation de l\'élément', null, 'errors');
	else {
	    $message = 'Élément(s) désactivé(s) avec succès.';
	    if (!empty($kit_affected_ref)) {
	        $message .='<br>Les kits suivants ont été affectés : '.implode(', ', $kit_affected_ref);
	    }

	    setEventMessages($message, null);
    }
}
if (!$error && ($massaction == 'delete' || ($action == 'delete' && $confirm == 'yes')) && $usercandelete)
{
    $kit_affected_ref = array();
    $error = 0;
	foreach ($toselect as $toselectid)
	{
	    $tmp_materiel = new Materiel($db);
	    $tmp_materiel->fetch($toselectid);

	    // On vérifie d'abords si le materiel est dans un kit
	    if($tmp_materiel->fk_kit) {
	        $kit_tmp = new Kit($db);
	        $kit_tmp->fetch($tmp_materiel->fk_kit);
	        if ($kit_tmp->fk_exploitation) { // Si le kit est en exploitation on annule la suppression
            	setEventMessages('Ce materiel est inclus dans une exploitation (réf. '. $kit_tmp->exploitation_ref .'). Il ne peut pas être supprimé avant la fin de l\'exploitation.', null, 'errors');
            	$error++;
	        }
	    } else {
    	    if ($tmp_materiel->fk_kit) $kit_affected_ref[] = $tmp_materiel->kit_ind . '-'. $tmp_materiel->kit_cote;

    	    if (!$tmp_materiel->delete()) $error ++;
	    }
	}

	$kit_affected_ref = array_unique($kit_affected_ref);

	if ($error > 0) setEventMessages('Erreur lors de la suppression d\'un ou plusieurs élement(s)', null, 'errors');
	else {
	    $message = 'Élément(s) supprimé(s) avec succès.';
	    if (!empty($kit_affected_ref)) {
	        $message .='<br>Les kits suivants ont été affectés : '.implode(', ', $kit_affected_ref);
	    }

	    setEventMessages($message, null);
    }
}

if (GETPOST('button_reactivate_x', 'alpha')) {
    $reactivation_id = GETPOST('button_reactivate_x', 'alpha');
    $materiel_to_reactivate = new Materiel($db);
    $materiel_to_reactivate->id = $reactivation_id;
    $materiel_to_reactivate->reactivate();

}


if (GETPOST('button_delete_x', 'alpha')) {
    $delete_id = GETPOST('button_delete_x', 'alpha');
    $materiel_to_delete = new Materiel($db);
    $materiel_to_delete->id = $delete_id;
    $materiel_to_delete->delete();

}




/*
 * View
 */
llxHeader("", $langs->trans("Materiel"));



$param = '';
if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
	$param .= '&contextpage='.urlencode($contextpage);
}
if ($limit > 0 && $limit != $conf->liste_limit) {
	$param .= '&limit='.urlencode($limit);
}
foreach ($search as $key => $val) {
	if (is_array($search[$key]) && count($search[$key])) {
		foreach ($search[$key] as $skey) {
			if ($skey != '') {
				$param .= '&search_'.$key.'[]='.urlencode($skey);
			}
		}
	} elseif ($search[$key] != '') {
		$param .= '&search_'.$key.'='.urlencode($search[$key]);
	}
}
if ($optioncss != '') {
	$param .= '&optioncss='.urlencode($optioncss);
}

print '<div class="fichecenter">';


$sql.="INNER JOIN ".MAIN_DB_PREFIX."c_etat_materiel as e ON m.fk_etat=e.rowid ";
$sql.="INNER JOIN ".MAIN_DB_PREFIX."c_origine_materiel as o ON m.fk_origine=o.rowid ";
$sql.="INNER JOIN ".MAIN_DB_PREFIX."c_type_materiel as t ON m.fk_type_materiel=t.rowid ";/* On joint les deux tables pour avoir le descriptif de l'état en entier (et pas juste une lettre) et aussi pour avoir le code du status du badge (rouge ou vert)*/
$sql.="LEFT JOIN ".MAIN_DB_PREFIX."c_marque as mm ON m.fk_marque=mm.rowid ";
$sql.="LEFT JOIN ".MAIN_DB_PREFIX."entrepot as w ON m.fk_entrepot=w.rowid ";
$sql.="LEFT JOIN ".MAIN_DB_PREFIX."c_proprietaire as p ON m.fk_proprietaire=p.rowid ";

$sql.="WHERE 1=1";


$sql = "SELECT m.rowid, t.indicatif as type_materiel, t.type as type_materiel_full,m.cote, m.precision_type as precision_type, m.modele, mm.marque,  m.notes_supplementaires, e.etat, e.badge_status_code, w.ref AS entrepot, p.proprietaire as proprietaire, o.origine";
$sql.= " FROM ".MAIN_DB_PREFIX."materiel as m ";

$sql.="INNER JOIN ".MAIN_DB_PREFIX."c_etat_materiel as e ON m.fk_etat=e.rowid ";
$sql.="INNER JOIN ".MAIN_DB_PREFIX."c_origine_materiel as o ON m.fk_origine=o.rowid ";
$sql.="INNER JOIN ".MAIN_DB_PREFIX."c_type_materiel as t ON m.fk_type_materiel=t.rowid ";/* On joint les deux tables pour avoir le descriptif de l'état en entier (et pas juste une lettre) et aussi pour avoir le code du status du badge (rouge ou vert)*/
$sql.="LEFT JOIN ".MAIN_DB_PREFIX."c_marque as mm ON m.fk_marque=mm.rowid ";
$sql.="LEFT JOIN ".MAIN_DB_PREFIX."entrepot as w ON m.fk_entrepot=w.rowid ";
$sql.="LEFT JOIN ".MAIN_DB_PREFIX."c_proprietaire as p ON m.fk_proprietaire=p.rowid ";

$sql.="WHERE 1=1";
if (empty($showdeleted)) {
    $sql .= ' AND m.active = 1';
} else {
    $sql .= ' AND m.active = 0';

}
if ($search_ref) {
    $sql .= natural_search(array('t.indicatif', 'm.cote'), str_replace('-', ' ', $search_ref), 0, 0);
}
if ($search_marque_modele) {
    $sql .= natural_search(array('mm.marque', 'm.modele'), $search_marque_modele, 0, 0);
}
if ($search_state != -1 and $search_state) $sql .= natural_search('m.fk_etat', $search_state);
if ($search_etat_etiquette != -1 and $search_etat_etiquette) $sql .= natural_search('m.fk_etat_etiquette', $search_etat_etiquette);
if ($search_exploitabilite != -1 and $search_exploitabilite) $sql .= natural_search('m.fk_exploitabilite', $search_exploitabilite);
if ($search_precision_type) $sql .= natural_search('m.precision_type', $search_precision_type);
if ($search_entrepot != -1 and $search_entrepot) $sql .= natural_search('w.rowid', $search_entrepot);

if ($search_proprietaire) {
    $sql .= natural_search('p.proprietaire', $search_proprietaire);
}

$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	/* This old and fast method to get and count full list returns all record so use a high amount of memory.
	$resql = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($resql);
	*/
	/* The slow method does not consume memory on mysql (not tested on pgsql) */
	/*$resql = $db->query($sql, 0, 'auto', 1);
	while ($db->fetch_object($resql)) {
		$nbtotalofrecords++;
	}*/
	/* The fast and low memory method to get and count full list converts the sql into a sql count */
	$sqlforcount = preg_replace('/^SELECT[a-z0-9\._\s\(\),]+FROM/i', 'SELECT COUNT(*) as nbtotalofrecords FROM', $sql);
	$resql = $db->query($sqlforcount);
	$objforcount = $db->fetch_object($resql);
	$nbtotalofrecords = $objforcount->nbtotalofrecords;
	if (($page * $limit) > $nbtotalofrecords) {	// if total of record found is smaller than page * limit, goto and load page 0
		$page = 0;
		$offset = 0;
	}
	$db->free($resql);
}

$sql .= $db->order($sortfield, $sortorder);
if ($limit) {
	$sql .= $db->plimit($limit + 1, $offset);
}


$resql = $db->query($sql);

// Definition of fields for lists
$arrayfields = array(
	'm.rowid'=>array('label'=>'ID', 'checked'=>1),
	//'pfp.ref_fourn'=>array('label'=>$langs->trans("RefSupplier"), 'checked'=>1, 'enabled'=>(! empty($conf->barcode->enabled))),
	'm.fk_type_materiel'=>array('label'=>'Réf.', 'checked'=>1, 'position'=>10),
	'm.cote'=>array('label'=>'Cote', 'checked'=>1, 'position'=>11),
	'm.fk_etat'=>array('label'=>'État', 'checked'=>1, 'position'=>12),
	'm.fk_exploitabilite'=>array('label'=>'Exploitabilité', 'checked'=>1, 'position'=>12),
	'm.fk_marque'=>array('label'=>'Marque - Modèle', 'checked'=>1, 'position'=>13),
	'm.precision_type'=>array('label'=>'Type d\'instrument', 'checked'=>1, 'position'=>13),
	'm.notes_supplementaires'=>array('label'=>'Notes supplémentaires', 'checked'=>1, 'position'=>14),
	'm.fk_origine'=>array('label'=>'Origine', 'checked'=>1, 'position'=>14),
	'm.entrepot'=>array('label'=>'Entrepôt', 'checked'=>1, 'position'=>15),
	// 'm.fk_proprietaire'=>array('label'=>'Propriétaire', 'checked'=>1, 'position'=>16),
	'm.kit'=>array('label'=>'Kit', 'checked'=>1, 'position'=>15));


print '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="formulaire">';
	if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
	print '<input type="hidden" name="action" value="list">';
	print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
	print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
	print '<input type="hidden" name="showdeleted" value="'.$showdeleted.'">';
	//print '<input type="hidden" name="page" value="'.$page.'">';
	print '<input type="hidden" name="type" value="'.$type.'">';
	$title = 'Liste des Materiels';
	if (empty($arrayfields['p.fk_product_type']['checked'])) print '<input type="hidden" name="search_type" value="'.dol_escape_htmltag($search_type).'">';


	$arrayofselected = is_array($toselect) ? $toselect : array();

    $arrayofmassactions['predeactivate'] = "<span class='fa fa-toggle-off paddingrightonly' style='color:#666; font-size:0.86em;'></span>Désactiver";
    $arrayofmassactions['predelete'] = "<span class='fa fa-trash paddingrightonly'></span>".$langs->trans("Delete");
	//if (in_array($massaction, array('presend', 'predelete'))) $arrayofmassactions = array();
	$massactionbutton = $form->selectMassAction('', $arrayofmassactions);
    $picto = 'materiel';
    if ($usercancreate) $newcardbutton = dolGetButtonTitle('Nouveau matériel', '', 'fa fa-plus-circle', DOL_URL_ROOT.'/custom/materiel/materiel_card.php?action=create', '', 1);
	if($resql)
	{
		$num = $db->num_rows($resql);
	}
	talm_print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'object_'.$object->picto, 0, $newcardbutton, '', $limit, 0, 0, 1);

	

	include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';

	if (!empty($showdeleted)) {
	    print '<table class="centpercent notopnoleftnoright">';
	    print '<tr><b style="color:red;"><span class="fa fa-exclamation-triangle paddingrightonly" style="color:red; font-size:0.86em;"></span>Seuls les objets désactivés sont affichés.</b> Appuyez sur <span class="fa fa-times paddingrightonly" style="color:#666; font-size:0.86em;"></span> pour désactiver cette fonctionnalité.</tr>';
	    print '</table>';
	}

	print '<div class="div-table-responsive">';
	print '<table class="tagtable liste">'."\n";

// Lines with input filters


	print '<tr class="liste_titre_filter">';


	print '<td class="liste_titre left">';
		print '<input class="flat" type="text" name="search_ref" size="8" value="'.dol_escape_htmltag($search_ref).'">';
	print '</td>';


	print '<td class="liste_titre maxwidthsearch">';
	print $formpreinventaire->selectSources($search_source, 'search_source');
	print '</td>';

	print '<td class="liste_titre left">';
	print '<input class="flat" type="text" name="search_precision" size="8" value="'.dol_escape_htmltag($precision_type).'">';
	print '</td>';

	print '<td class="liste_titre center">';
		print $form->selectarray('search_state', $materiel->getEtatDict(), $search_state, 1);
	print '</td>';


	print '<td class="liste_titre center">';
		print $form->selectarray('search_exploitabilite', $materiel->getExploitabiliteDict(), $search_exploitabilite, 1);
	print '</td>';


	print '<td class="liste_titre left">';
		print '<input class="flat" type="text" name="search_marque_modele" size="8" value="'.dol_escape_htmltag($search_marque_modele).'">';
	print '</td>';
 
	print '<td class="liste_titre left">';
	print $materiel->id;
	print '</td>';


	// print '<td class="liste_titre left">';
	// 	print '<input class="flat" type="text" name="search_proprietaire" size="8" value="'.dol_escape_htmltag($search_proprietaire).'">';
	// print '</td>';



	// print '<td class="liste_titre left">';
	// 	print $form->selectarray('search_etat_etiquette', getEtatEtiquetteDict(), $search_etat_etiquette, 1);
	// print '</td>';


	print '<td class="liste_titre center">';
		print $form->selectarray('search_kit', array(2=>'En kit', 1=>'Hors kit'), $search_kit, 1);
	print '</td>';

	print '<td class="liste_titre center maxwidthsearch">';
	if ($usercancreate) $searchpicto = $form->showFilterButtons(1);
	else $searchpicto = $form->showFilterButtons();

	print $searchpicto;
	print '</td>';

	print '</tr>';


print '<tr class="liste_titre">';

print_liste_field_titre($arrayfields['m.fk_type_materiel']['label'], $_SERVER["PHP_SELF"], "m.fk_type_materiel", "", $param, "", $sortfield, $sortorder);

print_liste_field_titre('Source', '', "", "", '', "");

print_liste_field_titre('Précision du type', '', "", "", '', "");

print_liste_field_titre($arrayfields['m.fk_etat']['label'], $_SERVER["PHP_SELF"], "m.fk_etat", "", $param, "align='center'", $sortfield, $sortorder);

print_liste_field_titre($arrayfields['m.fk_exploitabilite']['label'], $_SERVER["PHP_SELF"], "m.fk_exploitabilite", "", $param, "align='center'", $sortfield, $sortorder);

print_liste_field_titre($arrayfields['m.fk_marque']['label'], $_SERVER["PHP_SELF"], "mm.marque", "", $param, "", $sortfield, $sortorder);

print_liste_field_titre($arrayfields['m.entrepot']['label'], $_SERVER["PHP_SELF"], "m.fk_entrepot", "", $param, "", $sortfield, $sortorder);


//print_liste_field_titre('État étiquette', $_SERVER["PHP_SELF"], "m.fk_etat_etiquette", "", '', "align='center'", $sortfield, $sortorder);

print_liste_field_titre('En kit', '', "", "", '', "align='center'");



print '<th></th>';




// Draft MyObject
if ($conf->materiel->enabled == 1)
{
	$langs->load("orders");
	if ($resql)
	{
		$total = 0;
		// $num = $db->num_rows($resql);


		$var = true;
		if ($num > 0)
		{
			$i = 0;
			$totalarray = array();
			$totalarray['nbfield'] = 0;
			while ($i < ($limit ? min($num, $limit) : $num))
			{

				$obj = $db->fetch_object($resql);
                $materiel->fetch($obj->rowid);
                if($materiel->fk_kit) $kit->fetch($materiel->fk_kit);

                if (!empty($search_kit) && $search_kit != -1) {
                    if ($search_kit == 1 && $materiel->fk_kit) {
                        $i++;
                        continue; // hors kit
                    }
                    if ($search_kit == 2 && !$materiel->fk_kit) {
                        $i++;
                        continue; // en kit
                    }
                }
				
				if ($search_source != -1 && $search_source && $materiel->source_object->ref != $search_source) {
					$i++;
					continue;
				}	


				print '<tr class="oddeven">';

    			print '<td class="tdoverflowmax200">';
    			if (empty($showdeleted)) print $materiel->getNomUrl();
    			else print $materiel->ref;
    			print "</td>\n";

    			print '<td class="tdoverflowmax200">';

				
				if($materiel->fk_preinventaire != NULL)
				{
					print $materiel->source_object->getNomUrl();
				}
				else
				{
					print '<span class="badge badge-status8 badge-status" style="color:white;">À rapprocher</span>';
				}
    			
    			print "</td>\n";

				print '<td class="tdoverflowmax200">';

				print $materiel->precision_type;
    			
    			print "</td>\n";

    		    if (!$materiel->active) print '<td class="nowrap center"><span class="badge  badge-status5 badge-status">'.Désactivé.'</span></td>';
				else print '<td class="nowrap center"><span class="badge  badge-status'.$obj->badge_status_code.' badge-status">'.$obj->etat.'</span></td>';

				print '<td class="nowrap center"><span class="badge  badge-dot badge-status'.$materiel->exploitabilite_badge_code.' classfortooltip badge-status" title="'.$materiel->exploitabilite.'"></span></td>';

				$marque_modele = $obj->marque ? $obj->marque .' - '. $obj->modele: '<i>Aucun</i>';
				print '<td class="nowrap">'.$marque_modele.'</td>';

				$sqlEntrepot = "SELECT s.rowid,s.salle,s.fk_college FROM " . MAIN_DB_PREFIX . "salles as s WHERE rowid=".$materiel->fk_entrepot;
				$resqlEntrepot = $db->query($sqlEntrepot);

				if($resqlEntrepot->num_rows > 0)
				{
					$resEntrepot = $db->fetch_object($resqlEntrepot);
					$sqlEtablissement = "SELECT e.rowid,e.diminutif FROM " . MAIN_DB_PREFIX . "etablissement as e WHERE rowid= ".$resEntrepot->fk_college;
					$resqlEtablissement = $db->query($sqlEtablissement);
					$resEtablissement = $db->fetch_object($resqlEtablissement);
				}
				
				$entrepotRes = $resEntrepot->salle . ' / ' . $resEtablissement->diminutif;

				$entrepot = $materiel->fk_entrepot != null ? $entrepotRes : '<i>Aucun</i>';
				print '<td class="nowrap">'.$entrepot.'</td>';


				//print '<td class="tdoverflowmax200 center"><span class="badge  badge-dot badge-status'.$materiel->etat_etiquette_badge_code.' classfortooltip badge-status" title="'.$materiel->etat_etiquette.'"></span></td>';

				print '<td class="nowrap center">'.$materiel->KitStatut().'</td>';

    		// Action
    		print '<td class="nowrap center">';
        if (empty($showdeleted)){
            if ($usercandelete){
      			$selected = 0;
      			if (in_array($obj->rowid, $arrayofselected)) $selected = 1;
      			print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected ? ' checked="checked"' : '').'>';
            }
        } elseif ($usercancreate) { // Si le matériel est désactivé, on affiche le bouton de réactivation
            print '<div class="nowrap">';
            print '<button type="submit" class="button_reactivate" name="button_reactivate_x" value="'. $obj->rowid .'" style="border:unset; background-color:unset;"><span class="badge  badge-status4 badge-status"><span class="fas fa-trash-restore" style="color:white;"></span>';
            print '&nbsp;Restaurer</span></button>';

            print '<button type="submit" class="button_delete" name="button_delete_x" value="'. $obj->rowid .'" style="border:unset; background-color:unset;"><span class="badge  badge-status4 badge-status"><span class="fas fa-trash" style="color:white;"></span>';
            print '&nbsp;Supprimer</span></button></div>';
        }

        		print '</td>';

				print '</tr>';
				$i++;
			}
		}
		else
		{

			print '<tr class="oddeven"><td colspan="9" class="opacitymedium">Pas de materiel correspondant.</td></tr>';
		}
		print "</table><br>";

		$db->free($resql);
	}
	else
	{
		dol_print_error($db);
	}
}



	$db->free($resql);

	print "</table>";
	print "</div>";
	print '</form>';
print '<div class="fichethirdleft"></div><div class="fichetwothirdright"><div class="ficheaddleft">';



$NBMAX = 3;
$max = 3;


print '</div></div></div>';

// End of page
llxFooter();
$db->close();
