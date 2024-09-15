<?php
/* Copyright (C) 2022 Baptiste Diodati
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
 * \file    lib/scolarite_cles.lib.php
 * \ingroup scolarite
 * \brief   Library files with common functions for Cles
 */

/**
 * Prepare array of tabs for Cles
 *
 * @param	Cles	$object		Cles
 * @return 	array					Array of tabs
 */
function clesPrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("scolarite@scolarite");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/scolarite/cles_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;


	complete_head_from_modules($conf, $langs, $object, $head, $h, 'cles@scolarite');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'cles@scolarite', 'remove');

	return $head;
}
