<?php
// Gestion des actions de masse du module source


if ($massaction == 'preaddtopreinventory')
{
	print $form->formconfirm($_SERVER["PHP_SELF"], 'Ajouter au pré-inventaire', 'Êtes vous sûr de vouloir ajouter les sources sélectionnées au pré-inventaire ?', "addpreinventaire", null, '', 0, 200, 500, 1);
}
elseif ($massaction == 'prenoninventoriable')
{
	print $form->formconfirm($_SERVER["PHP_SELF"], 'Définir comme non inventoriable', 'Êtes vous sûr de vouloir définir les sources sélectionnées comme non inventoriable ?', "setnoninventoriable", null, '', 0, 200, 500, 1);
}
elseif ($massaction == 'predelete')
{
	print $form->formconfirm($_SERVER["PHP_SELF"], 'Supprimer', 'Êtes vous sûr de vouloir supprimer les sources sélectionnées ?', "delete", null, '', 0, 200, 500, 1);
}
