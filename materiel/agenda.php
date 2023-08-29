<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


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
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/entretien.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formkit.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/materiel.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/kit.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/entretien.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';


require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/canvas.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/genericobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/product/modules_product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';


// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));

// Security check
//if (! $user->rights->materiel->myobject->read) accessforbidden();
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0)
{
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();


/*Recupération données POST*/
$socid = GETPOST('socid', 'int');

$id = GETPOST('id', 'int');

//View mode
$mode = (GETPOST('mode', 'alpha') ? GETPOST('mode', 'alpha') : 'viewentretien');

/*
AJOUTER DONNÉES
*/
if (!empty($user->socid)) $socid = $user->socid;


$form = new Form($db);
$formfile = new FormFile($db);
$formproduct = new FormProduct($db);
$formmateriel = new FormMateriel($db);
$materiel = new Materiel($db);
$formkit = new FormKit($db);

/*
 * Actions
 */

if ($id > 0)
{
    $result = $materiel->fetch($id);
    if (!$result) {
        setEventMessages('Impossible de récupérer les données du matériel.', null, 'errors');
        header('Location: '.DOL_URL_ROOT.'/custom/materiel/materiel_list.php');
        exit;
    }
} else {
    setEventMessages('Impossible de récupérer les données du matériel.', null, 'errors');
    header('Location: '.DOL_URL_ROOT.'/custom/materiel/materiel_list.php');
}


$usercanread = ($user->rights->materiel->read);
$usercancreate = ($user->rights->materiel->create);
$usercandelete = ($user->rights->materiel->delete);

if (!$usercanread) accessforbidden();




/*
 * View
 */
llxHeader("", "Matériel - Historique");

$head = materiel_prepare_head($materiel);
$titre = 'Matériel - Historique';
$picto = ('materiel');
talm_fiche_head($head, 'agenda', $titre, -1, $picto);

$linkback = '<a href="'.DOL_URL_ROOT.'/custom/materiel/materiel_list.php/">Retour à la liste</a>';
talm_banner_tab($materiel, 'id', $linkback, 1, 'rowid');

print '<div class="fichecenter">';
print '<div class="fichehalfleft">';

print '<div class="underbanner clearboth"></div>';
dol_print_object_info($materiel, 1);
print '</div>';
print '</div>';
print '<div style="clear:both"></div>';

dol_fiche_end();

print '<br>';


$morehtmlright = dolGetButtonTitle('Entretiens', '', 'fa fa-tools paddingleft', $_SERVER["PHP_SELF"].'?mode=viewentretien&action=view&id='.$materiel->id, '', 1, array('morecss'=>'reposition '.($mode == 'viewentretien' ? 'btnTitleSelected' : '')));
$morehtmlright .= dolGetButtonTitle('Exploitations', '', 'fa fa-truck-loading paddingleft', $_SERVER["PHP_SELF"].'?mode=viewexploitation&action=view&id='.$materiel->id, '', 1, array('morecss'=>'reposition '.($mode == 'viewexploitation' ? 'btnTitleSelected' : '')));
print talm_load_fiche_titre('Historique', $morehtmlright, 'materiel', 0);

if ($mode == 'viewentretien') {
    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."entretien WHERE fk_materiel = " . $materiel->id;
    $sql .= " ORDER BY creation_timestamp DESC";
    $resql = $db->query($sql);
    $num = $db->num_rows($resql);
    $i = 0;
    if ($num < 1) {
        print 'Pas d\'entretien déclaré pour ce matériel.';
    }
    else {
        while ($i < $num)
        {
            $row = $db->fetch_object($resql);
            $entretien = new Entretien($db);
            $entretien->fetch($row->rowid);
            $historic = $entretien->getSuiviHistoric();
            print_titre($entretien->getNomUrl(0, '', 1) . ' : ' . $entretien->description);
            print '<div class="div-table-responsive">';
            print '<table class="tagtable liste">'."\n";
            print '<tr class="liste_titre">';
            print '<td>Utilisateur</td>';
            print '<td>Action</td>';
            print '<td class="right">Date</td>';
            print '</tr>';
            foreach($historic as $suivi_row) {   
                $user_author = new User($db);
                $user_author->fetch($suivi_row['fk_user']);
                print '<tr class="oddeven">';
                print '<td>';
                print $user_author->getNomUrl(1);
                print '</td>';
                print '<td>';
                print $suivi_row['description'];
                print '</td>';
                print '<td class="right">';
                print dol_print_date($suivi_row['date'], '%e %B %Y');
                print '</td></tr>';
            }   
            
            print '</table>';
            print '<br>';
            print '<br>';
            $i++;
        }
    }
} else {
    // Get exploitation data
    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."exploitation_suivi WHERE fk_materiel = " . $materiel->id;
    $sql .= " ORDER BY date_ajout DESC";
    $resql = $db->query($sql);
    $num = $db->num_rows($resql);
    $i = 0;

    // We will be grouping historic by exploitation ID
    $grouped_historic = array();

    if ($num < 1) {
        print 'Pas d\'exploitation déclarée pour ce matériel.';
    } else {
        while ($i < $num)
        {
            // Grouping rows by exploitation ID
            $suivi_row = $db->fetch_object($resql);
            $grouped_historic[$suivi_row->fk_exploitation][] = $suivi_row;    
            $i++;
        }
        foreach($grouped_historic as $exploitation_id=>$suivi_group) {   
            $exploitation = new Exploitation($db);
            $exploitation->fetch($exploitation_id);
            print_titre($exploitation->getNomUrl());
            print '<div class="div-table-responsive">';
            print '<table class="tagtable liste">'."\n";
            print '<tr class="liste_titre">';
            print '<td>Utilisateur</td>';
            print '<td>Action</td>';
            print '<td class="right">Date</td>';
            print '</tr>';
            foreach($suivi_group as $suivi_row) {
                $event = 'Unknown';
    
                if ($suivi_row->fk_localisation == 1 && $suivi_row->fk_etat == 1) $event = "À l'entrepôt";
                elseif ($suivi_row->fk_localisation == 1 && $suivi_row->fk_etat == 2) $event = "En expédition chez l'exploitant";
                elseif ($suivi_row->fk_localisation == 2 && $suivi_row->fk_etat == 1) $event = "Confirmation de la livraison chez l'exploitant";
                elseif ($suivi_row->fk_localisation == 2 && $suivi_row->fk_etat == 2) $event = "En cours de retour à l'entrepôt";

                $user_author = new User($db);
                $user_author->fetch($suivi_row->fk_user_author);
                print '<tr class="oddeven">';
                print '<td>';
                print $user_author->getNomUrl(1);
                print '</td>';
                print '<td>';
                print $event;
                print '</td>';
                print '<td class="right">';
                print dol_print_date($suivi_row->date_ajout, '%e %B %Y');
                print '</td></tr>';
            }
            
            
            print '</table>';
            print '<br>';
            print '<br>';

        }
    }
}




print '</div>';

// End of page
llxFooter();
$db->close();
