<?php

if($action == 'addAutorisation' || $action == 'editAutorisation')
{
	$autorisationClass = new Autorisation($db);
	if($action == 'editAutorisation')
	{
		$autorisationClass->fetch(GETPOST('autorisation_id','int'));
	}
	$autorisationClass->fk_engagement = GETPOST('engagement_id','int');
	$autorisationClass->fk_evenement = GETPOST('evenement_id','int');
	$autorisationClass->status = GETPOST('autorisation','boolean') ? Autorisation::STATUS_VALIDATED : Autorisation::STATUS_CANCELED;
	$autorisationClass->public_autorisation = GETPOST('public','text') ? : 'Aucun public';
	$autorisationClass->details_autorisation = GETPOST('details','text') ? : 'Aucun détail';

	if($action == 'addAutorisation')
	{
		$res = $autorisationClass->create($user);
		$subAction = 'créée';
	} else {
		$res = $autorisationClass->update($user);
		$subAction = 'modifiée';
	}

	if($res > 0) setEventMessage("Autorisation {$subAction} avec succès!");
	else setEventMessage('Une erreur est survenue.','errors');
}
