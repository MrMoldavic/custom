<?php

//Partie pour ajouter l'appele en base
if ($action == 'confirmAppel')
{
	$appelClass = new Appel($db);

	$checkInjustifiee = true;

	$eleveClass = new Eleve($db);
	$eleves = $eleveClass->fetchAll('','',0,0,array('a.fk_creneau'=>$creneauid,'a.status'=>Affectation::STATUS_VALIDATED),'AND',' INNER JOIN ' .MAIN_DB_PREFIX. 'souhait as s ON s.fk_eleve = t.rowid INNER JOIN '.MAIN_DB_PREFIX.'affectation as a ON a.fk_souhait=s.rowid');

	$agentClass = new Agent($db);
	$professeurs = $agentClass->fetchAll('','',0,0,array('a.fk_creneau'=>$creneauid,'a.status'=>Assignation::STATUS_VALIDATED),'AND',' INNER JOIN '.MAIN_DB_PREFIX.'assignation as a ON a.fk_agent=t.rowid');

	$everyAppelSent = $appelClass->checkIfAllAppelSent($creneauid);

	// Si on ne detecte pas de professeur dans l'appel en POST du créneau, on renvoie une erreur (Pour l'instant en pause)
	if (!$everyAppelSent) {
		setEventMessage('Erreur, Veuillez renseigner tous les champs.', 'errors');
	}
	else {
		// Insert ou update de tout les appels présents en POST
		$resInsertOrUpdateAppelEleves = $appelClass->InsertOrUpdateAppelEleves($eleves, $creneauid, $selectedDate);

		$resInsertOrUpdateAppelProfesseurs = $appelClass->InsertOrUpdateAppelProfesseurs($professeurs, $creneauid, $selectedDate);

		if($resInsertOrUpdateAppelEleves < 0 || $resInsertOrUpdateAppelProfesseurs < 0)
		{
			setEventMessage('Une erreur est survenue lors de l\'ajout de l\'appel', 'errors');
		}else setEventMessage('Appel réalisé avec succès!');
	}

	if($creneauid && !$everyAppelSent)
	{
		$action = 'returnFromError';
		$creneauid = GETPOST('creneauid','int');
	}
	else $action = 'create';
}
