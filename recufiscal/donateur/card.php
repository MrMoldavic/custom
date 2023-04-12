<?php

// ini_set('display_errors', '1');

// ini_set('display_startup_errors', '1');

// error_reporting(E_ALL);



// Load Dolibarr environment

@include "../../../main.inc.php";



require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/recufiscal.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/recufiscal.lib.php';

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';



// Load translation files required by the page

$langs->loadLangs(array("materiel@materiel"));



/*Recupération données POST*/
$action = (GETPOST('action', 'alpha') ? GETPOST('action', 'alpha') : 'create'); // If no action provided, set to 'create'

$cancel = GETPOST('cancel', 'alpha');

$id = GETPOST('id', 'int');

$confirm = GETPOST('confirm', 'alpha');

$socid = GETPOST('socid', 'int');



$nom = GETPOST('nom', 'alpha');

$prenom = GETPOST('prenom', 'alpha');

$societe = GETPOST('societe', 'alpha');

$address = GETPOST('address', 'alpha');

$zipcode = GETPOST('zipcode', 'alpha');

$town = GETPOST('town', 'alpha');

$phone = GETPOST('phone', 'alpha');

$email = GETPOST('email', 'alpha');

$notes = GETPOST('notes', 'alpha');



$redirect_url = (GETPOST('redirect_url', 'alpha') ? GETPOST('redirect_url', 'alpha') : DOL_URL_ROOT.'/custom/recufiscal/donateur/list.php');







$donateur = new Donateur($db);



$usercanread = ($user->rights->materiel->read);

$usercancreate = ($user->rights->materiel->create);

$usercandelete = ($user->rights->materiel->delete);



if (!$usercanread) accessforbidden();



/*

 *  Traitement des données et vérifications de sécurité

 */

if (!empty($user->socid)) $socid = $user->socid;



if ($id > 0)

{

	$result = $donateur->fetch($id);

	if (!$result) {

		header('Location: '.DOL_URL_ROOT.'/custom/recufiscal/donateur/list.php');

		setEventMessages('Impossible de récupérer les données du donateur.', null, 'errors');

		exit;

	}

	if ($action == 'create') $action = 'view';

}





/*

 * Actions
custom/recufiscal
 */

if ($cancel) $action = '';

    

// List of quick modification action names and type

$quick_modification_actions = array('setprenom'=>'text',

                                    'setnom'=>'text',

                                    'setsociete'=>'text',

                                    'setaddress'=>'text',

                                    'setzipcode'=>'text',

                                    'settown'=>'text',

                                    'setemail'=>'text',

                                    'setphone'=>'text'

                                );



if (array_key_exists($action, $quick_modification_actions))

{

    $field_name = str_replace('set', '', $action); // Remove the prefix 'set' from $action to get db field name

    $type = $quick_modification_actions[$action];

    $value = GETPOST($field_name);

    $result = $donateur->setValueFrom($field_name, $value, 'donateur', null, $type);

    if ($result > 0) setEventMessages('Valeur modifiée avec succès.', null);

    else setEventMessages('Erreur lors de la modification de la valeur', null, 'errors');

    header("Location: ".$_SERVER['PHP_SELF']."?id=".$donateur->id);

    exit;

}



if ($action == 'add')

{

    if ((empty($nom) || empty($prenom)) && empty($societe))

    {

        setEventMessages('Vous devez renseigner le nom et le prenom ou la société du donateur', null, 'errors');

        $action = "create";

        $error++;

    }

	if (!$error) {

		$donateur->nom = $nom;

		$donateur->prenom = $prenom;

		$donateur->societe = $societe;

		$donateur->address = $address;

		$donateur->zip = $zipcode;

		$donateur->town = $town;

		$donateur->phone = $phone;

		$donateur->email = $email;

		$donateur->notes = $notes;

		if (!$donateur->create($user)) {

			setEventMessages('Une erreur est survenue lors de la création du donateur : '.$donateur->error, null, 'errors');

			$action = 'create';

            print "error";

		} else {

			setEventMessages('Donateur créé avec succès', null);

			header('Location: '.DOL_URL_ROOT.$redirect_url);

			exit;

		}

	}

}



// Supprimer un donateur

if ($action == 'confirm_delete' && $confirm != 'yes') $action = '';

if ($action == 'confirm_delete' && $confirm == 'yes' && $usercandelete) {



    $result = $donateur->delete();

    if ($result > 0) {

        setEventMessages('Le donateur a bien été supprimé.', null);

        header('Location: '.DOL_URL_ROOT.'/custom/recufiscal/donateur/list.php');

        exit;

    } else {

        setEventMessages('Erreur lors de la suppression.', null, 'errors');

        $reload = 0;

        $action = '';

    }

}





/*

 * View

 */





if ($action == 'create' && $usercancreate)

{

    // Chargement de l'interface (top_menu et left_menu)

    llxHeader("", 'Nouveau donateur');



    //WYSIWYG Editor

    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';



	print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';

    print '<input type="hidden" name="action" value="add">';



	$picto = 'donateur';

	$title = 'Nouveau donateur';

    print talm_load_fiche_titre($title, '', $picto);



	dol_fiche_head('');



    print '<table class="border centpercent">';

    print '<tr></tr>';



    // Prenom

    print '<tr><td class="fieldrequired titlefieldcreate">Prenom</td>';

	print '<td colspan="3">';

    print '<input type="text" name="prenom" value="'.$prenom.'"/>';

    print  '</td></tr>';



    // Nom

    print '<tr><td class="fieldrequired titlefieldcreate">Nom</td>';

	print '<td colspan="3">';

    print '<input type="text" name="nom" value="'.$nom.'"/>';

    print  '</td></tr>';



    // Societe

    print '<tr><td class="fieldrequired titlefieldcreate">Société</td>';

	print '<td colspan="3">';

    print '<input type="text" name="societe" value="'.$societe.'"/>';

    print  '</td></tr>';


    // Address

    print '<tr><td class="fieldrequired titlefieldcreate">Adresse</td>';

    print '<td colspan="3">';

    print '<textarea name="address" id="address" class="quatrevingtpercent" rows="'.ROWS_2.'" wrap="soft">';

    print $adress;

    print '</textarea>';

    print '</td></tr>';



    // Zip / Town

    print '<tr><td>Code postal</td><td>';

    print '<input type="text" name="zipcode" value="'.$zipcode.'"/>';

    print '</td>';

    if ($conf->browser->layout == 'phone') print '</tr><tr>';

    print '<td class="tdtop">Ville</td><td>';

    print '<input type="text" name="town" value="'.$town.'"/>';

    print '</td></tr>';



    // Phone

    print '<tr><td>'.$form->editfieldkey('Phone', 'phone', '', $object, 0).'</td>';

    print '<td'.($conf->browser->layout == 'phone' ? ' colspan="3"' : '').'>'.img_picto('', 'object_phoning').' <input type="text" name="phone" id="phone" class="maxwidth200 widthcentpercentminusx" value="'.(GETPOSTISSET('phone') ?GETPOST('phone', 'alpha') : $object->phone).'"></td>';

    print '</tr><tr>';



    // Email

    print '<tr><td>E-mail</td>';

    print '<td colspan="3">'.img_picto('', 'object_email').' <input type="text" class="maxwidth500 widthcentpercentminusx" name="email" id="email" value="'.$email.'"></td></tr>';





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

 * Donateur card

 */



elseif ($donateur->id > 0) {

    // Fiche en mode edition

    if ($action == 'edit' && $usercancreate) {



        // llxHeader("", $langs->trans("Materiel"));

        // //WYSIWYG Editor

        // require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';



        // $type = $langs->trans('Product');

        // //print load_fiche_titre($langs->trans('Modify').' '.$type.' : '.(is_object($object->oldcopy)?$object->oldcopy->ref:$object->ref), "");



        // // Main official, simple, and not duplicated code

        // print '<form action="'.$_SERVER['PHP_SELF'].'?id='.$materiel->id.'" method="POST">'."\n";

        // print '<input type="hidden" name="token" value="'.newToken().'">';

        // print '<input type="hidden" name="action" value="update">';

        // print '<input type="hidden" name="id" value="'.$materiel->id.'">';

        // print '<input type="hidden" name="canvas" value="'.$materiel->canvas.'">';



        // $head = materiel_prepare_head($materiel);

        // $title = 'Modification - ' . $materiel->ref;

        // $picto = ('materiel');

        // print talm_load_fiche_titre($title, '', $picto);



        // dol_fiche_head('');



        // print '<table class="border allwidth">';







        // print '<table class="border centpercent">';



        // print '<tr>';

        // print '</td></tr>';



        // // Type materiel

        // print '<tr><td class="titlefieldcreate fieldrequired">Type de matériel</td><td>';

        // print $formmateriel->selectTypesMateriel($materiel->fk_type_materiel, 'fk_type_materiel', '', 1);

        // print ' <a href="'.DOL_URL_ROOT.'/admin/dict.php?id=44'.'">';

        // print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter un type de matériel"></span>';

        // print '</a>';

        // print '</td>';

        // print '</tr>';



        // // Précision du type

        // $fieldname = 'Type d\'instrument <span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title="Ex : fretless, stéréo..."><img src="/theme/eldy/img/info.png" alt="" style="vertical-align: middle; cursor: help"></span>';

        // print '<tr><td class="titlefieldcreate">'. $fieldname .'</td><td colspan="3"><input name="precision_type" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.dol_escape_htmltag($materiel->precision_type).'"></td></tr>';



        // // État

        // print '<tr><td class="titlefieldcreate fieldrequired">État du matériel</td><td colspan="3">';

        // $etatarray = $materiel->getEtatDict();

        // print $form->selectarray('fk_etat', $etatarray, $materiel->fk_etat);

        // print '</td></tr>';



        // // État étiquette

        // print '<tr><td class="titlefieldcreate fieldrequired">État de l\'étiquette</td><td colspan="3">';

        // $fk_etat_etiquette_array = getEtatEtiquetteDict();

        // print $form->selectarray('fk_etat_etiquette', $fk_etat_etiquette_array, $materiel->fk_etat_etiquette);

        // print '</td></tr>';



        // // Exploitabilité

        // print '<tr><td class="titlefieldcreate fieldrequired">Exploitabilité</td><td colspan="3">';

        // $exploitabilitearray = $materiel->getExploitabiliteDict();

        // print $form->selectarray('fk_exploitabilite', $exploitabilitearray, $materiel->fk_exploitabilite);

        // print '</td></tr>';



        // // Marque

        // print '<tr><td class="titlefieldcreate">Marque</td><td colspan="3">';

        // print $formmateriel->selectMarques($materiel->fk_marque, 'fk_marque', '', 1);

        // print ' <a href="'.DOL_URL_ROOT.'/admin/dict.php?id=55'.'">';

        // print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter une marque"></span>';

        // print '</a>';

        // print '</td>';

        // print '</tr>';



        // // Modèle

        // print '<tr><td class="titlefieldcreate">Modèle</td><td colspan="3"><input name="modele" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.dol_escape_htmltag($materiel->modele).'"></td></tr>';



        // // Notes supplémentaires

        // print '<tr><td class="titlefieldcreate tdtop">Notes supplémentaires</td><td colspan="3">';

        // $doleditor = new DolEditor('notes', $materiel->notes, '', 160, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '90%');

        // $doleditor->Create();

        // print "</td></tr>";



        // // Origine

        // print '<tr><td class="titlefieldcreate fieldrequired">Origine du matériel</td><td colspan="3">';

        // $originearray = $materiel->getOrigineDict();

        // print $form->selectarray('origine_materiel', $originearray, $materiel->fk_origine);

        // print '</td></tr>';



        // // Entrepôt

        // print '<tr><td class="titlefieldcreate">Entrepôt</td><td>';

        // print $formproduct->selectWarehouses($materiel->fk_entrepot, 'fk_entrepot', 'warehouseopen', 1);

        // print ' <a href="'.DOL_URL_ROOT.'/product/stock/card.php?action=create&amp;backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$object->id.'&action=create').'">';

        // print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter un entrepôt"></span>';

        // print '</a>';

        // print '</td>';

        // print '</tr>';



        // // Propriétaire

        // print '<tr><td class=titlefieldcreate">Propriétaire</td><td>';

        // print $formmateriel->selectProprietaires($materiel->fk_proprietaire, "idproprietaire", '', 1);

        // print ' <a href="'.DOL_URL_ROOT.'/admin/dict.php?id=56">';

        // print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter un propriétaire"></span>';

        // print '</a>';

        // print '</td>';

        // print '</tr>';



        // print '</table>';



        // dol_fiche_end();



        // print '<div class="center">';

        // print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';

        // print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

        // print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';

        // print '</div>';

        // print '</form>';

    }

    // Fiche en mode visu

    else {



        llxHeader("", "Donateur - ".$donateur->ref);

        $head = donateur_prepare_head($materiel);

        $titre = 'Donateur';

        $picto = ('donateur');

        talm_fiche_head($head, 'card', $titre, -1, $picto);



        $linkback = '<a href="'.DOL_URL_ROOT.'/custom/recufiscal/donateur/list.php/">Retour à la liste</a>';



        talm_banner_tab($donateur, 'id', $linkback, 1, 'rowid');



        print '<div class="fichecenter">';

        print '<div class="fichehalfleft">';

        print '<div class="underbanner clearboth"></div>';

        print '<table class="border tableforfield" width="100%">';



        // Prénom

        print '<tr><td class="titlefield">';

        print $form->editfieldkey("Prénom", 'prenom', $donateur->prenom, $donateur, $usercancreate);

        print '</td><td colspan="3">';

        print $form->editfieldval("Prénom", 'prenom', $donateur->prenom, $donateur, $usercancreate);

        print '</td></tr>';



        // Nom

        print '<tr><td class="titlefield">';

        print $form->editfieldkey("Nom", 'nom', $donateur->nom, $donateur, $usercancreate);

        print '</td><td colspan="3">';

        print $form->editfieldval("Nom", 'nom', $donateur->nom, $donateur, $usercancreate);

        print '</td></tr>';



        // Société

        print '<tr><td class="titlefield">';

        print $form->editfieldkey("Société", 'societe', $donateur->societe, $donateur, $usercancreate);

        print '</td><td colspan="3">';

        print $form->editfieldval("Société", 'societe', $donateur->societe, $donateur, $usercancreate);

        print '</td></tr>';



        // Adresse

        print '<tr><td class="titlefield">';

        print $form->editfieldkey("Adresse", 'address', $donateur->address, $donateur, $usercancreate);

        print '</td><td colspan="3">';

        print $form->editfieldval("Adresse", 'address', $donateur->address, $donateur, $usercancreate);

        print '</td></tr>';



        // Code postal

        print '<tr><td class="titlefield">';

        print $form->editfieldkey("Code postal", 'zipcode', $donateur->zip, $donateur, $usercancreate);

        print '</td><td colspan="3">';

        print $form->editfieldval("Code postal", 'zipcode', $donateur->zip, $donateur, $usercancreate);

        print '</td></tr>';



        // Ville

        print '<tr><td class="titlefield">';

        print $form->editfieldkey("Ville", 'town', $donateur->town, $donateur, $usercancreate);

        print '</td><td colspan="3">';

        print $form->editfieldval("Ville", 'town', $donateur->town, $donateur, $usercancreate);

        print '</td></tr>';



        // E-mail

        print '<tr><td class="titlefield">';

        print $form->editfieldkey("E-mail", 'email', $donateur->email, $donateur, $usercancreate);

        print '</td><td colspan="3">';

        print $form->editfieldval("E-mail", 'email', $donateur->email, $donateur, $usercancreate);

        print '</td></tr>';



        // Phone

        print '<tr><td class="titlefield">';

        print $form->editfieldkey("Téléphone", 'phone', $donateur->phone, $donateur, $usercancreate);

        print '</td><td colspan="3">';

        print $form->editfieldval("Téléphone", 'phone', $donateur->phone, $donateur, $usercancreate);

        print '</td></tr>';





        // Notes supplémentaires

        print '<tr><td class="titlefield">';

        print "Notes";

        print '</td><td colspan="3">';

        print($donateur->notes ? $donateur->notes : '<i>Pas de notes</i>');

        print '</td></tr>';



        print '</table>';

        print '</div>';

        print '<div class="fichehalfright"><div class="ficheaddleft">';

        print '<div class="underbanner clearboth"></div>';

        print '<table class="border tableforfield" width="100%">';



        // Nombre de reçu fiscaux

        print '<tr><td class="titlefield">';

        print "Nombre de reçus fiscaux";

        print '</td><td colspan="3">';

        print $donateur->donation_count;

        print '</td></tr>';





        // List of donation lines with total value

        print '<div class="div-table-responsive">';

        print '<table class="tagtable liste">'."\n";

        print '<tr class="liste_titre">';



        print '<td class="left">Description</td>';

        print '<td class="right">Montant</td>';



        $donateur->getDonationLines();

        $total = 0;

        if (empty($donateur->donation_lines))

        {

            print '<tr class="oddeven">';



            print '<td class="tdoverflowmax200">';

            print '<span class="opacitymedium">Aucun matériel renseigné</span>';

            print "</td>\n";



            print '<td class="tdoverflowmax200"></td>';

            print '</tr>';



        }

        else

        {

            foreach($donateur->donation_lines as $line)

            {

                $total += $line->valeur;

                print '<tr class="oddeven">';

    

                print '<td class="tdoverflowmax200">';

                print $line->description;

                print "</td>\n";

    

                print '<td class="tdoverflowmax200 right">';

                print price($line->valeur, 1, '', 0, -1, -1, $conf->currency);

                print "</td>\n";

    

                print '</tr>';

            }

        }



        // Print lines total value

        print '<tr class="oddeven">';



        print '<td class="tdoverflowmax200 right">';

        print '<span class="opacitymedium">Montant total des matériel</span>';

        print "</td>\n";



        print '<td class="tdoverflowmax200 right amountpaymentcomplete">';

        print price($total, 1, '', 0, -1, -1, $conf->currency);

        print "</td>\n";



        print '</tr>';



        print '</table>';

        print '</div>';



        print '</table>';

        print '</div>';



        print '</div></div>';

        print '<div style="clear:both"></div>';



        dol_fiche_end();

    }

}





// Confirm delete donor

if ((!empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile))) {

    print  $form->formconfirm("card.php?id=".$donateur->id, 'Supprimer le donateur', 'Êtes vous sûr de vouloir supprimer ce donateur ?', "confirm_delete", '', 0, "action-delete");

}





/* ************************************************************************** */

/*                                                                            */

/* Barre d'action                                                             */

/*                                                                            */

/* ************************************************************************** */

if ($action != 'create' && $action != 'edit') {

    print "\n".'<div class="tabsAction">'."\n";



    if (empty($reshook)) {



        if ($usercancreate) {

            print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit&amp;id='.$donateur->id.'">'.$langs->trans("Modify").'</a>';

        }



        if ($usercandelete) {

            if ($donateur->active) {

                print '<span id="action-delete" class="butActionDelete" >Supprimer</span>';

            }

        }

    }



    print "\n</div>\n";

}



// End of page

llxFooter();

$db->close();