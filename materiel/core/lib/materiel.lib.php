<?php
/* Copyright (C) 2006-2015  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2007       Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2009-2010  Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2015       Raphaël Doursenaud      <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2015-2016	Marcos García			<marcosgdf@gmail.com>
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
 * or see https://www.gnu.org/
 */

/**
 *	\file       htdocs/core/lib/product.lib.php
 *	\brief      Ensemble de fonctions de base pour le module produit et service
 * 	\ingroup	product
 */

/**
 * Prepare array with list of tabs
 *
 * @param   Materiel	$object		Object related to tabs
 * @return  array				Array of tabs to show
 */
function materiel_prepare_head($object)
{
	global $db, $langs, $conf, $user;

	$label = 'Matériel';

	$h = 0;
	$head = array();

	$head[$h][0] = DOL_URL_ROOT."/custom/materiel/card.php?id=".$object->id;
	$head[$h][1] = $label;
	$head[$h][2] = 'card';
	$h++;

	$head[$h][0] = DOL_URL_ROOT."/custom/materiel/materiel_entretien.php?id=".$object->id;
	$head[$h][1] = 'Entretiens';
	$head[$h][2] = 'Entretiens';
	$h++;

	$head[$h][0] = DOL_URL_ROOT."/custom/materiel/document.php?id=".$object->id;
	$head[$h][1] = 'Documents';
	$head[$h][2] = 'documents';
	$h++;

	$head[$h][0] = DOL_URL_ROOT."/custom/materiel/agenda.php?id=".$object->id;
	$head[$h][1] = 'Historique';
	$head[$h][2] = 'agenda';
	$h++;

	return $head;
}


function getListEntrepot() {

    global $db;
    $array_entrepot = array();

    $sql = "SELECT w.rowid, w.ref, w.lieu";
    $sql.= " FROM ".MAIN_DB_PREFIX."entrepot as w";

    $resql = $db->query($sql);

    $num = $db->num_rows($resql);
	$i = 0;
	while ($i < $num)
	{
		$obj = $db->fetch_object($resql);
		$array_entrepot[$obj->rowid] = $obj->ref;
		$i++;
	}
	return $array_entrepot;
}

function getDict($dict, $show_indicatif = 0) {
    global $db;

    $r_dict = array();
    $field_name = '';

    switch ($dict) {
        case 'etat_materiel':
            $field_name = 'etat';
        case 'origine_materiel':
            $field_name = 'origine';
        case 'type_materiel':
            $field_name = 'type';
        default:
            return -1;
    }

    $sql = "SELECT rowid, indicatif";
    $sql .= ", ".$field_name;
    $sql.= " FROM ".MAIN_DB_PREFIX.$dict;
    $sql.= " WHERE active = 1";

    $resql = $db->query($sql);
    $num = $db->num_rows($resql);

	$i = 0;
	while ($i < $num)
	{
		$obj = $db->fetch_object($resql);
		$r_dict[$obj->rowid] = $obj->rowid;
		if ($show_indicatif) $r_dict[$obj->rowid]['indicatif'] = $obj->indicatif;
		$i++;
	}
	return $r_dict;
}

function getEtatEtiquetteDict($with_badge = 0) {
    global $db;

    $etat_dict = array();
    $sql = "SELECT rowid, etat, badge_code";
    $sql.= " FROM ".MAIN_DB_PREFIX."c_etat_etiquette";
    $sql.= " WHERE active = 1";

    $resql = $db->query($sql);
    $num = $db->num_rows($resql);

		$i = 0;
		while ($i < $num)
		{
			$obj = $db->fetch_object($resql);
			$etat_dict[$obj->rowid] = $obj->etat;
			if ($with_badge) $etat_dict[$obj->rowid]['badge_code'] = $obj->badge_code;
			$i++;
		}
		return $etat_dict;
}


?>
