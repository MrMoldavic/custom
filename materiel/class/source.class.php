<?php
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/preinventaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/preinventaireline.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/recufiscal.class.php';

class Source extends CommonObject {

    public $element = 'source';
    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'source';

    // This object reffers to the facture, emprunt or recu_fiscal we want to add to the preinventaire
    public $source_reference_object;
    public $source_reference_type;
    public $source_type_id;
    public $source_table;

    public $active;
    public $datec;
    public $fk_source;
    public $inventoriable;
    public $fk_status;
    public $error = 'UnknownError';
    public $id;
    public $ref;
    public $total_ttc;
    public $total_specified;
    public $remaining_to_specify;
    public $source_type_array;
    public $lines = array(); // List of products specified for this source

    const STATUS_NON_INVENTORIABLE = 0;
    const STATUS_INCOMPLETE = 1;
    const STATUS_COMPLETE = 2;
    const STATUS_INVENTORIED = 3;
    const STATUS_NON_INVENTORIABLE_LABEL = "Non inventoriable";
    const STATUS_INCOMPLETE_LABEL = "Incomplet";
    const STATUS_COMPLETE_LABEL = "Complet";
    const STATUS_INVENTORIED_LABEL = "Inventorié";
    const LINE_INVENTORIABLE_LABEL = 'Inventoriable';
    const LINE_NON_INVENTORIABLE_LABEL = 'Non inventoriable';
    const LINE_AMORTISSABLE_LABEL = 'Amortissable';
    const LINE_NON_AMORTISSABLE_LABEL = 'Non amortissable';

    /**
     *  Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->canvas = '';
    }

    public function fetch($id)
    {
        $sql = "SELECT s.rowid, s.fk_type_source, s.fk_source, s.inventoriable, s.fk_status, s.datec, s.active";
        $sql .= " FROM ".MAIN_DB_PREFIX."source as s ";
        $sql .= " WHERE s.rowid = ".(int) $id;
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql) > 0) {
                $obj = $this->db->fetch_object($resql);
                $this->id                           = $obj->rowid;
                $this->source_type_id     = $obj->fk_type_source;
                $this->fk_source     = $obj->fk_source;
                $this->inventoriable     = $obj->inventoriable;
                $this->fk_status     = $obj->fk_status;
                $this->datec     = $obj->datec;
                $this->active     = $obj->active;
            } else {
                $this->error = 'Pas d\'entrée correspondante à cet ID';
                return 0; // No entry found
            }
        }
        else
        {
            $this->error = 'Database error';
            dol_print_error($this->db);
            return 0;
        }
        $this->create_reference_object($obj->fk_type_source);
        $this->fetch_reference_object($obj->fk_source);
        $this->ref = $this->source_reference_object->ref;
        $this->total_ttc = $this->source_reference_object->total_ttc;
        $this->fetch_lines();
        $this->checkStatus(); // Check for exemple if all the materiel are inventoried, and update the status if required
        return 1;
    }

    public function create_reference_object($source_type_id)
    {
        $this->source_type_array = getSourceTypeArray(1);
        $this->source_type_id = $source_type_id;
        $this->source_table = $this->source_type_array[$source_type_id]['tablename'];
        switch ($this->source_table) {
            case 'facture_fourn':
                $this->source_reference_object = new FactureFournisseur($this->db);
                $this->source_reference_type = 'Facture';
                break;
            case 'recu_fiscal':
                $this->source_reference_object = new RecuFiscal($this->db);
                $this->source_reference_type = 'Reçu Fiscal';
                break;
            // case 'emprunt':
            //     return new Emprunt($db);
                // $this->source_reference_type = 'Emprunt';
            //     break;
        }
    }

    public function fetch_reference_object($id)
    {
        if (!is_object($this->source_reference_object)) {
            $this->error = 'Tried to fetch source reference before it being created';
            return 0;
        } else {
            return $this->source_reference_object->fetch($id);
        }
    }

    /**
     * Insert the source reference to source table in the database
     *
     * @param $preinventoriable is the source preinventoriable (field preinventoriable in the database)
     * 
     */
    public function add($preinventoriable)
    {
        if (!$this->source_reference_object->id || !$this->source_type_id) return 0; 
        // Check if this source is already in the database
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."source";
        $sql .= " WHERE fk_type_source = ". $this->source_type_id;
        $sql .= " AND fk_source = ". $this->source_reference_object->id;
        $resql = $this->db->query($sql);
        if ($this->db->num_rows($resql) > 0) 
        {
            $this->error = "Cette source existe déjà";
            return 0; 
        }
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."source (";
        $sql .= "fk_type_source, ";
        $sql .= "fk_source, ";
        $sql .= "inventoriable";
        $sql .= ") VALUES (";
        $sql .= $this->source_type_id . ", ";
        $sql .= $this->source_reference_object->id . ", ";
        $sql .= $preinventoriable;
        $sql .= ")";
        $result = $this->db->query($sql);
        if ($result) {
            $this->db->commit();
            return 1;
        }
        else return 0; 
    }

    /**
     * Fetch every product line corresponding to this source in the table preinventaire
     */
    public function fetch_lines()
    {
        // Reset values
        $this->lines = array();
        $sql = "SELECT p.rowid";
        $sql .= " FROM ".MAIN_DB_PREFIX."preinventaire as p";
        $sql .= " WHERE p.fk_source = ".$this->id;
        $resql = $this->db->query($sql);
        $num = $this->db->num_rows($resql);
        $this->total_specified = 0;
        if ($num)
        {
            $i = 0;
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);
                $line = new PreinventaireLine($this->db);
                $line->fetch($obj->rowid);
                $this->total_specified += $line->valeur;
                $this->lines[$line->id] = $line;
                $i++;
            }
        }
        $this->remaining_to_specify = $this->total_ttc - $this->total_specified;
        return 1;
    }

    /**
     * Check and update source status
     */
    public function checkStatus()
    {
        if ($this->inventoriable) 
        {
            // Check if all the materiel are either inventoried or not inventoriable
            $inventoried = 1;
            foreach($this->lines as $line) 
            {
                if ($line->status == PreinventaireLine::STATUS_NON_INVENTORIE) $inventoried = 0;
            }
            if ($inventoried &&  $this->remaining_to_specify == 0) {
                if ($this->fk_status != Source::STATUS_INVENTORIED)
                {
                    $this->setStatus(Source::STATUS_INVENTORIED);
                }
            } 
            else // Else check if this source is complete or incomplete
            {
                if ($this->remaining_to_specify == 0) $this->setStatus(Source::STATUS_COMPLETE);
                else $this->setStatus(Source::STATUS_INCOMPLETE);
            }
        }
        else
        {
            $this->setStatus(Source::STATUS_NON_INVENTORIABLE);
        }
    }

	/**
	 *	Return label of a status
	 *
	 *	@param      int		$status        	Id status
	 *	@param      int		$mode          	0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=short label + picto, 6=long label + picto
	 *	@return     string        			Label of status
	 */
	public function LibStatus($status, $mode = 0)
	{
		$statusType = 'status0';
		switch ($status) {
            case Source::STATUS_NON_INVENTORIABLE:
                $labelStatus = Source::STATUS_NON_INVENTORIABLE_LABEL;
                $statusType = 'status6';
                break;
            case Source::STATUS_INCOMPLETE:
                $labelStatus = Source::STATUS_INCOMPLETE_LABEL;
                $statusType = 'status5';
                break;
            case Source::STATUS_COMPLETE:
                $labelStatus = Source::STATUS_COMPLETE_LABEL;
                $statusType = 'status4';
                break;
            case Source::STATUS_INVENTORIED:
                $labelStatus = Source::STATUS_INVENTORIED_LABEL;
                $statusType = 'status4';
                break;
        }
		return dolGetStatus($labelStatus, '', '', $statusType, $mode);
	}

    public function getNomUrl($withpicto = 1) 
    {
        global $conf;
        $url = DOL_URL_ROOT . '/custom/materiel/preinventaire/source/card.php?id='.$this->id;
        $label = '<u>Source</u>';
        $label .= '<br><b>Type : </b> '.$this->source_reference_type;
        $label .= '<br><b>Montant : </b> '.price($this->total_ttc, 1, '', 0, -1, -1, $conf->currency);
        $label .= '<br><b>État : </b> '.$this->LibStatus($this->fk_status, 6);
        $linkclose = ' title="'.dol_escape_htmltag($label, 1, 1).'"';
        $linkclose .= ' class="nowraponall classfortooltip"';
		$linkstart = '<a href="'.$url.'"';
		$linkstart .= $linkclose.'>';
		$linkend = '</a>';
        $picto = $this->source_reference_object->picto;
		if ($this->inventoriable) $result .= $linkstart;
        else $result .= '<span>';
		if ($withpicto) $result .= talm_img_object(($notooltip ? '' : $label), $picto, ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
		$result .= $this->source_reference_object->ref;
		if ($this->inventoriable) $result .= $linkend;
        else $result .= '</span>';
		return $result;
    }

    /**
     *  Return if at least one photo is available
     *
     * @param  string $sdir Directory to scan
     * @return boolean                 True if at least one photo is available, False if not
     */
    public function is_photo_available($sdir)
    {
        // phpcs:enable
        include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
        global $conf;
        $dir = $sdir;
        if (!empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO)) {
        	$dir .= '/'.get_exdir($this->id, 2, 0, 0, $this, 'source').$this->id."/photos/";
        } else {
        	$dir .= '/'.get_exdir(0, 0, 0, 0, $this, 'source').'/';
        }
        $nbphoto = 0;
        $dir_osencoded = dol_osencode($dir);
        if (file_exists($dir_osencoded)) {
            $handle = opendir($dir_osencoded);
            if (is_resource($handle)) {
                while (($file = readdir($handle)) !== false)
                {
                    if (!utf8_check($file)) {
                    	$file = utf8_encode($file); // To be sure data is stored in UTF8 in memory
                    }
                    if (dol_is_file($dir.$file) && image_format_supported($file) >= 0) {
                    	return true;
                    }
                }
            }
        }
        return false;
    }

    public function formAddObjectLine()
    {
        global $form, $inventoriable, $amortissable;
        //WYSIWYG Editor
        require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
        $array = array(1=>'Oui', 0=>'Non');
        print '<tr>';
        // Description
        print '<td>';
        $doleditor = new DolEditor('description', GETPOST('description', 'none'), '', 160, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '90%');
        $doleditor->Create();
        print '</td>';
        // Valeur
        print '<td>';
        print '<input type="number" min="0" step="0.01" name="valeur" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.GETPOST('valeur', 'float').'">';
        print '</td>';
        // Inventoriable
        print '<td>';
        print $form->selectarray('inventoriable', $array, $inventoriable);
        print '</td>';
        // Amortissable
        print '<td>';
        print $form->selectarray('amortissable', $array, $amortissable);
        print '</td>';

        // Amortissable
        print '<td>';
        print '<input type="submit" class="button" value="Ajouter" name="addline" id="addline">';
        print '</td>';
        print '<td>';
        print '<input type="number" min="0" step="0.01" placeholder="Nombre à ajouter..." id="nombre" name="nombre" maxlength="255">';
        print '</td>';
        print '</tr>';
    }

    public function addLine($description, $valeur, $inventoriable, $amortissable, $remaintospecify, $fksource, $nombre)
    {
        // First check if the value of the object we want to add doesn't exceed the specified value of the source
        if($remaintospecify == 0)
        {
            $remaintospecify = $this->remaining_to_specify;
        }
        if ($nombre == 0 || $nombre == "") $nombre = 1;
        $remain = floatval($remaintospecify);
        if (empty($this->line)) $this->fetch_lines();


        if (($remain != 0 && $valeur > $remain) || ($nombre != 0 && (($valeur * $nombre) > $remain))   ){
            $this->error = "La valeur du matériel dépasse la valeur à compléter de la source";
            return 0;
        }

        for($i = 1; $i <= $nombre; $i++)
        {
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."preinventaire (";
            $sql .= "fk_source, ";
            $sql .= "description, ";
            $sql .= "valeur, ";
            $sql .= "inventoriable, ";
            $sql .= "amortissable";
            $sql .= ") VALUES (";
            if(!empty($fksource))
            {
                $sql .= $fksource . ", ";
            }
            else
            {
                $sql .= $this->id . ", ";
            }
            $sql .= "'".$description."', ";
            $sql .= intval($valeur).", ";
            $sql .= $inventoriable.", ";
            $sql .= $amortissable;
            $sql .= ')';
            $resql = $this->db->query($sql);
        }
        if (!$resql) {
            $this->error = "Database Error";
            return 0;
        } else {
            // Check and update status
            $this->checkStatus();
            $this->db->commit();
            return 1;
        }
    }

    /**
     * Print objects line related to this source
     * @param $action action code (can be editline for exemple)
     * @param $lineid id of the line selected (used to print an edit form for the corresponding line we want to edit)
     */
    public function printObjectLines($action = '', $lineid ='')
    {
        global $conf, $form;
        //WYSIWYG Editor
        require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
        $this->fetch_lines();
        if (empty($this->lines)) return 1; // Stop the function here if there's no line
        else 
        {
            foreach ($this->lines as $line)
            {
                $array = array(1=>'Oui', 0=>'Non');
                // If this line is edited, we will print input fields instead of text
                $islineedited = (($action == 'editline' && $line->id == $lineid && $line->status != PreinventaireLine::STATUS_INVENTORIE) ? 1 : 0);
                if ($islineedited) {
                    print '<input type="hidden" name="lineid" value="'. $line->id .'" />';
                }
                print '<tr>';
                // Description
                print '<td>';
                if ($islineedited) {  
                    $doleditor = new DolEditor('description', $line->description, '', 160, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '90%');
                    $doleditor->Create();
                }
                else print $line->description;
                print '</td>';
                // Valeur
                print '<td>';
                if ($islineedited) {  
                    print '<input type="number" min="0" step="0.01" name="valeur" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.$line->valeur.'">';
                }
                else print price($line->valeur, 1, '', 0, -1, -1, $conf->currency);
                print '</td>';
                // Inventoriable
                print '<td class="center">';
                if ($islineedited) {  
                    print $form->selectarray('inventoriable', $array, $line->inventoriable);
                }
                else 
                {
                    $status = ($line->inventoriable ? 'status4' : 'status5');
                    $label = ($line->inventoriable ? Source::LINE_INVENTORIABLE_LABEL : Source::LINE_NON_INVENTORIABLE_LABEL);
                    print dolGetStatus($label, '', '', $status, 3);
                }
                print '</td>';
                // Amortissable
                print '<td class="center">';
                if ($islineedited) {  
                    print $form->selectarray('amortissable', $array, $line->amortissable);
                }
                else
                {
                    $status = ($line->amortissable ? 'status4' : 'status5');
                    $label = ($line->amortissable ? Source::LINE_AMORTISSABLE_LABEL : Source::LINE_NON_AMORTISSABLE_LABEL);
                    print dolGetStatus($label, '', '', $status, 3);
                }               
                print '</td>';
                // État
                print '<td class="center">';
				print $line->LibStatus($line->status, 4);
                print '</td>';
          
                if ($islineedited && $line->status != 2) // We don't allow modification on an already inventoried materiel
                {  
                    print '<td>';
                    print '<input type="submit" class="button" value="Modifier" name="save" id="addline">';
                    print '<input type="submit" class="button" value="Annuler" name="cancel" id="addline">';                
                    print '</td>';
                } 
                elseif ($line->status != 2) 
                {
                    // Modify / Remove button
                    print '<td align="center">';
                    print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?action=addline&description='.$line->description.'&valeur='.$line->valeur.'&inventoriable='.$line->inventoriable.'&amortissable='.$line->amortissable.'&remaintospecify='.intval($this->remaining_to_specify).'&fksource='.$line->fk_source.'">Cloner</a>';
                    print '&nbsp;&nbsp;&nbsp;&nbsp;';
                    print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?action=editline&id='. $this->id .'&lineid='.$line->id.'">'.img_edit().'</a>';
                    print '&nbsp;&nbsp;&nbsp;&nbsp;';
                    print '<a href="'.$_SERVER["PHP_SELF"].'?action=ask_deleteline&id='. $this->id .'&lineid='.$line->id.'">'.img_delete().'</a>';
                    print '</td>';
                } 
                else 
                {
                    print '<td align="center">';
                    print '</td>';
                }
                print '</tr>';
            }
        }
    }

    public function deleteLine($lineid)
    {
        // First see if the line is indeed associatied with this source
        if (!array_key_exists($lineid, $this->lines)) { 
            // This line doesn't correspond to this source
            $this->error = 'Ligne de matériel invalide pour cette source';
            return 0;
        } else {
            // Check if this line isn't inventoried (we don't allow deleting an inventoried line)
            if ($this->lines[$lineid]->status == PreinventaireLine::STATUS_INVENTORIE)
            {
                $this->error = 'Impossible de supprimer un matériel inventorié';
                return 0;
            }
            $sql = 'DELETE FROM '. MAIN_DB_PREFIX .'preinventaire';
            $sql .= ' WHERE rowid = ' . $lineid;
            $resql = $this->db->query($sql);
            if (!$resql) {
                $this->error = 'Database error';
                return 0;
            } else {
                // Check and update status
                $this->checkStatus();
                $this->db->commit();
                return 1;
            }
        }
    }

    public function updateLine($lineid, $description, $valeur, $inventoriable, $amortissable)
    {
        // Check if this line isn't inventoried, if so return an error
        if ($this->lines[$lienid]->status == PreinventaireLine::STATUS_INVENTORIE)
        {
            $this->error = 'Impossible de modifier un matériel inventorié';
            return 0;
        }
        // Check if the line is indeed associatied with this source
        $sql = 'SELECT rowid FROM '. MAIN_DB_PREFIX .'preinventaire';
        $sql .= ' WHERE fk_source = ' . $this->id;
        $sql .= ' AND rowid = ' . $lineid;
        $resql = $this->db->query($sql);
        if ($this->db->num_rows($resql) < 1) { 
            // This line doesn't correspond to this source
            $this->error = 'Ligne de matériel invalide pour cette source';
            return 0;
        } else {
            // Check if the value of the object we want to update doesn't exceed the specified value of the source
            if (empty($this->lines)) $this->fetch_lines();
            // Get the remaining to specify value without the value of the line we want to update
            $new_remaining_to_specify = $this->remaining_to_specify + $this->lines[$lineid]->valeur;
            if ($valeur > $new_remaining_to_specify) {
                $this->error = "La valeur du matériel dépasse la valeur à compléter de la source";
                return 0;
            } else {
                $sql = "UPDATE ". MAIN_DB_PREFIX ."preinventaire";
                $sql .= " SET description = '". $description ."', ";
                $sql .= "valeur = ". $valeur .", ";
                $sql .= "inventoriable = ". $inventoriable .", ";
                $sql .= "amortissable = ". $amortissable;
                $sql .= " WHERE rowid = ". $lineid;
                $resql = $this->db->query($sql);
                if (!$resql) {
                    $this->error = 'Database error';
                    return 0;
                } else {
                    // Check and update status
                    $this->checkStatus();
                    $this->db->commit();
                    return 1;
                }
            }
        }
    }

    /**
     * Update status of the source (Incomplete, Complete, etc...) in the database
     * @param int $statusId Id of the status
     */
    public function setStatus($statusId)
    {
        $sql = "UPDATE ".MAIN_DB_PREFIX."source SET ";
        $sql .= "fk_status = ".$statusId;
        $sql .= " WHERE rowid = " . $this->id;
        $resql = $this->db->query($sql);
        if (!$resql) return 0;
        else {
            $this->fk_status = $statusId;
            $this->db->commit();
            return 1;
        }
    }
    
    public function delete()
    {
        $this->fetch_lines();
        if (!empty($this->lines)) {
            foreach ($this->lines as $line) {
                $result = $this->deleteLine($line->id);
                if (!$result) return 0;
            }
        }
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."source";
        $sql .= " WHERE rowid = ". $this->id;
        $resql = $this->db->query($sql);
        if ($resql) {
            $this->db->commit();
            return 1;
        } else {
            return 0;
        }
    }
}