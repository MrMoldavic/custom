<?php
// Load Dolibarr environment
@include "../../main.inc.php";
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/entretien.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formentretien.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formexploitation.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';


// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));

/*Recupération données POST*/
$action = (GETPOST('action', 'alpha') ? GETPOST('action', 'alpha') : 'create'); // If no action provided, set to 'create'
$confirm = GETPOST('confirm', 'alpha');
$redirect_url = GETPOST('redirect_url', 'alpha') ? GETPOST('redirect_url', 'alpha') : '/custom/entretien/list.php';
$socid = GETPOST('socid', 'int');
$materiel_id = GETPOST('materiel_id', 'int');
$replacement_materiel_id = GETPOST('replacement_materiel_id', 'int');
$description = GETPOST('description', 'alpha');
$deadline_datetime = DateTime::createFromFormat("d/m/Y", GETPOST('deadline_date'));
$id = GETPOST('id', 'int');
$new_suivi = GETPOST('new_suivi', 'alphanohtml');
$agent = GETPOST('agent', 'integer');
$form = new Form($db);
$formmateriel = new FormMateriel($db);
$entretien = new Entretien($db);
$formentretien = new FormEntretien($db);
/*
 *  Traitement des données et vérifications de sécurité
 */

$usercanread = ($user->rights->entretien->read);
$usercancreate = ($user->rights->entretien->create);
$usercandelete = ($user->rights->entretien->delete);

if (!$usercanread) accessforbidden();

if (!empty($user->socid)) $socid = $user->socid;
if ($cancel) $action = '';
if ($id > 0)
{
	$result = $entretien->fetch($id);
	if (!$result) {
		header('Location: '.DOL_URL_ROOT.'/custom/entretien/list.php');
		setEventMessages('Impossible de récupérer les données de l\'entretien.', null, 'errors');
		exit;
	}
	if ($action == 'create') $action = 'view';
}


/*
 * Actions
 */
if ($action == 'add')
{
    if (empty($description))
    {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Description du problème'), null, 'errors');
        $action = "create";
        $error++;
    }
    if (empty($materiel_id))
    {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Matériel'), null, 'errors');
        $action = "create";
        $error++;
    }
	if (!$error) {
		$deadline_timestamp = (empty($deadline_datetime) ? NULL : $deadline_datetime->getTimestamp());
		$entretien->description = $description;
		$entretien->deadline_timestamp = $deadline_timestamp;
		$entretien->materiel_id = $materiel_id;
		$entretien->replacement_materiel_id = $replacement_materiel_id;
		if (!$entretien->create($user)) {
			setEventMessages('Une erreur est survenue lors de la création de l\'entretien : '.$entretien->error, null, 'errors');
			$action = 'create';
		} else {
			setEventMessages('Entretien créé avec succès', null);
			header('Location: '.DOL_URL_ROOT.$redirect_url);
			exit;
		}
	}
} elseif ($action == 'add_suivi') {
    if (empty($new_suivi)) setEventMessages("Veuillez renseigner un suivi valide.", null, 'errors');
    if ($agent == "0") setEventMessages("Veuillez renseigner un agent pour le suivi.", null, 'errors');
    else {
        $materiel_status = getMaterielSuiviStatus($entretien->materiel_id);
        if (isInExploitation($entretien->materiel_id) && ($materiel_status['fk_localisation'] != 1 || $materiel_status['etat_suivi'] != 1)) {
            setEventMessages('Le matériel doit être retourné à l\'entrepôt avant de démarrer le suivi d\'entretien', null, 'errors');
        } else {
            $result = $entretien->addSuivi($new_suivi, $agent, $user);
            if ($result) setEventMessages('Ligne de suivi ajoutée avec succès', null);
            else setEventMessages('Erreur lors de l\'ajout du suivi', null, 'errors');
            $action = 'view';
        }
    }
} elseif ($action == 'confirm_cloture' && $confirm == 'yes' && $usercandelete) {
    $action_historic = $entretien->getSuiviHistoric();
    if (empty($action_historic)) setEventMessages('Veuillez insérer au moins une ligne de suivi avant de pouvoir clôturer l\'entretien', null, 'errors');
    else {
        if (is_object($entretien->replacement_materiel_object))
        {
            $replacement_materiel_status = getMaterielSuiviStatus($entretien->replacement_materiel_id);
            if ($replacement_materiel_status['etat_suivi'] == 1 && $replacement_materiel_status['fk_localisation'] == 1) $replacementOutOfKit = 1;
        }
        $result = $entretien->cloture($user);
        if ($result) {
            setEventMessages('Entretien clôturé avec succès', null);
            if ($replacementOutOfKit) setEventMessages('<br>Le matériel de remplacement a été sorti de l\'exploitation', null);
			header('Location: '.DOL_URL_ROOT.'/custom/entretien/card.php?action=view&id='.$id);
            exit;
        }
        else setEventMessages('Erreur lors de la clôture de l\'entretien : ' . $entretien->error, null, 'errors');
    }
} elseif ($action == 'confirm_ouverture' && $confirm == 'yes' && $usercancreate) {
   
    $entretien_id = isMaterielInEntretien($entretien->materiel_id);

    if($entretien_id != 0 && ($entretien_id != $id))
    {
        setEventMessages('Impossible d\'ouvrir cet entretien, un plus récent éxiste déjà.', null, 'errors');
        header('Location: '.DOL_URL_ROOT.'/custom/entretien/card.php?action=view&id='.$id);
        exit;
    }
    else
    {
        $result = $entretien->ouverture($user);
    
        if($result)
        {
            setEventMessages('Entretien ouvert avec succès!', null);
            header('Location: '.DOL_URL_ROOT.'/custom/entretien/card.php?action=view&id='.$id);
            exit;
        }
        else setEventMessages('Erreur lors de la ouverture de l\'entretien : ' . $entretien->error, null, 'errors');
    }




   
}


/*
 * View
 */
if ($action == 'create' && $usercancreate)
{
    // Chargement de l'interface (top_menu et left_menu)
    llxHeader("", 'Nouvel entretien');

    //WYSIWYG Editor
    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

	print '<script src="'.DOL_URL_ROOT.'/custom/entretien/js/entretien.js"></script>';
	print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="redirect_url" value="'.$redirect_url.'">';
    print '<link rel="stylesheet" type="text/css" href="'.DOL_URL_ROOT.'/custom/entretien/css/entretien.css">';
	$picto = 'entretien';
	$title = 'Nouvel entretien';
    print talm_load_fiche_titre($title, '', $picto);
	dol_fiche_head('');
    
    print '<table class="border centpercent">';
    print '<tr></tr>';
    
    // Materiel
    print '<tr><td class="fieldrequired titlefieldcreate">Matériel</td>';
	print '<td colspan="3">';
	print $formentretien->printSelectMaterielForCreation('materiel_id', $materiel_id);
    print  '</td></tr>';
    
    // Matériel de remplacement (si le matériel à entretenir est en exploitation)
	$help_tooltip = '<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title="Remplace le matériel durant la durée de l\'entretien (seulement pour les matériels en exploitation)"><img src="/theme/eldy/img/info.png" alt="" style="vertical-align: middle; cursor: help"></span>';
    print '<tr><td class="titlefieldcreate">Matériel de remplacement '.$help_tooltip.'</td>';
	print '<td colspan="3">';
	print $form->selectarray('replacement_materiel_id', '', 'replacement_materiel_id', '--Sélectionnez un matériel--', 0, 0, '', 0, 0, 1);
    print  '</td></tr>';
    
    // Deadline
    print '<tr><td class="titlefieldcreate">Deadline (facultatif)</td>';
	print '<td colspan="3">';
	print $form->selectDate(-1, 'deadline_date', '', '', '', '', 1, 1);
    print  '</td></tr>';
	
    // Description
    print '<tr><td class="tdtop fieldrequired titlefieldcreate">Description du problème</td><td colspan="3">';
    $doleditor = new DolEditor('description', GETPOST('description', 'none'), '', 160, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '90%');
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
} elseif ($id > 0 && $usercanread) {
    // Chargement de l'interface (top_menu et left_menu)
    llxHeader("", 'Entretien ' . $entretien->ref);
    $head = entretien_prepare_head($entretien);
    $titre = 'entretien';
    $picto = 'entretien';
    talm_fiche_head($head, 'card', $titre, -1, $picto);
    $linkback = '<a href="'.DOL_URL_ROOT.'/custom/entretien/list.php/">Retour à la liste</a>';
    talm_banner_tab($entretien, 'id', $linkback, 1, 'rowid');
    print '<div class="fichecenter">';
	
    // Ajout d'une bannière indiquant qu'il faut retourner le matériel à l'entrepôt si il est en exploitation et pas à l'entrepot
    $exploitation_id = isInExploitation($entretien->materiel_id);
    if ($exploitation_id)
    {
        $exploitation = new Exploitation($db);
        $exploitation->fetch($exploitation_id);
    }
    if ($exploitation_id)
    {
        $position = getMaterielSuiviStatus($entretien->materiel_id);
        if ($position['fk_localisation'] != 1 || $position['etat_suivi'] != 1) {
            $exploitation_url = $exploitation->getNomUrl();
            $alert = '<div class="warning clearboth"><i class="fas fa-exclamation-triangle"></i>&nbsp;';
            $alert .= 'Ce matériel doit être retourné à l\'entrepôt pour démarrer l\'entretien. Dirigez vous vers la page de l\'exploitation pour retourner le matériel : ';
            $alert .= $exploitation_url;
            $alert .= '</div>';
            print $alert;
        }
    }
    print '<div class="fichehalfleft">';
    print '<div class="underbanner clearboth"></div>';
    
    //======================= Description de l'entretien =================================
    print '<table class="border tableforfield" width="100%">';
    
    // Matériel
    print '<tr><td class="titlefield">';
    print "Matériel";
    print '</td><td colspan="3">';
    print $entretien->materiel_object->getNomUrl();
    print '</td></tr>';
    
    // Exploitation
    print '<tr><td class="titlefield">';
    print "Exploitation";
    print '</td><td colspan="3">';
    print ($exploitation_id ? $exploitation->getNomUrl() : '<i>Pas en exploitation</i>');
    print '</td></tr>';
    
    // Localisation
    print '<tr><td class="titlefield">';
    print "Localisation";
    print '</td><td colspan="3">';
	$formentretien->printMaterielLocalisation($entretien->materiel_id);
    print '</td></tr>';
    
    // Matériel de remplacement
    print '<tr><td class="titlefield">';
	$help_tooltip = '<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title="Remplace le matériel pendant la durée de l\'entretien (seulement pour les matériels en exploitation)"><img src="/theme/eldy/img/info.png" alt="" style="vertical-align: middle; cursor: help"></span>';
    print "Matériel de remplacement " . $help_tooltip;
    print '</td><td colspan="3">';
	$sanitized_replacement_materiel_field = (!empty($entretien->replacement_materiel_object) ? $entretien->replacement_materiel_object->getNomUrl() : '<i>Pas de remplacement</i>');
    print $sanitized_replacement_materiel_field;
    print '</td></tr>';
    
    // Description
    print '<tr><td class="titlefield">';
    print "Description";
    print '</td><td colspan="3">';
    print $entretien->description;
    print '</td></tr>';
    
    // Deadline
    print '<tr><td class="titlefield">';
    print "Deadline";
    print '</td><td colspan="3">';
	$deadline_sanitized = (!empty($entretien->deadline_timestamp) ? date('d/m/Y', $entretien->deadline_timestamp) : '<i>Pas de deadline</i>');
    print $deadline_sanitized;
    print '</td></tr>';
    
    // SPACER
    print '<tr></tr>';
    
    // Date de création
    print '<tr><td class="titlefield">';
    print "Date de création";
    print '</td><td colspan="3">';
    print date('d/m/Y', strtotime($entretien->creation_timestamp));
    print '</td></tr>';
    
    // Créé par
    print '<tr><td class="titlefield">';
    print "Créé par";
    print '</td><td colspan="3">';
    print $entretien->user_author_object->getNomUrl(1);
    print '</td></tr>';
    print '</table>';
    print '</div>';
    
    //======================= Liste des actions d'entretien =================================
    print '<div class="fichehalfright"><div class="ficheaddleft">';
    print '<div class="underbanner clearboth"></div>';
    
    if($entretien->active)
    {
  /* FORMULAIRE D'ENTRÉE D'UNE NOUVELLE LIGNE DE SUIVI */
        print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="id" value="'.$entretien->id.'">';
        print '<input type="hidden" name="action" value="add_suivi">';
        print '<table class="noborder centpercent">';
        
        // Line for title
        print '<tr class="liste_titre">';
        print '<td>Nouvelle ligne de suivi</td>';
        print '<td colspan="2">Action faite par:</td>';
        print '</tr>';


        $sqlAgent = "SELECT prenom, nom, rowid FROM ".MAIN_DB_PREFIX."management_agent";
        $resqlAgent= $db->query($sqlAgent);
        
        // Line to enter new values
        print '<!-- line to add new entry -->';
        print '<tr class="oddeven nodrag nodrop nohover">';
        print '<td>';
        print '<input type="text" name="new_suivi" placeholder="Ex : Corde remplacée">';
        print '</td>';
        print '<td>';
        print '<select name="agent">';
        print '<option value="0">Choisir...</option>';
        foreach( $resqlAgent as $value )
        {
            print '<option value="'.$value['rowid'].'">'.$value['prenom'].' '.$value['nom'].'</option>';
        }
        print '</select>';
        print '</td>';
        print '<td class="right">';
        print '<input type="submit" class="button" name="add_suivi" value="Ajouter le suivi">';
        print '</td>';
        print "</tr>";
        print '</table>';
        print '</form>';
        print '</div>';
    }
  
    
    /* LISTE DES LIGNES DE SUIVI */
    print '<table class="noborder centpercent">';

    print '<tr class="liste_titre">';
    print '<td>Liste du suivi</td>';
    print '<td>Action faite par:</td>';
    print '<td>Date d\'ajout</td>';
    print '</tr>';

    $suivi_historic = $entretien->getSuiviHistoric();
    if (!$suivi_historic){ // Aucun historique de suivi pour cet entretien    
		print '<tr class="oddeven nodrag nodrop nohover"><td colspan="2" class="opacitymedium">Aucun historique de suivi pour cet entretien.</td></tr>';
    } else {
        foreach($suivi_historic as $suivi_row) {   
		    print '<tr class="oddeven">';
            print '<td>';
            print $suivi_row['description'];
            print '</td>';
            print '<td>';

            $sqlAgentSuivi = "SELECT prenom, nom, rowid FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid=".$suivi_row['fk_agent'];
            $resqlAgentSuivi = $db->query($sqlAgentSuivi);
            $objAgentSuivi = $db->fetch_object($resqlAgentSuivi);

            print $objAgentSuivi->prenom.' '.$objAgentSuivi->nom;
            print '</td>';
            print '<td>';
            print date('d/m/Y', $suivi_row['date']);
            print '</td></tr>';
        }     
    }
    print "</table>";
    print '</div>';
    print '</div></div>';
    print '<div style="clear:both"></div>';
    
    dol_fiche_end();
    print "\n".'<div class="tabsAction">'."\n";
    if ($usercandelete && $entretien->active) {
        print '<span id="action-cloture" class="butAction">Clôturer l\'entretien</span>'."\n";
        print $form->formconfirm("card.php?id=".$entretien->id, 'Clôturer l\'entretien ?', 'Êtes-vous sûr de vouloir clôturer cet entretien ?', "confirm_cloture", '', 0, "action-cloture");
    } 
    else
    {
        print '<span id="action-ouverture" class="butAction">Ouvrir l\'entretien</span>'."\n";
        print $form->formconfirm("card.php?id=".$entretien->id, 'Rouvrir l\'entretien', 'Êtes-vous sûr de vouloir rouvrir cet entretien ?', "confirm_ouverture", '', 0, "action-ouverture");
    }
    print "\n</div>\n";
}
// End of page
llxFooter();
$db->close();