<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// Load Dolibarr environment
@include "../../main.inc.php";

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/recufiscal.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/recufiscal.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));

/*Recupération données POST*/
$action = (GETPOST('action', 'alpha') ? GETPOST('action', 'alpha') : 'create'); // If no action provided, set to 'create'
$cancel = GETPOST('cancel', 'alpha');
$id = GETPOST('id', 'int');
$rowid = GETPOST('rowid', 'int');
$lineid = GETPOST('lineid', 'int');
$confirm = GETPOST('confirm', 'alpha');
$socid = GETPOST('socid', 'int');
$donateurs = GETPOST('donateurs', 'int');
$type = GETPOST('type', 'int');
$notes = GETPOST('notes', 'alpha');
$date_recu_fiscal = GETPOST('date_recu_fiscal', 'alpha');

$description = GETPOST('description', 'alpha');
$valeur = GETPOST('valeur', 'float');
$qty = GETPOST('qty', 'int');

$form = new Form($db);
$formfile = new FormFile($db);
$recufiscal = new RecuFiscal($db);
$donateur = new Donateur($db);
$materiel = new Materiel($db);
$usercanread = ($user->rights->materiel->read);
$usercancreate = ($user->rights->materiel->create);
$usercandelete = ($user->rights->materiel->delete);

/*
 *  Traitement des données et vérifications de sécurité
 */
if (!empty($user->socid)) $socid = $user->socid;

if ($id > 0)
{
	$result = $recufiscal->fetch($id);
    $object = $recufiscal; // Duplicate the object because some functions (like sendmail) user the variable name $object (et puis flemme de remplacer tous les noms)
	if (!$result) {
		header('Location: '.DOL_URL_ROOT.'/custom/recufiscal/list.php');
		setEventMessages('Impossible de récupérer les données du reçu fiscal.', null, 'errors');
		exit;
	}
	if ($action == 'create') $action = 'view';
}

if (!$usercanread) accessforbidden();
/*
 * Actions
 */

// List of quick modification action names and type
$quick_modification_actions = array('setdate_recu_fiscal'=>'text',
                                );

if (array_key_exists($action, $quick_modification_actions))
{
    $field_name = str_replace('set', '', $action); // Remove the prefix 'set' from $action to get db field name
    $type = $quick_modification_actions[$action];
    $value = GETPOST($field_name);

    $date = $recufiscal->dateToMySQL($value);
    $result = $recufiscal->setValueFrom($field_name, $date, 'recu_fiscal', null, $type);

    if ($result > 0) setEventMessages('Valeur modifiée avec succès.', null);
    else setEventMessages('Erreur lors de la modification de la valeur', null, 'errors');
    header("Location: ".$_SERVER['PHP_SELF']."?id=".$recufiscal->id);
    exit;
}


if ($action == 'addline' && $recufiscal->fk_statut == RecuFiscal::STATUS_DRAFT)
{
    // Check for valid data
    if (empty($description) || empty($valeur) || empty($qty) || $qty < 0 || $valeur < 0) {
        setEventMessages('Donnée(s) invalide(s). Vérifiez les champs', null, 'errors');
    }
    else {
        $result = $recufiscal->addLine($description, $valeur, $qty);
        if (!$result) setEventMessages('Erreur lors de l\'ajout du matériel : ' . $recufiscal->error, null, 'errors');
        else {
            setEventMessages('Matériel ajouté avec succès' , null);
            header('Location: '.$_SERVER["PHP_SELF"].'?id='.$recufiscal->id);
            exit;
        }
    }
}
/*
* Build doc
*/
include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';
if ($action == 'builddoc') {
	if (is_numeric(GETPOST('model', 'alpha'))) {
        
		$error = $langs->trans("ErrorFieldRequired", $langs->transnoentities("Model"));
	} else {
     
		// Reload to get all modified line records and be ready for hooks
        $object = new RecuFiscal($db);
		$ret = $object->fetch($rowid);
		/*if (empty($object->id) || ! $object->id > 0)
		{
			dol_print_error('Object must have been loaded by a fetch');
			exit;
		}*/
		// Save last template used to generate document
		if (GETPOST('model', 'alpha')) {
			$object->setDocModel($user, GETPOST('model', 'alpha'));
		}
		// Special case to force bank account
		//if (property_exists($object, 'fk_bank'))
		//{
		if (GETPOST('fk_bank', 'int')) {
			// this field may come from an external module
			$object->fk_bank = GETPOST('fk_bank', 'int');
		} elseif (!empty($object->fk_account)) {
			$object->fk_bank = $object->fk_account;
		}
		//}
		$outputlangs = $langs;
		$newlang = '';
		if (!empty($conf->global->MAIN_MULTILANGS) && empty($newlang) && GETPOST('lang_id', 'aZ09')) {
			$newlang = GETPOST('lang_id', 'aZ09');
		}
		if (!empty($conf->global->MAIN_MULTILANGS) && empty($newlang) && isset($object->thirdparty->default_lang)) {
			$newlang = $object->thirdparty->default_lang; // for proposal, order, invoice, ...
		}
		if (!empty($conf->global->MAIN_MULTILANGS) && empty($newlang) && isset($object->default_lang)) {
			$newlang = $object->default_lang; // for thirdparty
		}
		if (!empty($newlang)) {
			$outputlangs = new Translate("", $conf);
			$outputlangs->setDefaultLang($newlang);
		}
		// To be sure vars is defined
		if (empty($hidedetails)) {
			$hidedetails = 0;
		}
		if (empty($hidedesc)) {
			$hidedesc = 0;
		}
		if (empty($hideref)) {
			$hideref = 0;
		}
		if (empty($moreparams)) {
			$moreparams = null;
        }
        
		$result = $object->generateDocument($object->model_pdf, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
       
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
			$action = '';
		} else {
			if (empty($donotredirect)) {	// This is set when include is done by bulk action "Bill Orders"
				setEventMessages($langs->trans("FileGenerated"), null);
				// $urltoredirect = $_SERVER['REQUEST_URI'];
				// $urltoredirect = preg_replace('/#builddoc$/', '', $urltoredirect);
				// $urltoredirect = preg_replace('/action=builddoc&?/', '', $urltoredirect); // To avoid infinite loop
				header('Location: /custom/recufiscal/card.php?id='.$object->id.'');
				exit;
			}
		} 
	}
}

if ($action == 'remove_file') {
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		// if (empty($object->id) || !$object->id > 0) {
		// 	// Reload to get all modified line records and be ready for hooks
		// 	$ret = $object->fetch($id);
		// }
        $object = new RecuFiscal($db);
        $ret = $object->fetch($rowid);
        $upload_dir = $conf->recufiscal->dir_output;
		$langs->load("other");
		$filetodelete = GETPOST('file', 'alpha');
        $folder = substr($filetodelete, 0, -5);
		$file = $upload_dir.'/'.$folder.'/'.$filetodelete;
		$dirthumb = dirname($file).'/thumbs/'; // Chemin du dossier contenant la vignette (if file is an image)
		$ret = dol_delete_file($file, 0, 0, 0, $object);
		if ($ret) {
			// If it exists, remove thumb.
			$regs = array();
			if (preg_match('/(\.jpg|\.jpeg|\.bmp|\.gif|\.png|\.tiff)$/i', $file, $regs)) {
				$photo_vignette = basename(preg_replace('/'.$regs[0].'/i', '', $file).'_small'.$regs[0]);
				if (file_exists(dol_osencode($dirthumb.$photo_vignette))) {
					dol_delete_file($dirthumb.$photo_vignette);
				}
				$photo_vignette = basename(preg_replace('/'.$regs[0].'/i', '', $file).'_mini'.$regs[0]);
				if (file_exists(dol_osencode($dirthumb.$photo_vignette))) {
					dol_delete_file($dirthumb.$photo_vignette);
				}
			}
			setEventMessages($langs->trans("FileWasRemoved", $filetodelete), null, 'mesgs');
		} else {
			setEventMessages($langs->trans("ErrorFailToDeleteFile", $filetodelete), null, 'errors');
		}
		//Make a redirect to avoid to keep the remove_file into the url that create side effects

		header('Location: /custom/recufiscal/card.php?id='.$object->id.'');
		exit;
}

elseif ($action == 'add')
{
    if (empty($donateurs) || $donateurs == '-1')
    {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Donateur'), null, 'errors');
        $action = "create";
        $error++;
    }
    if (!array_key_exists($type, getTypeArray()))
    {
        setEventMessages('Option invalide pour le champs "Type"', null, 'errors');
        $action = "create";
        $error++;
    }
	if (!$error) {
		$recufiscal->fk_donateur = $donateurs;
		$recufiscal->fk_type = $type;
		$recufiscal->notes = $notes;
        $recufiscal->date_recu_fiscal = $date_recu_fiscal;

		if (!$recufiscal->create($user)) {
			setEventMessages('Une erreur est survenue lors de la création du reçu fiscal : '.$recufiscal->error, null, 'errors');
			$action = 'create';
		} else {
			setEventMessages('Reçu fiscal créé avec succès', null);
			header('Location: '.$_SERVER["PHP_SELF"].'?id='.$recufiscal->id);
			exit;
		}
	}
}
elseif ($action == 'confirm_valid' && $confirm == 'yes')
{
    $result = $recufiscal->setStatus(RecuFiscal::STATUS_VALIDATED);
    if (!$result)
    {
        setEventMessages('Une erreur est survenue lors de la validation du reçu fiscal.', null, 'errors');
    }
    else
    {
        setEventMessages('Reçu fiscal validé avec succès !', null);
        header('Location: '.DOL_URL_ROOT.'/custom/recufiscal/card.php?id='.$recufiscal->id);
        exit;
    }
}
elseif ($action == 'confirm_editLines' && $confirm == 'yes')
{
    $result = $recufiscal->setStatus(RecuFiscal::STATUS_DRAFT);
    if (!$result)
    {
        setEventMessages('Une erreur est survenue lors de la modification du statut du reçu fiscal', null, 'errors');
    }
    else
    {
        setEventMessages('Statut modifié avec succès', null);
        header('Location: '.DOL_URL_ROOT.'/custom/recufiscal/card.php?id='.$recufiscal->id);
        exit;
    }
}
elseif ($action == 'confirm_edit' && $confirm == 'yes') $action == "edit";

//update a product line
elseif ($action == 'updateline' && $usercancreate && !$cancel)
{
    $result = $recufiscal->updateLine($lineid, $description, $valeur, $qty);
    if ($result) {
        setEventMessages('Ligne de produit mise à jour' , null);
    }
    else {
        setEventMessages('Une erreur s\'est produite : '.$recufiscal->error , null, 'errors');
    }
    header('Location: '.$_SERVER["PHP_SELF"].'?id='.$recufiscal->id);
    exit;
}
//update a product line
elseif ($action == 'confirm_delete' && $usercancreate && $confirm == 'yes')
{
    $result = $recufiscal->delete();
    if ($result) {
        setEventMessages('Reçu fiscal supprimé avec succès' , null);
        header('Location: /custom/recufiscal/list.php');
        exit;
    }
    else {
        setEventMessages('Une erreur s\'est produite : '.$recufiscal->error , null, 'errors');
        $action = 'view';
    }
}
$formconfirm = '';
if ($action == 'ask_deleteline')
{
    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$recufiscal->id.'&lineid='.$lineid, 'Suppression d\'une ligne de produit', 'Êtes-vous sûr de vouloir supprimer cette ligne de produit ?', 'confirm_deleteline', '', 0, 1);
}	
// Remove a product line
elseif ($action == 'confirm_deleteline' && $confirm == 'yes' && $usercancreate && $recufiscal->fk_statut == RecuFiscal::STATUS_DRAFT)
{
    $result = $recufiscal->deleteLine($lineid);
    if ($result)
    {
        setEventMessages('Ligne de produit supprimée' , null);
        header('Location: '.$_SERVER["PHP_SELF"].'?id='.$recufiscal->id);
        exit;
    } else {
        setEventMessages('Erreur de suppression de la ligne de produit : '.$recufiscal->error, null, 'errors');
    }
}
elseif ($action == 'valid' && $recufiscal->fk_statut == RecuFiscal::STATUS_DRAFT)
{
    $text = "Êtes-vous sûr de vouloir valider le reçu fiscal sous la référence <b>{$recufiscal->ref}</b> ?";
    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$recufiscal->id, 'Valider le reçu fiscal', $text, 'confirm_valid', '', 1, 1);
}    
elseif ($action == 'editLines' && $recufiscal->fk_statut != RecuFiscal::STATUS_DRAFT)
{
    $text = "Êtes-vous sûr de vouloir repasser le reçu fiscal <b>{$recufiscal->ref}</b> au statut de brouillon ?";
    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$recufiscal->id, 'Modifier le reçu fiscal', $text, 'confirm_editLines', '', 1, 1);
}    
elseif ($action == 'edit' && $recufiscal->fk_statut != RecuFiscal::STATUS_DRAFT)
{
    $text = "Êtes-vous sûr de vouloir repasser le reçu fiscal <b>{$recufiscal->ref}</b> au statut de brouillon ?";
    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$recufiscal->id, 'Modifier le reçu fiscal', $text, 'confirm_edit', '', 1, 1);
}  
elseif ($action == 'setsent' && $recufiscal->fk_statut == RecuFiscal::STATUS_VALIDATED)
{
    $text = "Êtes-vous sûr de vouloir modifier le statut de ce reçu fiscal ?";
    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$recufiscal->id, 'Classer "Envoyé"', $text, 'confirm_setsent', '', 1, 1);
}  

// Actions to send emails
$triggersendname = '';
$paramname = 'id';
$autocopy = 'MAIN_MAIL_AUTOCOPY_SUPPLIER_INVOICE_TO';
$trackid = 'sinv'.$object->id;

// Allow to track if the mail has been successfully sent
$paramname2 = 'success';
$paramval2 = '1';
include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';

// TODO : Find more secure way to check if the mail has been sent
if (!(empty(GETPOST('success', 'int'))) || $action == 'confirm_setsent')
{
    // The receipt needs to be in validated status
    if ($recufiscal->fk_statut != RecuFiscal::STATUS_VALIDATED) $action = 'view';
    else {
        $result = $recufiscal->setStatus(RecuFiscal::STATUS_SENT);
        if ($result)
        {
            setEventMessages('Le statut du reçu fiscal a été modifié' , null);
            header('Location: '.$_SERVER["PHP_SELF"].'?id='.$recufiscal->id);
            exit;
        } else {
            setEventMessages('Erreur lors de la modification du statut du reçu fiscal', null, 'errors');
        }
    }
}

/*
 * View
 */

if ($action == 'create' && $usercancreate)
{
    // Chargement de l'interface (top_menu et left_menu)
    llxHeader("", 'Nouveau reçu fiscal');

    //WYSIWYG Editor
    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

	print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
    print '<input type="hidden" name="action" value="add">';

	$picto = 'recufiscal';
	$title = 'Nouveau reçu fiscal';
    print talm_load_fiche_titre($title, '', $picto);

	dol_fiche_head('');

    print '<table class="border centpercent">';
    print '<tr></tr>';

    // Donateur
    print '<tr><td class="fieldrequired titlefieldcreate">Donateur</td>';
	print '<td colspan="3">';
    print $form->selectarray('donateurs', getDonateurArray(), $donateurs, 1, 0, 0, 'style="min-width:200px;"', 0, 0, 0, '', '', 1);
    print ' <a href="'.DOL_URL_ROOT.'/custom/recufiscal/donateur/card.php'.'">';
    print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter un type donateur"></span>';
    print '</a>';
    print  '</td></tr>';

    // Type
    print '<tr><td class="fieldrequired titlefieldcreate">Type</td>';
	print '<td colspan="3">';
    print $form->selectarray('type', getTypeArray(), $type);
    print  '</td></tr>';

    print '<tr><td class="titlefieldcreate fieldrequired">Date du reçu fiscal</td><td colspan="3">';
    print $form->selectDate('', 'date_recu_fiscal', '', '', '', "add", 1, 1);
    print  '</td></tr>';


	// Notes
    print '<tr><td class="tdtop titlefieldcreate">Notes</td><td colspan="3">';
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
 * Reçu fiscal card
 */

elseif ($recufiscal->id > 0) {

    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

    llxHeader("", "Reçu fiscal - ".$recufiscal->ref);
    $head = recufiscal_prepare_head($recufiscal);
    $titre = 'Reçu fiscal';
    $picto = ('recufiscal');
    talm_fiche_head($head, 'card', $titre, -1, $picto);

    $linkback = '<a href="'.DOL_URL_ROOT.'/custom/recufiscal/list.php/">Retour à la liste</a>';

    talm_banner_tab($recufiscal, 'id', $linkback, 1, 'rowid');

    // Print confirm popup for deleting line (if action == ask_deleteline)
    print $formconfirm;   
    
    print '<div class="fichecenter">';
    print '<div class="fichehalfleft">';
    print '<div class="underbanner clearboth"></div>';
    print '<table class="border tableforfield" width="100%">';
    
    // Type
    print '<tr><td class="titlefield">';
    print 'Type';
    print '</td><td colspan="3">';	
    print '<span class="badgeneutral">';
    print $recufiscal->getLibType();
    print '</span>';
    print '</td></tr>';

    // Donateur
    print '<tr><td class="titlefield">';
    print 'Donateur';
    print '</td><td colspan="3">';	
    print $recufiscal->donateur_object->getNomUrl();
    print '</td></tr>';

    print '<tr>';
    print'<td class="titlefield">';
    print $form->editfieldkey("Date effective du reçu fiscal", 'date_recu_fiscal', $recufiscal->date_recu_fiscal, $recufiscal, $usercancreate, 'datepicker');
    print '</td>';
    print '<td colspan="3">';
    print $form->editfieldval("Date effective du reçu fiscal", 'date_recu_fiscal', $recufiscal->date_recu_fiscal, $recufiscal, $usercancreate, 'datepicker',"", null,null,"",0,"","id","tzuser");
    print '</td>';
    print '</tr>';
    

    // Notes
    print '<tr><td class="titlefield">';
    print 'Notes';
    print '</td><td colspan="3">';	
    print $recufiscal->notes ? $recufiscal->notes : '<i>Pas de notes</i>';
    print '</td></tr>';

    print '</table>';
    print '</div>';
    print '<div class="fichehalfright"><div class="ficheaddleft">';
    print '<div class="underbanner clearboth"></div>';
    print '<table class="border tableforfield" width="100%">';

    // Valeur totale
    print '<tr><td class="titlefield">';
    print "Montant total";
    print '</td><td colspan="3" class="amountpaymentcomplete">';
    print price($recufiscal->total_ttc, 1, '', 0, -1, -1, $conf->currency);
    print '</td></tr>';


    print '</table>';
    print '</div>';

    print '</div></div>';
    print '<div style="clear:both"></div>';
    print '<br>';


    /*
    * Lines
    */
    print '<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$recufiscal->id.(($action != 'editline') ? '#addline' : '#line_'.GETPOST('lineid')).'" method="POST">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="'.(($action != 'editline') ? 'addline' : 'updateline').'">';
    print '<input type="hidden" name="mode" value="">';
    print '<input type="hidden" name="id" value="'.$recufiscal->id.'">';

    print '<div class="div-table-responsive-no-min">';
    print '<table id="tablelines" class="noborder noshadow" width="100%">';
    print '<tr class="liste_titre">';
    print '<td>Description</td>';
    print '<td>Valeur unitaire</td>';
    print '<td class="center">Quantité</td>';
    print '<td>Valeur totale</td>';
    print '<td style="width: 80px"></td>'; // Empty column for edit and remove button
    print '</tr>';

    // Show object lines
    if (!empty($recufiscal->lines)){
        $ret = $recufiscal->printObjectLines($action, $lineid);
    }
    $num = count($source->lines);

    // Form to add new line
    // If the state is draft, show the form to add a new line
    if ($recufiscal->fk_statut == RecuFiscal::STATUS_DRAFT && $usercancreate)
    {
        if ($action != 'editline')
        {
            // Add product
            $recufiscal->formAddObjectLine();
        }
    }
    print '</table>';
    print '</form>';
    dol_fiche_end();
    print '<div class="fichecenter"><div class="fichehalfleft">';

	/*
	 * Generated documents
	 */

	$filename = dol_sanitizeFileName($recufiscal->ref);
	$filedir = $conf->recufiscal->dir_output.'/'.$filename;
	$urlsource = $_SERVER['PHP_SELF'].'?rowid='.$recufiscal->id;
 
	$genallowed	= (($object->paid == 0 || $user->admin) && $user->rights->materiel->read);
	$delallowed	= $user->rights->materiel->create;
    $model = 'html_cerfafr_materiel';
	print $recufiscal->showdocuments('recufiscal', $filename, $filedir, $urlsource, $genallowed, $delallowed, $model);
 
	// // Show links to link elements
	// $linktoelem = $form->showLinkToObjectBlock($object, null, array('materiel'));
	// $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);
	print '</div><div class="fichehalfright">';
	print '</div></div>';

    /* ************************************************************************** */
    /*                                                                            */
    /* Barre d'action                                                             */
    /*                                                                            */
    /* ************************************************************************** */
    if ($action != 'presend')
    {
        print "\n".'<div class="tabsAction">'."\n";
        
        if (empty($reshook)) {
        
            if ($usercancreate && $recufiscal->fk_statut == RecuFiscal::STATUS_VALIDATED) {
                print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit&amp;id='.$recufiscal->id.'">'.$langs->trans("Modifie le reçu").'</a>';
                print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=editLines&amp;id='.$recufiscal->id.'">'.$langs->trans("Modifier le contenu du reçu").'</a>';
                print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=presend&amp;id='.$recufiscal->id.'">Envoyer eMail</a>';
                print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=setsent&amp;id='.$recufiscal->id.'">Classer envoyé</a>';
            }
        
            if ($usercandelete) {
                if ($recufiscal->active) { 
        
                    // Validate button
                    if (!empty($recufiscal->lines) && $recufiscal->fk_statut == RecuFiscal::STATUS_DRAFT) print '<a class="butAction" href="/custom/recufiscal/card.php?id='.$recufiscal->id.'&amp;action=valid">Valider</a>';
                    
                    // Delete button
                    $text = 'Êtes-vous sûr de vouloir supprimer le reçu fiscal <b>'.$recufiscal->ref.'</b> ?';
                    print $form->formconfirm("card.php?id=".$recufiscal->id, 'Supprimer le reçu fiscal', $text, "confirm_delete", '', 0, "action-delete");
                    print '<span id="action-delete" class="butActionDelete">Supprimer</span>';
                }
            }
        }
        
        print "\n</div>\n";
    }
    print '<div class="fichecenter"><div class="fichehalfleft">';

    $object = $recufiscal;
    // Presend form
    $modelmail = 'SendingRecuFiscalToDnor';
    $defaulttopic = $conf->global->RECUFISCAL_MAIL_DEFAULT_TOPIC;
    $diroutput = $conf->recufiscal->dir_output;
    $autocopy = '';
    $trackid = 'rf'.$object->id;

    include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}





// End of page
llxFooter();
$db->close();