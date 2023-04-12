<?php
/**
 * Page-level DocBlock
 * @package pagepackage
 */

/**
 * Prepare array with list of tabs
 *
 * @param   Materiel	$object		Object related to tabs
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

	$head[$h][0] = DOL_URL_ROOT."/custom/materiel/document.php?id=".$object->id;
	$head[$h][1] = 'Documents';
	$head[$h][2] = 'documents';
	$h++;

	return $head;
}
