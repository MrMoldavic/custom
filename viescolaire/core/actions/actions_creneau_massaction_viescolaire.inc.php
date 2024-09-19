<?php
declare(strict_types=1);

if (in_array($massaction, ['telephone', 'mail', 'eleves', 'coordonnee'])) {
	print '<br>';
	$affectationClass = new Affectation($db);
	$eleveClass = new Eleve($db);
	$classeClass = new Classe($db);
	$parentClass = new Parents($db);

	$exitingInfos = [];
	$out = '';

	if($massaction === 'coordonnee') {
		$out .= '<table>';
		$out .= '<tr>';
		$out .= '<th>Prénom Élève</th><th>Nom Élève</th><th>Prénom Parent</th><th>Nom Parent</th><th>Email Parent</th><th>Téléphone Parent</th>';
		$out .= '</tr>';
	}

	foreach ($arrayofselected as $creneau) {

		$affectations = $affectationClass->fetchAll('', '', 0, 0, ['fk_creneau' => (int)$creneau, 'status' => Affectation::STATUS_VALIDATED]);

		foreach ($affectations as $affectation) {

			$eleves = $eleveClass->fetchAll('DESC', 't.fk_classe_etablissement', 0, 0, ['s.rowid' => $affectation->fk_souhait], '', ' INNER JOIN ' . MAIN_DB_PREFIX . 'souhait as s ON s.fk_eleve=t.rowid');

			foreach ($eleves as $eleve) {

				if ($massaction === 'eleves') {
					if ($eleve->prenom !== NULL && !in_array($eleve->id, $exitingInfos, true)) {
						$classeClass->fetch($eleve->fk_classe_etablissement);

						print "{$eleve->prenom} {$eleve->nom} / $classeClass->classe<br>";
						$exitingInfos[] = $eleve->id;
					}
				} else {

					$parents = $parentClass->fetchAll('', '', 0, 0, ['fk_famille' => $eleve->fk_famille]);

					if (empty($parents)) {
						print "{$eleve->prenom} {$eleve->nom}<strong style='color:red'>: Aucune famille connue</strong><br>";
					} elseif ($massaction === 'coordonnee') {
						foreach ($parents as $parent) {
							$out .= '<tr>';
							$out .= "<td>{$eleve->prenom}</td>";
							$out .= "<td>{$eleve->nom}</td>";
							$out .= "<td>{$parent->firstname}</td>";
							$out .= "<td>{$parent->firstname}</td>"; // Utilise deux fois le prénom, tu peux corriger si nécessaire
							$out .= '<td>' . ($parent->mail ?: 'Aucun mail connu') . '</td>';
							$out .= '<td>' . ($parent->tel ?: 'Aucun téléphone connu') . '</td>';
							$out .= '</tr>';
						}
					} else {
						foreach ($parents as $parent) {
							if ($massaction === 'mail' && !in_array($parent->mail, $exitingInfos, true)) {
								$out .= $parent->mail ? "{$parent->mail}<br>" : '<br>';

								(!empty($parent->mail) ? $exitingInfos[] = $parent->mail : '');
							}
							if ($massaction === 'telephone' && !in_array($parent->phone, $exitingInfos, true)) {
								$out .= $parent->phone ? "{$parent->phone}<br>" : '<br>';

								(!empty($parent->phone) ? $exitingInfos[] = $parent->phone : '');
							}
						}
					}

				}
			}
		}
	}

	if($massaction === 'coordonnee') {
		$out .= '</table>';
	}
	print $out;
}

