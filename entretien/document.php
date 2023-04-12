<?php

/* Uncomment this section to diplay all errors and warnings */
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
/* ---------------------------------------------------------*/

// Load Dolibarr environment
require '../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/materiel.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/entretien.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/exploitation.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formentretien.class.php';

if (!empty($conf->global->PRODUIT_PDF_MERGE_PROPAL))
	require_once DOL_DOCUMENT_ROOT.'/product/class/propalmergepdfproduct.class.php';

$id     = GETPOST('id', 'int');
$ref    = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$formentretien = new FormEntretien($db);
$object = new Entretien($db);
if ($id > 0 || !empty($ref))
{
    $result = $object->fetch($id);
    if (!empty($conf->entretien->enabled)) $upload_dir = $conf->entretien->multidir_output[1].'/'.get_exdir(0, 0, 0, 1, $object, 'entretien');
}
$modulepart = 'entretien';

// Get parameters
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
if (!$sortorder) $sortorder = "ASC";
if (!$sortfield) $sortfield = "position_name";

$usercanread = ($user->rights->entretien->read);
$usercancreate = ($user->rights->entretien->create);
$usercandelete = ($user->rights->entretien->delete);
$permissiontoadd = ($user->rights->entretien->create);
if (!$usercanread) accessforbidden();

/*
 * Actions
 */
$parameters = array('id'=>$id);

// Action submit/delete file/link
include_once DOL_DOCUMENT_ROOT.'/core/actions_linkedfiles.inc.php';

/*
 *	View
 */
$form = new Form($db);
llxHeader('', 'Entretien - Documents');
if ($object->id)
{
	$head = entretien_prepare_head($object);
	$titre = '$langs->trans("CardProduct".$object->type)';
	$picto = ('entretien');
	
    talm_fiche_head($head, 'documents', $titre, -1, $picto);
	
    // Build file list
	$filearray = dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview.*\.png)$', $sortfield, (strtolower($sortorder) == 'desc' ?SORT_DESC:SORT_ASC), 1);
	$totalsize = 0;
	foreach ($filearray as $key => $file)
	{
		$totalsize += $file['size'];
	}
    $shownav = 1;
    $linkback = '<a href="'.DOL_URL_ROOT.'/custom/entretien/list.php/">Retour à la liste</a>';
    talm_banner_tab($object, 'ref', $linkback, 1, 'ref');
    
    print '<div class="fichecenter">';
    print '<div class="underbanner clearboth"></div>';
    print '<table class="border tableforfield centpercent">';
    print '<tr><td class="titlefield">'.$langs->trans("NbOfAttachedFiles").'</td><td colspan="3">'.count($filearray).'</td></tr>';
    print '<tr><td>'.$langs->trans("TotalSizeOfAttachedFiles").'</td><td colspan="3">'.dol_print_size($totalsize, 1, 1).'</td></tr>';
    print '</table>';
    print '</div>';
    print '<div style="clear:both"></div>';
    
    dol_fiche_end();
    $param = '&id='.$object->id;
    include_once DOL_DOCUMENT_ROOT.'/core/tpl/document_actions_post_headers.tpl.php';
}
else
{
	print 'Erreur';
}
// End of page
llxFooter();
$db->close();