<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$res = @include "../../main.inc.php";





require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';



require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formkit.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/materiel.lib.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/kit.lib.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';



// Load translation files required by the page

$langs->loadLangs(array("materiel@materiel"));



$form = new Form($db);

$materiel = new Materiel($db);

$kit = new Kit($db);

$formmateriel = new FormMateriel($db);

$formkit = new FormKit($db);



/*

 * Data fetching

 */



$usercanread = ($user->rights->kit->read);

$usercancreate = ($user->rights->kit->create);

$usercandelete = ($user->rights->kit->delete);



$action = GETPOST('action', 'alpha');

$massaction = GETPOST('massaction', 'alpha');

$toselect = GETPOST('toselect', 'array');

$confirm = GETPOST('confirm', 'alpha');

$sortfield = GETPOST("sortfield", 'alpha');

$sortorder = GETPOST("sortorder", 'alpha');



$search_ref = GETPOST("search_ref", 'alpha');

$search_etat_etiquette = GETPOST("search_etat_etiquette", 'int');

$search_disponibilite = GETPOST("search_disponibilite", 'int');

$search_materiel = GETPOST("search_materiel", 'alpha');

$search_libelle = GETPOST("search_libelle", 'alpha');



if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers

	{

		$search_ref = "";

		$search_disponibilite = -1;

		$search_etat_etiquette = -1;

		$search_materiel = -1;

		$search_libelle = -1;

	}





if (!$sortfield) $sortfield = "k.rowid";

if (!$sortorder) $sortorder = "ASC";



// Security check

if (! $usercanread) accessforbidden();

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





if (GETPOST('cancel', 'alpha')) { $action = 'list'; $massaction = ''; }
if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') { $massaction = ''; }

if (!$error && ($massaction == 'delete' || ($action == 'delete' && $confirm == 'yes')) && $usercandelete)

{

    $error = 0;

	foreach ($toselect as $toselectid)

	{

	    $tmp_kit = new Kit($db);

	    $tmp_kit->fetch($toselectid);

	    if ($tmp_kit->fk_exploitation) {

	        setEventMessages('Le kit '. $tmp_kit->ref .' est en exploitation (réf: '. $tmp_kit->exploitation_ref .'). Il ne peut pas être supprimé avant la fin de l\'exploitation.', null, 'errors');

	        $error++;

	    }

	    elseif (!$tmp_kit->delete($user)) $error ++;

	}



	if ($error > 0) setEventMessages('<br>Erreur lors de la suppression d\'un ou plusieurs élement(s).', null, 'errors');

	else setEventMessages('Élément(s) supprimé(s) avec succès', null);



}











/*

 * View

 */



llxHeader("", 'Kit - Liste');





$sql = "SELECT DISTINCT k.rowid, k.active, tk.indicatif";

$sql.= " FROM ".MAIN_DB_PREFIX."kit as k ";

$sql.="INNER JOIN ".MAIN_DB_PREFIX."c_type_kit as tk ON k.fk_type_kit=tk.rowid ";

$sql.="INNER JOIN ".MAIN_DB_PREFIX."kit_content as kc ON k.rowid=kc.fk_kit ";



$sql.="WHERE k.active = 1";

if ($search_ref && $search_ref != '-1') {

    $sql .= natural_search(array('tk.indicatif', 'k.cote'), str_replace('-', ' ', $search_ref), 0, 0);

}

if ($search_materiel && $search_materiel != '-1') {

    $sql .= natural_search('kc.fk_materiel', $search_materiel);

    $sql .= natural_search('kc.active', 1);

}

if ($search_etat_etiquette && $search_etat_etiquette != '-1') {

    $sql .= natural_search('k.fk_etat_etiquette', $search_etat_etiquette);

}



if ($search_libelle && $search_libelle != '-1') {

    $sql .= natural_search('k.rowid', $search_libelle);

}



$sql .= $db->order($sortfield, $sortorder);

$resql = $db->query($sql);







// Definition of fields for lists

$arrayfields = array(

	'k.rowid'=>array('label'=>'ID', 'checked'=>1),

	'kc.fk_materiel'=>array('label'=>'Matériels', 'checked'=>1, 'position'=>10),

	'k.fk_type_kit_'=>array('label'=>'Libellé', 'checked'=>1, 'position'=>10),

	'tk.indicatif'=>array('label'=>'Réf.', 'checked'=>1, 'position'=>10),

	'k.cote'=>array('label'=>'Disponibilité', 'checked'=>1, 'position'=>11));





print '<div class="fichecenter">';



print '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="formulaire">';

print '<input type="hidden" name="token" value="'.newToken().'">';

print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';

print '<input type="hidden" name="action" value="list">';

print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';

print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

print '<input type="hidden" name="showdeleted" value="'.$showdeleted.'">';



$arrayofselected = is_array($toselect) ? $toselect : array();



$arrayofmassactions['predelete'] = "<span class='fa fa-trash paddingrightonly'></span>".$langs->trans("Delete");

$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

$picto = 'kit';

if ($usercancreate) $newcardbutton = dolGetButtonTitle('Nouveau kit', '', 'fa fa-plus-circle', DOL_URL_ROOT.'/custom/kit/card.php?action=create', '', 1);

talm_print_barre_liste('Kit', 0, $_SERVER["PHP_SELF"], '', '', '',$massactionbutton, $num, $nbtotalofrecords, $picto, 0, $newcardbutton, '', $limit, 0, 0, 1);



include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';



print '<div class="div-legende-badge" style="margin-bottom:1em;">';

print '<span>Légende badges matériels : </span>';

print '<span class="badge  badge-status4 badge-status" style="color:white;">Fonctionnel & OK</span>&nbsp;';

print '<span class="badge  badge-status2 badge-status" style="color:white;">Fonctionnel & A réparer</span>&nbsp;';

print '<span class="badge  badge-status4 badge-status" style="color:white; background-color:#905407;">Non fonctionnel & A réparer</span>&nbsp;';

print '<span class="badge  badge-status8 badge-status" style="color:white;">Non fonctionnel & Irréparable</span>&nbsp;';

print '</div>';





// Lines with input filters

print '<div class="div-table-responsive">';

print '<table class="tagtable liste">'."\n";

print '<tr class="liste_titre_filter">';



print '<td class="liste_titre left">';

print '<input class="flat" type="text" name="search_ref" size="8" value="'.dol_escape_htmltag($search_ref).'">';

print '</td>';



print '<td class="liste_titre ">';

print $formkit->selectKit($search_libelle, 'search_libelle', 1, 1, 0);

print '</td>';



print '<td class="liste_titre left">';

print $form->selectarray('search_disponibilite', array('1'=>'Disponible', '2'=>'En exploitation', '3'=>'Inexploitable'), $search_disponibilite, 1);

print '</td>';



print '<td class="liste_titre ">';

print $formmateriel->selectMateriels($search_materiel, 'search_materiel', '', 1);

print '</td>';





print '<td class="liste_titre center">';

print $form->selectarray('search_etat_etiquette', getEtatEtiquetteKitDict(), $search_etat_etiquette, 1);

print '</td>';



print '<td class="liste_titre center maxwidthsearch">';

$searchpicto = $form->showFilterButtons();

print $searchpicto;

print '</td>';



print '</tr>';







print '<tr class="liste_titre">';



print_liste_field_titre($arrayfields['tk.indicatif']['label'], $_SERVER["PHP_SELF"], "tk.indicatif", "", $param, "", $sortfield, $sortorder);



print_liste_field_titre($arrayfields['k.fk_type_kit_']['label'], $_SERVER["PHP_SELF"], "k.fk_type_kit", "", $param, "", $sortfield, $sortorder);



print_liste_field_titre($arrayfields['k.cote']['label'], $_SERVER["PHP_SELF"], "k.fk_type_kit", "", $param, "", $sortfield, $sortorder);



print_liste_field_titre($arrayfields['kc.fk_materiel']['label'], $_SERVER["PHP_SELF"], "kc.fk_materiel", "", $param, "", $sortfield, $sortorder);



print_liste_field_titre('État étiquette', $_SERVER["PHP_SELF"], "k.fk_etat_etiquette", "", '', "align='center'", $sortfield, $sortorder);



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

                if (!($kit->fetch($obj->rowid))){

                    setEventMessages('Impossible de récupérer les données du kit.', null, 'errors');

                }

                if ($search_disponibilite && $search_disponibilite != -1 && $kit->fk_disponibilite != $search_disponibilite){

                    $i++;

                    continue;

                }



				print '<tr class="oddeven">';



    			print '<td class="tdoverflowmax200">';

    			print $kit->getNomURL();

    			print "</td>\n";



    			print '<td class="tdoverflowmax200">';

    			print $kit->libelle;

    			print "</td>\n";



				print '<td class="nowrap"><span class="badge  badge-status'.$kit->c_disponibilite[$kit->fk_disponibilite]['badge_code'].' badge-status">'.$kit->c_disponibilite[$kit->fk_disponibilite]['disponibilite'].'</span></td>';



    			print '<td class="tdoverflowmax200">';

    			$mat_object_sorted = $kit->materiel_object;

    			usort($mat_object_sorted, function($a, $b) {return strcmp($a->fk_etat+$a->fk_exploitabilite, $b->fk_etat+$b->fk_exploitabilite);}); // ON TRIE LES MATERIELS SELON L'ÉTAT

    			foreach ($mat_object_sorted as $mat) {

    			    if ($mat->fk_etat == 1  && $mat->fk_exploitabilite == 1) print '<span class="badge  badge-status4 badge-status" style="color:white;">'.$mat->getNomURL(0, 'style="color:white;"').'</span> ';

    			    elseif ($mat->fk_etat == 2 && $mat->fk_exploitabilite == 1) print '<span class="badge  badge-status2 badge-status" style="color:white;">'.$mat->getNomURL(0, 'style="color:white;"').'</span> ';

    			    elseif ($mat->fk_etat == 2 && $mat->fk_exploitabilite == 2) print '<span class="badge  badge-status4 badge-status" style="color:white; background-color:#905407;">'.$mat->getNomURL(0, 'style="color:white;"').'</span>&nbsp;';

    			    elseif ($mat->fk_etat == 3 && $mat->fk_exploitabilite == 2) print '<span class="badge  badge-status8 badge-status" style="color:white;">'.$mat->getNomURL(0, 'style="color:white;"').'</span> ';

    			    else print '<span class="badge  badge-status5 badge-status">'.$mat->getNomURL(0).'</span> ';



    			}

    			print "</td>\n";



					print '<td class="tdoverflowmax200 center"><span class="badge  badge-dot badge-status'.$kit->etat_etiquette_badge_code.' classfortooltip badge-status" title="'.$kit->etat_etiquette.'"></span></td>';





    		// Action

    		print '<td class="nowrap center">';

    		if ($usercandelete) {

    			$selected = 0;

    			if (in_array($obj->rowid, $arrayofselected)) $selected = 1;

    			print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected ? ' checked="checked"' : '').'>';

    		}



    		print '</td>';

				print '</tr>';

				$i++;

				$total++;

			}

		}

		if (!$total) print '<tr class="oddeven"><td colspan="6" class="opacitymedium">Pas de kit correspondant.</td></tr>';

		print "</table><br>";



		$db->free($resql);



	}

	else dol_print_error($db);



}







	$db->free($resql);



	print "</table>";

	print "</div>";

	print '</form>';

print '<div class="fichethirdleft"></div><div class="fichetwothirdright"><div class="ficheaddleft">';



    // Fonction pour augmenter la tailler du select libelle pour afficher entierement le libelle

    print "<script>

    $(document).ready(function(){

        var select = $( '[name=\"search_libelle\"]' );

        var new_width = 'calc('+ select.next('.select2-container').css(\"width\") +' + 1em)';

        select.next('.select2-container').css('width', new_width);

	});

    </script>";





print '</div></div></div>';



// End of page

llxFooter();

$db->close();

