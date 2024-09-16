<?php

if($action == 'confirm_delete_key_pret')
{
	$error = 0;
	// On récupère l'affectation
	$attributionClass = new Attribution($db);
	$attributionClass->fetch($id);
	// On récupère la clé
	$cleClass = new Cles($db);
	$cleClass->fetch($attributionClass->fk_cle);

	// Si on fetch une clé qui n'existe pas, ou une attribution qui n'existe pas error
	if(!$cleClass->id || !$attributionClass->id) $error++;

	if(!$error)
	{
		// Update de la clé avec status disponible
		$cleClass->status = Cles::STATUS_DISPONIBLE;
		$resUpdateCle = $cleClass->update($user);

		// Update de l'attribution avec date de fin et nouveau status
		$attributionClass->status = Attribution::STATUS_TERMINATED;
		$attributionClass->date_fin_pret = date('Y-m-d H:i:s');
		$resUpdateAttribution = $attributionClass->update($user);

		// Message en output
		if($resUpdateAttribution > 0 && $resUpdateCle > 0) setEventMessage('Prêt terminé et clé de nouveau disponible!');
		else setEventMessage('Une erreur est survenue.','errors');

		// Redirection
		header('Location: '.$_SERVER['PHP_SELF'].'?id='.$attributionClass->id);
		exit;
	}
}
