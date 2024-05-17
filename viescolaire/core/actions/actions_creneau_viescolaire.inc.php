<?php


if ($action == 'desactivation')
{
	$souhaitClass = new Souhait($db);
	$souhaits = $souhaitClass->fetchAll('','',0,0,array('customsql'=>'fk_creneau='.$id.' AND date_fin IS NULL'));

	if($souhaits)
	{
		setEventMessage('Vous ne pouvez pas désactiver un créneau qui contient des élèves.','errors');
	}
	else
	{
		$creneauClass = new Creneau($db);
		$creneauClass->fetch($id);
		$creneauClass->status = Creneau::STATUS_CANCELED;
		$resUpdateDesactivation = $creneauClass->update($user);

		if($resUpdateDesactivation > 0) setEventMessage('Creneau désactivé avec succès!');
		else setEventMessage('Une erreur est survenue.','errors');
	}
}

if ($action == 'activation')
{
	$creneauClass = new Creneau($db);
	$creneauClass->fetch($id);
	$creneauClass->status = Creneau::STATUS_VALIDATED;
	$resUpdateActivation = $creneauClass->update($user);

	if($resUpdateActivation > 0) setEventMessage('Creneau activé avec succès!');
	else setEventMessage('Une erreur est survenue.','errors');
}

if ($action == 'deleteAgent')
{

	$assignationClass = new Assignation($db);
	$assignationClass->fetch(GETPOST('ida','int'),'');
	$resDelete = $assignationClass->delete($user);

	if($resDelete > 0) setEventMessage('Professeur supprimé avec succès!');
	else setEventMessage('Une erreur est survenue.','errors');

}
