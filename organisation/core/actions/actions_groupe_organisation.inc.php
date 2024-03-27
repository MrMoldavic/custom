<?php

// Action pour désactiver un groupe
if ($action == 'desactivation') {

	$groupeClass = new Groupe($db);
	$groupeClass->fetch($id);
	$groupeClass->status = Groupe::STATUS_CANCELED;
	$resUpdate = $groupeClass->update($user);

	if($resUpdate > 0)	setEventMessage('Groupe mis en retraite avec succès!');
	else setEventMessage('Une erreur est surevue','errors');

	header('Location: groupe_card.php?id='.$id);
	exit;
}

// Action pour ajouter un instru à un élève
if ($action == 'addInstru') {

	if((int) $instru != 0){
		$liaisonInstrumentClass = new LiaisonInstrument($db);
		$liaisonInstrumentClass->fk_engagement = $engagement;
		$liaisonInstrumentClass->fk_instrument = $instru; // variable reçu en POST
		$liaisonInstrumentClass->status = LiaisonInstrument::STATUS_VALIDATED;
		$resCreate = $liaisonInstrumentClass->create($user);
	}

	// Message de sortie
	if($resCreate > 0) setEventMessage('Instrument lié avec succès!');
	else setEventMessage('Une erreur est survenue.','errors');
}

// Action pour ajouter un instru à un élève
if ($action == 'confirm_deleteInstru') {

	$liaisonInstrumentClass = new LiaisonInstrument($db);
	$liaisonInstrumentClass->fetch(GETPOST('idLiaisonInstru','int'));
	$resConfirmDeleteLiaison = $liaisonInstrumentClass->delete($user);

	// Message de sortie
	if($resConfirmDeleteLiaison > 0) setEventMessage('Instrument supprimé avec succès!');
	else setEventMessage('Une erreur est survenue.','errors');
}

// Action pour supprimer un engagement
if ($action == 'confirm_deleteEngagement') {
	$engagementClass = new Engagement($db);
	$engagementClass->fetch($idEngagement);
	$resConfirmDeleteEngagement = $engagementClass->delete($user);

	// Message de sortie
	if($resConfirmDeleteEngagement > 0) setEventMessage('Engagement supprimé avec succès!');
	else setEventMessage('Une erreur est survenue','errors');
}

// Action pour cloturer/ouvrir un engagement
if($action == 'confirm_endEngagement')
{
	$engagementClass = new Engagement($db);
	$engagementClass->fetch($idEngagement);
	$engagementClass->date_fin_engagement = ($engagementClass->date_fin_engagement ? null : date('Y-m-d'));
	$resUpdate = $engagementClass->update($user);

	// Message de sortie
	if($resUpdate > 0) setEventMessage('Action réalisée avec succès!');
	else setEventMessage('Une erreur est survenue','errors');
}

// Imports des élèves manquants du créneau lié
if ($action == 'importAll') {

	if(!$object->fk_creneau)
	{
		setEventMessage('Aucun créneau affecté au groupe, veuillez en selectionner un.', 'errors');
		return -1;
	}

	$count = 0;
	// récupération de toutes les affectations
	$affectationClass = new Affectation($db);
	$affectations = $affectationClass->fetchAll('','',0,0,array('customsql'=>"fk_creneau=$creneau AND date_fin IS NULL"));

	foreach ($affectations as $val)
	{
		// Récupération du souhait puis de l'élève
		$souhaitClass = new Souhait($db);
		$souhaitClass->fetch($val->fk_souhait);

		$eleveClass = new Eleve($db);
		$eleveClass->fetch($souhaitClass->fk_eleve);
		// Recherche d'un engagement existant
		$engagementClass = new Engagement($db);
		$engagementClass->fetch('','',"AND fk_eleve=$eleveClass->id AND fk_groupe=$object->id");
		// Si il n'y a pas d'engagement
		if (!$engagementClass->id)
		{
			$engagementClass = new Engagement($db);
			$engagementClass->fk_groupe = $object->id;
			$engagementClass->fk_eleve = $eleveClass->id;
			$engagementClass->fk_user_creat = $user->id;
			$engagementClass->status = Engagement::STATUS_VALIDATED;

			$resAdd = $engagementClass->create($user);
			if($resAdd > 0) $count++;
		}
	}

	// Message de sortie
	if ($count == 0) setEventMessage('Tout les élèves sont déjà présents');
	else setEventMessage("$count import(s) réalisé(s) avec succès");
}


