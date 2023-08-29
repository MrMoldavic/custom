<?php

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/source.class.php';
include_once DOL_DOCUMENT_ROOT.'/core/lib/function.lib.php';
include_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

class Materiel extends CommonObject
{
    /**
     * @var string ID to identify managed object
     */
    public $element = 'materiel';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'materiel';

    public $error = 'Unknown Error';


    public $picto = 'materiel';

    public $regeximgext = '\.gif|\.jpg|\.jpeg|\.png|\.bmp|\.webp|\.xpm|\.xbm'; // See also into images.lib.php

    public $id;
    public $source_object;
    public $fk_preinventaire;
    public $date_creation;
    public $user_creation;
    public $date_modification;
    public $user_modification;
    public $date_suppression;
    public $user_suppression;

    public $ref;

    public $fk_etat_etiquette;
    public $etat_etiquette;
    public $etat_etiquette_badge_code;

    public $kit_tmp;

    public $active;

    public $label;

    public $fk_type_materiel;
    public $type_materiel;
    public $type_materiel_ind;

    public $cote;

    /**
     * Product description
     *
     * @var string
     */
    public $notes;


    /* FK et nom complet de l'état*/
    public $fk_etat;
    public $etat_ind;
    public $etat;
    public $etat_badge_code;


    /* FK et nom complet de l'exploitabilité*/
    public $fk_exploitabilite;
    public $exploitabilite_ind;
    public $exploitabilite;
    public $exploitabilite_badge_code;

    public $precision_type;

    public $fk_entrepot;
    public $entrepot_ref;

    public $fk_proprietaire;
    public $proprietaire;

    public $fk_origine;
    public $origine;

    public $fk_marque;
    public $marque_ind;
    public $marque;

    // Données sur le kit associé au matériel et sur sa localisation si il est en exploitation
    public $fk_kit;
    public $kit_ind;
    public $kit_cote;
    public $kit;

    public $fk_localisation;
    public $fk_etat_exploitation;

    public $modele;

    public $oldcopy;

	public $numoffiles;
	public $infofiles; // Used to return informations by function getDocumentsLink


    public $fields = array(
        'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-2, 'notnull'=>1, 'index'=>1, 'position'=>1, 'comment'=>'Id'),
        'fk_type_materiel' =>array('type'=>'integer', 'label'=>'TypeMat', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'showoncombobox'=>1, 'index'=>1, 'position'=>10, 'searchall'=>1, 'comment'=>'Reference of object'),
        'cote'        =>array('type'=>'integer', 'label'=>'Cote', 'enabled'=>1, 'visible'=>0, 'default'=>1, 'notnull'=>1, 'index'=>1, 'position'=>20),
        'fk_etat'         =>array('type'=>'integer', 'label'=>'Etat', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'showoncombobox'=>1),
        'modele'         =>array('type'=>'text', 'label'=>'Modèle', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'showoncombobox'=>1),
		'precision_type'   =>array('type'=>'tinytext', 'label'=>'TypeInstru', 'enabled'=>1, 'visible'=>0, 'position'=>61),
        'notes'          =>array('type'=>'text', 'label'=>'Notes', 'enabled'=>1, 'visible'=>0, 'position'=>62),
        'fk_origine'          =>array('type'=>'int', 'label'=>'Origine', 'enabled'=>1, 'visible'=>0, 'position'=>63),
        'fk_entrepot'         =>array('type'=>'integer', 'label'=>'WarehosueFK', 'enabled'=>1, 'visible'=>-2, 'notnull'=>1, 'position'=>500),
        'fk_proprietaire'           =>array('type'=>'integer', 'label'=>'ProprietaireFK', 'enabled'=>1, 'visible'=>-2, 'notnull'=>1, 'position'=>501)
    );


    /**
     *  Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->canvas = '';
		$this->numoffiles = 0;
    }

	public function strToArray($str) // Converti les listes d'id de la bdd en array (["1", "2", "3"])
	{
	    return json_decode($str, false);
	}

    public function create($user)
    {
        global $conf, $langs;
        $this->db->begin();
        if (!$this->checkDataForCreation()) {
            $this->error = 'Invalid Data';
            return 0;
        }
        $this->sanitizeDataForCreation();
        if (!$this->setCoteForCreation()) {
            $this->error = 'Unable to fetch equipment classification number';
            return 0;
        }
       

        if (!$this->insertToDatabase($user))  {
            $this->error = 'Database error';
            return 0;
        }
        return 1;
    }

    /**
     * Vérifie la validité des données pour la création d'un matériel
     * @return 1 si OK, 0 si KO
     */
    private function checkDataForCreation() 
    {
        // TODO check if fk_preinventaire isn't already used by a materiel
        if (empty($this->fk_type_materiel) || $this->fk_type_materiel == -1) return 0;
        if (empty($this->fk_marque) || $this->fk_marque == -1) return 0;
        if (empty($this->fk_etat) || $this->fk_etat == -1) return 0;
        if (empty($this->fk_etat_etiquette) || $this->fk_etat_etiquette == -1) return 0;
        if (empty($this->fk_exploitabilite) || $this->fk_exploitabilite == -1) return 0;
        if (empty($this->fk_origine) || $this->fk_origine == -1) return 0;
        if (empty($this->fk_preinventaire) || $this->fk_preinventaire == -1) return 0;

        // Check if the source exists
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."preinventaire";
        $sql .= " WHERE inventoriable = 1 AND rowid = ".$this->fk_preinventaire;
        $resql = $this->db->query($sql);
        if ($this->db->num_rows($resql) == 0) return 0;

        return 1; // Extra security is added by foreign key constraints on the database
    }

    /**
     * Formate les données pour l'insertion dans la base de données
     * @return 1 si OK, 0 si KO
     */
    private function sanitizeDataForCreation()
    {
        // For strings, we add quotation marks around to insert into the database, or set it to null if empty
        $this->precision_type = (!empty($this->precision_type) ? "'".$this->precision_type."'" : 'null');
        $this->modele = (!empty($this->modele) ? "'".$this->modele."'" : 'null');
        $this->notes = (!empty($this->notes) ? "'".$this->notes."'" : 'null');

        $this->fk_entrepot = ($this->fk_entrepot >= 0 ? $this->fk_entrepot : 'null');
        $this->fk_proprietaire = ($this->fk_proprietaire >= 0 ? $this->fk_proprietaire : 'null');
    }

    /**
     * Récupération de la cote du matériel à ajouter
     * 
     * Récupération de la cote la plus élévée parmi les matériels du même type que celui que l'on veut ajouter
     * @return 1 si OK, 0 si KO
     */
    private function setCoteForCreation()
    {
        $sql = "SELECT cote FROM ".MAIN_DB_PREFIX."materiel";
        $sql .= " WHERE fk_type_materiel = ".$this -> fk_type_materiel;
        $sql .= " ORDER BY cote DESC LIMIT 1";
        $result = $this->db->query($sql); 
        $cote_list = $this->db->fetch_object($result);
        if (empty($cote_list)) $this->cote = 1; // If $cote_list is empty, this is the first materiel of this type -> cote = 1
        else $this->cote = $cote_list->cote + 1;
        return 1;
    }

    /**
     * Insertion des données du matériel dans la base de données
     * @return 1 si OK, 0 si KO
     */
    private function insertToDatabase($user)
    {
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."materiel (";
        $sql .= "fk_preinventaire";
        $sql .= ", fk_type_materiel";
        $sql .= ", cote";
        $sql .= ", fk_etat";
        $sql .= ", fk_etat_etiquette";
        $sql .= ", fk_exploitabilite";
        $sql .= ", precision_type";
        $sql .= ", fk_marque";
        $sql .= ", modele";
        $sql .= ", notes_supplementaires";
        $sql .= ", fk_origine";
        $sql .= ", fk_entrepot";
        $sql .= ", fk_proprietaire";
        $sql .= ", fk_user_author";
        $sql .= ") VALUES (";
        $sql .= $this->fk_preinventaire;
        $sql .= ", ".$this -> fk_type_materiel;
        $sql .= ", ".$this ->cote;
        $sql .= ", ".$this->fk_etat;
        $sql .= ", ".$this->fk_etat_etiquette;
        $sql .= ", ".$this->fk_exploitabilite;
        $sql .= ", ".$this->precision_type;
        $sql .= ", ".$this->fk_marque;
        $sql .= ", ".$this->modele;
        $sql .= ", ".$this->notes;
        $sql .= ", ".$this->fk_origine;
        $sql .= ", ".$this->fk_entrepot;
        $sql .= ", ".$this->fk_proprietaire;
        $sql .= ", ".$user->id;
        $sql .= ")";
        $result = $this->db->query($sql);
        if ($result) {
            $this->db->commit();
            return 1;
        }
        else return 0; 
    }


    public function fetch($id = '')
    {

        global $langs, $conf, $fields;

        // Check parameters
        if (!$id) {
            $this->error = 'ErrorWrongParameters';
            dol_syslog(get_class($this)."::fetch ".$this->error);
            return -1;
        }

        $sql = "SELECT m.rowid, m.fk_preinventaire,m.fk_type_materiel, m.cote,m.ancienne_cote, m.fk_etat, m.fk_etat_etiquette, m.fk_exploitabilite, m.precision_type, m.notes_supplementaires, m.fk_entrepot, m.fk_marque, m.modele, m.fk_proprietaire, m.date_ajout, m.fk_user_author, m.fk_origine, m.active";
        $sql .= " ,e.indicatif as etat_ind, e.etat, e.badge_status_code as etat_status_code";
        $sql .= " ,ex.indicatif as exploitabilite_ind, ex.exploitabilite, ex.badge_status_code as exploitabilite_status_code";
        $sql .= " ,ee.etat as etat_etiquette, ee.badge_code as etat_etiquette_badge_code";
        $sql .= " ,p.proprietaire";
        $sql .= " ,t.indicatif as type_mat_ind, t.type";
        $sql .= " , o.origine";
        $sql .= " , mm.marque";
        $sql .= " ,w.ref";

        $sql .= " FROM ".MAIN_DB_PREFIX."materiel as m ";

        $sql.="INNER JOIN ".MAIN_DB_PREFIX."c_etat_materiel as e ON m.fk_etat=e.rowid ";
        $sql.="INNER JOIN ".MAIN_DB_PREFIX."c_exploitabilite as ex ON m.fk_exploitabilite=ex.rowid ";
        $sql.="INNER JOIN ".MAIN_DB_PREFIX."c_etat_etiquette as ee ON m.fk_etat_etiquette=ee.rowid ";
        $sql.="INNER JOIN ".MAIN_DB_PREFIX."c_origine_materiel as o ON m.fk_origine=o.rowid ";
        $sql.="INNER JOIN ".MAIN_DB_PREFIX."c_type_materiel as t ON m.fk_type_materiel=t.rowid ";/* On joint les deux tables pour avoir le descriptif de l'état en entier (et pas juste une lettre) et aussi pour avoir le code du status du badge (rouge ou vert)*/
        $sql.="LEFT JOIN ".MAIN_DB_PREFIX."c_proprietaire as p ON m.fk_proprietaire=p.rowid ";
        $sql.="LEFT JOIN ".MAIN_DB_PREFIX."entrepot as w ON m.fk_entrepot=w.rowid ";
        $sql.="LEFT JOIN ".MAIN_DB_PREFIX."c_marque as mm ON m.fk_marque=mm.rowid ";

        $sql .= " WHERE m.rowid = ".(int) $id;
        
        $resql = $this->db->query($sql);

        if ($resql) {
            if ($this->db->num_rows($resql) > 0) {
                $obj = $this->db->fetch_object($resql);

                if ($obj->fk_user_author) {
                    $cuser = new User($this->db);
                    $cuser->fetch($obj->fk_user_author);
                    $this->user_creation = $cuser;
                }

                if ($obj->fk_user_modif) {
                    $muser = new User($this->db);
                    $muser->fetch($obj->fk_user_modif);
                    $this->user_modification = $muser;
                }
                $this->date_creation     = $this->db->jdate($obj->date_ajout);
                $this->date_modification = $this->db->jdate($obj->tms);

                $this->id                           = $obj->rowid;
                $this->fk_preinventaire             = $obj->fk_preinventaire;
                $this->active                       = $obj->active;
                $this->fk_type_materiel             = $obj->fk_type_materiel;
                $this->type_materiel_ind            = $obj->type_mat_ind;
                $this->type_materiel                = $obj->type;
                $this->cote                         = $obj->cote;
                $this->ancienne_cote                = $obj->ancienne_cote;
                $this->label                        = $obj->type;
                $this->ref                          = $obj->type_mat_ind . '-' . $obj->cote;

                $this->fk_etat_etiquette                      = $obj->fk_etat_etiquette;
                $this->etat_etiquette                      = $obj->etat_etiquette;
                $this->etat_etiquette_badge_code                = $obj->etat_etiquette_badge_code;

                $this->fk_origine                      = $obj->fk_origine;
                $this->origine_ind                      = $obj->origine_ind;
                $this->origine                      = $obj->origine;

                $this->fk_etat                      = $obj->fk_etat;
                $this->etat_ind                      = $obj->etat_ind;
                $this->etat                         = $obj->etat;
                $this->etat_badge_code                = $obj->etat_status_code;

                $this->fk_exploitabilite                      = $obj->fk_exploitabilite;
                $this->exploitabilite_ind                      = $obj->exploitabilite_ind;
                $this->exploitabilite                         = $obj->exploitabilite;
                $this->exploitabilite_badge_code                = $obj->exploitabilite_status_code;

                $this->precision_type              = $obj->precision_type;
                $this->notes                        = $obj->notes_supplementaires;

                $this->fk_entrepot                  = $obj->fk_entrepot;
                $this->entrepot_ref                 = $obj->fk_entrepot ? $obj->ref : 'Pas d\'entrepôt';

                $this->fk_proprietaire              = $obj->fk_proprietaire;
                $this->proprietaire              = $obj->proprietaire ? $obj->proprietaire : '';

                $this->fk_marque                   =$obj->fk_marque ? $obj->fk_marque : '';
                $this->marque_ind                 =$obj->marque_ind ? $obj->marque_ind : '';
                $this->marque                   =$obj->marque ? $obj->marque : '';
                $this->modele                   =$obj->modele ? $obj->modele : '';


                // Maintenant on récupère les données du kit dans lequel est le materiel
        		$sql = "SELECT k.rowid, k.cote, tk.type, tk.indicatif";
        		$sql .= " FROM ".MAIN_DB_PREFIX."kit_content as kc ";
                $sql .="INNER JOIN ".MAIN_DB_PREFIX."kit as k ON kc.fk_kit=k.rowid ";
                $sql .="INNER JOIN ".MAIN_DB_PREFIX."c_type_kit as tk ON k.fk_type_kit=tk.rowid ";
        		$sql .= "WHERE (";
        		$sql .= "kc.fk_materiel = " . $this->id;
        		$sql .= " AND kc.active = 1";
        		$sql .= ")";

        		$resql = $this->db->query($sql);
                if ($resql) {
        		    $num = $this->db->num_rows($resql);
        			if ($num > 0)
        		    {
            			$obj = $this->db->fetch_object($resql);
                        $this->fk_kit = $obj->rowid;
                        $this->kit_ind = $obj->indicatif;
                        $this->kit_cote = $obj->cote;
        		    }
                    else
                    {
                         $this->fk_kit = 0;
                    }
                }
                else
                {
                    dol_print_error($this->db);
                    return 0;
                }

                // // Get the source object
                $sql = "SELECT fk_source FROM ".MAIN_DB_PREFIX."preinventaire";
                $sql .= " WHERE rowid = " . $this->fk_preinventaire;
                $resql = $this->db->query($sql);

                if ($resql) {
                    $obj = $this->db->fetch_object($resql);
                    $this->source_object = new Source($this->db);
                    $this->source_object->fetch($obj->fk_source);
                }
              
                return 1;
            }
        }
        else
        {
            dol_print_error($this->db);
            return 0;
        }

    }

    public function getEtatDict($get_badge = 0)
    {
        global $langs, $conf;
        $array_etat = array();

        $sql = "SELECT rowid, indicatif, etat, badge_status_code";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_etat_materiel";
        $sql .= " WHERE active = 1";
        $resql = $this->db->query($sql);

        $num = $this->db->num_rows($resql);
		$i = 0;
		while ($i < $num)
		{
			$obj = $this->db->fetch_object($resql);
			$array_etat[$obj->rowid] = $obj->etat;
			if ($get_badge) $array_etat[$obj->rowid]['badge_code'] = $obj->badge_status_code;
			$i++;
		}
		return $array_etat;
    }

    public function getExploitabiliteDict($get_badge = 0)
    {
        $array_exploitabilite = array();

        global $langs, $conf;

        $sql = "SELECT rowid, indicatif, exploitabilite, badge_status_code";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_exploitabilite";
        $sql .= " WHERE active = 1";

        $resql = $this->db->query($sql);

        $num = $this->db->num_rows($resql);
		$i = 0;
		while ($i < $num)
		{
			$obj = $this->db->fetch_object($resql);
			$array_exploitabilite[$obj->rowid] = $obj->exploitabilite;
			if ($get_badge)
			{
			    $array_exploitabilite[$obj->rowid]['badge_code'] = $obj->badge_status_code;
			}
			$i++;
		}
		return $array_exploitabilite;
    }

    public function getMarqueDict()
    {
        $array_marque = array();

        global $langs, $conf;

        $sql = "SELECT rowid, marque";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_marque";
        $sql .= " WHERE active = 1";

        $resql = $this->db->query($sql);

        $num = $this->db->num_rows($resql);
		$i = 0;
		while ($i < $num)
		{
			$obj = $this->db->fetch_object($resql);
			$array_marque[$obj->rowid] = $obj->marque;
			$i++;
		}
		return $array_marque;
    }

    public function getOrigineDict()
    {
        $array_origine = array();

        global $langs, $conf;

        $sql = "SELECT rowid, origine";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_origine_materiel";
        $sql .= " WHERE active = 1";

        $resql = $this->db->query($sql);

        $num = $this->db->num_rows($resql);
		$i = 0;
		while ($i < $num)
		{
			$obj = $this->db->fetch_object($resql);
			$array_origine[$obj->rowid] = $obj->origine;
			$i++;
		}
		return $array_origine;
    }

    public function getTypeMaterielDict($short = 0)
    {
        $array_type = array();

        global $langs, $conf;

        $sql = "SELECT rowid, indicatif, type";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_type_materiel";
        $sql .= " WHERE active = 1";

        $resql = $this->db->query($sql);

        $num = $this->db->num_rows($resql);
		$i = 0;
		while ($i < $num)
		{
			$obj = $this->db->fetch_object($resql);
			$array_type[$obj->rowid] = $obj->indicatif. ($short ? '' : '-' .$obj->type );
			$i++;
		}
		return $array_type;
    }

    /* Récupération de l'URL avec la tooltip à utiliser dans la liste*/
    public function getNomUrl($notooltip = 0, $style ='')
    {
        global $conf, $langs;
        include_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';

        $new_notes = $this->notes;
        if (!$new_notes) {
            $new_notes = '<i>Pas de notes</i>';
        }

        $label = '<u>Matériel</u>';
        $label .= '<br><b>Type : </b> '.$this->type_materiel;
        $label .= '<br><b>Marque : </b> '.$this->marque;
        $label .= '<br><b>Modèle : </b> '.($this->modele ? $this->modele : '<i>Pas de modèle</i>');
        $label .= '<br><b>État : </b> '.$this->etat;
        $label .= '<br><b>Exploitabilité : </b> '.$this->exploitabilite;
        $label .= '<br><br><b>Notes : </b> '.$new_notes;

        $tmpphoto = $this->show_photos('materiel', $conf->materiel->multidir_output[1], 1, 1, 0, 0, 0, 80);
        $label .= '<br>'.$tmpphoto;
        $linkclose = '';
        
        if (empty($notooltip)) {
            if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
                $label = $langs->trans("ShowProduct");
                $linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
            }

            $linkclose .= ' title="'.dol_escape_htmltag($label, 1, 1).'"';
            $linkclose .= ' class="nowraponall classfortooltip"';
        }
        $url = DOL_URL_ROOT.'/custom/materiel/materiel_card.php?id='.$this->id;
        $linkstart = '<a href="'.$url.'" '.$style;
        $linkstart .= $linkclose.'>';
        $linkend = '</a>';

        $result = $linkstart;
        $result .= (img_object(($notooltip ? '' : $label), 'product', ($notooltip ? 'class="paddingright"'.$style : 'class="paddingright classfortooltip"'.$style), 0, 0, $notooltip ? 0 : 1));

        $cote = "";
    
        if(strlen($this->cote) == 1)
        {
            $cote = "00".$this->cote;
        }
        elseif(strlen($this->cote) == 2)
        {
            $cote = "0".$this->cote;
        }
        else
        {
            $cote = $this->cote;
        }
        $result .= $this->type_materiel_ind . '-' . $cote;
        $result .= $linkend;

        return $result;
    }

    /**
     *    Return label of status of object
     *
     * @param  int $mode 0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto
     * @param  int $type 0=Sell, 1=Buy, 2=Batch Number management
     * @return string          Label of status
     */
    public function getLibStatut($mode = 0, $type = 0)
    {
        switch ($type)
        {
			case 0:
            return $this->LibStatut($this->status, $mode, $type);
			default:
				//Simulate previous behavior but should return an error string
            return $this->LibStatut($this->status_buy, $mode, $type);
        }
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
    /**
     *    Return label of a given status
     *
     * @param  int 		$status 	Statut
     * @param  int		$mode       0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
     * @param  int 		$type   	0=Status "to sell", 1=Status "to buy", 2=Status "to Batch"
     * @return string              	Label of status
     */
    public function LibStatut($status, $mode = 0, $type = 0)
    {
        // phpcs:enable
        global $conf, $langs;

        $labelStatus = $labelStatusShort = '';

        if ($status == 0) {
            // $type   0=Status "to sell", 1=Status "to buy", 2=Status "to Batch"
            if ($type == 0) {
                $labelStatus = $langs->trans('ProductStatusNotOnSellShort');
                $labelStatusShort = $langs->trans('ProductStatusNotOnSell');
            }
            elseif ($type == 1) {
                $labelStatus = $langs->trans('ProductStatusNotOnBuyShort');
                $labelStatusShort = $langs->trans('ProductStatusNotOnBuy');
            }
            elseif ($type == 2) {
                $labelStatus = $langs->trans('ProductStatusNotOnBatch');
                $labelStatusShort = $langs->trans('ProductStatusNotOnBatchShort');
            }
        }
        elseif ($status == 1) {
            // $type   0=Status "to sell", 1=Status "to buy", 2=Status "to Batch"
            if ($type == 0) {
                $labelStatus = $langs->trans('ProductStatusOnSellShort');
                $labelStatusShort = $langs->trans('ProductStatusOnSell');
            }
            elseif ($type == 1) {
                $labelStatus = $langs->trans('ProductStatusOnBuyShort');
                $labelStatusShort = $langs->trans('ProductStatusOnBuy');
            }
            elseif ($type == 2) {
                $labelStatus = $langs->trans('ProductStatusOnBatch');
                $labelStatusShort = $langs->trans('ProductStatusOnBatchShort');
            }
        }
    }

    /**
     *    Met à jour la cote du materiel lors de la modification du type de materiel
     *
     * @return int        <0 if KO, >0 if OK
     */

    public function UpdateCote()
    {
        if ($this->fk_type_materiel and $this->id)
        {
            $this->db->begin();

            $sql = "SELECT cote";
            $sql .= " FROM ".MAIN_DB_PREFIX."materiel";
            $sql .= " WHERE fk_type_materiel = ".$this->fk_type_materiel;
            $sql .= " AND rowid <> ".$this->id;
            $sql .= " ORDER BY cote DESC";

            $result = $this->db->query($sql); //On recupère la plus grosse cote des objets du même type pour avoir celle de l'objet qu'on insère
            $cote_list = $this->db->fetch_object($result);
            if (empty($cote_list))
            {
                $this->cote = 1;
            } else {
                $this->cote = $cote_list->cote + 1;
            }

            $sql = "UPDATE ".MAIN_DB_PREFIX."materiel";
            $sql .= " SET cote = ".$this->cote;
            $sql .= " WHERE rowid = ".$this->id;
			if (!$this->db->query($sql)) return -1;
			if (!$this->db->commit()) return -1;
			return 1;
        }
        else
        {
            return -1;
        }
    }


    /**
     *    Desactive le materiel dans la base de donnée (modification du champs 'active')
     *
     * @return int        <0 if KO, >0 if OK
     */
    public function deactivate($user)
    {
        global $conf;
        if ($this->id)
        {
            $this->db->begin();
            $this->fetch($this->id);
            if ($this->fk_kit) {

    			//On vérifie si le kit n'est pas vide après la suppression de ce matériel (donc si il n'y a qu'un matériel dans le kit) OU SI LE KIT EST EN EXPLOITATION
    			$sql = "SELECT count(*) as nb_mat FROM ";
                $sql .= MAIN_DB_PREFIX."kit_content";
                $sql .= " WHERE fk_kit = ".$this->fk_kit;
                $sql .= " AND active = 1";
                $resql = $this->db->query($sql);
            	$nb_mat_data = $this->db->fetch_object($resql);
                if ($nb_mat_data->nb_mat == 1) { //Si ce materiel etait le dernier du kit, on le supprime
        			$kit_tmp = new Kit($this->db);
        			$kit_tmp->fetch($this->fk_kit);
        			if($kit_tmp->fk_exploitation) return 0; // Si le kit est en exploitation, on annule la désactivation du materiel

        			if(!$kit_tmp->delete()) return 0;
            	}

                $sql = "UPDATE ".MAIN_DB_PREFIX."kit_content";
                $sql .= " SET";
                $sql .= " date_suppression = '" . date('Y-m-d H:i:s') . "',";
                $sql .= " fk_user_delete = ".$user->id;
                $sql .= ", active = 0";
                $sql .= " WHERE fk_materiel = ".$this->id;
                $sql .= " AND date_suppression IS NULL"; // On rajoute IS NULL sinon ça va update toutes les dates des evenements de ce materiel
                if (!$result = $this->db->query($sql)) return 0;
                if (!$this->db->commit()) return 0;
            }
            $sql = "UPDATE ";
            $sql .= MAIN_DB_PREFIX."materiel";
            $sql .= " SET active = 0";
            $sql .= " WHERE rowid = ".$this->id;
      			if (!$this->db->query($sql)) return 0;
      			if (!$this->db->commit()) return 0;
			return 1;
        }
        else
        {
            return 0;
        }
    }

        /**
     *    Supprime le materiel de la base de donnée et supprimer les fichiers associés
     *
     * @return int        <0 if KO, >0 if OK
     */
    public function delete()
    {
        global $conf;
        include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        if ($this->id)
        {
            if ($this->fk_kit) {

    			//On vérifie si le kit n'est pas vide après la suppression de ce matériel (donc si il n'y a qu'un matériel dans le kit) OU SI LE KIT EST EN EXPLOITATION
    			$sql = "SELECT count(*) as nb_mat FROM ";
                $sql .= MAIN_DB_PREFIX."kit_content";
                $sql .= " WHERE fk_kit = ".$this->fk_kit;
                $sql .= " AND active = 1";
                $resql = $this->db->query($sql);
            	$nb_mat_data = $this->db->fetch_object($resql);
                if ($nb_mat_data->nb_mat == 1) { //Si ce materiel etait le dernier du kit, on le supprime
        			$kit_tmp = new Kit($this->db);
        			$kit_tmp->fetch($this->fk_kit);
        			if($kit_tmp->fk_exploitation) return 0; // Si le kit est en exploitation, on annule la suppresssion du materiel
        			if(!$kit_tmp->delete()) return 0;


                $sql = "DELETE FROM ";
                $sql .= MAIN_DB_PREFIX."kit_content";
                $sql .= " WHERE fk_materiel = ".$this->id;
    			if (!$this->db->query($sql)) return 0;
    			if (!$this->db->commit()) return 0;
            	}
            }
            $sql = "DELETE";
            $sql .= " FROM ".MAIN_DB_PREFIX."materiel";
            $sql .= " WHERE rowid = ".$this->id;
			if (!$this->db->query($sql)) return -1;

            // We remove directory
            $ref = dol_sanitizeFileName($this->ref);
            if ($conf->materiel->dir_output) {
                $dir = $conf->materiel->dir_output."/".$ref;
                if (file_exists($dir)) {
                    $res = @dol_delete_dir_recursive($dir);
                }
            }
			return 1;
        }
        else
        {
            return -1;
        }
    }



    /**
     *    Réactive le materiel dans la base de donnée (modification du champs 'active')
     *
     * @return int        <0 if KO, >0 if OK
     */
    public function reactivate()
    {
        global $conf;
        if ($this->id)
        {
            $sql = "UPDATE ";
            $sql .= MAIN_DB_PREFIX."materiel";
            $sql .= " SET active = 1";
            $sql .= " WHERE rowid = ".$this->id;
			if (!$this->db->query($sql)) return -1;
			if (!$this->db->commit()) return -1;
			return 1;
        }
        else
        {
            return -1;
        }
    }


    /**
     *    Met à jour les données du matériel
     *
     * @return int        <0 if KO, >0 if OK
     */

    public function update()
    {
        global $conf, $langs;

        $error = 0;
        if (empty($this->precision_type)) $this->precision_type = '';
        if (empty($this->notes)) $this->notes = '';
        if (empty($this->fk_entrepot) || $this->fk_entrepot == -1) $this->fk_entrepot = 'NULL';
        if (empty($this->fk_proprietaire) || $this->fk_proprietaire == -1) $this->fk_proprietaire = 'NULL';

        $this->db->begin();
        $sql = "UPDATE ".MAIN_DB_PREFIX."materiel";
        $sql .= " SET";
        $sql .= " fk_type_materiel = " . $this->fk_type_materiel;
        $sql .= ", fk_etat = " . $this->fk_etat;
        $sql .= ", fk_etat_etiquette = " . $this->fk_etat_etiquette;
        $sql .= ", fk_exploitabilite = " . $this->fk_exploitabilite;
        $sql .= ", fk_marque = " . $this->fk_marque;
        $sql .= ", modele = '" . $this->modele . "'";
        $sql .= ", precision_type = '" . $this->precision_type . "'";
        $sql .= ", notes_supplementaires = '" . $this->notes . "'";
        $sql .= ", fk_origine = " . $this->fk_origine;
        $sql .= ", fk_entrepot = " . $this->fk_entrepot;
        $sql .= ", fk_proprietaire = " . $this->fk_proprietaire;

        $sql .= " WHERE rowid = ".$this->id;
        if (!$result = $this->db->query($sql)) return -1;
        if (!$this->db->commit()) return -1;

        /*UPDATE COTE SI TYPE DE MATERIEL A CHANGÉ*/
        if ($this->oldcopy->fk_type_materiel != $this->fk_type_materiel) {
            if (!$this->UpdateCote()) return -1;
        }
        return 1;

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
        	$dir .= '/'.get_exdir($this->id, 2, 0, 0, $this, 'materiel').$this->id."/photos/";
        } else {
        	$dir .= '/'.get_exdir(0, 0, 0, 0, $this, 'materiel').'/';
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


    public function KitStatut()
    {
        $status = ($this->fk_kit ? 'status4' : 'status5');
        $label = ($this->fk_kit ? 'En kit' : 'Hors kit');
        return dolGetStatus($label, $label, '', $status, 3);
    }


    public function ExploitabiliteStatut()
    {
        $status = ($this->fk_exploitabilite == 1 ? 'status4' : 'status8');
        $label = ($this->fk_exploitabilite == 1 ? 'Fonctionnel' : 'Non fonctionnel');
        return dolGetStatus($label, $label, '', $status, 3);
    }

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
		//print 'filedir='.$filedir;

		// if (preg_match('/massfilesarea_/', $modulepart)) {
        //     var_dump("kjzbegfze");
		// 	$out .= '<div id="show_files"><br></div>'."\n";
		// 	$title = $langs->trans("MassFilesArea").' <a href="" id="togglemassfilesarea" ref="shown">('.$langs->trans("Hide").')</a>';
		// 	$title .= '<script>
		// 		jQuery(document).ready(function() {
		// 			jQuery(\'#togglemassfilesarea\').click(function() {
		// 				if (jQuery(\'#togglemassfilesarea\').attr(\'ref\') == "shown")
		// 				{
		// 					jQuery(\'#'.$modulepart.'_table\').hide();
		// 					jQuery(\'#togglemassfilesarea\').attr("ref", "hidden");
		// 					jQuery(\'#togglemassfilesarea\').text("('.dol_escape_js($langs->trans("Show")).')");
		// 				}
		// 				else
		// 				{
		// 					jQuery(\'#'.$modulepart.'_table\').show();
		// 					jQuery(\'#togglemassfilesarea\').attr("ref","shown");
		// 					jQuery(\'#togglemassfilesarea\').text("('.dol_escape_js($langs->trans("Hide")).')");
		// 				}
		// 				return false;
		// 			});
		// 		});
		// 		</script>';
		// }
       
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
					if ($modulesubdir) {
						$relativepath = $modulesubdir."/".$file["name"]; // Cas propal, facture...
					}
					if ($modulepart == 'export') {
						$relativepath = $file["name"]; // Other case
					}
					$out .= '<tr class="oddeven">';

					$documenturl = DOL_URL_ROOT.'/document.php';
					if (isset($conf->global->DOL_URL_ROOT_DOCUMENT_PHP)) {
						$documenturl = $conf->global->DOL_URL_ROOT_DOCUMENT_PHP; // To use another wrapper
					}

					// Show file name with link to download
					$out .= '<td class="minwidth200 tdoverflowmax300">';
					$out .= '<a class="documentdownload paddingright" href="'.$documenturl.'?modulepart='.$modulepart.'&amp;file='.urlencode($relativepath).($param ? '&'.$param : '').'"';

					$mime = dol_mimetype($relativepath, '', 0);
					if (preg_match('/text/', $mime)) {
						$out .= ' target="_blank" rel="noopener noreferrer"';
					}
					$out .= '>';
					$out .= img_mime($file["name"], $langs->trans("File").': '.$file["name"]);
					$out .= dol_trunc($file["name"], 40);
					$out .= '</a>'."\n";
					$out .= $this->showPreview($file, $modulepart, $relativepath, 0, $param);
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
}
?>
