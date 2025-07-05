<?php
/* Copyright (C) 2025 Baptiste Diodati <baptiste.diodati@tousalamusique.com>
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
 * \file    lbcsellerurlmanagement/lib/lbcsellerurlmanagement.lib.php
 * \ingroup lbcsellerurlmanagement
 * \brief   Library files with common functions for LbcSellerUrlManagement
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function lbcsellerurlmanagementAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("lbcsellerurlmanagement@lbcsellerurlmanagement");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/lbcsellerurlmanagement/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/lbcsellerurlmanagement/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/lbcsellerurlmanagement/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	$head[$h][0] = dol_buildpath("/lbcsellerurlmanagement/admin/about.php", 1);
	$head[$h][1] = $langs->trans("Dictionaries");
	$head[$h][2] = 'dictionaries';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@lbcsellerurlmanagement:/lbcsellerurlmanagement/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@lbcsellerurlmanagement:/lbcsellerurlmanagement/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'lbcsellerurlmanagement@lbcsellerurlmanagement');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'lbcsellerurlmanagement@lbcsellerurlmanagement', 'remove');

	return $head;
}
