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
 * \file    lib/organisation_evenement.lib.php
 * \ingroup organisation
 * \brief   Library files with common functions for Evenement
 */

/**
 * Prepare array of tabs for Evenement
 *
 * @param	Evenement	$object		Evenement
 * @return 	array					Array of tabs
 */
function evenementPrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("organisation@organisation");

	$showtabofpagecontact = 1;
	$showtabofpagenote = 1;
	$showtabofpagedocument = 1;
	$showtabofpageagenda = 1;

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/organisation/evenement_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;

	$head[$h][0] = dol_buildpath("/organisation/evenement_organisation.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Organisation");
	$head[$h][2] = 'Organisation';
	$h++;

	$head[$h][0] = dol_buildpath("/organisation/evenement_poste.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Postes");
	$head[$h][2] = 'Postes';
	$h++;

/*	$head[$h][0] = dol_buildpath("/organisation/evenement_repetition.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Repetition");
	$head[$h][2] = 'Repetition';
	$h++;*/

	$head[$h][0] = dol_buildpath('/organisation/evenement_autorisations.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans('Autorisations');
	$head[$h][2] = 'Autorisations';
	$h++;

	if ($showtabofpagenote) {
		if (isset($object->fields['note_public']) || isset($object->fields['note_private'])) {
			$nbNote = 0;
			if (!empty($object->note_private)) {
				$nbNote++;
			}
			if (!empty($object->note_public)) {
				$nbNote++;
			}
			$head[$h][0] = dol_buildpath('/organisation/evenement_note.php', 1).'?id='.$object->id;
			$head[$h][1] = $langs->trans('Notes');
			if ($nbNote > 0) {
				$head[$h][1] .= (empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER) ? '<span class="badge marginleftonlyshort">'.$nbNote.'</span>' : '');
			}
			$head[$h][2] = 'note';
			$h++;
		}
	}

	if ($showtabofpagedocument) {
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
		$upload_dir = $conf->organisation->dir_output."/evenement/".dol_sanitizeFileName($object->ref);
		$nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
		$nbLinks = Link::count($db, $object->element, $object->id);
		$head[$h][0] = dol_buildpath("/organisation/evenement_document.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans('Documents');
		if (($nbFiles + $nbLinks) > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">'.($nbFiles + $nbLinks).'</span>';
		}
		$head[$h][2] = 'document';
		$h++;
	}

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@organisation:/organisation/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@organisation:/organisation/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'evenement@organisation');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'evenement@organisation', 'remove');

	return $head;
}
