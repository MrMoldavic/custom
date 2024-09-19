<?php

if ($action == 'confirm_clone') {
    setEventMessage('Vous êtes bien sur le nouveau souhait!');
}

if ($action == 'newaffectation') {

    if (GETPOST('fk_creneau', 'int') == '-1') {
        setEventMessage('Veuillez selectionner un creneau valide', 'errors');
    } elseif (dol_mktime(12, 0, 0, GETPOST('date_debutmonth', 'int'), GETPOST('date_debutday', 'int'), GETPOST('date_debutyear', 'int')) == '') {
        setEventMessage('Veuillez selectionner un date de début valide', 'errors');
    } else {
		$souhaitClass = new Souhait($db);
		$souhaitClass->fetch($id);

        if ($souhaitClass->status === 0) {
            $affectation = new Affectation($db);
            $affectation->fk_souhait = GETPOST('fk_souhait', 'int');
            $affectation->fk_creneau = GETPOST('fk_creneau', 'int');
            $affectation->date_debut = dol_mktime(12, 0, 0, GETPOST('date_debutmonth', 'int'), GETPOST('date_debutday', 'int'), GETPOST('date_debutyear', 'int'));
            $affectation->date_fin = dol_mktime(12, 0, 0, GETPOST('date_finmonth', 'int'), GETPOST('date_finday', 'int'), GETPOST('date_finyear', 'int'));
            $affectation->description = GETPOST('description', 'text');

            if ($affectation->create($user) < 0) {
                setEventMessage('Une erreur est survenue', 'error');
            }
        }
    }
}

if ($action == 'desactivation') {

    $souhait = new Souhait($db);
    $souhait->fetch($id);
    $souhait->status = Souhait::STATUS_CANCELED;
    $res = $souhait->update($user);

    if ($res > 0) setEventMessage('Souhait desactivé avec succès!');
    else setEventMessage('Une erreur est survenue', 'errors');
}

if ($action == 'activation') {

    $souhait = new Souhait($db);
    $souhait->fetch($id);
    $souhait->status = Souhait::STATUS_DRAFT;
    $res = $souhait->update($user);

    if ($res > 0) setEventMessage('Souhait activé avec succès!');
    else setEventMessage('Une erreur est survenue', 'errors');

}

if ($action == 'confirm_setdraft') {
    $affectationClass = new Affectation($db);
	$affectationClass->fetch('','',' AND fk_souhait='.$id.' AND status='.Affectation::STATUS_VALIDATED);
	$affectationClass->status = Affectation::STATUS_CANCELED;
	$resUpdate = $affectationClass->update($user);

	if ($resUpdate > 0) setEventMessage('Souhait disponible à l\'affectation!');
	else setEventMessage('Une erreur est survenue', 'errors');
}

if($action == 'confirm_delete_affectation')
{
	$affectation = new Affectation($db);
	$affectation->fetch(GETPOST('affectationid','int'));
	$res = $affectation->delete($user);

	if ($res > 0) setEventMessage('Affectation supprimée avec succès!');
	else setEventMessage('Une erreur est survenue', 'errors');
}
