<?php

// Load Dolibarr environment
@include "../../../main.inc.php";
require_once DOL_DOCUMENT_ROOT.'/custom/kit/class/typekit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/formmateriel.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/materiel.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

// Load translation files required by the page
$langs->loadLangs(array("materiel@materiel"));
if (!empty($user->socid)) $socid = $user->socid;

$typekit = new TypeKit($db);
$form = new Form($db);
$formfile = new FormFile($db);
$formmateriel = new FormMateriel($db);
$materiel = new Materiel($db);

// Security check
$usercanread = ($user->rights->kit->read);
$usercancreate = ($user->rights->kit->create);
$usercandelete = ($user->rights->kit->delete);
$usercanmanagekittype = ($user->rights->kit->managekittype);

if (!$usercanmanagekittype) accessforbidden();
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0)
{
	$action = '';
	$socid = $user->socid;
}

/*Recupération données POST*/
$action = (GETPOST('action', 'alpha') ? GETPOST('action', 'alpha') : 'create');
$id = GETPOST('id', 'int');
$cancel = GETPOST('cancel', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$nom = GETPOST('name', 'text');
$ref = GETPOST('ref', 'text');
$types_materiel = GETPOST('type_materiel', 'array');
$arrayofselected = is_array($types_materiel) ? $types_materiel : array();

if (($action == 'edit' || $action == 'update') && $id)
{
    if (!$typekit->fetch($id)) {
        setEventMessages('Erreur lors de la récupération des données du type de kit', null, 'errors');
        header('Location: '.DOL_URL_ROOT.'/custom/kit/typekit/list.php');
        exit;
    }
}

/*
 * Actions
 */
if ($cancel) {
    if ($action != 'update') header('Location: '.DOL_URL_ROOT.'/custom/kit/typekit/card.php?action=create');
    else header('Location: '.DOL_URL_ROOT.'/custom/kit/typekit/list.php');
    exit;
}

if ($action == 'add' && $usercanmanagekittype){
    $error = 0;
    if (!$nom || $nom == '')
    {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Nom'), null, 'errors');
        $action = "create";
        $error++;
    }
    if (!$ref || $ref == '')
    {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Référence'), null, 'errors');
        $action = "create";
        $error++;
    }
    if (empty($types_materiel))
    {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Type de matériel'), null, 'errors');
        $action = "create";
        $error++;
    }
    if (!$error) // INSERT LE TYPE DE KIT DANS LA BDD
    {
        global $conf, $langs;
        $error = 0;

        $now = dol_now();
        $db->begin();
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_type_kit (";
        $sql .= "indicatif";
        $sql .= ", type";
        $sql .= ") VALUES (";
        $sql .= "'".$ref."'";
        $sql .= ", '".$nom."'";
        $sql .= ")";
        $result = $db->query($sql);

        if (!$result) $error++;
        else 
        {
            $id = $db->last_insert_id(MAIN_DB_PREFIX."c_type_kit");
            // Insert kit type details
            foreach ($types_materiel as $id_type_materiel) {
                $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_type_kit_det (";
                $sql .= "fk_type_kit";
                $sql .= ", fk_type_materiel";
                $sql .= ") VALUES (";
                $sql .= $id;
                $sql .= ", ".$id_type_materiel;
                $sql .= ")";
                $result = $db->query($sql);
                if (!$result) $error++;
            }
        }

        if (!$error) {
            $db->commit();
	        	setEventMessages('Type de kit ajouté avec succès', null);
				header('Location: '.DOL_URL_ROOT.'/custom/kit/typekit/list.php');
                exit;
        }
        else
        {
            $db->rollback();
            setEventMessages('Une erreur est survenue', null);
            exit;
        }
    }
}


if ($action == 'update' && $id && $usercanmanagekittype)
{
    $error = 0;
    if (!$nom || $nom == '')
    {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Nom'), null, 'errors');
        $action = "edit";
        $error++;
    }
    if (!$ref || $ref == '')
    {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Référence'), null, 'errors');
        $action = "edit";
        $error++;
    }
    if (empty($types_materiel))
    {
        setEventMessages($langs->trans('ErrorFieldRequired', 'Type de matériel'), null, 'errors');
        $action = "edit";
        $error++;
    }

    // Insertion du type de kit dans la BDD
    if (!$error) 
    {
        $db->begin();
        $sql = "UPDATE ".MAIN_DB_PREFIX."c_type_kit";
        $sql .= " SET";
        $sql .= " indicatif = '" . $ref . "'";
        $sql .= ", type = '" . $nom . "'";
        $sql .= " WHERE rowid = ".$id;
        if (!$db->query($sql)) $error++;
        if (!$db->commit()) $error++;
        if ($error) {
            $db->rollback();
            setEventMessages('Erreur lors de la modification du type de kit', null, 'errors');
            $action = 'edit';
            exit;
        }

        // Update allowed materiel type
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."c_type_kit_det";
        $sql .= " WHERE fk_type_kit = ".$id;
        $db->query($sql);
        $db->commit();

        foreach ($types_materiel as $allowed_type_materiel_id)
        {
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_type_kit_det (";
            $sql .= "fk_type_kit, ";
            $sql .= "fk_type_materiel";
            $sql .= ") VALUES (";
            $sql .= $id . ", ";
            $sql .= $allowed_type_materiel_id . ")";
            $resql = $db->query($sql);
        }
        setEventMessages('Type de kit modifié avec succès.', null);              
        header('Location: '.DOL_URL_ROOT.'/custom/kit/typekit/list.php');
        exit;
    }
}


/*
 * View
 */
llxHeader("", $langs->trans("Materiel"));

//Recupération de la liste des types de matériel par classe
$type_materiel_array = array();
$sql = "SELECT tm.rowid, tm.indicatif, tm.type, c.classe FROM ".MAIN_DB_PREFIX."c_type_materiel as tm";
$sql.=" INNER JOIN ".MAIN_DB_PREFIX."c_classe_materiel as c ON tm.fk_classe=c.rowid";
$resql = $db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);
	$var = true;
	if ($num > 0)
	{
		$i = 0;
		while ($i < $num)
		{
			$type_mat = $db->fetch_array($resql);
			$type_materiel_array[$type_mat['classe']][$type_mat['rowid']] = $type_mat['indicatif']. ' - ' .$type_mat['type'];
			$i++;
		}
	}
}

if ($action == 'create' && $usercanmanagekittype)
{
    //WYSIWYG Editor
    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
    print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="add">';
    print '<input type="hidden" name="type" value="'.$type.'">'."\n";
    if (!empty($modCodeProduct->code_auto))
    print '<input type="hidden" name="code_auto" value="1">';
    if (!empty($modBarCodeProduct->code_auto))
    print '<input type="hidden" name="barcode_auto" value="1">';
    print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
    $picto = 'kit';
    $title = 'Nouveau type de kit';
    $linkback = "";
    print talm_load_fiche_titre($title, $linkback, $picto);
    talm_fiche_head('');
    print '<table class="border centpercent">';
    print '<tr>';
    $tmpcode = '';
    if ($refalreadyexists)
    {
        print $langs->trans("RefAlreadyExists");
    }
    print '</td></tr>';
    
    // Nom du kit
    print '<tr><td class="titlefieldcreate fieldrequired">Nom du type de kit</td><td colspan="3"><input name="name" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.dol_escape_htmltag(GETPOST('name', 'alphanohtml')).'"></td></tr>';
    
    // Indicatif du kit
    print '<tr><td class="titlefieldcreate fieldrequired">Indicatif</td><td colspan="3"><input name="ref" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.dol_escape_htmltag(GETPOST('ref', 'alphanohtml')).'"></td></tr>';
    
    print '<tr></tr>'; // Spacer
    
    //Type de matériel à inclure dans le kit
    print '<tr><td class="titlefieldcreate fieldrequired">Type(s) de matériel</td></tr>';
    
    foreach($type_materiel_array as $classe=>$types_mat_array) {
        print '<tr><td></td><td colspan="3">
        <span>'. talm_img_picto($classe, 'materiel') . '&nbsp;' . $classe .'</span>
        </td></tr>';

        foreach ($types_mat_array as $id => $type) {
            $selected = 0;
            if (in_array($id, $arrayofselected)) $selected = 1;
            print '<tr><td></td><td colspan="3">
            <input type="checkbox" name="type_materiel[]" id="materiel_type_rowid'.$id.'" class="maxwidth400onsmartphone" maxlength="255" value="'.$id.'"'.($selected ? ' checked="checked"' : '').'>
            <label for="materiel_type_rowid'.$id.'">'.$type.'</label>
            </td></tr>';
        }

        print '<tr></tr>';
    }
    
    print "</table>";
    dol_fiche_end();
        print '<div class="center">';
        print '<input type="submit" class="button" value="Ajouter">';
        print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
        print '</div>';
        print '</form>';
}
elseif ($action == 'edit' && $id)
{
    //WYSIWYG Editor
    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
    print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="update">';
    print '<input type="hidden" name="type" value="'.$type.'">'."\n";
    print '<input type="hidden" name="id" value="'.$id.'">'."\n";
    print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
    $picto = 'kit';
    $title = 'Modifier un type de kit';
    $linkback = "";
    print talm_load_fiche_titre($title, $linkback, $picto);
    dol_fiche_head('');
    print '<table class="border centpercent">';
    print '<tr>';
    print '</td></tr>';
    
    // Nom du kit
    print '<tr><td class="titlefieldcreate fieldrequired">Nom du type de kit</td><td colspan="3"><input name="name" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.dol_escape_htmltag($typekit->title).'"></td></tr>';
    
    // Indicatif du kit
    print '<tr><td class="titlefieldcreate fieldrequired">Indicatif</td><td colspan="3"><input name="ref" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.dol_escape_htmltag($typekit->indicatif).'"></td></tr>';
    
    print '<tr></tr>'; // Spacer
    
    //Type de matériel à inclure dans le kit
    print '<tr><td class="titlefieldcreate fieldrequired">Type de matériel :</td></tr>';
    foreach($type_materiel_array as $classe=>$types_mat_array) {
        print '<tr><td></td><td colspan="3"><span>'. talm_img_picto($classe, 'materiel') . '&nbsp;' . $classe .'</span></td></tr>';
        foreach ($types_mat_array as $id => $type) {
            $selected = 0;
            if (in_array($id, $typekit->allowed_materiel_type_ids)) $selected = 1;
            print '<tr><td></td><td colspan="3">
                <input type="checkbox" name="type_materiel[]" id="materiel_type_rowid'.$id.'" class="maxwidth400onsmartphone" maxlength="255" value="'.$id.'""'.($selected ? ' checked="checked"' : '').'>
                <label for="materiel_type_rowid'.$id.'">'.$type.'</label>
                </td></tr>';
        }
        print '<tr></tr>';
    }
    
    print "</table>";
    dol_fiche_end();
        print '<div class="center">';
        print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
        print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
        print '</div>';
        print '</form>';
}
// End of page
llxFooter();
$db->close();
