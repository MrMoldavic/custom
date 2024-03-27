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
 * \file    lib/organisation_engagement.lib.php
 * \ingroup organisation
 * \brief   Library files with common functions for Engagement
 */

/**
 * Prepare array of tabs for Engagement
 *
 * @param	Engagement	$object		Engagement
 * @return 	array					Array of tabs
 */
function engagementPrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("organisation@organisation");

	$showtabofpagecontact = 1;
	$showtabofpagenote = 1;
	$showtabofpagedocument = 1;
	$showtabofpageagenda = 1;

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/organisation/engagement_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'engagement@organisation');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'engagement@organisation', 'remove');

	return $head;
}
