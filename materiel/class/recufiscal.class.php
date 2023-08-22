<?php
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/source.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/preinventaireline.class.php';

Class RecuFiscal extends CommonObject 
{
    public $element = 'recufiscal';
    public $table_element = 'recu_fiscal';
    public $picto = 'recufiscal';
    public $error = 'Unknown Error';

    public $id;
    public $active;
    public $ref;
    public $fk_type;
    public $fk_donateur;
    public $donateur_object;
    public $total_ttc;
    public $notes;
    public $fk_statut;
    public $datec;
    public $fk_user_author;
    public $tms;
    public $fk_user_modif;

    public $lines = array();

    const TYPE_DON_NATURE = 1;
    const TYPE_ABANDON_DE_FRAIS = 2;
    
    const STATUS_DRAFT = 0;
    const STATUS_VALIDATED = 1;
    const STATUS_SENT = 2;
    const STATUS_REPATRIATION = 3;
    const STATUS_REPATRIATED = 4;
    const STATUS_CANCELED = 5;
    const STATUS_RECEIPT_PROBLEM = 6;
    
    const STATUS_DRAFT_LABEL = 'Brouillon (à valider)';
    const STATUS_VALIDATED_LABEL = 'Validé (en attente d\'envoi)';
    const STATUS_SENT_LABEL = 'Envoyé';
    const STATUS_REPATRIATION_LABEL = 'En rapatriement';
    const STATUS_REPATRIATED_LABEL = 'Rapatrié';
    const STATUS_CANCELED_LABEL = 'Annulé';
    const STATUS_RECEIPT_PROBLEME = 'Problème de reçu';

    /**
    *  Constructor
    * @param DoliDB $db Database handler
    * @return void
    */
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function fetch($id) 
    {
        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."recu_fiscal";
        $sql .= " WHERE rowid = ".$id;
        $resql = $this->db->query($sql);
        
        if ($resql)
        {
            if ($this->db->num_rows($resql) < 1) 
            {
                $this->error = "Invalid ID";
                return 0;
            }
            else
            {
                $obj = $this->db->fetch_object($resql);
                
                $this->id = $obj->rowid;
                $this->active = $obj->active;
                $this->ref = $obj->ref;
                $this->fk_type = $obj->fk_type;
                $this->fk_donateur = $obj->fk_donateur;
                $this->notes = $obj->notes;
                $this->fk_statut = $obj->fk_statut;
                $this->datec = $this->db->jdate($obj->datec);
                $this->date_recu_fiscal = $this->db->jdate($obj->date_recu_fiscal);
                $this->fk_user_author = $obj->fk_user_author;
                $this->datem = $this->db->jdate($obj->tms);
                $this->fk_user_modif = $obj->fk_user_modif;

                $this->donateur_object = new Donateur($this->db);
                $this->donateur_object->fetch($this->fk_donateur);
                
                $this->fetch_lines();

                return 1;
            }
        } 
        else 
        {
            $this->error = "Database error";
            return 0;
        }
    } 
    /**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0)
	{
		global $conf, $langs;
       
		$langs->load("bills");
		if (!dol_strlen($modele)) {
			$modele = 'html_cerfafr_materiel';
			if ($this->model_pdf) {
				$modele = $this->model_pdf;
			} elseif (!empty($conf->global->DON_ADDON_MODEL)) {
				$modele = $conf->global->DON_ADDON_MODEL;
			}
		}
		// Increase limit for PDF build
		$err = error_reporting();
		error_reporting(0);
		@set_time_limit(120);
		error_reporting($err);
		$srctemplatepath = '';
		// If selected modele is a filename template (then $modele="modelname:filename")
		$tmp = explode(':', $modele, 2);
		if (!empty($tmp[1])) {
			$modele = $tmp[0];
			$srctemplatepath = $tmp[1];
		}
     
		// Search template files
		$file = ''; $classname = ''; $filefound = 1;
        $file = DOL_DOCUMENT_ROOT."/custom/recufiscal/html_cerfafr_materiel.modules.php";
       
		// Charge le modele
        require_once $file;

        $object = $this;
        $classname = $modele;
        $obj = new $classname($this->db);
        
        // We save charset_output to restore it because write_file can change it if needed for
        // output format that does not support UTF8.
        $sav_charset_output = $outputlangs->charset_output;
        if ($obj->write_file($object, $outputlangs, $srctemplatepath, $hidedetails, $hidedesc, $hideref) > 0) {
            $outputlangs->charset_output = $sav_charset_output;
            // we delete preview files
            require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
            dol_delete_preview($object);
            return 1;
        } else {
            $outputlangs->charset_output = $sav_charset_output;
            dol_syslog("Erreur dans don_create");
            dol_print_error($this->db, $obj->error);
            return 0;
        }
	}

    public function delete()
    {
        // Check if there is no source related to this receipt
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."source ";
        $sql .= "WHERE fk_origine = " . $this->id;
        $sql .= " AND fk_type_source = 2";
        $resql = $this->db->query($sql);
        
        if (!$resql)
        {
            // Query failed
            $this->error = 'Erreur lors de la vérification des sources';
            return 0;
        }
        
        $num = $this->db->num_rows($resql);
        if ($num > 0)
        {
            // A source has been found related to this receipt
            $this->error = 'Ce reçu fiscal est lié à une source';
            return 0;
        }
        
        // Delete every line related to this receipt
        $this->fetch_lines();
        if (!empty($this->lines))
        {
            $error = 0;
            foreach($this->lines as $id=>$line)
            {
                $result = $this->deleteLine($id);
                if (!$result) $error++;
            }
            if ($error)
            {
                // One or several lines couldn't be deleted
                $this->error = 'Erreur lors de la suppression d\'une ou plusieurs ligne de produit';
                return 0;                
            }
        }
        
        // Delete the receipt from the database
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."recu_fiscal ";
        $sql .= "WHERE rowid = ".$this->id;
        $resql = $this->db->query($sql);
        if (!$resql)
        {
            $this->error = 'Erreur lors de la suppression du reçu fiscal';
            return 0;
        }
        else
        {
            $this->db->commit();
            return 1;
        }
    }
    
    public function deleteLine($id)
    {
        // Check if there is no source related to this receipt
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."source ";
        $sql .= "WHERE fk_origine = " . $this->id;
        $sql .= " AND fk_type_source = 2";
        $resql = $this->db->query($sql);
        $num = $this->db->num_rows($resql);
        if ($num > 0)
        {
            // A source has been found related to this receipt
            $this->error = 'Ce reçu fiscal est lié à une source';
            return 0;
        }

        // Check if the line id corresponds to this receipt
        if (!array_key_exists($id, $this->lines))
        {
            $this->error = "Ligne de produit invalide pour ce reçu fiscal";
            return 0;
        }

        // Delete line from database
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."recu_fiscal_det ";
        $sql .= "WHERE rowid = ".$id;
        $resql = $this->db->query($sql);
        if (!$resql) return 0;
        else 
        {
            $this->db->commit();
            return 1;
        }
    }

    
	/**
	 * Appelle les fonctions de vérification des données et d'ajout du recu fiscal dans la base de données
	 * @param $user Utilisateur qui crée l'entretien
	 * @return int 1 if OK, 0 if KO
	 */
	public function create($user)
	{
		if (!$this->checkAndSanitizeDataForCreation()) {
			$this->error = 'Invalid data';
			return 0;
		} elseif (!$this->insertToDatabase($user)) {
			$this->error = 'Database error';
			return 0;
		}
		return 1;
	}

    /**
     * Vérification des données pour la création de l'entretien
     */
    private function checkAndSanitizeDataForCreation()
    {
        if (empty($this->fk_donateur)) return 0;
        if (empty($this->fk_type)) return 0;

        return 1;
    }

    private function dateToMySQL($date){
        $tabDate = explode('/' , $date);
        $date  = $tabDate[2].'-'.$tabDate[1].'-'.$tabDate[0];
        $date = date('Y-m-d', strtotime($date) );
        return $date;
    }
    
	private function insertToDatabase($user)
	{
        // TODO : Check for doubles
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."recu_fiscal (";
		$sql .= "fk_donateur";
		$sql .= ", fk_type";
        $sql .= ", notes";
        $sql .= ", date_recu_fiscal";
		$sql .= ", fk_user_author"; 
		$sql .= ") VALUES (";
		$sql .= $this->fk_donateur;
		$sql .= ", ".$this->fk_type;
		$sql .= ", '". $this->notes."'";
        $sql .= ", '".$this->dateToMySQL($this->date_recu_fiscal)."'";
		$sql .= ", ". $user->id;
		$sql .= ")";
		$result = $this->db->query($sql);

		if ($result) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."recu_fiscal");
            
            // Now add the ref containing the id
            $donateur = new Donateur($this->db);
            $donateur->fetch($this->fk_donateur);
            // Création de la réference dans le format : DON-I-<ANNÉE>-<ID>-<NOM DONATEUR>
            $ref = 'DON-I-'.date("Y").'-'.$this->id.'-'.(!empty($donateur->nom) ? $donateur->nom : $donateur->societe);
            $sql = "UPDATE ".MAIN_DB_PREFIX."recu_fiscal";
            $sql .= " SET ref = '".$ref."'";
            $sql .= " WHERE rowid = ".$this->id;
            $resql = $this->db->query($sql);
            
			if (!$resql)
            {
                $this->error = 'Error while updating reference';
                return 0;
            }
            else 
            {
                $this->db->commit();
                return 1;
            }
		} else return 0;
	}

    /**
     * Fetch every product line corresponding to this recufiscal in the table recu_fiscal_det
     */
    public function fetch_lines()
    {
        // Reset values
        $this->lines = array();

        $sql = "SELECT rd.rowid";
        $sql .= " FROM ".MAIN_DB_PREFIX."recu_fiscal_det as rd";
        $sql .= " WHERE rd.fk_recu_fiscal = ".$this->id;
        $resql = $this->db->query($sql);
        $num = $this->db->num_rows($resql);

        $this->total_ttc = 0;
        
        if ($num)
        {
            $i = 0;
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);
                $line = new RecuFiscalLine($this->db);
                $line->fetch($obj->rowid);
                $this->total_ttc += $line->valeur * $line->qty;
                $this->lines[$line->id] = $line;

                $i++;
            }
        }

        return 1;
    }


    public function getNomUrl($withpicto = 1, $notooltip = 0, $style = '')
    {
        $label = '<u>Reçu fiscal</u>';
        $label .= '<br><b>Réf : </b> '.$this->ref;
        $label .= '<br><b>Donateur : </b> '.$this->donateur_object->ref;
        $label .= '<br><b>Montant : </b> '.price($this->total_ttc, 1, '', 0, -1, -1, $conf->currency);
        $label .= '<br><b>Notes : </b> '.($this->notes ? $this->notes : '<i>Pas de notes</i>');
        
        $linkclose = '';

        if (empty($notooltip)) {
            $linkclose .= ' title="'.dol_escape_htmltag($label, 1, 1).'"';
            $linkclose .= ' class="nowraponall classfortooltip"';
        }
        $url = DOL_URL_ROOT.'/custom/recufiscal/card.php?action=view&id='.$this->id;
        $linkstart = '<a href="'.$url.'" '.$style;
        $linkstart .= $linkclose.'>';
        $linkend = '</a>';
        $result = $linkstart;
        if (!$nopicto) $result .= (talm_img_object(($notooltip ? '' : $label), 'recufiscal', ($notooltip ? 'class="paddingright"'.$style : 'class="paddingright classfortooltip"'.$style), 0, 0, $notooltip ? 0 : 1));
		$result .= $this->ref;
        $result .= $linkend;

        return $result;
    }

    /**     
     * Return the label of the type
     */
    public function getLibType()
    {
        if ($this->fk_type == self::TYPE_DON_NATURE) {
            return 'Don en nature';
        } elseif ($this->fk_type == self::TYPE_ABANDON_DE_FRAIS) {
            return 'Abandon de frais';
        }
    }

    /**
     *  Return if at least one photo is available
     *
     * @param  string $sdir Directory to scan
     * @return boolean True if at least one photo is available, False if not
     */
    public function is_photo_available($sdir)
    {
        // phpcs:enable
        include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';

        global $conf;

        $dir = $sdir;
        if (!empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO)) {
        	$dir .= '/'.get_exdir($this->id, 2, 0, 0, $this, 'recufiscal').$this->id."/photos/";
        } else {
        	$dir .= '/'.get_exdir(0, 0, 0, 0, $this, 'recufiscal').'/';
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
            case RecuFiscal::STATUS_DRAFT:
                $labelStatus = RecuFiscal::STATUS_DRAFT_LABEL;
                $statusType = 'status0';
                break;
            case RecuFiscal::STATUS_VALIDATED:
                $labelStatus = RecuFiscal::STATUS_VALIDATED_LABEL;
                $statusType = 'status7';
                break;
            case RecuFiscal::STATUS_SENT:
                $labelStatus = RecuFiscal::STATUS_SENT_LABEL;
                $statusType = 'status4';
                break;
            default:
                $labelStatus = 'Status Error';
                $statusType = 'status6';
                break;
        }
		
		return dolGetStatus($labelStatus, '', '', $statusType, $mode);
	}

    public function formAddObjectLine()
    {
        global $form;
        //WYSIWYG Editor
        require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

        print '<tr>';

        // Description
        print '<td>';
        $doleditor = new DolEditor('description', GETPOST('description', 'none'), '', 160, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '90%');
        $doleditor->Create();
        print '</td>';

        // Valeur
        print '<td>';
        print '<input type="number" step="0.01" name="valeur" maxlength="255" value="'.GETPOST('valeur', 'float').'">';
        print '</td>';

        // Quantity
        print '<td class="center">';
        print '<input type="number" step="1" name="qty" maxlength="255" value="'.GETPOST('qty', 'int').'">';
        print '</td>';

        // BLANK
        print '<td>';
        print '</td>';

        // Submit button
        print '<td>';
        print '<input type="submit" class="button" value="Ajouter" name="addline" id="addline">';
        print '</td>';

        print '</tr>';
    }
    public function addLine($description, $valeur, $qty)
    {
        // First check if the status is draft
        if ($this->fk_statut != 0) return 0;

        $sql = "INSERT INTO ".MAIN_DB_PREFIX."recu_fiscal_det (";
        $sql .= "fk_recu_fiscal, ";
        $sql .= "description, ";
        $sql .= "valeur, ";
        $sql .= "qty";
        $sql .= ") VALUES (";
        $sql .= $this->id . ", ";
        $sql .= "'".str_replace("'","\'",$description)."', ";
        $sql .= $valeur . ", ";
        $sql .= $qty;
        $sql .= ')';

        $resql = $this->db->query($sql);
        if (!$resql) {
            $this->error = "Database Error";
            return 0;
        } else {
            $this->db->commit();
            return 1;
        }
    }

    public function setStatus($statusid)
    {
        $sql = "UPDATE ".MAIN_DB_PREFIX."recu_fiscal ";
        $sql .= "SET fk_statut = {$statusid} ";
        $sql .= "WHERE rowid = {$this->id}";
        $resql = $this->db->query($sql);
        if (!$resql)
        {
            $this->error = "Query error";
            return 0;
        }
        else 
        {
            $this->db->commit();
            return 1;
        }
    }


    /**
     * Print objects line related to this recufiscal
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
                // If this line is edited, we will print input fields instead of text
                $islineedited = (($action == 'editline' && $line->id == $lineid && $this->fk_statut == RecuFiscal::STATUS_DRAFT) ? 1 : 0);

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

                // Valeur unitaire
                print '<td>';
                if ($islineedited) {  
                    print '<input type="number" step="0.01" name="valeur" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.$line->valeur.'">';
                }
                else print price($line->valeur, 1, '', 0, -1, -1, $conf->currency);
                print '</td>';

                // Quantity
                print '<td class="center">';
                if ($islineedited) {  
                    print '<input type="number" step="1" name="qty" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.$line->qty.'">';
                }
                else print $line->qty;
                print '</td>';

                // Valeur totale
                print '<td>';
                print price($line->valeur * $line->qty, 1, '', 0, -1, -1, $conf->currency);
                print '</td>';

                
                if ($islineedited && $this->fk_statut == RecuFiscal::STATUS_DRAFT) 
                {  
                    print '<td>';
                    print '<input type="submit" class="button" value="Modifier" name="save" id="addline">';
                    print '<input type="submit" class="button" value="Annuler" name="cancel" id="addline">';                
                    print '</td>';
                } 
                elseif ($this->fk_statut == RecuFiscal::STATUS_DRAFT) 
                {
                    // Modify / Remove button
                    print '<td align="center">';
                    print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?action=editline&id='. $this->id .'&lineid='.$line->id.'">'.img_edit().'</a>';
                    print '&nbsp;';
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


    // public function deleteLine($lineid)
    // {
    //     // First see if the line is indeed associatied with this source
    //     if (!array_key_exists($lineid, $this->lines)) { 
    //         // This line doesn't correspond to this source
    //         $this->error = 'Ligne de matériel invalide pour cette source';
    //         return 0;
    //     } else {
    //         // Check if this line isn't inventoried (we don't allow deleting an inventoried line)
    //         if ($this->lines[$lineid]->status == PreinventaireLine::STATUS_INVENTORIE)
    //         {
    //             $this->error = 'Impossible de supprimer un matériel inventorié';
    //             return 0;
    //         }

    //         $sql = 'DELETE FROM '. MAIN_DB_PREFIX .'preinventaire';
    //         $sql .= ' WHERE rowid = ' . $lineid;
    //         $resql = $this->db->query($sql);
    //         if (!$resql) {
    //             $this->error = 'Database error';
    //             return 0;
    //         } else {
    //             // Check and update status
    //             $this->checkStatus();
    //             $this->db->commit();
    //             return 1;
    //         }
    //     }
    // }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *      Show the box with list of available documents for object
	 *
	 *      @param      string				$modulepart         propal, facture, facture_fourn, ...
	 *      @param      string				$modulesubdir       Sub-directory to scan (Example: '0/1/10', 'FA/DD/MM/YY/9999'). Use '' if file is not into subdir of module.
	 *      @param      string				$filedir            Directory to scan
	 *      @param      string				$urlsource          Url of origin page (for return)
	 *      @param      int					$genallowed         Generation is allowed (1/0 or array of formats)
	 *      @param      int					$delallowed         Remove is allowed (1/0)
	 *      @param      string				$modelselected      Model to preselect by default
	 *      @param      integer				$allowgenifempty	Show warning if no model activated
	 *      @param      integer				$forcenomultilang	Do not show language option (even if MAIN_MULTILANGS defined)
	 *      @param      int					$iconPDF            Show only PDF icon with link (1/0)
	 * 		@param		int					$notused	        Not used
	 * 		@param		integer				$noform				Do not output html form tags
	 * 		@param		string				$param				More param on http links
	 * 		@param		string				$title				Title to show on top of form
	 * 		@param		string				$buttonlabel		Label on submit button
	 * 		@param		string				$codelang			Default language code to use on lang combo box if multilang is enabled
	 * 		@return		int										<0 if KO, number of shown files if OK
	 *      @deprecated                                         Use print xxx->showdocuments() instead.
	 */
	public function show_documents($modulepart, $modulesubdir, $filedir, $urlsource, $genallowed, $delallowed = 0, $modelselected = '', $allowgenifempty = 1, $forcenomultilang = 0, $iconPDF = 0, $notused = 0, $noform = 0, $param = '', $title = '', $buttonlabel = '', $codelang = '')
	{
		// phpcs:enable
		$this->numoffiles = 0;
		print $this->showdocuments($modulepart, $modulesubdir, $filedir, $urlsource, $genallowed, $delallowed, $modelselected, $allowgenifempty, $forcenomultilang, $iconPDF, $notused, $noform, $param, $title, $buttonlabel, $codelang);
		return $this->numoffiles;
	}
	/**
	 *      Return a string to show the box with list of available documents for object.
	 *      This also set the property $this->numoffiles
	 *
	 *      @param      string				$modulepart         Module the files are related to ('propal', 'facture', 'facture_fourn', 'mymodule', 'mymodule:MyObject', 'mymodule_temp', ...)
	 *      @param      string				$modulesubdir       Existing (so sanitized) sub-directory to scan (Example: '0/1/10', 'FA/DD/MM/YY/9999'). Use '' if file is not into a subdir of module.
	 *      @param      string				$filedir            Directory to scan (must not end with a /). Example: '/mydolibarrdocuments/facture/FAYYMM-1234'
	 *      @param      string				$urlsource          Url of origin page (for return)
	 *      @param      int|string[]        $genallowed         Generation is allowed (1/0 or array list of templates)
	 *      @param      int					$delallowed         Remove is allowed (1/0)
	 *      @param      string				$modelselected      Model to preselect by default
	 *      @param      integer				$allowgenifempty	Allow generation even if list of template ($genallowed) is empty (show however a warning)
	 *      @param      integer				$forcenomultilang	Do not show language option (even if MAIN_MULTILANGS defined)
	 *      @param      int					$iconPDF            Deprecated, see getDocumentsLink
	 * 		@param		int					$notused	        Not used
	 * 		@param		integer				$noform				Do not output html form tags
	 * 		@param		string				$param				More param on http links
	 * 		@param		string				$title				Title to show on top of form. Example: '' (Default to "Documents") or 'none'
	 * 		@param		string				$buttonlabel		Label on submit button
	 * 		@param		string				$codelang			Default language code to use on lang combo box if multilang is enabled
	 * 		@param		string				$morepicto			Add more HTML content into cell with picto
	 *      @param      Object              $object             Object when method is called from an object card.
	 *      @param		int					$hideifempty		Hide section of generated files if there is no file
	 *      @param      string              $removeaction       (optional) The action to remove a file
	 * 		@return		string              					Output string with HTML array of documents (might be empty string)
	 */
	public function showdocuments($modulepart, $modulesubdir, $filedir, $urlsource, $genallowed, $delallowed = 0, $modelselected = '', $object = null, $allowgenifempty = 1, $forcenomultilang = 0, $iconPDF = 0, $notused = 0, $noform = 0, $param = '', $title = '', $buttonlabel = '', $codelang = '', $morepicto = '', $hideifempty = 0, $removeaction = 'remove_file')
	{
		global $dolibarr_main_url_root;
		// Deprecation warning
		if (!empty($iconPDF)) {
			dol_syslog(__METHOD__.": passing iconPDF parameter is deprecated", LOG_WARNING);
		}
   
		global $langs, $conf, $user, $hookmanager;
		global $form;
		$reshook = 0;
		if (is_object($hookmanager)) {
			$parameters = array(
				'modulepart'=>&$modulepart,
				'modulesubdir'=>&$modulesubdir,
				'filedir'=>&$filedir,
				'urlsource'=>&$urlsource,
				'genallowed'=>&$genallowed,
				'delallowed'=>&$delallowed,
				'modelselected'=>&$modelselected,
				'allowgenifempty'=>&$allowgenifempty,
				'forcenomultilang'=>&$forcenomultilang,
				'noform'=>&$noform,
				'param'=>&$param,
				'title'=>&$title,
				'buttonlabel'=>&$buttonlabel,
				'codelang'=>&$codelang,
				'morepicto'=>&$morepicto,
				'hideifempty'=>&$hideifempty,
				'removeaction'=>&$removeaction
			);
			$reshook = $hookmanager->executeHooks('showDocuments', $parameters, $object); // Note that parameters may have been updated by hook
			// May report error
			if ($reshook < 0) {
				setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
			}
		}
		// Remode default action if $reskook > 0
		if ($reshook > 0) {
			return $hookmanager->resPrint;
		}
		if (!is_object($form)) {
			$form = new Form($this->db);
		}
       
		include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		// For backward compatibility
		if (!empty($iconPDF)) {
			return $this->getDocumentsLink($modulepart, $modulesubdir, $filedir);
		}
		// Add entity in $param if not already exists
		if (!preg_match('/entity\=[0-9]+/', $param)) {
			$param .= ($param ? '&' : '').'entity='.(!empty($object->entity) ? $object->entity : $conf->entity);
		}
		$printer = 0;
		// The direct print feature is implemented only for such elements
		if (in_array($modulepart, array('contract', 'facture', 'supplier_proposal', 'propal', 'proposal', 'order', 'commande', 'expedition', 'commande_fournisseur', 'expensereport', 'delivery', 'ticket'))) {
			$printer = (!empty($user->rights->printing->read) && !empty($conf->printing->enabled)) ?true:false;
		}
		$hookmanager->initHooks(array('formfile'));
		// Get list of files
		$file_list = null;
		if (!empty($filedir)) {
			$file_list = dol_dir_list($filedir, 'files', 0, '', '(\.meta|_preview.*.*\.png)$', 'date', SORT_DESC);
		}
		if ($hideifempty && empty($file_list)) {
			return '';
		}
		$out = '';
		$forname = 'builddoc';
		$headershown = 0;
		$showempty = 0;
		$i = 0;
		$out .= "\n".'<!-- Start show_document -->'."\n";
       
		$titletoshow = $langs->trans("Documents");
		if (!empty($title)) {
			$titletoshow = ($title == 'none' ? '' : $title);
		}
   
		// Show table
		if ($genallowed) {
			$modellist = array();
            include_once DOL_DOCUMENT_ROOT.'/custom/materiel/modules_materiel.php';
            $modellist = ModeleMateriel::liste_modeles($this->db);
			
			// Set headershown to avoid to have table opened a second time later
			$headershown = 1;
			if (empty($buttonlabel)) {
				$buttonlabel = $langs->trans('Generate');
			}
			if ($conf->browser->layout == 'phone') {
				$urlsource .= '#'.$forname.'_form'; // So we switch to form after a generation
			}
			if (empty($noform)) {
				$out .= '<form action="'.$urlsource.'" id="'.$forname.'_form" method="post">';
			}
			$out .= '<input type="hidden" name="action" value="builddoc">';
            $out .= '<input type="hidden" name="id" value="'.$object->id.'">';
			$out .= '<input type="hidden" name="page_y" value="">';
			$out .= '<input type="hidden" name="token" value="'.newToken().'">';
			$out .= load_fiche_titre($titletoshow, '', '');
			$out .= '<div class="div-table-responsive-no-min">';
			$out .= '<table class="liste formdoc noborder centpercent">';
			$out .= '<tr class="liste_titre">';
			$addcolumforpicto = ($delallowed || $printer || $morepicto);
			$colspan = (4 + ($addcolumforpicto ? 1 : 0));
			$colspanmore = 0;
			$out .= '<th colspan="'.$colspan.'" class="formdoc liste_titre maxwidthonsmartphone center">';
			// Model
			if (!empty($modellist)) {
				asort($modellist);
				$out .= '<span class="hideonsmartphone">'.$langs->trans('Model').' </span>';
				if (is_array($modellist) && count($modellist) == 1) {    // If there is only one element
					$arraykeys = array_keys($modellist);
					$modelselected = $arraykeys[0];
				}
				$morecss = 'minwidth75 maxwidth200';
				if ($conf->browser->layout == 'phone') {
					$morecss = 'maxwidth100';
				}
				$out .= $form->selectarray('model', $modellist, $modelselected, $showempty, 0, 0, '', 0, 0, 0, '', $morecss);
				if ($conf->use_javascript_ajax) {
					$out .= ajax_combobox('model');
				}
			} else {
				$out .= '<div class="float">'.$langs->trans("Files").'</div>';
			}
			// Language code (if multilang)
			if (($allowgenifempty || (is_array($modellist) && count($modellist) > 0)) && !empty($conf->global->MAIN_MULTILANGS) && !$forcenomultilang && (!empty($modellist) || $showempty)) {
				include_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
				$formadmin = new FormAdmin($this->db);
				$defaultlang = ($codelang && $codelang != 'auto') ? $codelang : $langs->getDefaultLang();
				$morecss = 'maxwidth150';
				if ($conf->browser->layout == 'phone') {
					$morecss = 'maxwidth100';
				}
				$out .= $formadmin->select_language($defaultlang, 'lang_id', 0, null, 0, 0, 0, $morecss);
			} else {
				$out .= '&nbsp;';
			}
			// Button
			$genbutton = '<input class="button buttongen reposition" id="'.$forname.'_generatebutton" name="'.$forname.'_generatebutton"';
			$genbutton .= ' type="submit" value="'.$buttonlabel.'"';
			if (!$allowgenifempty && !is_array($modellist) && empty($modellist)) {
				$genbutton .= ' disabled';
			}
			$genbutton .= '>';
			if ($allowgenifempty && !is_array($modellist) && empty($modellist) && empty($conf->dol_no_mouse_hover) && $modulepart != 'unpaid') {
				$langs->load("errors");
				$genbutton .= ' '.img_warning($langs->transnoentitiesnoconv("WarningNoDocumentModelActivated"));
			}
			if (!$allowgenifempty && !is_array($modellist) && empty($modellist) && empty($conf->dol_no_mouse_hover) && $modulepart != 'unpaid') {
				$genbutton = '';
			}
			if (empty($modellist) && !$showempty && $modulepart != 'unpaid') {
				$genbutton = '';
			}
			$out .= $genbutton;
			$out .= '</th>';
			if (!empty($hookmanager->hooks['formfile'])) {
				foreach ($hookmanager->hooks['formfile'] as $module) {
					if (method_exists($module, 'formBuilddocLineOptions')) {
						$colspanmore++;
						$out .= '<th></th>';
					}
				}
			}
			$out .= '</tr>';
			// Execute hooks
			$parameters = array('colspan'=>($colspan + $colspanmore), 'socid'=>(isset($GLOBALS['socid']) ? $GLOBALS['socid'] : ''), 'id'=>(isset($GLOBALS['id']) ? $GLOBALS['id'] : ''), 'modulepart'=>$modulepart);
			if (is_object($hookmanager)) {
				$reshook = $hookmanager->executeHooks('formBuilddocOptions', $parameters, $GLOBALS['object']);
				$out .= $hookmanager->resPrint;
			}
		}

		// Get list of files
		if (!empty($filedir)) {
			$link_list = array();
			if (is_object($object)) {
				require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
				$link = new Link($this->db);
				$sortfield = $sortorder = null;
				$res = $link->fetchAll($link_list, $object->element, $object->id, $sortfield, $sortorder);
               
			}
			$out .= '<!-- html.formfile::showdocuments -->'."\n";
			// Show title of array if not already shown
			if ((!empty($file_list) || !empty($link_list) || preg_match('/^massfilesarea/', $modulepart))
				&& !$headershown) {
				$headershown = 1;
				$out .= '<div class="titre">'.$titletoshow.'</div>'."\n";
				$out .= '<div class="div-table-responsive-no-min">';
				$out .= '<table class="noborder centpercent" id="'.$modulepart.'_table">'."\n";
			}
			// Loop on each file found
			if (is_array($file_list)) {
				// Defined relative dir to DOL_DATA_ROOT
				$relativedir = '';
				if ($filedir) {
					$relativedir = preg_replace('/^'.preg_quote(DOL_DATA_ROOT, '/').'/', '', $filedir);
					$relativedir = preg_replace('/^[\\/]/', '', $relativedir);
                    
				}
				// Get list of files stored into database for same relative directory
				if ($relativedir) {
					completeFileArrayWithDatabaseInfo($file_list, $relativedir);
					//var_dump($sortfield.' - '.$sortorder);
					if (!empty($sortfield) && !empty($sortorder)) {	// If $sortfield is for example 'position_name', we will sort on the property 'position_name' (that is concat of position+name)
						$file_list = dol_sort_array($file_list, $sortfield, $sortorder);
					}
				}
				foreach ($file_list as $file) {
					// Define relative path for download link (depends on module)
					$relativepath = $file["name"]; // Cas general
					$out .= '<tr class="oddeven">';
					$documenturl = DOL_URL_ROOT.'/document.php';
					if (isset($conf->global->DOL_URL_ROOT_DOCUMENT_PHP)) {
						$documenturl = $conf->global->DOL_URL_ROOT_DOCUMENT_PHP; // To use another wrapper
					}
					// Show file name with link to download
					$out .= '<td class="minwidth200 tdoverflowmax300">';
					$out .= '<a class="documentdownload paddingright" href="'.$documenturl.'?modulepart='.$modulepart.'&amp;file='.urlencode($modulesubdir).'/'.urlencode($relativepath).($param ? '&'.$param : '').'"';
					$mime = dol_mimetype($relativepath, '', 0);
					if (preg_match('/text/', $mime)) {
						$out .= ' target="_blank" rel="noopener noreferrer"';
					}
					$out .= '>';
					$out .= img_mime($file["name"], $langs->trans("File").': '.$file["name"]);
					$out .= dol_trunc($file["name"], 40);
					$out .= '</a>'."\n";
					// $out .= $this->showPreview($file, $modulepart, $relativepath, 0, $param);
					$out .= '</td>';
					// Show file size
					$size = (!empty($file['size']) ? $file['size'] : dol_filesize($filedir."/".$file["name"]));
					$out .= '<td class="nowraponall right">'.dol_print_size($size, 1, 1).'</td>';
					// Show file date
					$date = (!empty($file['date']) ? $file['date'] : dol_filemtime($filedir."/".$file["name"]));
					$out .= '<td class="nowrap right">'.dol_print_date($date, 'dayhour', 'tzuser').'</td>';
					// Show share link
					$out .= '<td class="nowraponall">';
					if (!empty($file['share'])) {
						// Define $urlwithroot
						$urlwithouturlroot = preg_replace('/'.preg_quote(DOL_URL_ROOT, '/').'$/i', '', trim($dolibarr_main_url_root));
						$urlwithroot = $urlwithouturlroot.DOL_URL_ROOT; // This is to use external domain name found into config file
						//$urlwithroot=DOL_MAIN_URL_ROOT;					// This is to use same domain name than current
						//print '<span class="opacitymedium">'.$langs->trans("Hash").' : '.$file['share'].'</span>';
						$forcedownload = 0;
						$paramlink = '';
						if (!empty($file['share'])) {
							$paramlink .= ($paramlink ? '&' : '').'hashp='.$file['share']; // Hash for public share
						}
						if ($forcedownload) {
							$paramlink .= ($paramlink ? '&' : '').'attachment=1';
						}
						$fulllink = $urlwithroot.'/document.php'.($paramlink ? '?'.$paramlink : '');
                    
						$out .= img_picto($langs->trans("FileSharedViaALink"), 'globe').' ';
						$out .= '<input type="text" class="quatrevingtpercentminusx width75" id="downloadlink'.$file['rowid'].'" name="downloadexternallink" title="'.dol_escape_htmltag($langs->trans("FileSharedViaALink")).'" value="'.dol_escape_htmltag($fulllink).'">';
						$out .= ajax_autoselect('downloadlink'.$file['rowid']);
					} else {
						//print '<span class="opacitymedium">'.$langs->trans("FileNotShared").'</span>';
					}
					$out .= '</td>';
                    
					// Show picto delete, print...
					if ($delallowed || $printer || $morepicto) {
						$out .= '<td class="right nowraponall">';
						if ($delallowed) {
							$tmpurlsource = preg_replace('/#[a-zA-Z0-9_]*$/', '', $urlsource);
							$out .= '<a class="reposition" href="'.$tmpurlsource.((strpos($tmpurlsource, '?') === false) ? '?' : '&').'action='.urlencode($removeaction).'&token='.newToken().'&file='.urlencode($relativepath);
							$out .= ($param ? '&'.$param : '');
							//$out.= '&modulepart='.$modulepart; // TODO obsolete ?
							//$out.= '&urlsource='.urlencode($urlsource); // TODO obsolete ?
							$out .= '">'.img_picto($langs->trans("Delete"), 'delete').'</a>';
						}
						if ($printer) {
							$out .= '<a class="marginleftonly reposition" href="'.$urlsource.(strpos($urlsource, '?') ? '&' : '?').'action=print_file&token='.newToken().'&printer='.urlencode($modulepart).'&file='.urlencode($relativepath);
							$out .= ($param ? '&'.$param : '');
							$out .= '">'.img_picto($langs->trans("PrintFile", $relativepath), 'printer.png').'</a>';
						}
						if ($morepicto) {
							$morepicto = preg_replace('/__FILENAMEURLENCODED__/', urlencode($relativepath), $morepicto);
							$out .= $morepicto;
						}
						$out .= '</td>';
					}
					if (is_object($hookmanager)) {
						$parameters = array('colspan'=>($colspan + $colspanmore), 'socid'=>(isset($GLOBALS['socid']) ? $GLOBALS['socid'] : ''), 'id'=>(isset($GLOBALS['id']) ? $GLOBALS['id'] : ''), 'modulepart'=>$modulepart, 'relativepath'=>$relativepath);
						$res = $hookmanager->executeHooks('formBuilddocLineOptions', $parameters, $file);
						if (empty($res)) {
							$out .= $hookmanager->resPrint; // Complete line
							$out .= '</tr>';
						} else {
							$out = $hookmanager->resPrint; // Replace all $out
						}
					}
				}
				$this->numoffiles++;
			}
			// Loop on each link found
			if (is_array($link_list)) {
				$colspan = 2;
				foreach ($link_list as $file) {
					$out .= '<tr class="oddeven">';
					$out .= '<td colspan="'.$colspan.'" class="maxwidhtonsmartphone">';
					$out .= '<a data-ajax="false" href="'.$file->url.'" target="_blank" rel="noopener noreferrer">';
					$out .= $file->label;
					$out .= '</a>';
					$out .= '</td>';
					$out .= '<td class="right">';
					$out .= dol_print_date($file->datea, 'dayhour');
					$out .= '</td>';
					if ($delallowed || $printer || $morepicto) {
						$out .= '<td></td>';
					}
					$out .= '</tr>'."\n";
				}
				$this->numoffiles++;
			}
			if (count($file_list) == 0 && count($link_list) == 0 && $headershown) {
				$out .= '<tr><td colspan="'.(3 + ($addcolumforpicto ? 1 : 0)).'"><span class="opacitymedium">'.$langs->trans("None").'</span></td></tr>'."\n";
			}
		}
		if ($headershown) {
			// Affiche pied du tableau
			$out .= "</table>\n";
			$out .= "</div>\n";
			if ($genallowed) {
				if (empty($noform)) {
					$out .= '</form>'."\n";
				}
			}
		}
		$out .= '<!-- End show_document -->'."\n";
		//return ($i?$i:$headershown);
		return $out;
	}
    public function showPreview($file, $modulepart, $relativepath, $ruleforpicto = 0, $param = '')
    {
        global $langs, $conf;
 
        $out = '';
        if ($conf->browser->layout != 'phone' && !empty($conf->use_javascript_ajax)) {
           // $urladvancedpreview = getAdvancedPreviewUrl($modulepart, $relativepath, 1, $param); // Return if a file is qualified for preview
                    $out .= '<a class="pictopreview '.$urladvancedpreview['css'].'" href="'.$file['path']."/".$file['name'].'"'.(empty($urladvancedpreview['mime']) ? '' : ' mime="'.$urladvancedpreview['mime'].'"').' '.(empty($urladvancedpreview['target']) ? '' : ' target="'.$urladvancedpreview['target'].'"').'>';
                if (empty($ruleforpicto)) {
                    $out.= img_picto($langs->trans('Preview').' '.$file['name'], 'detail');
                    $out .= '<span class="fa fa-search-plus pictofixedwidth" style="color: gray"></span>';
                } 
                else 
                {
                    $out .= img_mime($relativepath, $langs->trans('Preview').' '.$file['name'], 'pictofixedwidth');
                }
                    $out .= '</a>';
            
        }
     
        return $out;
    }

    public function updateLine($lineid, $description, $valeur, $qty)
    {
        // Check if the recufiscal is in draft mode
        if ($this->fk_statut != RecuFiscal::STATUS_DRAFT)
        {
            $this->error = 'Le reçu ne peut pas être modifié si il n\'est pas en brouillon';
            return 0;
        }

        // Check if the line is indeed associatied with this recufiscal
        if (!array_key_exists($lineid, $this->lines)) { 
            // This line doesn't correspond to this recufiscal
            $this->error = 'Ligne de matériel invalide pour ce reçu fiscal';
            return 0;
        } else {
            $sql = "UPDATE ". MAIN_DB_PREFIX ."recu_fiscal_det";
            $sql .= " SET description = '". $description ."', ";
            $sql .= "valeur = ". $valeur .", ";
            $sql .= "qty = ". $qty;
            $sql .= " WHERE rowid = ". $lineid;
            $resql = $this->db->query($sql);
            if (!$resql) {
                $this->error = 'Database error';
                return 0;
            } else {
                $this->db->commit();
                return 1;
            }
        }
    }

}


class RecuFiscalLine {

    public $table_element = 'recu_fiscal_det';
    public $element = 'recufiscalline';

    public $id;
    public $valeur;
    public $valeur_tot;

    public $description;
    public $qty;
    public $datec;

    public $fk_recu_fiscal;

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
        $sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'recu_fiscal_det';
        $sql .= ' WHERE rowid = '.$id;

        $resql = $this->db->query($sql);
        if (!$resql) return 0;
        elseif ($this->db->num_rows($resql < 1)) {
            $obj = $this->db->fetch_object($resql);
            $this->id = $obj->rowid;
            $this->valeur = $obj->valeur;
            $this->valeur_tot = $obj->valeur * $obj->qty;
            $this->description = $obj->description;
            $this->qty = $obj->qty;
            $this->datec = $this->db->jdate($obj->datec);
            $this->fk_recu_fiscal = $obj->fk_recu_fiscal;
            
            return 1;

        } else return 0; // No entry found
    }
}


class Donateur extends CommonObject 
{
    public $error = 'Unknown Error';
    public $table_element = 'donateur';
    public $element = 'donateur';

    public $id;
    public $active;
    public $nom;
    public $prenom;
    public $societe;
    public $country_code = 'FR';
    public $address;
    public $zip;
    public $town;
    public $phone;
    public $email;
    public $notes;

    public $donation_count;
    public $donation_value;
    public $donation_lines;

    
    /**
    *  Constructor
    * @param DoliDB $db Database handler
    * @return void
    */
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function fetch($id)
    {
        $sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'donateur';
        $sql .= ' WHERE rowid = '.$id;
        $resql = $this->db->query($sql);
        if (!$resql) return 0;
        else {
            $obj = $this->db->fetch_object($resql);

            $this->id = $id;
            $this->active = $obj->active;
            $this->ref = (!empty($obj->prenom) ? $obj->prenom . ' ' . $obj->nom : $obj->societe);

            $this->nom = $obj->nom;
            $this->prenom = $obj->prenom;
            $this->societe = $obj->societe;
            $this->address = $obj->address;
            $this->zipcode = $obj->zipcode;
            $this->town = $obj->town;
            $this->phone = $obj->phone;
            $this->email = $obj->email;
            $this->notes = $obj->notes;

            // Get donation count
            $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."recu_fiscal ";
            $sql .= "WHERE fk_donateur = {$this->id}";
            $resql = $this->db->query($sql);
            if (!$resql)
            {
                $this->error = 'Error while fetching donation data';
                return 0;
            }
            else $this->donation_count = $this->db->fetch_array($resql)[0];

            // Get donation total value
            $sql = "SELECT SUM(montant) FROM ".MAIN_DB_PREFIX."recu_fiscal ";
            $sql .= "WHERE fk_donateur = {$this->id}";
            $resql = $this->db->query($sql);
            if (!$resql)
            {
                $this->error = 'Error while fetching donation data';
                return 0;
            }
            else $this->donation_value = $this->db->fetch_array($resql)[0]; 
            
            return 1;
        }
    }

    public function create($user)
    {
        // Check data
        if ((empty($this->nom) || empty($this->prenom)) && empty($this->societe))
        {
            $this->error = 'Invalid Data';
            return 0;
        }

        // Check if the donor doesn't already exists (among the active donors)
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."donateur ";
        $sql .= "WHERE active = 1 && ((societe = '" . $this->societe . "' AND societe != '')";
        $sql .= " OR (prenom = '" .$this->prenom ."'";
        $sql .= " AND prenom != ''"; // Prevents empty strings to interfere
        $sql .= " AND nom = '" .$this->nom ."'";        
        $sql .= " AND nom != ''";
        $sql .= "))";
        $resql = $this->db->query($sql);
        
        if (!$resql)
        {
            $this->error = "Database error";
            return 0;
        }
        else 
        {
            $num = $this->db->num_rows($resql);
            if ($num > 0)
            {
                $this->error = 'Ce donateur existe déjà';
                return 0;
            }
        }               
        
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."donateur (";
        $sql .= "nom, ";
        $sql .= "prenom, ";
        $sql .= "societe, ";
        $sql .= "address, ";
        $sql .= "phone, ";
        $sql .= "zipcode, ";
        $sql .= "town, ";
        $sql .= "email, ";
        $sql .= "notes";
        $sql .= ") VALUES (";
        $sql .= "'".$this->nom . "', ";
        $sql .= "'".$this->prenom . "', ";
        $sql .= "'".$this->societe . "', ";
        $sql .= "'".$this->address . "', ";
        $sql .= "'".$this->phone . "', ";
        $sql .= "'".$this->zip . "', ";
        $sql .= "'".$this->town . "', ";
        $sql .= "'".$this->email . "', ";
        $sql .= "'".$this->notes . "')";
        $resql = $this->db->query($sql);
        
        if (!$resql)
        {
            $this->error = 'Database error';
            return 0;
        }
        else return 1;
    }


    public function updateDonateur()
    {
        global $conf, $langs;

        $error = 0;
        // if (empty($this->precision_type)) $this->precision_type = '';
        // if (empty($this->notes)) $this->notes = '';
        // if (empty($this->fk_entrepot) || $this->fk_entrepot == -1) $this->fk_entrepot = 'NULL';
        // if (empty($this->fk_proprietaire) || $this->fk_proprietaire == -1) $this->fk_proprietaire = 'NULL';

        $this->db->begin();
        $sql = "UPDATE ".MAIN_DB_PREFIX."donateur";
        $sql .= " SET";
        $sql .= " nom = '" . $this->nom."'";
        $sql .= ", prenom = '" . $this->prenom."'";
        $sql .= ", societe = '" . $this->societe."'";
        $sql .= ", address = '" . $this->address."'";
        $sql .= ", zipcode = '" . $this->zipcode."'";
        $sql .= ", town = '" . $this->town."'";
        $sql .= ", phone = '" . $this->phone."'";
        $sql .= ", email = '" . $this->email."'";
        $sql .= ", notes = '" . $this->notes . "'";
        $sql .= " WHERE rowid = ".$this->id;

        $resql = $this->db->query($sql);

        if (!$resql = $this->db->query($sql)) return -1;
        if (!$this->db->commit()) return -1;
        
        return 1;

    }


    public function getNomUrl($withpicto = 1, $notooltip = 0, $style = '')
    {
        $label = '<u>Donateur</u>';
        $label .= '<br><b>Identifiant : </b> '.$this->ref;
        $label .= '<br><b>Adresse : </b> '.($this->address ? $this->address : '<i>Pas d\'adresse</i>');
        $label .= '<br><b>Code postal : </b> '.($this->zip ? $this->zip : '<i>Pas de code postal</i>');
        $label .= '<br><b>Ville : </b> '.($this->town ? $this->town : '<i>Pas de ville</i>');
        $label .= '<br><br><b>Notes : </b> '.($this->notes ? $this->notes : '<i>Pas de notes</i>');
        // TODO : Add more info in the tooltip
        $linkclose = '';

        if (empty($notooltip)) {
            $linkclose .= ' title="'.dol_escape_htmltag($label, 1, 1).'"';
            $linkclose .= ' class="nowraponall classfortooltip"';
        }
        $url = DOL_URL_ROOT.'/custom/recufiscal/donateur/card.php?action=view&id='.$this->id;
        $linkstart = '<a href="'.$url.'" '.$style;
        $linkstart .= $linkclose.'>';
        $linkend = '</a>';
        $result = $linkstart;
        if (!$nopicto) $result .= (talm_img_object(($notooltip ? '' : $label), 'donateur', ($notooltip ? 'class="paddingright"'.$style : 'class="paddingright classfortooltip"'.$style), 0, 0, $notooltip ? 0 : 1));
		$result .= $this->ref;
        $result .= $linkend;

        return $result;
    }

    public function delete()
    {
        $sql = "UPDATE ".MAIN_DB_PREFIX."donateur ";
        $sql .= "SET active = 0 ";
        $sql .= "WHERE rowid = {$this->id}";
        $resql = $this->db->query($sql);
        if (!$resql) return 0;
        else
        {
            $this->db->commit();
            return 1;
        }
    }

    /**
     *  Return if at least one photo is available
     *
     * @param  string $sdir Directory to scan
     * @return boolean True if at least one photo is available, False if not
     */
    public function is_photo_available($sdir)
    {
        // phpcs:enable
        include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';

        global $conf;

        $dir = $sdir;
        if (!empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO)) {
        	$dir .= '/'.get_exdir($this->id, 2, 0, 0, $this, 'donateur').$this->id."/photos/";
        } else {
        	$dir .= '/'.get_exdir(0, 0, 0, 0, $this, 'donateur').'/';
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

    public function getDonationLines($id_Recu)
    {
        // Fetch every source ID of type 'recu_fiscal' from this donor 
        $sql = "SELECT r.description, r.valeur, r.qty, r.rowid FROM ".MAIN_DB_PREFIX."recu_fiscal_det as r ";
        $sql .= "WHERE r.fk_recu_fiscal = $id_Recu";
        
        
        $resql = $this->db->query($sql);
        
        if (!$resql)
        {
            $this->error = 'Error while retrieving donation data';
        }
        else
        {
            foreach($resql as $value)
            {
                $this->donation_lines[] = $value;
            }
            return 1;
        }
    }


    public function getRecuFiscaux()
    {
        // Fetch every source ID of type 'recu_fiscal' from this donor 
        $sql = "SELECT s.rowid, s.ref FROM ".MAIN_DB_PREFIX."recu_fiscal as s ";
        $sql .= "WHERE fk_donateur = {$this->id} ";
        
        $resql = $this->db->query($sql);
        if (!$resql)
        {
            $this->error = 'Error while retrieving recu  data';
        }
        else 
        {
            foreach($resql as $value)
            {
                // Looping through results
                $this->donations[] = $value;

            }
            return 1;
        }
    }




}