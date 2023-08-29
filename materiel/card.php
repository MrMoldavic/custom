<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


@include "../../main.inc.php";

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/entretien.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/exploitation.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/materiel.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/entretien.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/exploitation.lib.php';

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/genericobject.class.php';

// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));
$action = GETPOST('action', 'alpha');

// Security check
$usercanread = ($user->rights->materiel->read);
$usercancreate = ($user->rights->materiel->create);
$usercandelete = ($user->rights->materiel->delete);
if (!$usercanread) accessforbidden();

$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
    $action = '';
    $socid = $user->socid;
}


/*Recupération données POST*/
$id = GETPOST('id', 'int');
$preinventaire_line_id = GETPOST('preinventaire_line_id', 'alpha'); //id de la ligne dans le preinventaire
$type_materiel = GETPOST('idtypemateriel', 'alpha'); //id du type de matériel
$etat = (GETPOST('etat', 'alpha') ? GETPOST('etat', 'alpha') : '9'); //etat du matériel (fk) 9-> Valeur "A definir" dans le dictionnaire
$fk_etat_etiquette = GETPOST('fk_etat_etiquette', 'alpha'); //etat de l'etiquette (fk)
$exploitabilite = GETPOST('exploitabilite', 'alpha'); //etat du matériel (fk)
$precision_type = GETPOST('precision_type', 'alpha'); //id de la marque
$fk_marque = GETPOST('idmarque', 'alpha'); //précision du type de materiel
$modele = GETPOST('modele', 'alpha'); //modele
$notes = GETPOST('notes', 'alpha'); //notes supplémentaires
$entrepot = GETPOST('fk_default_warehouse', 'alpha'); //id de l'entrepot
$proprietaire = GETPOST('idproprietaire', 'int'); //id de l'adhérent propriétaire
$origine_materiel = GETPOST('origine_materiel', 'alpha'); //id de l'origine


$action = (GETPOST('action', 'alpha') ? GETPOST('action', 'alpha') : 'view');
$cancel = GETPOST('cancel', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$socid = GETPOST('socid', 'int');
$duration_value = GETPOST('duration_value', 'int');
$duration_unit = GETPOST('duration_unit', 'alpha');
 

$form = new Form($db);
$formfile = new FormFile($db);
$formproduct = new FormProduct($db);
$formmateriel = new FormMateriel($db);
$materiel = new Materiel($db);

if ($id > 0) {
    $result = $materiel->fetch($id);

    if (!$result) exit;
}

/*
 * Actions
 */
if ($cancel) $action = '';
    

// List of quick modification action names and type
$quick_modification_actions = array('setfk_etat'=>'int',
                                    'setfk_etat_etiquette'=>'int',
                                    'setfk_exploitabilite'=>'int',
                                    'setfk_entrepot'=>'int',
                                    'setfk_origine'=>'int',
                                    'setfk_marque'=>'int',
                                    'setmodele'=>'text',
                                    'setprecision_type'=>'text');

if (array_key_exists($action, $quick_modification_actions))
{
    $field_name = str_replace('set', '', $action); // Remove the prefix 'set' from $action to get db field name
    $type = $quick_modification_actions[$action];
    $value = GETPOST($field_name);
    $result = $materiel->setValueFrom($field_name, $value, 'materiel', null, $type);
    if ($result > 0) setEventMessages('Valeur modifiée avec succès.', null);
    else setEventMessages('Erreur lors de la modification de la valeur', null, 'errors');
    header("Location: ".$_SERVER['PHP_SELF']."?id=".$materiel->id);
    exit;
}

if ($action == 'confirm_entretien') {
	if (!$id) exit;
	$urlparameters = '?materiel_id='.$id;
	$urlparameters .= '&redirect_url='.urlencode(DOL_URL_ROOT.'/custom/materiel/card.php?id='.$id);
	header('Location: '.DOL_URL_ROOT.'/custom/entretien/card.php'.$urlparameters);
}

// Add a materiel
if ($action == 'add' && $usercancreate) {
    $error = 0;
    if (!GETPOST('idtypemateriel', 'alphanohtml')) {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Type de matériel'), null, 'errors');
        $action = "create";
        $error++;
    }
    if (empty($etat)) {
        setEventMessages($langs->trans('ErrorFieldRequired', 'État du matériel'), null, 'errors');
        $action = "create";
        $error++;
    }
    if (empty($fk_marque) || $fk_marque == -1) {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Marque'), null, 'errors');
        $action = "create";
        $error++;
    }
    if (empty($fk_etat_etiquette) || $fk_etat_etiquette == -1) {
        setEventMessages($langs->trans('ErrorFieldRequired', 'État de l\'étiquette'), null, 'errors');
        $action = "create";
        $error++;
    }
    if (empty($preinventaire_line_id) || $preinventaire_line_id == -1) {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Source préinventaire'), null, 'errors');
        $action = "create";
        $error++;
    }
    if (!$error) {
        $materiel->fk_preinventaire         = GETPOST('preinventaire_line_id', 'alphanohtml');
        $materiel->fk_type_materiel         = GETPOST('idtypemateriel', 'alphanohtml');
        $materiel->fk_etat                  = GETPOST('etat', 'alphanohtml');
        $materiel->fk_etat_etiquette        = GETPOST('fk_etat_etiquette', 'alpha');
        $materiel->fk_exploitabilite        = GETPOST('exploitabilite', 'alphanohtml');
        $materiel->precision_type           = GETPOST('precision_type', 'alpha');
        $materiel->fk_marque                = GETPOST('idmarque', 'alpha');
        $materiel->modele                   = GETPOST('modele', 'alpha');
        $materiel->notes                    = GETPOST('notes', 'alpha');
        $materiel->fk_origine               = GETPOST('origine_materiel', 'alpha');
        $materiel->fk_entrepot              = GETPOST('fk_entrepot', 'alpha');
        $materiel->fk_proprietaire          = GETPOST('idproprietaire', 'alpha');
        $result = $materiel->create($user);
        if (!$result) setEventMessages('Erreur lors de la création du matériel : ' . $materiel->error, null, 'errors');
        else setEventMessages('Matériel créé avec succès', null);
    }
    if (!$error)
    {
        $lastMateriel = "SELECT MAX(rowid) as max FROM ".MAIN_DB_PREFIX."materiel WHERE fk_user_author=".$user->id;
        $resqlLastMateriel = $db->query($lastMateriel);
        $objectLastMateriel = $db->fetch_object($resqlLastMateriel);

        header('Location: '.DOL_URL_ROOT.'/custom/materiel/card.php?id='.$objectLastMateriel->max);
    }
}

// Mise à jour d'un matériel
if ($action == 'update' && $usercancreate) {
    if (GETPOST('cancel', 'alpha')) {
        $action = '';
    } else {
        if ($materiel->id > 0) {
            $error = 0;

            if (!GETPOST('fk_type_materiel', 'alphanohtml') || GETPOST('fk_type_materiel', 'alphanohtml') == -1) {
                setEventMessages($langs->trans('ErrorFieldRequired', 'Type de matériel'), null, 'errors');
                $action = "edit";
                $error++;
            }
            if (!GETPOST('fk_etat', 'alphanohtml')) {
                setEventMessages($langs->trans('ErrorFieldRequired', 'État du matériel'), null, 'errors');
                $action = "edit";
                $error++;
            }
            if (!GETPOST('fk_etat_etiquette', 'alphanohtml')) {
                setEventMessages($langs->trans('ErrorFieldRequired', 'État de l\'étiquette'), null, 'errors');
                $action = "edit";
                $error++;
            }
            if (!GETPOST('fk_exploitabilite', 'alphanohtml')) {
                setEventMessages($langs->trans('ErrorFieldRequired', 'Exploitabilité'), null, 'errors');
                $action = "edit";
                $error++;
            }
            if (!$error) {
                $materiel->oldcopy = clone $materiel;

                $materiel->fk_type_materiel         = GETPOST('fk_type_materiel', 'alphanohtml');
                $materiel->fk_etat                  = GETPOST('fk_etat', 'alphanohtml');
                $materiel->fk_etat_etiquette        = GETPOST('fk_etat_etiquette', 'alphanohtml');
                $materiel->fk_exploitabilite        = GETPOST('fk_exploitabilite', 'alphanohtml');
                $materiel->fk_marque                = GETPOST('fk_marque', 'alphanohtml');
                $materiel->modele                   = GETPOST('modele', 'alphanohtml');
                $materiel->precision_type           = GETPOST('precision_type', 'alpha');
                $materiel->notes                    = GETPOST('notes', 'alpha');
                $materiel->fk_origine               = GETPOST('origine_materiel', 'alpha');
                $materiel->fk_entrepot              = GETPOST('fk_entrepot', 'alpha');
                $materiel->fk_proprietaire          = GETPOST('idproprietaire', 'alpha');
                if ($materiel->update() > 0) {
                    setEventMessages('Données mises à jour.', null);
                    $materiel->fetch($id);
                    $action = 'view';
                } else {
                    setEventMessages('Erreur lors de la mise à jour des données.', null, 'errors');
                    $action = 'edit';
                }
            }
        }
    }
}



// Supprimer un matériel
if ($action == 'confirm_delete' && $confirm != 'yes') $action = '';
if ($action == 'confirm_delete' && $confirm == 'yes' && $usercandelete) {
    // On vérifie d'abords si le materiel est dans un kit
    if ($materiel->fk_kit) {
        $kit_tmp = new Kit($db);
        $kit_tmp->fetch($materiel->fk_kit);
        if ($kit_tmp->fk_exploitation) { // Si le kit est en exploitation on annule la suppression
            setEventMessages('Ce materiel est inclus dans une exploitation (réf. '. $kit_tmp->exploitation_ref .'). Il ne peut pas être supprimé avant la fin de l\'exploitation.', null, 'errors');
        }
    } else {
        $result = $materiel->delete();

        if ($result > 0) {
            setEventMessages('Le matériel a bien été supprimé.', null);
            header('Location: '.DOL_URL_ROOT.'/custom/materiel/list.php');
            exit;
        } else {
            setEventMessages('Erreur lors de la suppression.', null, 'errors');
            $reload = 0;
            $action = '';
        }
    }
}


// Désactiver un matériel
if ($action == 'confirm_deactivate' && $confirm != 'yes') $action = '';
elseif ($action == 'confirm_deactivate' && $confirm == 'yes' && $usercandelete) {
    // On vérifie d'abords si le materiel est dans un kit
    if ($materiel->fk_kit) {
        $kit_tmp = new Kit($db);
        $kit_tmp->fetch($materiel->fk_kit);
        if ($kit_tmp->fk_exploitation) { // Si le kit est en exploitation on annule la désactivation
            setEventMessages('Ce materiel est inclus dans une exploitation (réf. '. $kit_tmp->exploitation_ref .'). Il ne peut pas être désactivé avant la fin de l\'exploitation.', null, 'errors');
        } else {
            $result = $materiel->deactivate($user);

            if ($result > 0) {
                setEventMessages('Le matériel a bien été désactivé.', null);
                header('Location: '.DOL_URL_ROOT.'/custom/materiel/list.php');
                exit;
            } else {
                setEventMessages('Erreur lors de la désactivation.', null, 'errors');
                $reload = 0;
                $action = '';
            }
        }
    } 
    else 
    {
        $result = $materiel->deactivate($user);
        if ($result > 0) {
            setEventMessages('Le matériel a bien été désactivé.', null);
            header('Location: '.DOL_URL_ROOT.'/custom/materiel/list.php');
            exit;
        } else {
            setEventMessages('Erreur lors de la désactivation.', null, 'errors');
            $reload = 0;
            $action = '';
        }
    }
}


// Réactiver un matériel
if ($action == 'confirm_reactivate' && $confirm != 'yes') {
    $action = '';
}
if ($action == 'confirm_reactivate' && $confirm == 'yes' && $usercandelete) {
    $result = $materiel->reactivate();
    if ($result > 0) {
        setEventMessages('Le matériel a bien été réactivé.', null);
        header('Location: '.DOL_URL_ROOT.'/custom/materiel/list.php');
        exit;
    } else {
        setEventMessages('Erreur lors de la réactivation.', null, 'errors');
        $reload = 0;
        $action = '';
    }
}



/*
 * View
 */
if ($action == 'create') {
    if (!$usercancreate) accessforbidden();

    llxHeader("", $langs->trans("Materiel"));
    
    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

    print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="add">';
    print '<input type="hidden" name="type" value="'.$type.'">'."\n";
    print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
    $picto = 'materiel';
    $title = 'Nouveau Matériel';
    print talm_load_fiche_titre($title, '', $picto);

    dol_fiche_head('');

    print '<table class="border centpercent">';

    print '<tr>';
    print '</td></tr>';

    // Source du préinventaire
    print '<tr><td class="titlefieldcreate fieldrequired" style="max-width:300px">Source préinventaire</td><td>';
    print $form->selectarray('preinventaire_line_id', $formmateriel->getPreinventaireLinesForCreation(), $preinventaire_line_id, 1, 0, 0, 'style="max-width:500px;"', 0, 0, 0, '', '', 1);
    print '</td>';
    print '</tr>';

    // Type materiel
    print '<tr><td class="titlefieldcreate fieldrequired">Type de matériel</td><td>';
    print $formmateriel->selectTypesMateriel(GETPOST('idtypemateriel'), 'idtypemateriel', '', 1);
    print ' <a href="'.DOL_URL_ROOT.'/custom/materiel/typemat/card.php'.'">';
    print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter un type de matériel"></span>';
    print '</a>';
    print '</td>';
    print '</tr>';

    // Précision du type
    $fieldname = 'Type d\'instrument <span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title="Ex : fretless, stéréo..."><img src="/theme/eldy/img/info.png" alt="" style="vertical-align: middle; cursor: help"></span>';
    print '<tr><td classe="titlefieldcreate">'. $fieldname .'</td><td colspan="3"><input name="precision_type" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.dol_escape_htmltag(GETPOST('precision_type', 'alphanohtml')).'"></td></tr>';


    // Marque materiel
    print '<tr><td class="titlefieldcreate fieldrequired">Marque</td><td>';
    print $formmateriel->selectMarques(GETPOST('idmarque'), 'idmarque', '', 1);
    print ' <a href="'.DOL_URL_ROOT.'/admin/dict.php?id=62'.'">';
    print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter une marque"></span>';
    print '</a>';
    print '</td>';
    print '</tr>';


    // Modèle
    print '<tr><td class="titlefieldcreate">Modèle</td><td colspan="3"><input name="modele" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.dol_escape_htmltag($modele).'"></td></tr>';

   
    // État
    print '<tr><td class="titlefieldcreate fieldrequired">État du matériel</td><td colspan="3">';
    $etatarray = $materiel->getEtatDict();
    print $form->selectarray('etat', $etatarray, $etat, 0, 0, 0, 'style="min-width:200px;"');
    print '</td></tr>';

    // État étiquette
    print '<tr><td class="titlefieldcreate fieldrequired">État de l\'étiquette</td><td colspan="3">';
    $fk_etat_etiquette_array = getEtatEtiquetteDict();
    print $form->selectarray('fk_etat_etiquette', $fk_etat_etiquette_array, GETPOST('fk_etat_etiquette'), 0, 0, 0, 'style="min-width:200px;"');
    print '</td></tr>';


    // Exploitabilité
    print '<tr><td class="titlefieldcreate fieldrequired">Exploitabilité</td><td colspan="3">';
    $exploitabilitearray = $materiel->getExploitabiliteDict();
    print $form->selectarray('exploitabilite', $exploitabilitearray, GETPOST('exploitabilite'), 0, 0, 0, 'style="min-width:200px;"');
    print '</td></tr>';


    // Notes supplémentaires
    print '<tr><td class="titlefieldcreate tdtop">Descriptif de l\'objet</td><td colspan="3">';

    $doleditor = new DolEditor('notes', GETPOST('notes', 'none'), '', 160, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '50%');
    $doleditor->Create();

    print "</td></tr>";


    // Origine
    print '<tr><td class="titlefieldcreate fieldrequired">Origine du matériel</td><td colspan="3">';
    $originearray = $materiel->getOrigineDict();
    print $form->selectarray('origine_materiel', $originearray, GETPOST('origine_materiel'), 0, 0, 0, 'style="min-width:200px;"');
    print '</td></tr>';


    // Entrepôt

    $sqlSalle = "SELECT s.rowid,s.salle,s.fk_college FROM " . MAIN_DB_PREFIX . "salles as s";
    $resqlSalle = $db->query($sqlSalle);
    $salles = [];

    foreach ($resqlSalle as $val) {
        $sqlEtablissement = "SELECT e.rowid,e.diminutif FROM " . MAIN_DB_PREFIX . "etablissement as e WHERE rowid= ".$val['fk_college'];
        $resqlEtablissement = $db->query($sqlEtablissement);
        $resEtablissement = $db->fetch_object($resqlEtablissement);

        $salles[$val['rowid']] = $val['salle'] .' / '.$resEtablissement->diminutif;
    }

    print '<tr><td classe="titlefieldcreate">Entrepôt</td><td>';
    print $form->selectarray('fk_entrepot', $salles, $entrepot);
    print ' <a href="'.DOL_URL_ROOT.'/custom/scolarite/salle_card.php?action=create">';
    print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="'.$langs->trans("AddWarehouse").'"></span>';
    print '</a>';
    print '</td>';
    print '</tr>';


    // Propriétaire
    print '<tr><td class="titlefieldcreate">Propriétaire</td><td>';
    print $formmateriel->selectProprietaires($proprietaire, "idproprietaire",'', 1);
    print ' <a href="'.DOL_URL_ROOT.'/admin/dict.php?id=63">';
    print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter un propriétaire"></span>';
    print '</a>';
    print '</td>';
    print '</tr>';


    print '</table>';

    dol_fiche_end();

    print '<div class="center">';
    print '<input type="submit" class="button" value="'.$langs->trans("Create").'">';
    print ' &nbsp; &nbsp; ';
    print '<input type="button" class="button" value="'.$langs->trans("Cancel").'" onClick="javascript:history.go(-1)">';
    print '</div>';

    print '</form>';
}

    /*
     * Materiel card
     */

    elseif ($materiel->id > 0) {
        // Fiche en mode edition
        if ($action == 'edit' && $usercancreate) {

            llxHeader("", $langs->trans("Materiel"));
            //WYSIWYG Editor
            require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

            $type = $langs->trans('Product');
            //print load_fiche_titre($langs->trans('Modify').' '.$type.' : '.(is_object($object->oldcopy)?$object->oldcopy->ref:$object->ref), "");

            // Main official, simple, and not duplicated code
            print '<form action="'.$_SERVER['PHP_SELF'].'?id='.$materiel->id.'" method="POST">'."\n";
            print '<input type="hidden" name="token" value="'.newToken().'">';
            print '<input type="hidden" name="action" value="update">';
            print '<input type="hidden" name="id" value="'.$materiel->id.'">';
            print '<input type="hidden" name="canvas" value="'.$materiel->canvas.'">';

            $head = materiel_prepare_head($materiel);
            $title = 'Modification - ' . $materiel->ref;
            $picto = ('materiel');
            print talm_load_fiche_titre($title, '', $picto);

            dol_fiche_head('');
            
            print '<table class="border allwidth">';



            print '<table class="border centpercent">';

            print '<tr>';
            print '</td></tr>';

            // Type materiel
            print '<tr><td class="titlefieldcreate fieldrequired">Type de matériel</td><td>';
            print $formmateriel->selectTypesMateriel($materiel->fk_type_materiel, 'fk_type_materiel', '', 1);
            print ' <a href="'.DOL_URL_ROOT.'/admin/dict.php?id=44'.'">';
            print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter un type de matériel"></span>';
            print '</a>';
            print '</td>';
            print '</tr>';

            // Précision du type
            $fieldname = 'Type d\'instrument <span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title="Ex : fretless, stéréo..."><img src="/theme/eldy/img/info.png" alt="" style="vertical-align: middle; cursor: help"></span>';
            print '<tr><td class="titlefieldcreate">'. $fieldname .'</td><td colspan="3"><input name="precision_type" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.dol_escape_htmltag($materiel->precision_type).'"></td></tr>';

            // État
            print '<tr><td class="titlefieldcreate fieldrequired">État du matériel</td><td colspan="3">';
            $etatarray = $materiel->getEtatDict();
            print $form->selectarray('fk_etat', $etatarray, $materiel->fk_etat);
            print '</td></tr>';

            // État étiquette
            print '<tr><td class="titlefieldcreate fieldrequired">État de l\'étiquette</td><td colspan="3">';
            $fk_etat_etiquette_array = getEtatEtiquetteDict();
            print $form->selectarray('fk_etat_etiquette', $fk_etat_etiquette_array, $materiel->fk_etat_etiquette);
            print '</td></tr>';

            // Exploitabilité
            print '<tr><td class="titlefieldcreate fieldrequired">Exploitabilité</td><td colspan="3">';
            $exploitabilitearray = $materiel->getExploitabiliteDict();
            print $form->selectarray('fk_exploitabilite', $exploitabilitearray, $materiel->fk_exploitabilite);
            print '</td></tr>';

            // Marque
            print '<tr><td class="titlefieldcreate">Marque</td><td colspan="3">';
            print $formmateriel->selectMarques($materiel->fk_marque, 'fk_marque', '', 1);
            print ' <a href="'.DOL_URL_ROOT.'/admin/dict.php?id=62'.'">';
            print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter une marque"></span>';
            print '</a>';
            print '</td>';
            print '</tr>';

            // Modèle
            print '<tr><td class="titlefieldcreate">Modèle</td><td colspan="3"><input name="modele" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.dol_escape_htmltag($materiel->modele).'"></td></tr>';

            // Notes supplémentaires
            print '<tr><td class="titlefieldcreate tdtop">Descriptif de l\'objet</td><td colspan="3">';
            $doleditor = new DolEditor('notes', $materiel->notes, '', 160, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '90%');
            $doleditor->Create();
            print "</td></tr>";

            // Origine
            print '<tr><td class="titlefieldcreate fieldrequired">Origine du matériel</td><td colspan="3">';
            $originearray = $materiel->getOrigineDict();
            print $form->selectarray('origine_materiel', $originearray, $materiel->fk_origine);
            print '</td></tr>';

            // Entrepôt

            $sqlSalle = "SELECT s.rowid,s.salle,s.fk_college FROM " . MAIN_DB_PREFIX . "salles as s";
            $resqlSalle = $db->query($sqlSalle);
            $salles = [];

            foreach ($resqlSalle as $val) {
                $sqlEtablissement = "SELECT e.rowid,e.diminutif FROM " . MAIN_DB_PREFIX . "etablissement as e WHERE rowid= ".$val['fk_college'];
                $resqlEtablissement = $db->query($sqlEtablissement);
                $resEtablissement = $db->fetch_object($resqlEtablissement);

                $salles[$val['rowid']] = $val['salle'] .' / '.$resEtablissement->diminutif;
            }

            print '<tr><td classe="titlefieldcreate">Entrepôt</td><td>';
            print $form->selectarray('fk_entrepot', $salles, 'fk_entrepot');
            print ' <a href="'.DOL_URL_ROOT.'/custom/scolarite/salle_card.php?action=create">';
            print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="'.$langs->trans("AddWarehouse").'"></span>';
            print '</a>';
            print '</td>';
            print '</tr>';

            // Propriétaire
            print '<tr><td class=titlefieldcreate">Propriétaire</td><td>';
            print $formmateriel->selectProprietaires($materiel->fk_proprietaire, "idproprietaire", '', 1);
            print ' <a href="'.DOL_URL_ROOT.'/admin/dict.php?id=63">';
            print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter un propriétaire"></span>';
            print '</a>';
            print '</td>';
            print '</tr>';

            print '</table>';

            dol_fiche_end();

            print '<div class="center">';
            print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
            print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
            print '</div>';
            print '</form>';
        }
        else {
            llxHeader("", "Materiel - ".$materiel->ref);
            $head = materiel_prepare_head($materiel);
            $titre = 'Matériel - Historique';
            $picto = ('materiel');
            talm_fiche_head($head, 'card', $titre, -1, $picto);

            $linkback = '<a href="'.DOL_URL_ROOT.'/custom/materiel/list.php/">Retour à la liste</a>';

            talm_banner_tab($materiel, 'id', $linkback, 1, 'rowid');

            print '<div class="fichecenter">';
            print '<div class="fichehalfleft">';
            print '<div class="underbanner clearboth"></div>';
            print '<table class="border tableforfield" width="100%">';

            // Source
            print '<tr><td class="titlefield">';
            print 'Source';
            print '</td><td colspan="3">';
            if($materiel->source_object)
            {
                print $materiel->source_object->getNomUrl();
            }
            else
            {
                print 'Source indéfinie';
            }
         
            print '</td></tr>';

            // Marque
            $marque_dict = $materiel->getMarqueDict();
            $typeformat = 'select;0:Aucune,';
            $i = 0;
            foreach ($marque_dict as $key=>$marque) {
                $typeformat .= $key.':'.$marque;
                if ($key != array_key_last($marque_dict)) $typeformat .= ',';
            }
            print '<tr><td class="titlefield">';
            print $form->editfieldkey("Marque", 'fk_marque', $materiel->fk_marque, $materiel, $usercancreate, $typeformat);
            print '</td><td colspan="3">';
            print '<span class="badge  badge-status4 badge-status" style="color:white;">';
            print $form->editfieldval("Marque", 'fk_marque', $materiel->fk_marque, $materiel, $usercancreate, $typeformat);
            print '</span>';
            print '</td></tr>';

            // Modèle
            print '<tr><td class="titlefield">';
            print $form->editfieldkey("Modèle", 'modele', $materiel->modele, $materiel, $usercancreate);
            print '</td><td colspan="3">';
            print '<span class="badge  badge-status4 badge-status" style="color:white;">';
            print $form->editfieldval("Modèle", 'modele', $materiel->modele, $materiel, $usercancreate);
            print '</span>';
            print '</td></tr>';

            // Précision du type
            print '<tr><td class="titlefield">';
            print $form->editfieldkey("Précision du type", 'precision_type', $materiel->precision_type, $materiel, $usercancreate, 'string', '', 0, 0, 'id', 'Ex : fretless, stéréo...');
            print '</td><td colspan="3">';
            print $form->editfieldval("Précision du type", 'precision_type', $materiel->precision_type, $materiel, $usercancreate);
            print '</td></tr>';

            // Type
            print '<tr><td class="titlefield">';
            print 'Type';
            print '</td><td colspan="3">';
            print $materiel->type_materiel_ind . ' - ' . $materiel->type_materiel;
            print '</td></tr>';

            // Etat
            print '<tr><td class="titlefield">';
            $etat_materiel_dict = $materiel->getEtatDict();
            $typeformat = 'select;';
            $i = 0;
            foreach ($etat_materiel_dict as $key=>$etat_materiel) {
                $typeformat .= $key.':'.$etat_materiel;
                if ($key != array_key_last($etat_materiel_dict)) $typeformat .= ',';
            }

            print $form->editfieldkey("État", 'fk_etat', $materiel->fk_etat, $materiel, $usercancreate, $typeformat);
            print '</td><td colspan="3">';
            print '<span class="badge  badge-status2 badge-status style="color:white;">';
            print $form->editfieldval("État", 'fk_etat', $materiel->fk_etat, $materiel, $usercancreate, $typeformat);
            print '</span>';
            print '</td></tr>';

            // Etat étiquette
            print '<tr><td class="titlefield">';
            $etat_etiquette_dict = getEtatEtiquetteDict();
            $typeformat = 'select;';
            $i = 0;
            foreach ($etat_etiquette_dict as $key=>$etat_etiquette) {
                $typeformat .= $key.':'.$etat_etiquette;
                if ($key != array_key_last($etat_etiquette_dict)) $typeformat .= ',';
            }

            print $form->editfieldkey("État de l'étiquette", 'fk_etat_etiquette', $materiel->fk_etat_etiquette, $materiel, $usercancreate, $typeformat);
            print '</td><td colspan="3">';
            print $form->editfieldval("État de l'étiquette", 'fk_etat_etiquette', $materiel->fk_etat_etiquette, $materiel, $usercancreate, $typeformat);
            print '</td></tr>';

            // Exploitabilité
            print '<tr><td class="titlefield">';
            $exploitabilite_materiel_dict = $materiel->getExploitabiliteDict();
            $typeformat = 'select;';
            $i = 0;
            foreach ($exploitabilite_materiel_dict as $key=>$exploitabilite_materiel) {
                $typeformat .= $key.':'.$exploitabilite_materiel;
                if ($key != array_key_last($exploitabilite_materiel_dict)) $typeformat .= ',';
            }

            print $form->editfieldkey("Exploitabilité", 'fk_exploitabilite', $materiel->fk_exploitabilite, $materiel, $usercancreate, $typeformat);
            print '</td><td colspan="3">';
            print $form->editfieldval("Exploitabilité", 'fk_exploitabilite', $materiel->fk_exploitabilite, $materiel, $usercancreate, $typeformat);
            print '</td></tr>';

            


            // Notes supplémentaires
            print '<tr><td class="titlefield">';
            print "Descriptif de l'objet";
            print '</td><td colspan="3">';
            print($materiel->notes ? $materiel->notes : '<i>Pas de notes</i>');
            print '</td></tr>';

            print '</table>';
            print '</div>';
            print '<div class="fichehalfright"><div class="ficheaddleft">';
            print '<div class="underbanner clearboth"></div>';
            print '<table class="border tableforfield" width="100%">';

            // Kit
            print '<tr><td class="titlefield">';
            print "Kit";
            print '</td><td colspan="3">';
            if ($materiel->fk_kit) {
                $kit = new Kit($db);
                $kit->fetch($materiel->fk_kit);
                print $kit->getNomUrl();
            } else print '<i>Pas de kit</i>';
            print '</td></tr>';

            // Exploitation
            print '<tr><td class="titlefield">';
            print "Exploitation";
            print '</td><td colspan="3">';
            $exploitation_id = isInExploitation($materiel->id);
            if ($exploitation_id) {
                $exploitation = new Exploitation($db);
                $exploitation->fetch($exploitation_id);
                print $exploitation->getNomUrl();
            } else print '<i>Pas d\'exploitation</i>';
            print '</td></tr>';

            // Entretien
            print '<tr><td class="titlefield">';
            print "Entretien";
            print '</td><td colspan="3">';
            $entretien_id = isMaterielInEntretien($materiel->id);
            if ($entretien_id) {
                $entretien = new Entretien($db);
                $entretien->fetch($entretien_id);
                print $entretien->getNomUrl();
            } else print '<i>Pas d\'entretien</i>';
            print '</td></tr>';

            // Entrepot
            print '<tr><td class="titlefield">';

            $sqlSalle = "SELECT s.rowid,s.salle,s.fk_college FROM " . MAIN_DB_PREFIX . "salles as s";
            $resqlSalle = $db->query($sqlSalle);
            $salles = [];

            foreach ($resqlSalle as $val) {
                $sqlEtablissement = "SELECT e.rowid,e.diminutif FROM " . MAIN_DB_PREFIX . "etablissement as e WHERE rowid= ".$val['fk_college'];
                $resqlEtablissement = $db->query($sqlEtablissement);
                $resEtablissement = $db->fetch_object($resqlEtablissement);

                $salles[$val['rowid']] = $val['salle'] .' / '.$resEtablissement->diminutif;
            }

            $entrepot_list = $salles;
            $typeformat = 'select;0:Aucun,';
            $i = 0;

            foreach ($entrepot_list as $key=>$entrepot) {
                $typeformat .= $key.':'.$entrepot;
                if ($key != array_key_last($entrepot_list)) $typeformat .= ',';
            }

            print $form->editfieldkey("Entrepôt", 'fk_entrepot', $materiel->fk_entrepot, $materiel, $usercancreate, $typeformat);
            print '</td><td colspan="3">';
            print $form->editfieldval("Entrepôt", 'fk_entrepot', $materiel->fk_entrepot, $materiel, $usercancreate, $typeformat);
            print '</td></tr>';

            // Origine
            print '<tr><td class="titlefield">';
            $origine_dict = $materiel->getOrigineDict();
            $typeformat = 'select;';
            $i = 0;

            foreach ($origine_dict as $key=>$origine) {
                $typeformat .= $key.':'.$origine;
                if ($key != array_key_last($origine_dict)) $typeformat .= ',';
            }

            print $form->editfieldkey("Origine", 'fk_origine', $materiel->fk_origine, $materiel, $usercancreate, $typeformat);
            print '</td><td colspan="3">';
            print $form->editfieldval("Origine", 'fk_origine', $materiel->fk_origine, $materiel, $usercancreate, $typeformat);
            print '</td></tr>';

            // Propriétaire
            print '<tr><td class="titlefield">';

            print "Propriétaire";
            print '</td><td colspan="3">';
            $proprietaire = $materiel->proprietaire ? $materiel->proprietaire : '<i>Pas de propriétaire</i>';
            print $proprietaire;
            print '</td></tr>';

            // Ancienne cote
            print '<tr><td class="titlefield">';

            print "Ancienne Cote";
            print '</td><td colspan="3">';
            $ancienne_cote = $materiel->ancienne_cote ? $materiel->ancienne_cote : '<i>Pas d\'ancienne cote</i>';
            print '<span class="badge  badge-status8 badge-status">'.$ancienne_cote.'</span>';
            print '</td></tr>';
 

            



            print '</table>';
            print '</div>';

            print '</div></div>';
            print '<div style="clear:both"></div>';

            dol_fiche_end();
        }
    } elseif ($action != 'create') exit;


// Confirm delete product
if ((!empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile))) {
    if ($materiel->fk_kit && $materiel->active) {
        print $form->formconfirm("card.php?id=".$materiel->id, 'Désactiver le matériel', 'La désactivation de ce matériel va affecter le kit <b>' .$materiel->kit_ind .'-'.$materiel->kit_cote.'</b>.<br><br> Êtes vous sûr de vouloir le désactiver ?', "confirm_deactivate", '', 0, "action-deactivate");
    } elseif ($materiel->active) {
        print $form->formconfirm("card.php?id=".$materiel->id, 'Désactiver le matériel', 'Êtes-vous sûr de vouloir désactiver ce matériel ?', "confirm_deactivate", '', 0, "action-deactivate");
    } else {
        print $form->formconfirm("card.php?id=".$materiel->id, 'Réactiver le matériel', 'Êtes vous sûr de vouloir réactiver ce matériel ?', "confirm_reactivate", '', 0, "action-reactivate");
    }
    if ($materiel->active && !isMaterielInEntretien($materiel->id)) print $form->formconfirm($_SERVER['PHP_SELF'].'?id='.$materiel->id, 'Entretien', 'Êtes-vous sûr de vouloir créer un ticket d\'entretien pour ce matériel ?', "confirm_entretien", '', 0, "action-entretien");
}


/* ************************************************************************** */
/*                                                                            */
/* Barre d'action                                                             */
/*                                                                            */
/* ************************************************************************** */
if ($action != 'create' && $action != 'edit') {
    print "\n".'<div class="tabsAction">'."\n";

    if (empty($reshook)) {
        if ($materiel->active && $conf->entretien->enabled && $user->rights->entretien->create) {
            if (isMaterielInEntretien($materiel->id)) {
                $tooltip = 'Ce matériel est déjà en entretien';
                print '<span id="action-entretien" class="butActionRefused classfortooltip" title="'.$tooltip.'" style="background-color: #fcfc4b; color:#7d6300;">Entretien</span>';
            } else {
                print '<span id="action-entretien" class="butAction" style="background-color: #fbfb00; color:#7d6300 !important;">Entretien</span>';
            }
        }
        if ($usercancreate) {
            print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit&amp;id='.$materiel->id.'">'.$langs->trans("Modifier").'</a>';
            print '<a class="butAction" href="card.php?action=create&idtypemateriel='.$materiel->fk_type_materiel.'&precision_type='.$materiel->precision_type.'&idmarque='.$materiel->fk_marque.'&modele='.$materiel->modele.'&etat='.$materiel->fk_etat.'&fk_etat_etiquette='.$materiel->fk_etat_etiquette.'&exploitabilite='.$materiel->fk_exploitabilite.'&notes='.$materiel->notes.'&origine_materiel='.$materiel->fk_origine.'&fk_default_warehouse='.$materiel->fk_entrepot.'&idproprietaire='.$materiel->fk_proprietaire.'">'.$langs->trans("Cloner").'</a>';
        }  

        if ($usercandelete) {
            if ($materiel->active) {
                print '<span id="action-deactivate" class="butActionDelete" style="background-color: #F5D2C0; color:#363636;">Désactiver</span>';
            } else {
                print '<span id="action-reactivate" class="butActionDelete" style="background-color: #FF8D53; color:#363636;">Réactiver</span>';
            }
        }
    }

    print "\n</div>\n";
}

// End of page
llxFooter();
$db->close();