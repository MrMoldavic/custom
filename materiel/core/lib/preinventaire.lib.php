<?php

function getSourceTypeArray($addtablename = 1) {
    global $db;
    $source_array = array();

    $sql = "SELECT rowid, type, table_name FROM ".MAIN_DB_PREFIX."c_type_source";
    $sql .= " WHERE active = 1";
    $resql = $db->query($sql);
    $num = $db->num_rows($resql);
    while ($i < $num) {
        $obj = $db->fetch_object($resql);
        if (!$addtablename) $source_array[$obj->rowid] = $obj->type;
        else {
            $source_array[$obj->rowid]['type'] = $obj->type;
            if ($addtablename) $source_array[$obj->rowid]['tablename'] = $obj->table_name;
        }
        $i++;
    }
    return $source_array;
}