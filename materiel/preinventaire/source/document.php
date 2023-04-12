<?php

// ini_set('display_errors', '1');

// ini_set('display_startup_errors', '1');

// error_reporting(E_ALL);



@include_once "../../../../main.inc.php";

require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/source.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/source.lib.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';



if (!empty($conf->global->PRODUIT_PDF_MERGE_PROPAL))

	require_once DOL_DOCUMENT_ROOT.'/product/class/propalmergepdfproduct.class.php';



// Load translation files required by the page

$langs->loadLangs(array('other', 'products'));



$id      = GETPOST('id', 'int');

$ref     = GETPOST('ref', 'alpha');

$action  = GETPOST('action', 'alpha');

$confirm = GETPOST('confirm', 'alpha');



$object = new Source($db);

if ($id > 0 || !empty($ref))

{

    $result = $object->fetch($id);



    if (!empty($conf->source->enabled)) $upload_dir = $conf->source->multidir_output[1].'/'.get_exdir(0, 0, 0, 1, $object, 'source');



}



$modulepart = 'source';





// Get parameters

$sortfield = GETPOST("sortfield", 'alpha');

$sortorder = GETPOST("sortorder", 'alpha');

if (!$sortorder) $sortorder = "ASC";

if (!$sortfield) $sortfield = "position_name";



$usercanread = ($user->rights->materiel->read);

$usercancreate = ($user->rights->materiel->create);

$usercandelete = ($user->rights->materiel->delete);



$permissiontoadd = $user->rights->materiel->create; // Used by the formfile script 



if (!$usercanread) {

    accessforbidden();

}



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





llxHeader('', 'Source - Documents');







if ($object->id)

{

	$head = source_prepare_head($object);

	$titre = '$langs->trans("CardProduct".$object->type)';

	$picto = ('source');





	talm_fiche_head($head, 'documents', $titre, -1, $picto);



	// Build file list

	$filearray = dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview.*\.png)$', $sortfield, (strtolower($sortorder) == 'desc' ?SORT_DESC:SORT_ASC), 1);



	$totalsize = 0;

	foreach ($filearray as $key => $file)

	{

		$totalsize += $file['size'];

	}





    $shownav = 1;



    $linkback = '<a href="'.DOL_URL_ROOT.'/custom/source/list.php/">Retour à la liste</a>';

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

