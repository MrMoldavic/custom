<?php
/* Copyright (C) 2023 Baptiste Diodati <baptiste.diodati@gmail.com>
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
 * \file    lib/organisation_morceau.lib.php
 * \ingroup organisation
 * \brief   Library files with common functions for Morceau
 */

/**
 * Prepare array of tabs for Morceau
 *
 * @param	Morceau	$object		Morceau
 * @return 	array					Array of tabs
 */
function morceauPrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("organisation@organisation");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/organisation/morceau_card.php", 1).'?id='.$object->id;
	$head[$h][1] ='Fiche du morceau';
	$head[$h][2] = 'card';
	$h++;

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'morceau@organisation');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'morceau@organisation', 'remove');

	return $head;
}
