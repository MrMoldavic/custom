<?php



/**

 * Prepare array with list of tabs

 *

 * @param   Source	$object		Object related to tabs

 * @return  array				Array of tabs to show

 */

function source_prepare_head($object)
{

    global $db, $langs, $conf, $user;


    $label = 'Source';



    $h = 0;

    $head = array();



    $head[$h][0] = DOL_URL_ROOT."/custom/materiel/preinventaire/source/card.php?id=".$object->id;

    $head[$h][1] = $label;

    $head[$h][2] = 'card';

    $h++;



    $head[$h][0] = DOL_URL_ROOT."/custom/materiel/preinventaire/source/document.php?id=".$object->id;

    $head[$h][1] = 'Documents';

    $head[$h][2] = 'documents';

    $h++;





    return $head;

}