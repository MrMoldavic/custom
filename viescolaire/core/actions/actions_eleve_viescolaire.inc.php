<?php

if($action == 'confirm_desactivation')
{
	// On va chercher l'année scolaire actuelle
	$dictionaryClass = new Dictionary($db);
	$anneeActuelle = $dictionaryClass->fetchByDictionary('c_annee_scolaire',['annee','rowid'],0,'',' WHERE annee_actuelle=1');

	// On va chercher les souhaits de l'élève qui pourraient être actifs
	$souhaitClass = new Souhait($db);
	$souhaits = $souhaitClass->fetchAll('','',0,0,['fk_eleve'=>$id,'fk_annee_scolaire'=>$anneeActuelle->rowid,'status'=>4]);

	// Si il y en plus que 0, on génère une erreur
	if(count($souhaits) > 0 && (GETPOST('stateInscription', 'int') == 9 || $action == 'confirm_desactivation'))
	{
		setEventMessage('Des souhaits non désactivés existent encore, action impossible.','errors');
	}
	// Sinon on update le status de l'élève normalement
	else
	{
		$eleve = new Eleve($db);
		$eleve->fetch($id);
		$eleve->status = ($action == 'confirm_desactivation' ? $eleve::STATUS_CANCELED : GETPOST('stateInscription', 'int'));
		$res = $eleve->update($user);

		if(count($res) > 0) setEventMessage('Élève modifié avec succès!');
		else setEventMessage('Une erreur est survenue.','errors');
	}

}

// confirmation d'activation d'un élève
if ($action == 'confirm_activation') {

	// Appel de la classe de l'élève et modification de son status
	$eleve = new Eleve($db);
	$eleve->fetch($id);
	$eleve->status = $eleve::STATUS_DRAFT;
	$resUpdate = $eleve->update($user);

	// Si résultat supérieur à 0 OK, si KO message d'erreur
	if($resUpdate > 0) setEventMessage('Élève activé avec succès!');
	else setEventMessage('Une erreur est survenue.','errors');
}


// confirmation de suppression d'un élève
if ($action == 'confirm_delete') {
	// On va chercher l'année scolaire actuelle
	$dictionaryClass = new Dictionary($db);
	$anneeActuelle = $dictionaryClass->fetchByDictionary('c_annee_scolaire',['annee','rowid'],0,'',' WHERE annee_actuelle=1');

	// On va chercher les souhaits de l'élève qui pourraient être actifs
	$souhaitClass = new Souhait($db);
	$souhaits = $souhaitClass->fetchAll('','',0,0,['fk_eleve'=>$id,'fk_annee_scolaire'=>$anneeActuelle->rowid,'status'=>4]);

	// Si il y en plus que 0, on génère une erreur
	if(count($souhaits) > 0)
	{
		setEventMessage('Des souhaits non désactivés existent encore, action impossible.','errors');
		$action = "view";
	}else{
		//TODO : aller supprimer tout les souhaits existants

		$eleveClass = new Eleve($db);
		$eleveClass->fetch($id);
		$resDelete = $eleveClass->delete($user);

		// Si résultat supérieur à 0 OK, si KO message d'erreur
		if($resDelete > 0) setEventMessage('Élève supprimé avec succès!');
		else setEventMessage('Une erreur est survenue.','errors');

		header('Location: '.DOL_URL_ROOT.'/custom/viescolaire/eleve_list.php');
		exit;
	}
}
