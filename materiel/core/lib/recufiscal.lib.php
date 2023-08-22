<?php



function getDonateurArray() {

    global $db;

    $donateur_array = array();

    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."donateur";

    $sql .= " WHERE active = 1";

    $resql = $db->query($sql);



    $i = 0;

    $num = $db->num_rows($resql);

    while ($i < $num)

    {

        $obj = $db->fetch_object($resql);

        $label = ($obj->nom ? $obj->nom.' '.$obj->prenom : $obj->societe);

        $donateur_array[$obj->rowid] = $label;

        $i++;

    }


    return $donateur_array;

}


/**

 * Prepare array with list of tabs

 *

 * @param   Source	$object		Object related to tabs

 * @return  array				Array of tabs to show

 */

function donateur_prepare_head($object)

{
    global $db, $langs, $conf, $user;



    $label = 'Donateur';



    $h = 0;

    $head = array();



    $head[$h][0] = DOL_URL_ROOT."/custom/recufiscal/donateur/card.php?id=".$object->id;

    $head[$h][1] = $label;

    $head[$h][2] = 'card';

    $h++;



    $head[$h][0] = DOL_URL_ROOT."/custom/recufiscal/donateur/document.php?id=".$object->id;

    $head[$h][1] = 'Documents';

    $head[$h][2] = 'documents';

    $h++;

    

    return $head;

}



function recufiscal_prepare_head($object)

{

    global $db, $langs, $conf, $user;



    $label = 'Reçu Fiscal';



    $h = 0;

    $head = array();



    $head[$h][0] = DOL_URL_ROOT."/custom/recufiscal/card.php?id=".$object->id;

    $head[$h][1] = $label;

    $head[$h][2] = 'card';

    $h++;



    $head[$h][0] = DOL_URL_ROOT."/custom/recufiscal/document.php?id=".$object->id;

    $head[$h][1] = 'Documents';

    $head[$h][2] = 'documents';

    $h++;

    

    return $head;

}



function getTypeArray()
{

    $type_array = array();
    $type_array[RecuFiscal::TYPE_DON_NATURE] = 'Don en nature';
    $type_array[RecuFiscal::TYPE_ABANDON_DE_FRAIS] = 'Abandon de frais';

    return $type_array;

}