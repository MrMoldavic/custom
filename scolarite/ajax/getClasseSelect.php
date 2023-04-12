<?php
@include "../../../main.inc.php";

/*  SOURCE TYPE ID
 * 1 : Facture
 * 2 : Reçu fiscal
 * 3 : Emprunt
 */
$etablissementid = GETPOST('etablissementid', 'int');

$sql = "SELECT s.rowid,s.classe FROM ".MAIN_DB_PREFIX."classe as s WHERE fk_college =".$etablissementid;
$resql = $db->query($sql);
// $object = $db->fetch_object($resql);
$result = [];

foreach($resql as $val)
{
    $result[$val['rowid']]=$val['classe'];
}

print json_encode($result);
