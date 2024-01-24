<?php

if ($action == 'confirm_clone') {
    setEventMessage('Vous êtes bien sur le nouveau souhait.');
}
if ($action == 'newaffectation') {

    if (GETPOST('fk_creneau', 'int') == '-1') {
        setEventMessage('Veuillez selectionner un creneau valide', 'errors');
    } elseif (dol_mktime(12, 0, 0, GETPOST('date_debutmonth', 'int'), GETPOST('date_debutday', 'int'), GETPOST('date_debutyear', 'int')) == '') {
        setEventMessage('Veuillez selectionner un date de début valide', 'errors');
    } else {
        $sql = 'SELECT status FROM ' . MAIN_DB_PREFIX . 'souhait WHERE rowid=' . $id;
        $resql = $db->query($sql);

        $statutSouhait = $db->fetch_object($resql);

        if ($statutSouhait->status == 0) {
            $affectation = new Affectation($db);
            $affectation->fk_souhait = GETPOST('fk_souhait', 'int');
            $affectation->fk_creneau = GETPOST('fk_creneau', 'int');
            $affectation->fk_souhait = GETPOST('fk_souhait', 'int');
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
    $souhait->status = $souhait::STATUS_CANCELED;
    $res = $souhait->update($user);

    if ($res > 0) setEventMessage('Souhait desactivé avec succès!');
    else setEventMessage('Une erreur est survenue', 'errors');
}

if ($action == 'activation') {

    $souhait = new Souhait($db);
    $souhait->fetch($id);
    $souhait->status = $souhait::STATUS_DRAFT;
    $res = $souhait->update($user);

    if ($res > 0) setEventMessage('Souhait activé avec succès!');
    else setEventMessage('Une erreur est survenue', 'errors');

}

if ($action == 'confirm_setdraft') {
    $affectation = new Affectation($db);

    $sql = 'UPDATE ' . MAIN_DB_PREFIX . 'affectation SET status = ' . $affectation::STATUS_CANCELED . ', date_fin = CURDATE() WHERE fk_souhait=' . $id;
    $resql = $db->query($sql);
}

if($action == 'confirm_delete_affectation')
{
	$affectation = new Affectation($db);
	$affectation->fetch(GETPOST('affectationid','int'));
	$res = $affectation->delete($user);

	if ($res > 0) setEventMessage('Affectation supprimée avec succès!');
	else setEventMessage('Une erreur est survenue', 'errors');
}
