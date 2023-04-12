<?php

@include "../../../../main.inc.php";

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/materiel.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formkit.class.php';

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
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');

$search_ref = GETPOST("search_ref", 'alpha');
$search_state = GETPOST("search_state", 'alpha');
$search_etat_etiquette = GETPOST("search_etat_etiquette", 'alpha');
$search_exploitabilite = GETPOST("search_exploitabilite", 'alpha');
$search_marque_modele = GETPOST("search_marque_modele", 'alpha');
$search_entrepot = GETPOST("search_entrepot", 'alpha');
$search_proprietaire = GETPOST("search_proprietaire", 'alpha');
$search_kit = GETPOST("search_kit", 'alpha');


if (!$sortfield) $sortfield = "m.rowid";
if (!$sortorder) $sortorder = "ASC";

if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers
	{
		$search_ref = "";
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

$facture_fourn = new FactureFournisseur($db);

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

print '<div class="fichecenter">';


$sql = "SELECT rowid";
$sql.= " FROM ".MAIN_DB_PREFIX."facture_fourn as f ";
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
	//'m.precision_type'=>array('label'=>'Type d\'instrument', 'checked'=>1, 'position'=>13),
	'm.notes_supplementaires'=>array('label'=>'Notes supplémentaires', 'checked'=>1, 'position'=>14),
	'm.fk_origine'=>array('label'=>'Origine', 'checked'=>1, 'position'=>14),
	'm.entrepot'=>array('label'=>'Entrepôt', 'checked'=>1, 'position'=>15),
	'm.fk_proprietaire'=>array('label'=>'Propriétaire', 'checked'=>1, 'position'=>16),
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
	if (empty($arrayfields['p.fk_product_type']['checked'])) print '<input type="hidden" name="search_type" value="'.dol_escape_htmltag($search_type).'">';


	$arrayofselected = is_array($toselect) ? $toselect : array();

    $arrayofmassactions['predeactivate'] = "<span class='fa fa-toggle-off paddingrightonly' style='color:#666; font-size:0.86em;'></span>Désactiver";
    $arrayofmassactions['predelete'] = "<span class='fa fa-trash paddingrightonly'></span>".$langs->trans("Delete");
	//if (in_array($massaction, array('presend', 'predelete'))) $arrayofmassactions = array();
	$massactionbutton = $form->selectMassAction('', $arrayofmassactions);
    $picto = 'materiel';
    if ($usercancreate) $newcardbutton = dolGetButtonTitle('Nouveau matériel', '', 'fa fa-plus-circle', DOL_URL_ROOT.'/custom/materiel/card.php?action=create', '', 1);
	talm_print_barre_liste('Matériels', 0, $_SERVER["PHP_SELF"], '', '', '',$massactionbutton, $num, $nbtotalofrecords, $picto, 0, $newcardbutton, '', $limit, 0, 0, 1);

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
	    print $formproduct->selectWarehouses($search_entrepot, 'search_entrepot', 'warehouseopen', 1);
	print '</td>';


	print '<td class="liste_titre left">';
		print '<input class="flat" type="text" name="search_proprietaire" size="8" value="'.dol_escape_htmltag($search_proprietaire).'">';
	print '</td>';



	print '<td class="liste_titre left">';
		print $form->selectarray('search_etat_etiquette', getEtatEtiquetteDict(), $search_etat_etiquette, 1);
	print '</td>';


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

print_liste_field_titre($arrayfields['m.fk_etat']['label'], $_SERVER["PHP_SELF"], "m.fk_etat", "", $param, "align='center'", $sortfield, $sortorder);

print_liste_field_titre($arrayfields['m.fk_exploitabilite']['label'], $_SERVER["PHP_SELF"], "m.fk_exploitabilite", "", $param, "align='center'", $sortfield, $sortorder);

print_liste_field_titre($arrayfields['m.fk_marque']['label'], $_SERVER["PHP_SELF"], "mm.marque", "", $param, "", $sortfield, $sortorder);

print_liste_field_titre($arrayfields['m.entrepot']['label'], $_SERVER["PHP_SELF"], "m.fk_entrepot", "", $param, "", $sortfield, $sortorder);

print_liste_field_titre($arrayfields['m.fk_proprietaire']['label'], $_SERVER["PHP_SELF"], "p.proprietaire", "", $param, "", $sortfield, $sortorder);

print_liste_field_titre('État étiquette', $_SERVER["PHP_SELF"], "m.fk_etat_etiquette", "", '', "align='center'", $sortfield, $sortorder);

print_liste_field_titre('Kit', '', "", "", '', "align='center'");



print '<th></th>';




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
                $facture_fourn->fetch($obj->rowid);

				foreach ($facture_fourn->lines as $line) {
					print '<tr class="oddeven">';

					print '<td class="tdoverflowmax200">';
					print $line->ref;
					print "</td>\n";
					if ($facture_fourn->paye) print '<td class="nowrap center"><span class="badge  badge-status4 badge-status">'.Payée.'</span></td>';
					else print '<td class="nowrap center"><span class="badge  badge-status5 badge-status">Impayée</span></td>';

					print '<td class="nowrap center"><span class="badge  badge-dot badge-status'.$materiel->exploitabilite_badge_code.' classfortooltip badge-status" title="'.$materiel->exploitabilite.'"></span></td>';

					$marque_modele = $obj->marque ? $obj->marque .' '. $obj->modele: '<i>Aucun</i>';
					print '<td class="nowrap">'.$marque_modele.'</td>';

					$entrepot = $obj->entrepot ? $obj->entrepot : '<i>Aucun</i>';
					print '<td class="nowrap">'.$entrepot.'</td>';

					$proprietaire = $obj->proprietaire ? $obj->proprietaire : '<i>Aucun</i>';
					print '<td class="nowrap">'.$proprietaire.'</td>';

					print '<td class="tdoverflowmax200 center"><span class="badge  badge-dot badge-status'.$materiel->etat_etiquette_badge_code.' classfortooltip badge-status" title="'.$materiel->etat_etiquette.'"></span></td>';

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
						
				}
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
