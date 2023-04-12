<?php


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

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formkit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/materiel.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/kit.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/canvas.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/genericobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
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

$action = (GETPOST('action', 'alpha') ? GETPOST('action', 'alpha') : 'view');
$cancel = GETPOST('cancel', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$socid = GETPOST('socid', 'int');

$id = GETPOST('id', 'int');

/*
AJOUTER DONNÉES
*/


if (!empty($user->socid)) $socid = $user->socid;


$form = new Form($db);
$formfile = new FormFile($db);
$formproduct = new FormProduct($db);
$formmateriel = new FormMateriel($db);
$kit = new Kit($db);
$formkit = new FormKit($db);

/*
 * Actions
 */

if ($id > 0)
{
    $result = $kit->fetch($id);
    if (!$result) {
        setEventMessages('Impossible de récupérer les données du kit.', null, 'errors');
        header('Location: '.DOL_URL_ROOT.'/custom/kit/list.php');
        exit;
    }
} else {
    setEventMessages('Impossible de récupérer les données du kit.', null, 'errors');
    header('Location: '.DOL_URL_ROOT.'/custom/kit/list.php');
}


$usercanread = ($user->rights->kit->read);
$usercancreate = ($user->rights->kit->create);
$usercandelete = ($user->rights->kit->delete);

if (!$usercanread) {
    accessforbidden();
}




/*
 * View
 */
llxHeader("", "Kit - Historique");

$head = kit_prepare_head($kit);
$titre = 'Kit - Historique';
$picto = ('kit');
talm_fiche_head($head, 'historique', $titre, -1, $picto);

$linkback = '<a href="'.DOL_URL_ROOT.'/custom/kit/list.php/">Retour à la liste</a>';
talm_banner_tab($kit, 'id', $linkback, 1, 'rowid');

print '<div class="fichecenter">';
print '<div class="fichehalfleft">';

print '<div class="underbanner clearboth"></div>';
dol_print_object_info($kit, 1);
print '</div>';
print '</div>';
print '<div style="clear:both"></div>';

dol_fiche_end();



print '<br>';
print_titre('Historique des modifications du kit');
print '<div class="div-table-responsive">';
	print '<table class="tagtable liste">'."\n";
	print '<tr class="liste_titre">';
	    print '<td>Date</td>';
	    print '<td>Utilisateur</td>';
	    print '<td>Évênement</td>';
	    print '<td>Matériel lié</td>';
	print '</tr>';

	if (!$kit->active) { // Affichage de la date de suppression du kit
			print '<tr class="oddeven">';
				print '<td class="tdoverflowmax200">';
				print date('d/m/Y H:i', $kit->date_suppression);
				print '</td>';
				print '<td class="tdoverflowmax200">';
				print $kit->user_suppression->getNomUrl(1, '', 0, 0, 0);
				print '</td>';
				print '<td class="tdoverflowmax200">';
				print 'Suppression du kit';
				print '</td>';
				print '<td class="tdoverflowmax200">';
				print '</td>';
			print '</tr>';
	}

	$history_data = $formkit->kitHistory($kit->id);
	foreach ($history_data as $history_row) {
        $envent_date = (array_key_exists ('date_ajout', $history_row) ? $history_row['date_ajout'] : $history_row['date_suppression']);
        $event = (array_key_exists ('date_ajout', $history_row) ? 'Ajout d\'un matériel' : 'Suppression d\'un matériel');
        $event_user_id = (array_key_exists ('date_ajout', $history_row) ? $history_row['fk_user_author'] : $history_row['fk_user_delete']); // Si le matériel est supprimé, l'id de l'utilisateur sera l'id de celui qui l'a supprimé
        $user = new User($db);
        $user->fetch($event_user_id);
        $materiel = new Materiel($db);
        $materiel->fetch($history_row['fk_materiel']);
    	print '<tr class="oddeven">';
    		print '<td class="tdoverflowmax200">';
    		print date('d/m/Y H:i', strtotime($envent_date));
    		print '</td>';
    		print '<td class="tdoverflowmax200">';
    		print $user->getNomUrl(1, '', 0, 0, 0);
    		print '</td>';
    		print '<td class="tdoverflowmax200">';
    		print $event;
    		print '</td>';
    		print '<td class="tdoverflowmax200">';
    		print $materiel->getNomUrl();
    		print '</td>';
    	print '</tr>';
	}

	print '<tr class="oddeven">';
		print '<td class="tdoverflowmax200">';
		print date('d/m/Y H:i', $kit->date_creation);
		print '</td>';
		print '<td class="tdoverflowmax200">';
		print $kit->user_creation->getNomUrl(1, '', 0, 0, 0);
		print '</td>';
		print '<td class="tdoverflowmax200">';
		print 'Création du kit';
		print '</td>';
		print '<td class="tdoverflowmax200">';
		print '</td>';
	print '</tr>';


	print '</table>';
print '</div>';




// End of page
llxFooter();
$db->close();
