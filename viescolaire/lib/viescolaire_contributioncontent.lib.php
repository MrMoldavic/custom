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
 * \file    lib/viescolaire_contributioncontent.lib.php
 * \ingroup viescolaire
 * \brief   Library files with common functions for ContributionContent
 */

/**
 * Prepare array of tabs for ContributionContent
 *
 * @param	ContributionContent	$object		ContributionContent
 * @return 	array					Array of tabs
 */
function contributioncontentPrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("viescolaire@viescolaire");

	$showtabofpagecontact = 0;
	$showtabofpagenote = 0;
	$showtabofpagedocument = 0;
	$showtabofpageagenda = 0;

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/viescolaire/contributioncontent_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'contributioncontent@viescolaire');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'contributioncontent@viescolaire', 'remove');

	return $head;
}
