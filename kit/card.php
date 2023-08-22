<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Load Dolibarr environment
@include "../../main.inc.php";
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formkit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/materiel.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/kit.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/entretien.class.php';


// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));

// Security check
if (!$user->rights->kit->read) accessforbidden();

$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0)
{
	$action = '';
	$socid = $user->socid;
}

/*Recupération données POST*/
$action = (GETPOST('action', 'alpha') ? GETPOST('action', 'alpha') : 'view');
$cancel = GETPOST('cancel', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$socid = GETPOST('socid', 'int');
$id = GETPOST('id', 'int');
$kit_type_id = GETPOST('idtypekit', 'int');
$fk_materiel = array_values(array_diff(GETPOST('materiel', 'array'), array(-1)));
$fk_etat_etiquette = GETPOST('fk_etat_etiquette', 'int');
$ref = GETPOST('ref', 'alpha');
$notes = GETPOST('notes', 'alpha');

/*
 *  AJOUTER DONNÉES
 */
$form = new Form($db);
$formmateriel = new FormMateriel($db);
$kit = new Kit($db);
$formkit = new FormKit($db);

/*
 * Actions
 */
if ($cancel) $action = '';

if ($id > 0)
{
    $result = $kit->fetch($id);
    if (!$result) {
        setEventMessages('Impossible de récupérer les données du kit.', null, 'errors');
        header('Location: '.DOL_URL_ROOT.'/custom/kit/list.php');
        exit;
    }
}


$usercanread = ($user->rights->kit->read);
$usercancreate = ($user->rights->kit->create);
$usercandelete = ($user->rights->kit->delete);

if ($action == 'add' && $usercancreate)
{
    $error = 0;
    if (empty($kit_type_id))
    {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Type de kit'), null, 'errors');
        $action = "create";
        $error++;
    }
    if (empty($fk_materiel))
    {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Matériels à inclure'), null, 'errors');
        $action = "create";
        $error++;
    }
    if (!$error)
    {
        $kit->type_kit_id         = $kit_type_id;
        $kit->ref                  = $ref;
        $kit->notes          = $notes;
        $kit->fk_materiel = $fk_materiel;
        $kit->fk_etat_etiquette = $fk_etat_etiquette;
        if ($kit->create($user)) {
            setEventMessages('Kit ajouté avec succès !', null);
        }
        else {
            setEventMessages('Erreur lors de la création du kit. Contactez l\'administrateur.', null, 'errors');
            $action = 'create';
            $error++;
        }
    }
    if (!$error) {
        header('Location: '.DOL_URL_ROOT.'/custom/kit/list.php');
        exit;
    }
}
elseif ($action == 'update' && $usercancreate)
{
    // On vérifie si le kit est en exploitation ou non
    if ($kit->fk_exploitation) { 
        setEventMessages('Ce kit est en exploitation (réf: '. $kit->exploitation_ref .'). Il ne peut pas être modifié avant la fin de l\'exploitation.', null, 'errors');
    }
    // On vérifie si le kit est actif
    elseif (!$kit->active) { 
        setEventMessages('Ce kit est supprimé, il ne peut pas être modifié.', null, 'errors');
    }
	else 
    {
        if (empty($fk_materiel))
        {
            setEventMessages($langs->trans('ErrorFieldRequired', 'Matériel(s) inclus'), null, 'errors');
            $action = "edit";
        }
        else
        {
            $kit->oldcopy = clone $kit;
            $kit->notes          = $notes;
            $kit->fk_materiel    = $fk_materiel;
            $kit->fk_etat_etiquette    = $fk_etat_etiquette;

            if ($kit->update($user)) {
                setEventMessages('Kit modifié avec succès !', null);
                header('Location: '.DOL_URL_ROOT.'/custom/kit/card.php?id='.$kit->id);
                exit;
            }
            else {
                setEventMessages('Erreur lors de la modification du kit. Contactez l\'administrateur.', null, 'errors');
                $action = 'edit';
            }
        }
	}
}
// Supprimer un kit
if ($action == 'confirm_delete' && $confirm != 'yes') { $action = ''; }
if ($action == 'confirm_delete' && $confirm == 'yes' && $usercandelete)
{
    if (!$kit->active) { // On vérifie si le kit est supprimé
        setEventMessages('Ce kit est déjà supprimé.', null, 'errors');
        $error++;
    }
    if ($kit->fk_exploitation) { // On vérifie si le kit est en exploitation ou non
        setEventMessages('Ce kit est en exploitation (réf: '. $kit->exploitation_ref .'). Il ne peut pas être supprimé avant la fin de l\'exploitation.', null, 'errors');
        $error++;
    }
	else {
	    $result = $kit->delete($user);
        if ($result > 0)
        {
        	setEventMessages('Le kit a bien été supprimé.', null);
            header('Location: '.DOL_URL_ROOT.'/custom/kit/list.php');
            exit;
        }
        else
        {
        	setEventMessages('Erreur lors de la suppression du kit.', null, 'errors');
            $reload = 0;
            $action = '';
        }
	}
}
// Etat étiquette
if ($action == 'setfk_etat_etiquette' && $usercancreate)
{
	if (!$kit->active) setEventMessages('Ce kit ne peut pas être modifié', null, 'errors');
    /* On change l'etat du materiel' */
	else {
		$result = $kit->setValueFrom('fk_etat_etiquette', GETPOST('fk_etat_etiquette'), 'kit', null, 'int');
		if ($result) {
		    setEventMessages('Valeur modifiée avec succès.', null);
		} else {
		    setEventMessages('Erreur lors de la modification de la valeur', null, 'errors');
		}
	}
	header("Location: ".$_SERVER['PHP_SELF']."?id=".$kit->id);
	exit;
}
/*
 * View
 */
if (is_object($kit)) llxHeader("", 'Kit ' . $kit->ref);
else llxHeader("", 'Kit');
if ($action == 'create' && !GETPOST('idtypekit', 'int') && $usercancreate) // SELECTION DU TYPE DE KIT
    {
        //WYSIWYG Editor
        require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
        print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="create">';
        print '<input type="hidden" name="type" value="'.$type.'">'."\n";
				if (!empty($modCodeProduct->code_auto))
				print '<input type="hidden" name="code_auto" value="1">';
				if (!empty($modBarCodeProduct->code_auto))
				print '<input type="hidden" name="barcode_auto" value="1">';
				print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
				$picto = 'kit';
				$title = 'Sélectionner le type de kit';
        $linkback = "";
        print talm_load_fiche_titre($title, $linkback, $picto);
        dol_fiche_head('');
        print '<table class="border centpercent">';
        print '<tr>';
        $tmpcode = '';
        print '</td></tr>';
        // Type de Kit
        print '<tr><td class="fieldrequired titlefieldcreate">Type de kit : </td><td>';
        print $formkit->selectTypeKit();
        print ' <a href="'.DOL_URL_ROOT.'/custom/kit/typekit/card.php">';
        print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter un type de kit"></span>';
        print '</a>';
        print '</td>';
        print '</tr>';
        print "</table>";
		dol_fiche_end();
		print '<div class="center">';
		print '<input type="submit" class="button" value="Suivant">';
		print '</div>';
		print '</form>';
    }
if ($action == 'create' && GETPOST('idtypekit', 'int') && $usercancreate)  // Type de kit choisi -> création d'un nouveau kit
    {
        $kit_type_dict = getKitTypeDict();
        //WYSIWYG Editor
        require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
        print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="add">';
        print '<input type="hidden" name="idtypekit" value="'.GETPOST('idtypekit', 'int').'">';
        print '<input type="hidden" name="type" value="'.$type.'">'."\n";
        print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
        $picto = 'kit';
        $title = 'Nouveau Kit - '. $kit_type_dict[$kit_type_id]['type'];
        $linkback = "";
        print talm_load_fiche_titre($title, $linkback, $picto);
        dol_fiche_head('');
        print '<table class="border centpercent">';
        $formkit->printKitMaterielSelect('', GETPOST('idtypekit', 'int')); // Affichage du formulaire de selection des matériels
        // État étiquette
        print '<tr><td class="titlefieldcreate fieldrequired">État de l\'étiquette</td><td colspan="3">';
        $fk_etat_etiquette_array = getEtatEtiquetteKitDict();
        print $form->selectarray('fk_etat_etiquette', $fk_etat_etiquette_array, GETPOST('fk_etat_etiquette'), 0, 0, 0, 'style="min-width:200px;"');
        print '</td></tr>';
        // Notes supplémentaires
        print '<tr><td class="tdtop titlefieldcreate">Notes sur le kit (facultatif)</td><td colspan="3">';
        $doleditor = new DolEditor('notes', GETPOST('notes', 'none'), '', 160, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '90%');
        $doleditor->Create();
        print "</td></tr>";
        print "</table>";
		dol_fiche_end();
		print '<div class="center">';
		print '<input type="submit" class="button" value="'.$langs->trans("Create").'">';
		print ' &nbsp; &nbsp; ';
		print '<input type="button" class="button" value="'.$langs->trans("Cancel").'" onClick="javascript:history.go(-1)">';
		print '</div>';
		print '</form>';
	}
    /*
     * Kit card
     */
    elseif ($kit->id > 0)
    {
        // Fiche en mode edition
		if ($action == 'edit' && $usercancreate && $kit->active)
		{
        $kit_type_dict = getKitTypeDict();
        //WYSIWYG Editor
        require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
        print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="update">';
        print '<input type="hidden" name="idtypekit" value="'.$kit->fk_type_kit.'">';
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
        print '<input type="hidden" name="id" value="'.$kit->id.'">';
		$picto = 'kit';
		$title = 'Modification - '. $kit->ref;
        $linkback = "";
        print talm_load_fiche_titre($title, $linkback, $picto);
        dol_fiche_head('');
        print '<table class="border centpercent">';
        print '<tr>';
        $tmpcode = '';
		if ($refalreadyexists)
        {
            print $langs->trans("RefAlreadyExists");
        }
        print '</td></tr>';
        $formkit->printKitMaterielSelect($kit, $kit->type_kit->id); // Affichage du formulaire de selection des matériels
        // État étiquette
        print '<tr><td class="fieldrequired">État de l\'étiquette</td><td colspan="3">';
        $fk_etat_etiquette_array = getEtatEtiquetteKitDict();
        print $form->selectarray('fk_etat_etiquette', $fk_etat_etiquette_array, $kit->fk_etat_etiquette);
        print '</td></tr>';
        // Notes supplémentaires
        print '<tr><td class="tdtop">Notes sur le kit (facultatif)</td><td colspan="3">';
        $doleditor = new DolEditor('notes', $kit->notes, '', 160, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '90%');
        $doleditor->Create();
        print "</td></tr>";
        print "</table>";
		dol_fiche_end();
		print '<div class="center">';
		print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
		print ' &nbsp; &nbsp; ';
		print '<input type="button" class="button" value="'.$langs->trans("Cancel").'" onClick="javascript:history.go(-1)">';
		print '</div>';
		print '</form>';
		}
        // Fiche en mode visu
        elseif ($usercanread)
		{
		    $head = kit_prepare_head($kit);
            $titre = 'fdf';
            $picto = ('kit');
            talm_fiche_head($head, 'card', $titre, -1, $picto);
            $linkback = '<a href="'.DOL_URL_ROOT.'/custom/kit/list.php/">Retour à la liste</a>';
            talm_banner_tab($kit, 'id', $linkback, 1, 'rowid');
            print '<div class="fichecenter">';
						if (!$kit->active) print '<div class="info clearboth"><i class="fas fa-info-circle"></i>&nbsp; Ce kit a été supprimé, il ne peut pas être modifié</div>';
            print '<div class="fichehalfleft">';
            print '<div class="underbanner clearboth"></div>';
            print '<table class="border tableforfield" width="100%">';
			// Type de kit
			print '<tr><td class="titlefield">';
			print "Type de kit";
			print '</td><td colspan="3">';
			print $kit->type_kit->title;
			print '</td></tr>';
			// Etat étiquette
			print '<tr><td class="titlefield">';
			$etat_etiquette_dict = getEtatEtiquetteKitDict();
			$typeformat = 'select;';
			$i = 0;
			foreach ($etat_etiquette_dict as $key=>$etat_etiquette) {
			    $typeformat .= $key.':'.$etat_etiquette;
			    if ($key != array_key_last($etat_etiquette_dict)) $typeformat .= ',';
			}
			print $form->editfieldkey("État de l'étiquette", 'fk_etat_etiquette', $kit->fk_etat_etiquette, $kit, $usercancreate, $typeformat);
			print '</td><td colspan="3">';
			print $form->editfieldval("État de l'étiquette", 'fk_etat_etiquette', $kit->fk_etat_etiquette, $kit, $usercancreate, $typeformat);
			print '</td></tr>';
			// Notes
			print '<tr><td class="titlefield">';
			print "Notes";
			print '</td><td colspan="3">';
			$notes = $kit->notes ? $kit->notes : '<i>Pas de notes</i>';
			print $notes;
			print '</td></tr>';
			// Materiels inclus :
			print '<tr><td class="titlefield">';
			$tooltip_legende = '<span class="badge  badge-status4 badge-status" style="color:white;">Fonctionnel & OK</span><br><br>';
			$tooltip_legende.= '<span class="badge  badge-status2 badge-status" style="color:white;">Fonctionnel & A réparer</span><br><br>';
			$tooltip_legende.= '<span class="badge  badge-status4 badge-status" style="color:white; background-color:#905407;">Non fonctionnel & A réparer</span><br><br>';
			$tooltip_legende.= '<span class="badge  badge-status8 badge-status" style="color:white;">Non fonctionnel & Irréparable</span>';
			print "<span title='".$tooltip_legende."' class='classfortooltip'>Materiels inclus</span>";
			print '</td><td colspan="3">';
    			$mat_object_sorted = $kit->materiel_object;
    			$is_non_fonctionnel = 0; //on regarde si un des matériels est non fonctionnel pour afficher une alerte
    			usort($mat_object_sorted, function($a, $b) {return strcmp($a->fk_etat+$a->fk_exploitabilite, $b->fk_etat+$b->fk_exploitabilite);}); // ON TRIE LES MATERIELS SELON L'ÉTAT
    			foreach ($mat_object_sorted as $mat) {
    			    if ($mat->fk_exploitabilite == 2) $is_non_fonctionnel++;
    			    if ($mat->fk_etat == 1  && $mat->fk_exploitabilite == 1) print '<span class="badge  badge-status4 badge-status" style="color:white;">'.$mat->getNomURL(0, 'style="color:white;"').'</span> ';
    			    elseif ($mat->fk_etat == 2 && $mat->fk_exploitabilite == 1) print '<span class="badge  badge-status2 badge-status" style="color:white;">'.$mat->getNomURL(0, 'style="color:white;"').'</span> ';
    			    elseif ($mat->fk_etat == 2 && $mat->fk_exploitabilite == 2) print '<span class="badge  badge-status4 badge-status" style="color:white; background-color:#905407;">'.$mat->getNomURL(0, 'style="color:white;"').'</span>&nbsp;';
    			    elseif ($mat->fk_etat == 3 && $mat->fk_exploitabilite == 2) print '<span class="badge  badge-status8 badge-status" style="color:white;">'.$mat->getNomURL(0, 'style="color:white;"').'</span> ';
    			    else print '<span class="badge  badge-status5 badge-status">'.$mat->getNomURL(0).'</span> ';
    			}
			print '</td></tr>';
            print '</table>';
            if ($is_non_fonctionnel) print '<br><div><b style="color:red;"><span class="fa fa-exclamation-triangle paddingrightonly" style="color:red; font-size:0.86em;"></span>Un des matériels inclus dans le kit est non fonctionnel.</b> <br>Ce kit ne peut pas être mis en exploitation.</div>';
            print '</div>';
            print '</div>';
            print "\n".'<div class="tabsAction">'."\n";
            if (empty($reshook) && $kit->active)
        	{
        	    if ($usercancreate) print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit&amp;id='.$kit->id.'">'.$langs->trans("Modify").'</a>';
        	    if ($usercandelete) print '<span id="action-delete" class="butActionDelete">'.$langs->trans('Delete').'</span>'."\n";
            }
            print "\n</div>\n";
            print "<table class='centpercent notopnoleftnoright table-fiche-title'></table>";
            print "\n<br>\n";
            print "\n<br>\n";
            print_titre("Description des matériels inclus");
            foreach($kit->materiel_object as $mat) {
            $mat_link = '<a href="'.DOL_URL_ROOT.'/custom/materiel/card.php?id='.$mat->id.'" style="color:rgb(0, 123, 140);">'.$mat->ref.'</a>';
            print talm_load_fiche_titre($mat_link, '', 'materiel');
            print '<div class="fichecenter">';
            print '<div class="fichehalfleft">';
            print '<div class="underbanner clearboth"></div>';
            print '<table class="border tableforfield" width="100%">';
			// Type
			print '<tr><td class="titlefield">';
			print 'Type';
			print '</td><td colspan="3">';
			print $mat->type_materiel_ind . '-' . $mat->type_materiel;
			print '</td></tr>';
			// Etat
			print '<tr><td class="titlefield">';
			print "État";
			print '</td><td colspan="3">';
			print '<span class="badge  badge-status'.$mat->etat_badge_code.' badge-status">'.$mat->etat.'</span>';
			print '</td></tr>';
			// Exploitabilité
			print '<tr><td class="titlefield">';
			print "Exploitabilité";
			print '</td><td colspan="3">';
			print '<span class="badge  badge-status'.$mat->exploitabilite_badge_code.' badge-status">'.$mat->exploitabilite.'</span>';
			print '</td></tr>';
			// Marque
			print '<tr><td class="titlefield">';
			print 'Marque';
			print '</td><td colspan="3">';
			$marque = $mat->marque ? $mat->marque : '<i>Pas de marque</i>';
			print $marque;
			print '</td></tr>';
			// Modèle
			print '<tr><td class="titlefield">';
			print 'Modèle';
			print '</td><td colspan="3">';
			$modele = $mat->modele ? $mat->modele : '<i>Pas de modele</i>';
			print $modele;
			print '</td></tr>';
			// Type d'instrument
			print '<tr><td class="titlefield">';
			print 'Type d\'instrument';
			print '</td><td colspan="3">';
			$type_instrument = $mat->type_instrument ? $mat->type_instrument : '<i>Pas de type d\'instrument</i>';
			print $type_instrument;
			print '</td></tr>';
			// Notes supplémentaires
			print '<tr><td class="titlefield">';
			print "Notes supplémentaires";
			print '</td><td colspan="3">';
			$notes = $mat->notes ? $mat->notes : '<i>Pas de notes</i>';
			print $notes;
			print '</td></tr>';
            print '</table>';
            print '</div>';
            print '<div class="fichehalfright"><div class="ficheaddleft">';
            print '<div class="underbanner clearboth"></div>';
            print '<table class="border tableforfield" width="100%">';
			// Entrepot
			print '<tr><td class="titlefield">';
			print 'Entrepôt';
			print '</td><td colspan="3">';
			print $mat->entrepot_ref;
			print '</td></tr>';
			// Origine
			print '<tr><td class="titlefield">';
			print "Origine";
			print '</td><td colspan="3">';
			print $mat->origine;
			print '</td></tr>';
			// Propriétaire
			print '<tr><td class="titlefield">';
			print "Propriétaire";
			print '</td><td colspan="3">';
			$proprietaire = $mat->proprietaire ? $mat->proprietaire : '<i>Pas de propriétaire</i>';
			print $proprietaire;
			print '</td></tr>';
            print '</table>';
            print '</div>';
            print '</div></div>';
            print '<div style="clear:both"></div>';
            }
            print '</table>';
            print '</div>';
            print '</div></div>';
            print '<div style="clear:both"></div>';
            dol_fiche_end();
        }
    }
    elseif ($action != 'create')
    {
        exit;
    }
// Confirm delete product
if (($action == 'delete' && (empty($conf->use_javascript_ajax) || !empty($conf->dol_use_jmobile)))	// Output when action = clone if jmobile or no js
	|| (!empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile)))
{
    print $form->formconfirm("card.php?id=".$kit->id, 'Supprimer le kit', 'Êtes-vous sûr de vouloir supprimer ce kit ?', "confirm_delete", '', 0, "action-delete");
}
// End of page
llxFooter();
$db->close();