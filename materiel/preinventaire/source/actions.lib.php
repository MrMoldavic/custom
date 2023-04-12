<?php
if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') { $massaction = ''; }

if (!$error && ($massaction == 'addpreinventaire' || ($action == 'addpreinventaire' && $confirm == 'yes')) && $usercancreate)
{
	foreach ($toselect as $toselectid)
	{		
		$source = new Source($db);
		$source->create_reference_object($sourcetypeid);
		$source->source_reference_object->fetch($toselectid);
		$result = $source->add(1);
		if (!$result) $error++;
	}
		$toselect = array();
		if (!$error) setEventMessages('Source ajoutées au pré-inventaire', null);
		else setEventMessages('Une erreur est survenue lors l\'ajout des sources au pré-inventaire', null, 'errors');
}
if (!$error && ($massaction == 'setnoninventoriable' || ($action == 'setnoninventoriable' && $confirm == 'yes')) && $usercancreate)
{
	foreach ($toselect as $toselectid)
	{		
		$source = new Source($db);
		$source->create_reference_object($sourcetypeid);
		$source->source_reference_object->fetch($toselectid);
		$result = $source->add(0);
		if (!$result) $error++;
	}
		$toselect = array();
		if (!$error) setEventMessages('Source ajoutées définie comme non inventoriable', null);
		else setEventMessages('Une erreur est survenue lors la modification du status de la source', null, 'errors');
}