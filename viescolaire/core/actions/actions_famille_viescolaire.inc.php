<?php
declare(strict_types=1);

// Actipn pour modifier l'état de la famille
if( $action === 'stateModify')
{
    $familleClass = new Famille($db);
    $familleClass->fetch($id);
    $familleClass->status = GETPOST('stateInscription', 'int');
    $resUpdate = $familleClass->update($user);

    if($resUpdate > 0) {
        setEventMessage('Etat modifié avec succès!');
    } else {
        setEventMessage('Une erreur est survenue.','errors');
    }
}

// Action pour supprimer un parent
if ($action === 'deleteParent') {

    $parentsClass = new Parents($db);
    $parentsClass->fetch($parentId);
    $resDelete = $parentsClass->delete($user);

    if($resDelete > 0)
    {
        setEventMessage('Parent supprimé avec succès!');
    }else {
        setEventMessage('Une erreur est survenue.', 'errors');
    }
}

// Action pour supprimer une contribution
if($action === 'deleteContribution')
{
    $contributionClass = new Contribution($db);
    $contributionClass->fetch($contributionId);

    $contributionContentClass = new ContributionContent($db);
    $contents = $contributionContentClass->fetchAll('ASC','rowid','','',['fk_contribution'=>$contributionId],'ASC');
    if($contents)
    {
        $contributionContentClass = new ContributionContent($db);
        foreach ($contents as $content)
        {
            $contributionContentClass->fetch($content->id);
            $contributionContentClass->delete($user);

        }
    }

    $result = $contributionClass->delete($user);

    if($result > 0) {
        setEventMessage('Contribution et son contenu supprimé avec succès!');
    }
    else {
        setEventMessage('Une erreur est survenue.', 'errors');
    }
}
