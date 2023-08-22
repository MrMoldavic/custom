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
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

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


llxHeader('', 'Matériel - Entretiens');



if ($object->id)
{
	$head = materiel_prepare_head($object);
	$titre = '$langs->trans("CardProduct".$object->type)';
    $picto = 'materiel';

	talm_fiche_head($head, 'Entretiens', $titre, -1, $picto);

    $shownav = 1;

    $linkback = '<a href="'.DOL_URL_ROOT.'/custom/materiel/list.php/">Retour à la liste</a>';
    talm_banner_tab($object, 'id', $linkback, 1, 'rowid');

    print '<div class="fichecenter">';

    // print '<div class="underbanner clearboth"></div>';

    print '<h3>Liste des Entretiens en cours ou connus pour ce matériel : </h3>';

    $sqlEntretiens = "SELECT * FROM ".MAIN_DB_PREFIX."entretien WHERE fk_materiel=".$object->id." ORDER BY rowid DESC";
    $resqlEntretiens = $db->query($sqlEntretiens);

    if($resqlEntretiens->num_rows == 0) print "Aucun entretien connu pour ce matériel";

    foreach($resqlEntretiens as $value)
    {
        $entretien = new Entretien($db);
        $entretien_id = isMaterielInEntretien($object->id);
        if ($entretien_id) {
            $entretien->fetch($entretien_id);
        }
       

        print '<div class="entretien-accordion'.($entretien_id == $value['rowid'] ? '-opened' : '').'">';
       
        print '<h3><span class="badge badge-status'.($entretien_id == $value['rowid'] ? '4' : '3').' badge-status">'.($value['active'] == 1 ? 'Entretien en cours '.$entretien->getNomUrl() : 'Entretien ENT-'.$value['rowid'].' Terminé le : '.date('d/m/Y à H:i', strtotime($value['suppression_timestamp']))).'</span> <span class="badge badge-status8 badge-status"> Sujet : '.$value['description'].'</span></h3>';
    
        print '<table class="tagtable liste">';
        print '<tbody>';
    
        print '<tr class="liste_titre">
        <th class="wrapcolumntitle liste_titre">Message de suivi</th>
        <th class="wrapcolumntitle liste_titre">Envoyé le</th>
        <th class="wrapcolumntitle liste_titre">Par</th>
        </tr>';

        $sqlSuivi = "SELECT * FROM ".MAIN_DB_PREFIX."entretien_suivi WHERE fk_entretien=".$value['rowid']." ORDER BY rowid DESC";
        $resqlSuivi = $db->query($sqlSuivi);

        $entretien->fetch($value['rowid']);
        print '<tr class="oddeven">';
        print '<td>'.$entretien->getNomUrl().'</td>';
        print '</tr>';
        
        foreach($resqlSuivi as $val)
        {
            $sqlUser = "SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid = ".$val['fk_user_author'];
            $resqlUser = $db->query($sqlUser);
            $objUser = $db->fetch_object($resqlUser);

            

            print '<tr class="oddeven">';
            print '<td>'.$val['description'].'</td>';
            print '<td>'.date('d/m/Y à H:i', strtotime($val['tms'])).'</td>';
            print '<td>'.$objUser->firstname.' '.$objUser->lastname.'</td>';
            print '</tr>';
        }

        print '</tbody>';
        print '</table>';
    
      
        print '</div>';
      
    }
    print '</div>';
    
    print '<div style="clear:both"></div>';

    dol_fiche_end();


    print '<script>
    $( ".entretien-accordion" ).accordion({
        collapsible: true,
        active: 2,
    });
    </script>';

 print '<script>
    $( ".entretien-accordion-opened" ).accordion({
        collapsible: true,
    });
    </script>';


}
else
{
	print 'Erreur';
}
// End of page
llxFooter();
$db->close();
