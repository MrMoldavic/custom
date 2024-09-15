<?php

// Action qui permet d'actier ou de désactiver une salle, avec message de validation
if ($action == 'activation' || $action == 'desactivation') {

	$salleClass = new Salle($db);
	$salleClass->fetch($id);
	$salleClass->status = ($action == 'activation' ? Salle::STATUS_VALIDATED : Salle::STATUS_CANCELED);
	$resUpdate = $salleClass->update($user);

	if($resUpdate > 0) {
		setEventMessage('Salle '.($action == 'activation' ? 'activée' : 'désactivée').' avec succès!');
	} else {
		setEventMessage('Une erreur est survenue','errors');
	}
}
