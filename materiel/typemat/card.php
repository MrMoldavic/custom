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

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/materiel.lib.php';


// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));

$form = new Form($db);
$materiel = new Materiel($db);
$formmateriel = new FormMateriel($db);

/*
 * Data fetching
 */

$usercanread = ($user->rights->materiel->read);
$usercancreate = ($user->rights->materiel->create);
$usercandelete = ($user->rights->materiel->delete);
$usercanreadtype = ($user->rights->materiel->readtype);
$usercanmanagetype = ($user->rights->materiel->modifytype);

$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$id = GETPOST('id', 'int');
$rowid = GETPOST('rowid', 'int');
$massaction = GETPOST('massaction', 'alpha');
$toselect = GETPOST('toselect', 'array');
$confirm = GETPOST('confirm', 'alpha');
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');

$indicatif = GETPOST('indicatif', 'alpha');
$type = GETPOST('type', 'alpha');
$fk_classe = GETPOST('idclasse', 'alpha');

$search_indicatif = GETPOST("search_indicatif", 'alpha');
$search_type = GETPOST("search_type", 'alpha');
$search_classe = GETPOST("search_classe_materiel", 'alpha');


$actl = array();
$actl[0] = img_picto($langs->trans("Disabled"), 'switch_off');
$actl[1] = img_picto($langs->trans("Activated"), 'switch_on');
$acts[0] = "activate";
$acts[1] = "disable";


if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers
	{
		$search_indicatif = "";
		$search_type = "";
		$search_classe = "";
	}


if (!$sortfield) $sortfield = "tm.type";
if (!$sortorder) $sortorder = "ASC";

// Security check
if (! $usercanmanagetype) accessforbidden();
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0)
{
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();




/*
 * Actions
 */


if (GETPOST('actionadd') || GETPOST('actionmodify')) {
	$ok = 1;

	// Vérification des données
	if (!$indicatif)
	{
			setEventMessages($langs->trans('ErrorFieldRequired', 'Indicatif'), null, 'errors');
			$ok = 0;
	}
	if (!$type)
	{
			setEventMessages($langs->trans('ErrorFieldRequired', 'Type'), null, 'errors');
			$ok = 0;
	}
	if ($fk_classe < 1)
	{
			setEventMessages($langs->trans('ErrorFieldRequired', 'Classe'), null, 'errors');
			$ok = 0;
	}
	if (!is_numeric($fk_classe))
	{
			setEventMessages($langs->transnoentities("ErrorFieldMustBeANumeric", 'Classe'), null, 'errors');
			$ok = 0;
	}
	// Vérification si la classe existe bien
	$existing_class = getClasseDict();
	if (!array_key_exists($fk_classe, $existing_class)) {
		$ok = 0;
	}

	/* Vérifier si le type n'existe pas déjà */
	$existing_type = array();

	$sql = "SELECT rowid, indicatif, type";
	$sql .= " FROM ".MAIN_DB_PREFIX."c_type_materiel";
	if (GETPOST('actionmodify')) $sql .= " WHERE rowid <> ". $rowid;
	$resql = $db->query($sql);
	$num = $db->num_rows($resql);
	$i = 0;
	while ($i < $num)
	{
		$obj = $db->fetch_object($resql);
		$existing_type[$obj->rowid]['indicatif'] = $obj->indicatif;
		$existing_type[$obj->rowid]['type'] = $obj->type;
		$i++;
	}

	if (in_array_r($indicatif, $existing_type)) {
		setEventMessages('Cet indicatif est déjà utilisé', null, 'errors');
			$ok = 0;
	}
	if (in_array_r($type, $existing_type)) {
		setEventMessages('Ce type de matériel existe déjà', null, 'errors');
			$ok = 0;
	}

	if (GETPOST('actionadd') && $ok)
	{
			$sql = "INSERT INTO ".MAIN_DB_PREFIX."c_type_materiel (";
			$sql .= "indicatif";
			$sql .= ", type";
			$sql .= ", fk_classe";
			$sql .= ") VALUES (";
			$sql .= "'".$indicatif."'";
			$sql .= ", '".$type."'";
			$sql .= ", ".$fk_classe;
			$sql .= ")";

			$result = $db->query($sql);
			if ($result) {
					$id = $db->last_insert_id(MAIN_DB_PREFIX."c_type_materiel");
					setEventMessages('Type de matériel ajouté avec succès', null);

					$indicatif = '';
					$type = '';
					$fk_classe = '';
			}
			else
			{
				setEventMessages('Erreur lors de la création du type de matériel', null, 'errors');
			}

	}	elseif (GETPOST('actionmodify') && $ok)
	{
			$sql = "UPDATE ".MAIN_DB_PREFIX."c_type_materiel SET";
			$sql .= " indicatif = '". $indicatif ."'";
			$sql .= ", type = '". $type ."'";
			$sql .= ", fk_classe = ". $fk_classe;
			$sql .= " WHERE rowid = ". $rowid;
			if (!$db->query($sql)) setEventMessages('Erreur lors de la modification du type de matériel', null, 'errors');
			if (!$db->commit()) setEventMessages('Erreur lors de la modification du type de matériel', null, 'errors');
			else setEventMessages('Type de matériel modifié avec succès', null);


	}

}

if ($action)
{
	if ($action == 'activate' && $id) {
			$sql = "UPDATE ".MAIN_DB_PREFIX."c_type_materiel";
			$sql .= " SET active = 1";
			$sql .= " WHERE rowid = ".$id;
		if (!$db->query($sql)) setEventMessages('Erreur lors de l\'activation du type de matériel', null, 'errors');
		if (!$db->commit()) setEventMessages('Erreur lors de l\'activation du type de matériel', null, 'errors');
	}
	elseif ($action == 'disable' && $id) {
			$sql = "UPDATE ".MAIN_DB_PREFIX."c_type_materiel";
			$sql .= " SET active = 0";
			$sql .= " WHERE rowid = ".$id;
			if (!$db->query($sql)) setEventMessages('Erreur lors de la désactivation du type de matériel', null, 'errors');
			if (!$db->commit()) setEventMessages('Erreur lors de la désactivation du type de matériel', null, 'errors');
	}
	elseif ($action == 'confirm_delete' && $confirm == 'yes')       // delete
	{

			$sql = "DELETE FROM ";
			$sql .= MAIN_DB_PREFIX."c_type_materiel";
			$sql .= " WHERE rowid = ".$id;

	    $result = $db->query($sql);
	    if (!$result)
	    {
	        if ($db->errno() == 'DB_ERROR_CHILD_EXISTS')
	        {
	            setEventMessages($langs->transnoentities("ErrorRecordIsUsedByChild"), null, 'errors');
	        }
	        else
	        {
	            dol_print_error($db);
	        }
	    }
	}

}


$sql = "SELECT tm.rowid, tm.indicatif, tm.type, tm.fk_classe, tm.active, c.classe";
$sql.= " FROM ".MAIN_DB_PREFIX."c_type_materiel as tm ";
$sql.="INNER JOIN ".MAIN_DB_PREFIX."c_classe_materiel as c ON tm.fk_classe=c.rowid ";
$sql.="WHERE 1=1";
if ($search_classe && $search_classe != '-1') {
    $sql .= natural_search('tm.fk_classe', $search_classe);
}
if ($search_indicatif && $search_indicatif != '') {
    $sql .= natural_search('tm.indicatif', $search_indicatif);
}
if ($search_type && $search_type != '') {
    $sql .= natural_search('tm.type', $search_type);
}

$sql .= $db->order($sortfield, $sortorder);

$resql = $db->query($sql);


// Definition of fields for lists
$arrayfields = array(
	'tm.rowid'=>array('label'=>'ID', 'checked'=>1),
	'tm.indicatif'=>array('label'=>'Indicatif', 'checked'=>1, 'position'=>10),
	'tm.type'=>array('label'=>'Type de matériel', 'checked'=>1, 'position'=>10),
	'tm.fk_classe'=>array('label'=>'Classe de matériel', 'checked'=>1, 'position'=>11));



/*
 * View
 */

llxHeader("", $langs->trans("Materiel"));


// Confirmation of the deletion of the line
if ($action == 'delete')
{
		print $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$id, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_delete', '', 0, 1);
}

$arrayofselected = is_array($toselect) ? $toselect : array();
$arrayofmassactions['predeletetk'] = "<span class='fa fa-trash paddingrightonly'></span>".$langs->trans("Delete");
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);
$picto = 'materiel';
talm_print_barre_liste('Types de matériel', 0, $_SERVER["PHP_SELF"], '', '', '',$massactionbutton, $num, $nbtotalofrecords, $picto, 0, '', '', $limit, 0, 0, 1);

include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';


print '<div class="fichecenter">';


/* FORMULAIRE D'ENTRÉE DANS LE DICTIONNAIRE */

print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="from" value="'.dol_escape_htmltag(GETPOST('from', 'alpha')).'">';

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';

// Line for title
print '<tr class="liste_titre">';
print '<td>Indicatif</td>';
print '<td>Type</td>';
print '<td>Classe</td>';
print '<td style="min-width: 26px;"></td>';
print '<td style="min-width: 26px;"></td>';
print '</tr>';

// Line to enter new values
print '<!-- line to add new entry -->';
print '<tr class="oddeven nodrag nodrop nohover">';

print '<td>';
print '<input type="text" name="indicatif" value="'. (GETPOST('actionmodify') ? '' : $indicatif) .'">';
print '</td>';
print '<td>';
print '<input type="text" name="type" value="'. (GETPOST('actionmodify') ? '' : $type) .'">';
print '</td>';
print '<td>';
print $formmateriel->selectClasses(1, (GETPOST('actionmodify') ? '' : $fk_classe));
print ' <a href="'.DOL_URL_ROOT.'/admin/dict.php?id=57'.'">';
print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter une classe de matériel"></span>';
print '</a>';
print '</td>';
print '<td>';
print '</td>';

print '<td>';
if ($action != 'edit')
{
	print '<input type="submit" class="button" name="actionadd" value="'.$langs->trans("Add").'">';
}
print '</td>';


print "</tr>";

print '</table>';
print '</div>';
print '</form>';


print '<br>';


/* Liste des types de matériel */

print '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="formulaire">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="showdeleted" value="'.$showdeleted.'">';


// Lines with input filters
print '<div class="div-table-responsive">';
print '<table class="tagtable liste">'."\n";
print '<tr class="liste_titre_filter">';

print '<td class="liste_titre left">';
print '<input class="flat" type="text" name="search_indicatif" size="8" value="'.dol_escape_htmltag($search_indicatif).'">';
print '</td>';

print '<td class="liste_titre left">';
print '<input class="flat" type="text" name="search_type" size="8" value="'.dol_escape_htmltag($search_type).'">';
print '</td>';

print '<td class="liste_titre">';
$classe_dict = getClasseDict();
print $form->selectarray('search_classe_materiel', $classe_dict, $search_classe, 1);
print '</td>';

print '<td class="liste_titre center">';
print '</td>';


print '<td class="liste_titre center">';
print '</td>';

print '<td class="liste_titre center maxwidthsearch">';
$searchpicto = $form->showFilterButtons();
print $searchpicto;
print '</td>';

print '</tr>';



print '<tr class="liste_titre">';

print_liste_field_titre($arrayfields['tm.indicatif']['label'], $_SERVER["PHP_SELF"], "tm.indicatif", "", $param, "", $sortfield, $sortorder);

print_liste_field_titre($arrayfields['tm.type']['label'], $_SERVER["PHP_SELF"], "tm.type", "", $param, "", $sortfield, $sortorder);

print_liste_field_titre($arrayfields['tm.fk_classe']['label'], $_SERVER["PHP_SELF"], "tm.fk_classe", "", $param, "", $sortfield, $sortorder);

print '<td class="wrapcolumntitle liste_titre">État';
print '</td>';

print '<td class="nowrap"></td>';
print '<td class="nowrap"></td>';


// Draft MyObject
if ($conf->materiel->enabled == 1)
{
	$langs->load("orders");
	if ($resql)
	{
		$total = 0;
		$num = $db->num_rows($resql);

		$var = true;
		if ($num > 0)
		{
			$i = 0;
			while ($i < $num)
			{

				$obj = $db->fetch_object($resql);

				print '<tr class="oddeven">';

				if ($action == 'edit' && $id == $obj->rowid)
				{

    			print '<td class="tdoverflowmax200">';
					print '<input type="text" name="indicatif" value="'. $obj->indicatif .'">';
    			print "</td>\n";

    			print '<td class="tdoverflowmax200">';
					print '<input type="text" name="type" value="'. $obj->type .'">';
    			print "</td>\n";

    			print '<td class="tdoverflowmax200">';
					print $formmateriel->selectClasses(1, $obj->fk_classe);
    			print "</td>\n";

    			print '<td class="tdoverflowmax200">';
          print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?'.'action='.$acts[$obj->active].'&id='. $obj->rowid .'">'.$actl[$obj->active].'</a>';
    			print "</td>\n";


					print '<td class="center" colspan="2">';
					print '<input type="hidden" name="rowid" value="'.dol_escape_htmltag($obj->rowid).'">';
					print '<input type="submit" class="button" name="actionmodify" value="'.$langs->trans("Modify").'">';
					print '<input type="submit" class="button" name="actioncancel" value="'.$langs->trans("Cancel").'">';
					print '</td>';

				} else {

    			print '<td class="tdoverflowmax200">';
    			print $obj->indicatif;

    			print "</td>\n";

    			print '<td class="tdoverflowmax200">';
    			print $obj->type;
    			print "</td>\n";

    			print '<td class="tdoverflowmax200">';
    			print $obj->classe;
    			print "</td>\n";

    			print '<td class="tdoverflowmax200">';
          print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?'.'action='.$acts[$obj->active].'&id='. $obj->rowid .'">'.$actl[$obj->active].'</a>';
    			print "</td>\n";

					// Modify link
					if ($usercanmanagetype) print '<td align="center"><a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?action=edit&id='. $obj->rowid .'">'.img_edit().'</a></td>';
					else print '<td>&nbsp;</td>';


					print '<td class="center">';
					if ($usercanmanagetype) print '<a href="'.$_SERVER["PHP_SELF"].'?action=delete&id='. $obj->rowid .'">'.img_delete().'</a>';
					else print '<td>&nbsp;</td>';
					print '</td>';

				}

				print '</tr>';
				$i++;
			}
		}
		else
		{

			print '<tr class="oddeven"><td colspan="6" class="opacitymedium">Pas de type de matériel correspondant.</td></tr>';
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


print '</div></div></div></div>';

// End of page
llxFooter();
$db->close();
