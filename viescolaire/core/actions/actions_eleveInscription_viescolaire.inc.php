<?php

if ($action == 'confirm_delete_inscription') {
	$inscriptionClass = new Inscription($db);
	$inscriptionClass->fetch($inscriptionid);
	if($inscriptionClass->id)
	{
		$resDelete = $inscriptionClass->delete($user);
		if($resDelete > 0) setEventMessage('Inscription supprimée avec succès!');
		else setEventMessage('Une erreur est survenue lors de la suppression','warnings');
	}
	else setEventMessage('Une erreur est survenue','errors');

	unset($inscriptionClass);
}
