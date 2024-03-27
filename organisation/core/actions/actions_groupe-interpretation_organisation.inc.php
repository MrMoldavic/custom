<?php

if ($action == 'confirm_delete_interpretation') {
	// On fetch l'interpretation
	$interpretationClass = new Interpretation($db);
	$interpretationClass->fetch($interpretation);
	$resDelete = $interpretationClass->delete($user);

	// Message de sortie
	if($resDelete > 0) setEventMessage('Interprétation supprimée avec succès!');
	else setEventMessage('Une erreur est survenue','errors');
}
