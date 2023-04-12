<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

@include "../../../main.inc.php";

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formpreinventaire.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/preinventaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));

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
// $id = GETPOST('id', 'int');
// $type_materiel = GETPOST('idtypemateriel', 'alpha'); //id du type de matériel
// $etat = GETPOST('etat', 'alpha'); //etat du matériel (fk)

$action = (GETPOST('action', 'alpha') ? GETPOST('action', 'alpha') : 'create');
$cancel = GETPOST('cancel', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$socid = GETPOST('socid', 'int');

$form = new Form($db);
$formpreinventaire = new FormPreinventaire($db);

if ($id > 0) {
    //$result = $materiel->fetch($id);
    if (!$result) exit;
}

/*
 * Actions
 */
if ($cancel) $action = '';
 

// Add a materiel
if ($action == 'add' && $usercancreate) {
    $error = 0;
    // ADD TO PREINVENTAIRE
}







/*
 * View
 */
if ($action == 'create') {
    if (!$usercancreate) accessforbidden();

    llxHeader("", $langs->trans("Pré-inventaire"));
    //WYSIWYG Editor
    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

    print '<script src="./js/card.js"></script>';
    print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="add">';
    print '<input type="hidden" name="type" value="'.$type.'">'."\n";
    print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
    $picto = 'materiel';
    $title = 'Nouveau Matériel - Pré-inventaire';
    print talm_load_fiche_titre($title, '', $picto);

    dol_fiche_head('');

    print '<table class="border centpercent">';

    print '<tr>';
    print '</td></tr>';

    // Type de source
    print '<tr><td class="titlefieldcreate fieldrequired">Type de source</td><td>';
    print $form->selectarray('sourcetypeid', getSourceTypeArray(), '', 1);
    print ' <a href="'.DOL_URL_ROOT.'/custom/materiel/typemat/card.php'.'">';
    print '</a>';
    print '</td>';
    print '</tr>';

    // Source
    print '<tr><td class="titlefieldcreate fieldrequired">Source</td><td>';
    print '<div id="source_select_wrapper">';
    print $form->selectarray('sourceid', array(), '', '-- Sélectionnez un type de source --', 0, 0, 'style="min-width:200px;"', 0, 0, 1, '', '', 1);
    print '</div>';
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




/* ************************************************************************** */
/*                                                                            */
/* Barre d'action                                                             */
/*                                                                            */
/* ************************************************************************** */
// Ajouter plus tard

// End of page
llxFooter();
$db->close();