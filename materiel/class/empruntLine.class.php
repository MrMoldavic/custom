<?php 

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

class EmpruntLine extends CommonObject {

    public $table_element = 'emprunt_det';
    public $element = 'empruntdet';

    public $id;
    public $fk_emprunt;
    public $valeur;

    public $valeur_tot;

    public $description;
    public $qty;
    public $datec;

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
        $sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'emprunt_det';
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
            $this->fk_emprunt = $obj->fk_emprunt;
            
            return 1;

        } else return 0; // No entry found
    }
}