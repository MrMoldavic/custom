<?php

if ($massaction == 'predelete')
{
	print $form->formconfirm($_SERVER["PHP_SELF"], 'Supprimer', 'Êtes vous sûr de vouloir supprimer le(s) type(s) de kit sélectionné(s) ?', "delete", null, '', 0, 200, 500, 1);
}

