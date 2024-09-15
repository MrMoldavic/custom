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
 * \file    lib/viescolaire_eleve.lib.php
 * \ingroup viescolaire
 * \brief   Library files with common functions for Eleve
 */

/**
 * Prepare array of tabs for Eleve
 *
 * @param	Eleve	$object		Eleve
 * @return 	array					Array of tabs
 */
function elevePrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("viescolaire@viescolaire");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/viescolaire/eleve_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;

	$head[$h][0] = dol_buildpath("/viescolaire/eleve_absence.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Absence");
	$head[$h][2] = 'Absence';
	$h++;

	$head[$h][0] = dol_buildpath("/viescolaire/eleve_materiel.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Materiel");
	$head[$h][2] = 'Materiel';
	$h++;

	$head[$h][0] = dol_buildpath("/viescolaire/eleve_appreciation.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Appréciation");
	$head[$h][2] = 'Appréciation';
	$h++;

	if (isset($object->fields['note_public']) || isset($object->fields['note_private'])) {
		$nbNote = 0;
		if (!empty($object->note_private)) {
			$nbNote++;
		}
		if (!empty($object->note_public)) {
			$nbNote++;
		}
		$head[$h][0] = dol_buildpath('/viescolaire/eleve_note.php', 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans('Notes');
		if ($nbNote > 0) {
			$head[$h][1] .= (empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER) ? '<span class="badge marginleftonlyshort">'.$nbNote.'</span>' : '');
		}
		$head[$h][2] = 'note';
		$h++;
	}

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'eleve@viescolaire');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'eleve@viescolaire', 'remove');

	return $head;
}
