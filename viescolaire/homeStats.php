<?php


$dashboardlines = array();
require_once DOL_DOCUMENT_ROOT.'/core/class/workboardresponse.class.php';

// Appel de la fonction load_board pour afficher les stats de la classe
include_once DOL_DOCUMENT_ROOT.'/custom/viescolaire/class/eleve.class.php';
$board = new Eleve($db);
$dashboardlines[$board->element] = $board->load_board($user);


// Appel de la fonction load_board pour afficher les stats de la classe
include_once DOL_DOCUMENT_ROOT.'/custom/viescolaire/class/contribution.class.php';
$board = new Contribution($db);
$dashboardlines[$board->element.'_total'] = $board->load_board($user);


$object = new stdClass();
$parameters = array();
$action = '';
$reshook = $hookmanager->executeHooks(
	'addOpenElementsDashboardLine',
	$parameters,
	$object,
	$action
); // Note that $action and $object may have been modified by some hooks
if ($reshook == 0) {
	$dashboardlines = array_merge($dashboardlines, $hookmanager->resArray);
}

$dashboardgroup = array(
	'eleves' =>
		array(
			'groupName' => 'Eleves',
			'globalStatsKey' => 'eleves',
			'stats' =>
				array('eleve'),
		),
	'contribution' =>
		array(
			'groupName' => 'Contributions',
			'globalStatsKey' => 'contribution',
			'stats' =>
				array('contribution_total','contribution_actual'),
		),
);

$object = new stdClass();
$parameters = array(
	'dashboardgroup' => $dashboardgroup
);
$reshook = $hookmanager->executeHooks('addOpenElementsDashboardGroup', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook == 0) {
	$dashboardgroup = array_merge($dashboardgroup, $hookmanager->resArray);
}

// Calculate total nb of late
$totallate = $totaltodo = 0;

//Remove any invalid response
//load_board can return an integer if failed, or WorkboardResponse if OK
$valid_dashboardlines = array();
foreach ($dashboardlines as $workboardid => $tmp) {
	if ($tmp instanceof WorkboardResponse) {
		$tmp->id = $workboardid; // Complete the object to add its id into its name
		$valid_dashboardlines[$workboardid] = $tmp;
	}
}

// We calculate $totallate. Must be defined before start of next loop because it is show in first fetch on next loop
foreach ($valid_dashboardlines as $board) {
	if ($board->nbtodolate > 0) {
		$totaltodo += $board->nbtodo;
		$totallate += $board->nbtodolate;
	}
}

$openedDashBoardSize = 'info-box-sm'; // use sm by default
foreach ($dashboardgroup as $dashbordelement) {
	if (is_array($dashbordelement['stats']) && count($dashbordelement['stats']) > 2) {
		$openedDashBoardSize = ''; // use default info box size : big
		break;
	}
}

$totalLateNumber = $totallate;
$totallatePercentage = ((!empty($totaltodo)) ? round($totallate / $totaltodo * 100, 2) : 0);
if (!empty($conf->global->MAIN_USE_METEO_WITH_PERCENTAGE)) {
	$totallate = $totallatePercentage;
}

$boxwork = '';
$boxwork .= '<div class="box">';

// Show dashboard
$nbworkboardempty = 0;
$isIntopOpenedDashBoard = $globalStatInTopOpenedDashBoard = array();
if (!empty($valid_dashboardlines)) {
	$openedDashBoard = '';

	$boxwork .= '<tr class="nobottom nohover"><td class="tdboxstats nohover flexcontainer centpercent"><div style="display: flex: flex-wrap: wrap">';

	foreach ($dashboardgroup as $groupKey => $groupElement) {


		$boards = array();

		// Scan $groupElement and save the one with 'stats' that lust be used for Open object dashboard
		if (empty($conf->global->MAIN_DISABLE_NEW_OPENED_DASH_BOARD)) {
			foreach ($groupElement['stats'] as $infoKey) {
				if (!empty($valid_dashboardlines[$infoKey])) {
					$boards[] = $valid_dashboardlines[$infoKey];
					$isIntopOpenedDashBoard[] = $infoKey;
				}
			}
		}

		if (!empty($boards)) {
			if (!empty($groupElement['lang'])) {
				$langs->load($groupElement['lang']);
			}
			$groupName = $langs->trans($groupElement['groupName']);
			$groupKeyLowerCase = strtolower($groupKey);

			// global stats
			$globalStatsKey = false;
			if (!empty($groupElement['globalStatsKey']) && empty($groupElement['globalStats'])) { // can be filled by hook
				$globalStatsKey = $groupElement['globalStatsKey'];
				$groupElement['globalStats'] = array();
			}

			$openedDashBoard .= '<div class="box-flex-item"><div class="box-flex-item-with-margin">'."\n";
			$openedDashBoard .= '	<div class="info-box '.$openedDashBoardSize.'">'."\n";
			$openedDashBoard .= '		<span class="info-box-icon bg-infobox-'.$groupKeyLowerCase.'">'."\n";
			$openedDashBoard .= '		<i class="fa fa-dol-'.$groupKeyLowerCase.'"></i>'."\n";

			// Show the span for the total of record
			if (!empty($groupElement['globalStats'])) {
				$globalStatInTopOpenedDashBoard[] = $globalStatsKey;
				$openedDashBoard .= '<span class="info-box-icon-text" title="'.$groupElement['globalStats']['text'].'">'.$nbTotal.'</span>';
			}

			$openedDashBoard .= '</span>'."\n";
			$openedDashBoard .= '<div class="info-box-content">'."\n";

			$openedDashBoard .= '<div class="info-box-title" title="'.strip_tags($groupName).'">'.$groupName.'</div>'."\n";
			$openedDashBoard .= '<div class="info-box-lines">'."\n";

			foreach ($boards as $board) {
				$openedDashBoard .= '<div class="info-box-line">';

				if (!empty($board->labelShort)) {
					$infoName = '<span class="marginrightonly" title="'.$board->label.'">'.$board->labelShort.'</span>';
				} else {
					$infoName = '<span class="marginrightonly">'.$board->label.'</span>';
				}


				$textLateTitle = $langs->trans("NActionsLate", $board->nbtodolate);
				$textLateTitle .= ' ('.$langs->trans("Late").' = '.$langs->trans("DateReference").' > '.$langs->trans("DateToday").' '.(ceil(empty($board->warning_delay) ? 0 : $board->warning_delay) >= 0 ? '+' : '').ceil(empty($board->warning_delay) ? 0 : $board->warning_delay).' '.$langs->trans("days").')';

				if ($board->id == 'bank_account') {
					$textLateTitle .= '<br><span class="opacitymedium">'.$langs->trans("IfYouDontReconcileDisableProperty", $langs->transnoentitiesnoconv("Conciliable")).'</span>';
				}

				$textLate = '';
				if ($board->nbtodolate > 0) {
					$textLate .= '<span title="'.dol_escape_htmltag($textLateTitle).'" class="classfortooltip badge badge-warning">';
					$textLate .= '<i class="fa fa-exclamation-triangle"></i> '.$board->nbtodolate;
					$textLate .= '</span>';
				}

				$nbtodClass = '';
				if ($board->nbtodo > 0) {
					$nbtodClass = 'badge badge-info';
				} else {
					$nbtodClass = 'opacitymedium';
				}

				// Forge the line to show into the open object box
				$labeltoshow = $board->label;
				if ($board->total > 0) {
					$labeltoshow .= ' : '.price($board->total, 0, $langs, 1, -1, -1, $conf->currency);
				}
				$openedDashBoard .= '<a href="'.$board->url.'" class="info-box-text info-box-text-a">'.$infoName.'<span class="classfortooltip'.($nbtodClass ? ' '.$nbtodClass : '').'" title="'.$labeltoshow.'" >';

				if($board->nbtodo != 0) {
					$openedDashBoard .= $board->nbtodo;
				}
				if ($board->total > 0) {
					$openedDashBoard .= '<span class="badge badge-status4 badge-status" style="color:white;">'.price($board->total, 0, $langs, 1, -1, -1, $conf->currency).'</span><br>';
				}

				//$openedDashBoard .= '</span>';
				if ($textLate) {
					if ($board->url_late) {
						$openedDashBoard .= '</a>';
						$openedDashBoard .= ' <a href="'.$board->url_late.'" class="info-box-text info-box-text-a paddingleft">';
					} else {
						$openedDashBoard .= ' ';
					}
					$openedDashBoard .= $textLate;
				}

				$openedDashBoard .= '</a>'."\n";
				if ($board->total_content > 0) {
					$openedDashBoard .= 'Reçues <span class="badge badge-status3 badge-status" style="color:white;">'.price($board->total_content, 0, $langs, 1, -1, -1, $conf->currency).'</span>';
				}
				$openedDashBoard .= '</div>'."\n";

			}

			// TODO Add hook here to add more "info-box-line"

			$openedDashBoard .= '		</div><!-- /.info-box-lines --></div><!-- /.info-box-content -->'."\n";
			$openedDashBoard .= '	</div><!-- /.info-box -->'."\n";
			$openedDashBoard .= '</div><!-- /.box-flex-item-with-margin -->'."\n";
			$openedDashBoard .= '</div><!-- /.box-flex-item -->'."\n";
			$openedDashBoard .= "\n";
		}
	}

	if (!empty($isIntopOpenedDashBoard)) {
		for ($i = 1; $i <= 10; $i++) {
			$openedDashBoard .= '<div class="box-flex-item filler"></div>';
		}
	}

	$nbworkboardcount = 0;
	foreach ($valid_dashboardlines as $infoKey => $board) {
		if (in_array($infoKey, $isIntopOpenedDashBoard)) {
			// skip if info is present on top
			continue;
		}
		if (empty($board->nbtodo)) {
			$nbworkboardempty++;
		}
		$nbworkboardcount++;




		$textlate = $langs->trans("NActionsLate", $board->nbtodolate);
		$textlate .= ' ('.$langs->trans("Late").' = '.$langs->trans("DateReference").' > '.$langs->trans("DateToday").' '.(ceil($board->warning_delay) >= 0 ? '+' : '').ceil($board->warning_delay).' '.$langs->trans("days").')';


		$boxwork .= '<div class="boxstatsindicator thumbstat150 nobold nounderline"><div class="boxstats130 boxstatsborder">';
		$boxwork .= '<div class="boxstatscontent">';
		$boxwork .= '<span class="boxstatstext" title="'.dol_escape_htmltag($board->label).'">'.$board->img.' <span>'.$board->label.'</span></span><br>';
		$boxwork .= '<a class="valignmiddle dashboardlineindicator" href="'.$board->url.'"><span class="dashboardlineindicator'.(($board->nbtodo == 0) ? ' dashboardlineok' : '').'">'.$board->nbtodo.'</span></a>';
		if ($board->total > 0 && !empty($conf->global->MAIN_WORKBOARD_SHOW_TOTAL_WO_TAX)) {
			$boxwork .= '&nbsp;/&nbsp;<a class="valignmiddle dashboardlineindicator" href="'.$board->url.'"><span class="dashboardlineindicator'.(($board->nbtodo == 0) ? ' dashboardlineok' : '').'">'.price($board->total).'</span></a>';
		}
		$boxwork .= '</div>';
		if ($board->nbtodolate > 0) {
			$boxwork .= '<div class="dashboardlinelatecoin nowrap">';
			$boxwork .= '<a title="'.dol_escape_htmltag($textlate).'" class="valignmiddle dashboardlineindicatorlate'.($board->nbtodolate > 0 ? ' dashboardlineko' : ' dashboardlineok').'" href="'.((!$board->url_late) ? $board->url : $board->url_late).'">';
			//$boxwork .= img_picto($textlate, "warning_white", 'class="valigntextbottom"');
			$boxwork .= img_picto(
				$textlate,
				"warning_white",
				'class="inline-block hideonsmartphone valigntextbottom"'
			);
			$boxwork .= '<span class="dashboardlineindicatorlate'.($board->nbtodolate > 0 ? ' dashboardlineko' : ' dashboardlineok').'">';
			$boxwork .= $board->nbtodolate;
			$boxwork .= '</span>';
			$boxwork .= '</a>';
			$boxwork .= '</div>';
		}
		$boxwork .= '</div></div>';
		$boxwork .= "\n";
	}

	$boxwork .= '<div class="boxstatsindicator thumbstat150 nobold nounderline"><div class="boxstats150empty"></div></div>';
	$boxwork .= '<div class="boxstatsindicator thumbstat150 nobold nounderline"><div class="boxstats150empty"></div></div>';
	$boxwork .= '<div class="boxstatsindicator thumbstat150 nobold nounderline"><div class="boxstats150empty"></div></div>';
	$boxwork .= '<div class="boxstatsindicator thumbstat150 nobold nounderline"><div class="boxstats150empty"></div></div>';

	$boxwork .= '</div>';
	$boxwork .= '</td></tr>';
} else {
	$boxwork .= '<tr class="nohover">';
	$boxwork .= '<td class="nohover valignmiddle opacitymedium">';
	$boxwork .= $langs->trans("NoOpenedElementToProcess");
	$boxwork .= '</td>';
	$boxwork .= '</tr>';
}

$boxwork .= '</td></tr>';

$boxwork .= '</table>'; // End table array of working board
$boxwork .= '</div>';

if (!empty($isIntopOpenedDashBoard)) {
	print '<div class="fichecenter">';
	print '<div class="opened-dash-board-wrap"><div class="box-flex-container">'.$openedDashBoard.'</div></div>';
	print '</div>';
}

print '<div class="clearboth"></div>';

print '<div class="fichecenter fichecenterbis">';


/*
 * Show widgets (boxes)
 */

$boxlist = '<div class="twocolumns">';

$boxlist .= '<div class="firstcolumn fichehalfleft boxhalfleft" id="boxhalfleft">';
if (!empty($nbworkboardcount)) {
	$boxlist .= $boxwork;
}

$boxlist .= $resultboxes['boxlista'];

$boxlist .= '</div>';

$boxlist .= '<div class="secondcolumn fichehalfright boxhalfright" id="boxhalfright">';

$boxlist .= $resultboxes['boxlistb'];

$boxlist .= '</div>';
$boxlist .= "\n";

$boxlist .= '</div>';


print $boxlist;

print '</div>';
