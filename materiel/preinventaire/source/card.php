<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
@include "../../../../main.inc.php";
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/source.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/source.class.php';

// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));

// Security check
$usercanread =   ($user->rights->materiel->read);
$usercancreate = ($user->rights->materiel->create);
$usercandelete = ($user->rights->materiel->delete);
if (! $usercanread) accessforbidden();
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
    $action = '';
    $socid = $user->socid;
}

/*Recupération données POST*/
$id = GETPOST('id', 'int');
$lineid = GETPOST('lineid', 'int');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'alpha');
$description = GETPOST('description', 'alpha');
$valeur = GETPOST('valeur', 'float');
$inventoriable = GETPOST('inventoriable', 'int');
$amortissable = GETPOST('amortissable', 'int');
$remaintospecify = GETPOST('remaintospecify', 'int');

$fksource = GETPOST('fksource', 'int');

$nombre = GETPOST('nombre', 'int');

/*
 * AJOUTER DONNÉES
 */
$form = new Form($db);
$source = new Source($db);

/*
 * Actions
 */
if ($cancel) $action = '';

if ($id > 0) {
    $result = $source->fetch($id);
    if (!$result || !$source->inventoriable) {
        setEventMessages('Impossible de récupérer les données de la source. ' . $source->error, null, 'errors');
        header('Location: '.DOL_URL_ROOT.'/custom/materiel/preinventaire/source/list.php');
        exit;
    }
    $result = $source->fetch_lines();
    if (!$result) {
        setEventMessages('Impossible de récupérer les données de la source. ' . $source->error, null, 'errors');
        header('Location: '.DOL_URL_ROOT.'/custom/materiel/preinventaire/source/list.php');
        exit;
    }
}
if ($action == 'addline')
{
    $error = 0;
    // Check for valid data
    if (empty($description) || empty($valeur)) {
        setEventMessages('Donnée(s) invalide(s). Vérifiez les champs', null, 'errors');
    } else {
        $result = $source->addLine($description, $valeur, $inventoriable, $amortissable, $remaintospecify, $fksource, $nombre);
        if (!$result) setEventMessages('Erreur lors de l\'ajout du matériel : ' . $source->error, null, 'errors');
        else {
            setEventMessages('Matériel ajouté avec succès' , null);
            header('Location: '.$_SERVER["PHP_SELF"].'?id='.(isset($source->id) ? $source->id : $fksource));
            exit;
        }
    }
}
elseif ($action == 'confirm_import' && $confirm == 'yes')
{
    // This import only works with recu fiscaux
    // Check the source type
    if ($source->source_type_id != 2)
    {
        setEventMessages('La source de référence ne supporte pas l\'import des matériels', null, 'errors');
        $action = 'view';
    }
    else
    {
        // Loop through the source reference lines and add the line to the source
        $error = 0;
        foreach ($source->source_reference_object->lines as $line)
        {
            $description = $line->description;
            $valeur = intval($line->valeur);
            $fksource = $source->id;
            $nombre = 1;
            $remaintospecify = $source->remaining_to_specify;

            //Set these value to default since we don't know their value
            $inventoriable = $amortissable = 1;
            for ($i = 0; $i < $line->qty; $i++) // Repeat for the quantity
            {
                $result = $source->addLine($description, $valeur, $inventoriable, $amortissable, $remaintospecify, $fksource, $nombre);
                if (!$result) $error++;
            }
        }
        if ($error)
        {
            setEventMessages('Une erreur est survenue lors de l\'import d\'un ou plusieurs matériel(s)', null, 'errors');
            header('Location: '.$_SERVER["PHP_SELF"].'?id='.(isset($source->id) ? $source->id : $fksource));
            exit;
        }
        else
        {
            setEventMessages('Matériel importés avec succès. Merci de vérifier les valeurs des champs "Inventoriable" et "Amortissable".' , null);
            header('Location: '.$_SERVER["PHP_SELF"].'?id='.(isset($source->id) ? $source->id : $fksource));
            exit;
        }
    }
    // Check for valid data
    if (empty($description) || empty($valeur)) {
        setEventMessages('Donnée(s) invalide(s). Vérifiez les champs', null, 'errors');
    } else {
        $result = $source->addLine($description, $valeur, $inventoriable, $amortissable);
        if (!$result) setEventMessages('Erreur lors de l\'ajout du matériel : ' . $source->error, null, 'errors');
        else {
            setEventMessages('Matériel ajouté avec succès' , null);
            header('Location: '.$_SERVER["PHP_SELF"].'?id='.(isset($source->id) ? $source->id : $fksource));
            exit;
        }
    }
}

$formconfirm = '';
if ($action == 'ask_deleteline')
{
    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$source->id.'&lineid='.$lineid, 'Supprimer un matériel', 'Êtes-vous sûr de vouloir supprimer ce matériel ?', 'confirm_deleteline', '', 0, 1);
}	

// Remove a product line
elseif ($action == 'confirm_deleteline' && $confirm == 'yes' && $usercancreate)
{
    $result = $source->deleteline($lineid);
    if ($result > 0)
    {
        setEventMessages('Matériel supprimé' , null);
        header('Location: '.$_SERVER["PHP_SELF"].'?id='.$source->id);
        exit;
    } else {
        setEventMessages('Erreur de suppression de la ligne de matériel : '.$source->error, null, 'errors');
        $action = '';
    }
}

//update a product line
elseif ($action == 'updateline' && $usercancreate && !$cancel)
{
    $result = $source->updateLine($lineid, $description, $valeur, $inventoriable, $amortissable);
    if ($result) {
        setEventMessages('Matériel mis à jour' , null);
    }
    else {
        setEventMessages('Une erreur s\'est produite : '.$source->error , null, 'errors');
    }
    header('Location: '.$_SERVER["PHP_SELF"].'?id='.$source->id);
    exit;
}

// Supprimer la source
if ($action == 'confirm_delete' && $confirm != 'yes') $action = '';
if ($action == 'confirm_delete' && $confirm == 'yes' && $usercandelete) {
    $result = $source->delete();
    if ($result > 0) {
        setEventMessages('La source a bien été supprimé.', null);
        header('Location: '.DOL_URL_ROOT.'/custom/materiel/preinventaire/source/list.php');
        exit;
    } else {
        setEventMessages('Erreur lors de la suppression.', null, 'errors');
        setEventMessages($source->error, null, 'errors');
        $action = '';
    }
}

/*
 * View
 */
llxHeader("", 'Source');
if ($id > 0) {
    $head = source_prepare_head($source);
    $titre = 'Source';
    $picto = 'source';
    talm_fiche_head($head, 'card', $titre, -1, $picto);
    $linkback = '<a href="'.DOL_URL_ROOT.'/custom/materiel/preinventaire/source/list.php/">Retour à la liste</a>';
    talm_banner_tab($source, 'id', $linkback, 1, 'rowid');

    // Print confirm popup for deleting line (if action == ask_deleteline)
    print $formconfirm;
    print '<div class="fichecenter">';
    print '<div class="fichehalfleft">';
    print '<div class="underbanner clearboth"></div>';
    print '<table class="border tableforfield" width="100%">';

    // Type de source
    print '<tr><td class="titlefield">';
    print "Type";
    print '</td><td colspan="3">';
    print '<span class="badgeneutral">';
    print $source->source_reference_type;
    print '</span>';
    print '</td></tr>';
    
    // Source de référence
    print '<tr><td class="titlefield">';
    print "Source de référence";
    print '</td><td colspan="3">';
    print $source->source_reference_object->getNomUrl(1);
    print '</td></tr>';
    
    // Date de création
    print '<tr><td class="titlefield">';
    print "Création";
    print '</td><td colspan="3">';
    print dol_print_date($source->source_reference_object->datec, '%e %B %Y');
    print '</td></tr>';
    print '</table>';
    print '</div>';
    print '<div class="fichehalfright">';
    print '<div class="ficheaddleft">';
    print '<div class="underbanner clearboth"></div>';
    print '<table class="border tableforfield centpercent">';
    
    // Montant de la source
    print '<tr><td class="titlefield">';
    print "Montant de la source";
    print '</td><td colspan="3" class="right">';
    print price($source->source_reference_object->total_ttc, 1, '', 0, -1, -1, $conf->currency);
    print '</td></tr>';
    
    // Valeur cumulée
    print '<tr><td class="titlefield">';
    print "Valeur cumulée";
    print '</td><td colspan="3" class="right">';
    print price($source->total_specified, 1, '', 0, -1, -1, $conf->currency);
    print '</td></tr>';
    
    // Reste à compléter
    print '<tr><td class="titlefield">';
    print "Reste à compléter";
    print '</td><td colspan="3" class="right '. ($source->remaining_to_specify ? 'amountremaintopay' : 'amountpaymentcomplete') .'">';
    print price($source->remaining_to_specify, 1, '', 0, -1, -1, $conf->currency);
    print '</td></tr>';
    print '</table>';
    print '</div>';
    print '</div>';
    print '</div></div>';
    print '<div style="clear:both"></div>';
    print '<br>';
   


    print '<table id="tablelines" class="noborder noshadow" width="100%">';
    print '<tr class="liste_titre">';
  
    if($source->source_reference_object->table_element == "recu_fiscal")
    {
        print '<td>Contenu du don</td>';
        $detail = "SELECT * FROM ".MAIN_DB_PREFIX."recu_fiscal_det WHERE fk_recu_fiscal = ".$source->source_reference_object->id;
    }
    else
    {
        print '<td>Contenu de la facture</td>';
        $detail = "SELECT * FROM ".MAIN_DB_PREFIX."facture_fourn_det WHERE fk_facture_fourn = ".$source->source_reference_object->id;
    }
    print '</tr>';
    print '<tr>';

    $resqlDetail = $db->query($detail);
    $objDetail = $db->fetch_object($resqlDetail);

    print '<td>';
    foreach($resqlDetail as $value)
    {
        print '- '.$value['description'].' (x'.$value['qty'].') '.(round($objDetail->total_ht) != 0 ? round($objDetail->total_ht) : $value['valeur']).'€<br>';
    }
    print '</td>';

    print '<tr>';
    print '</table>';

    /*
     * Lines
     * // NEED TO MODIFY
     */
    print '<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$source->id.(($action != 'editline') ? '#addline' : '#line_'.GETPOST('lineid')).'" method="POST">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="'.(($action != 'editline') ? 'addline' : 'updateline').'">';
    print '<input type="hidden" name="mode" value="">';
    print '<input type="hidden" name="id" value="'.$source->id.'">';
    print '<div class="div-table-responsive-no-min">';
    print '<table id="tablelines" class="noborder noshadow" width="100%">';
    print '<tr class="liste_titre">';
    print '<td>Description</td>';
    print '<td>Valeur</td>';
    print '<td style="width: 80px">Inventoriable</td>';
    print '<td style="width: 80px">Amortissable</td>';
    print '<td class="center" style="width: 80px">État</td>';
    print '<td class="center" style="width: 80px">Actions</td>'; // Empty column for edit and remove button
    print '</tr>';


    // Show object lines
    if (!empty($source->lines)){
        $ret = $source->printObjectLines($action, $lineid);
    }
    $num = count($source->lines);
    // Form to add new line
    // If the source price isn't completed, show the form to add a new line
    if ($source->fk_status == Source::STATUS_INCOMPLETE && $usercancreate)
    {
        if ($action != 'editline')
        {
            // Add product
            $source->formAddObjectLine();
        }
    }
    print '</table>';
    print '</form>';

    print dol_get_fiche_end();

    /* ************************************************************************** */
    /*                                                                            */
    /* Barre d'action                                                             */
    /*                                                                            */
    /* ************************************************************************** */
    print "\n".'<div class="tabsAction">'."\n";
        if ($usercandelete) {
            $text = 'Êtes vous sûr de vouloir supprimer cette source ? <br> Cela entrainera la suppression de toutes les données liées à cette source. <br> Une fois supprimée, elle devra être re-traitée.';
            print $form->formconfirm("card.php?id=".$source->id, 'Supprimer la source', $text, "confirm_delete", '', 0, "action-delete");
            $text = 'Êtes-vous sûr de vouloir importer les matériels de la source de référence <b>'.$source->source_reference_object->ref   .'</b> ?';
            print $form->formconfirm("card.php?id=".$source->id, 'Importer les matériels', $text, "confirm_import", '', 0, "action-import");
            if ($source->fk_status == Source::STATUS_INCOMPLETE) print '<span id="action-import" class="butAction">Importer les matériels</span>';
            print '<span id="action-delete" class="butActionDelete">Supprimer</span>';
        }
    print "\n</div>\n";

   
}



// End of page
llxFooter();
$db->close();