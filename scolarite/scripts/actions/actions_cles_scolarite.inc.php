<?php

if($action == 'confirm_key_lost')
{
	$error = 0;
	// On récupère la clé
	$cleClass = new Cles($db);
	$cleClass->fetch($id);

	if(!$cleClass->id) $error++;
	if(!$error && $cleClass->status != Cles::STATUS_DISPONIBLE)
	{
		$attributionClass = new Attribution($db);
		$attributionClass->fetch('','',' AND fk_cle='.$id.' ORDER BY rowid DESC');

		// Si on fetch une clé qui n'existe pas, ou une attribution qui n'existe pas error
		if(!$attributionClass->id) $error++;
	}

	if(!$error)
	{
		if($cleClass->status != Cles::STATUS_DISPONIBLE)
		{
			// Update de l'attribution avec date de fin et nouveau status
			$attributionClass->status = Attribution::STATUS_PROBLEME;
			$attributionClass->tms = date('Y-m-d H:i:s');
			$resUpdateAttribution = $attributionClass->update($user);
		}

		// Update de la clé avec status disponible
		$cleClass->status = Cles::STATUS_PERDUE;
		$resUpdateCle = $cleClass->update($user);

		// Message en output
		if(($resUpdateAttribution ? $resUpdateAttribution > 0 : '1') && $resUpdateCle > 0) setEventMessage('Clé confirmée comme perdue.');
		else setEventMessage('Une erreur est survenue.','errors');

	}else setEventMessage('Une erreur est survenue.','errors');

	// Redirection
	header('Location: '.$_SERVER['PHP_SELF'].'?id='.$cleClass->id);
	exit;
}

if($action == 'confirm_key_found')
{
	$error = 0;
	// On récupère la clé
	$cleClass = new Cles($db);
	$cleClass->fetch($id);

	if(!$cleClass->id) $error++;

	// Si clé en cours de prêt
	if(!$error)
	{
		// On fetch sa dernière attribution
		$attributionClass = new Attribution($db);
		$attributionClass->fetch('','',' AND fk_cle='.$id.' ORDER BY rowid DESC');
	}

	if(!$error) {

		// Si il a été demandé de cloturer l'attribution
		if ($closeOrNot == 'on')
		{
			// clé disponible et on termine l'attribution
			$cleClass->status = Cles::STATUS_DISPONIBLE;

			$attributionClass->status = Attribution::STATUS_TERMINATED;
			$attributionClass->date_fin_pret = date('Y-m-d H:i:s');

			$message = 'Clé disponible et attribution terminée!';

			$resUpdateAttribution = $attributionClass->update($user);
		}
		// Cas si clé est non attribuée ou attribuée mais pas coché de cloture d'attribution
		elseif($attributionClass->status == Attribution::STATUS_PROBLEME)
		{
			// clé en cours de prêt et attribution en cours
			$cleClass->status = Cles::STATUS_VALIDATED;
			$message = 'Clé de retour en prêt!';

			$attributionClass->status = Attribution::STATUS_VALIDATED;
			$resUpdateAttribution = $attributionClass->update($user);
		}
		// Sinon, on passe la clé disponible (Aucune attribution en cours)
		else{
			$cleClass->status = Cles::STATUS_DISPONIBLE;
			$message = 'Clé de nouveau disponible!';
		}

		// On update la clé
		$resUpdateCle = $cleClass->update($user);

		// Message de fin
		if (($resUpdateAttribution ? $resUpdateAttribution > 0 : '1') && $resUpdateCle > 0) setEventMessage($message);
		else setEventMessage('Une erreur est survenue.', 'errors');

	} else setEventMessage('Une erreur est survenue.', 'errors');

	// redirection
	header('Location: '.$_SERVER['PHP_SELF'].'?id='.$cleClass->id);
	exit;
}
