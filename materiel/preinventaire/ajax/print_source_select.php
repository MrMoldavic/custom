<?php
@include "../../../../main.inc.php";
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formpreinventaire.class.php';

/*  SOURCE TYPE ID
 * 1 : Facture
 * 2 : Reçu fiscal
 * 3 : Emprunt
 */
$source_type_id = GETPOST('sourcetypeid', 'int');
$formpreinventaire = new FormPreinventaire($db);

// Show select for facture
if ($source_type_id == 1) 
{
    print $formpreinventaire->selectFactures();
}
