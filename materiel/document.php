<?php
/* Copyright (C) 2003-2007 Rodolphe Quiedeville  <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Marc Barilley / Ocebo <marc@ocebo.com>
 * Copyright (C) 2005-2012 Regis Houssin         <regis.houssin@inodbox.com>
 * Copyright (C) 2005      Simon TOSSER          <simon@kornog-computing.com>
 * Copyright (C) 2013      Florian Henry          <florian.henry@open-concept.pro>
 * Copyright (C) 2013      Cédric Salvador       <csalvador@gpcsolutions.fr>
 * Copyright (C) 2017      Ferran Marcet       	 <fmarcet@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *       \file       htdocs/product/document.php
 *       \ingroup    product
 *       \brief      Page des documents joints sur les produits
 */

//  ini_set('display_errors', '1');
//  ini_set('display_startup_errors', '1');
//  error_reporting(E_ALL);

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/materiel.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/entretien.class.php';

if (!empty($conf->global->PRODUIT_PDF_MERGE_PROPAL))
	require_once DOL_DOCUMENT_ROOT.'/product/class/propalmergepdfproduct.class.php';

// Load translation files required by the page
$langs->loadLangs(array('other', 'products'));

$id     = GETPOST('id', 'int');
$ref    = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');

$object = new Materiel($db);
if ($id > 0 || !empty($ref))
{
    $result = $object->fetch($id);

    if (!empty($conf->materiel->enabled)) $upload_dir = $conf->materiel->multidir_output[1].'/'.get_exdir(0, 0, 0, 1, $object, 'materiel');

}

$modulepart = 'materiel';


// Get parameters
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
if (!$sortorder) $sortorder = "ASC";
if (!$sortfield) $sortfield = "position_name";

$usercanread = ($user->rights->materiel->read);
$usercancreate = ($user->rights->materiel->create);
$usercandelete = ($user->rights->materiel->delete);
$permissiontoadd = $user->rights->materiel->create;

if (!$usercanread) accessforbidden();

/*
 *  Actions
 */

$parameters = array('id'=>$id);


// Action submit/delete file/link
include_once DOL_DOCUMENT_ROOT.'/core/actions_linkedfiles.inc.php';



/*
 *	View
 */

$form = new Form($db);


llxHeader('', 'Matériel - Documents');



if ($object->id)
{
	$head = materiel_prepare_head($object);
	$titre = '$langs->trans("CardProduct".$object->type)';
	$picto = ('materiel');

	talm_fiche_head($head, 'documents', $titre, -1, $picto);

	// Build file list
	$filearray = dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview.*\.png)$', $sortfield, (strtolower($sortorder) == 'desc' ?SORT_DESC:SORT_ASC), 1);

	$totalsize = 0;
	foreach ($filearray as $key => $file)
	{
		$totalsize += $file['size'];
	}


    $shownav = 1;

    $linkback = '<a href="'.DOL_URL_ROOT.'/custom/materiel/materiel_list.php/">Retour à la liste</a>';
    talm_banner_tab($object, 'id', $linkback, 1, 'rowid');

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
