<?php

if($action == "confirm_delete")
{
	$programmationClass = new Programmation($db);
	$programmations = $programmationClass->fetchAll('','',0,0,array('fk_proposition'=>$selectedProposition));

	if(count($programmations) > 0)
	{
		foreach ($programmations as $programmation)
		{
			$programmationClass->fetch($programmation->id);
			$resDelete = $programmationClass->delete($user);

			if($resDelete < 0) exit;
		}
	}

	$propositionClass = new Proposition($db);
	$propositionClass->fetch($selectedProposition);
	$resDelete = $propositionClass->delete($user);

	if($resDelete > 0)	setEventMessage('Groupe et leur(s) morceau(x) supprimés la conduite avec succès!');
	else setEventMessage('Une erreur est surevue','errors');
}

// Supprime une interpretation de la conduite
if($action == "confirm_deleteProgrammation")
{
	$programmationClass = new Programmation($db);
	$programmationClass->fetch($programmationId);
	$res = $programmationClass->delete($user);

	if($res > 0) setEventMessage('Interpretation supprimée de la conduite avec succès!');
	else setEventMessage('Une erreur est survenue','errors');
}

// Change la position d'une interpretation de la conduite
if($action == "changePositionProgrammation")
{
	// Check si la position donnée est inférieur à zéro
	if((int)GETPOST('positionProgrammation', 'int') <= 0)
	{
		setEventMessage('Veuillez renseigner une position valide.','errors');
		return -1;
	}

	// Fetch de la proposition
	$programmationClass = new Programmation($db);
	$resProgrammation = $programmationClass->fetch('','',' AND position='.GETPOST('positionProgrammation','alpha').' AND fk_proposition='.GETPOST('idProposition','int').' AND fk_evenement='.GETPOST('id','int'));

	if($resProgrammation)
	{
		$programmationClass = new Programmation($db);
		$resqlAllAboveProgrammation = $programmationClass->fetchAll('','',0,0,array('customsql'=>'position>='.GETPOST('positionProgrammation','alpha').' AND fk_evenement='.GETPOST('id','int').' AND fk_proposition='.GETPOST('idProposition','int')));

		foreach($resqlAllAboveProgrammation as $valueProgrammation)
		{
			if($valueProgrammation->position < (GETPOST('positionProgrammation','alpha')+1))
			{
				$valueProgrammation->position = $valueProgrammation->position+1;
				$valueProgrammation->update($user);
			}
		}
	}

	$programmationClass = new Programmation($db);
	$programmationClass->fetch(GETPOST('idProgrammation','int'));
	$programmationClass->position = GETPOST('positionProgrammation','int');
	$res = $programmationClass->update($user);

	if($res > 0) setEventMessage('Programmation mise à jour avec succès!');
	else setEventMessage('Une erreur est survenue','errors');
}


// Change la position d'une proposition de la conduite
if ($action == 'changePositionProposition') {
	// Check si la position donnée est inférieur à zéro
	if ((int)GETPOST('positionProposition', 'int') <= 0) {
		setEventMessage('Veuillez renseigner une position valide.', 'errors');
		return -1;
	}

	// Fetch de la proposition
	$propositionClass = new Proposition($db);
	$resProposition = $propositionClass->fetch('', '', ' AND position=' . GETPOST('positionProposition', 'alpha'));

	if ($resProposition) {
		$resqlAllAbove = $propositionClass->fetchAll('', '', 0, 0, array('customsql' => 'position>=' . GETPOST('positionProposition', 'alpha')." AND fk_evenement=".GETPOST('id','int').' AND (status='.Proposition::STATUS_VALIDATED.' OR status='.Proposition::STATUS_PROGRAMMED.')'));

		foreach ($resqlAllAbove as $value) {
			if ($value->position <= (GETPOST('positionProposition', 'alpha') + 1)) {
				$value->position = $value->position + 1;
				$value->update($user);
			}
		}
	}

	$propositionClass = new Proposition($db);
	$propositionClass->fetch(GETPOST('IdProposition', 'alpha'));
	$propositionClass->position = GETPOST('positionProposition', 'alpha');
	$res = $propositionClass->update($user);


	if ($res > 0) setEventMessage('Positions mises à jour avec succès!');
	else setEventMessage('Une erreur est survenue', 'errors');
}

if($action == 'confirm_export_conduite')
{
	if((int)GETPOST('id', 'int') <= 0)
	{
		setEventMessage('Une erreur est survenue lors la récupération de l\'événement.','errors');
		return -1;
	}

	$res = $object->createConduite(GETPOST('id','int'));

	if($res > 0) setEventMessage('Création de conduite réalisée avec succès!');
	else setEventMessage('Une erreur est survenue lors de la création de la conduite.','errors');
}

if($action == 'handleProposition')
{
	if((int)GETPOST('propositionId', 'int') <= 0)
	{
		setEventMessage('Une erreur est survenue lors la récupération de la proposition.','errors');
		return -1;
	}

	// Récupération de la plus haute position connue active de ce concert
	$propositionClass = new Proposition($db);
	$propositionClass->fetch('','',' AND fk_evenement='.GETPOST('id','int').' AND status='.Proposition::STATUS_VALIDATED.' ORDER BY position DESC');

	// Plus haute position connue de ce concert
	$positionMax = $propositionClass->position;


	// Fetch de la proposition à gérer
	$propositionClass->fetch(GETPOST('propositionId','int'));
	// Regarde si l'action est de désactiver ou d'activer la proposition
	$propositionClass->status = GETPOST('typeAction','alpha') == 'activateProposition' ? Proposition::STATUS_VALIDATED : Proposition::STATUS_DRAFT;
	// Si on active la proposition, on fait plus haite position+1, sinon on met null
	$propositionClass->position = GETPOST('typeAction','alpha') == 'activateProposition' ? $positionMax+1 : null;
	$resUpdate = $propositionClass->update($user);

	// Si l'action est de désactiver
	if(GETPOST('typeAction','alpha') == 'desactivateProposition')
	{
		// Va chercher toutes les propostions plus hautes que celle déjà modifiée
		$higherPropositions = $propositionClass->fetchAll('','',0,0,array('customsql'=>"position<=$positionMax AND position>1 AND fk_evenement=".GETPOST('id','int').' AND status='.Proposition::STATUS_VALIDATED),'AND');

		// Pour chaque propostion, on décrémente sa position de 1
		foreach ($higherPropositions as $higherProposition)
		{
			$propositionClass->fetch($higherProposition->id);
			$propositionClass->position = $propositionClass->position-1;
			$resUpdateAbovePropositions = $propositionClass->update($user);
		}
	}

	if($resUpdate > 0) setEventMessage('Proposition modifiée avec succès!');
	else setEventMessage('Une erreur est survenue.','errors');
}

// Supprime une interpretation de la conduite
if ($action == 'handle_validation_proposition') {
	$propositionClass = new Proposition($db);
	$propositionClass->fetch($selectedProposition);
	if(GETPOST('subAction','aZ') == 'deprogramProposition')
	{
		$propositionClass->status = Proposition::STATUS_VALIDATED;
		$subAction = 'déprogrammé';
	} else {
		$propositionClass->status = Proposition::STATUS_PROGRAMMED;
		$subAction = 'programmé';
	}
	$resUpdate = $propositionClass->update($user);

	if ($resUpdate > 0) setEventMessage("Groupe $subAction avec succès!");
	else setEventMessage('Une erreur est survenue', 'errors');
}

if($action == 'updateAllPositions')
{
	$propositionClass = new Proposition($db);
	$propositions = $propositionClass->fetchAll('','',0,0,array('customsql'=>'fk_evenement='.GETPOST('id','int').' AND (status='.Proposition::STATUS_VALIDATED.' OR status='.Proposition::STATUS_PROGRAMMED.')'));

	$count = 1;
	foreach ($propositions as $proposition)
	{
		$propositionClass->fetch($proposition->id);
		$propositionClass->position = $count;
		$resUpdate = $propositionClass->update($user);

		if($resUpdate < 0){
			setEventMessage('Une erreur est survenue', 'errors');
			exit;
		}
		$count++;
	}

	setEventMessage('Positions mises à jour avec succès!');
}
