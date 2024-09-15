<?php
/* Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    lib/scolarite_dispositif.lib.php
 * \ingroup scolarite
 * \brief   Library files with common functions for Dispositif
 */

/**
 * Prepare array of tabs for Dispositif
 *
 * @param	Dispositif	$object		Dispositif
 * @return 	array					Array of tabs
 */
function dispositifPrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("scolarite@scolarite");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/scolarite/dispositif_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'dispositif@scolarite');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'dispositif@scolarite', 'remove');

	return $head;
}
