<?php

if ($action == 'deletePoste')
{
	// Fetch du poste
	$posteClass = new Poste($db);
	$posteClass->fetch(GETPOST('idPoste', 'int'));
	$resDelete = $posteClass->delete($user);

	// Message de sortie
	if($resDelete > 0) setEventMessage('Poste supprimé avec succès!');
	else setEventMessage('Une erreur est survenue lors de la suppression.','errors');
}

if($action == 'affectationPoste')
{
	$count = 0;

	// Récupération des agents affectés au poste
	foreach ($affectationPostes as $agent)
	{
		// Recherche si cet agent est déjà affecté ailleurs
		$posteClass = new Poste($db);
		$posteClass->fetch('',''," AND fk_agent=$agent AND fk_evenement=$object->id");

		if($posteClass->id && ($posteClass->id != $ligneId[$count]))
		{
			setEventMessage('Cet agent à déjà un rôle prévu à cet événement','errors');
			break;
		} else {
			$agentClass = new Agent($db);
			$agentClass->fetch($agent);

			$posteClass->fetch($ligneId[$count]);
			if($posteClass->fk_agent != $agentClass->id)
			{
				$posteClass->fk_agent = $agentClass->id;
				$resUpdate = $posteClass->update($user);

				// Message de sortie
				if($resUpdate > 0) setEventMessage("$agentClass->prenom $agentClass->nom affecté(e) avec succès!");
				else setEventMessage('Une erreur est survenue lors de l\'affectation.','errors');
			}
		}

		$count++;
	}
}

if($action == 'addPostes')
{
	for($i=0; $i<$iteration; $i++)
	{
		$posteClass = new Poste($db);
		$posteClass->fk_evenement = $object->id;
		$posteClass->fk_type_poste = $typePoste;
		$posteClass->status = Poste::STATUS_VALIDATED;
		$resCreate = $posteClass->create($user);
	}
	// Message de sortie
	if($resCreate > 0) setEventMessage("$i poste(s) créé(s) avec succès!");
	else setEventMessage('Une erreur est survenue lors de la suppression.','errors');
}
