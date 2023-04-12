<?php

class PreinventaireLine {

    public $id;
    public $valeur;
    public $description;
    public $ref;
    public $inventoriable;
    public $amortissable;
    public $datec;

    public $fk_source;
    public $status = 0; // TODO Modify this value if inventoried

    const STATUS_NON_INVENTORIABLE = 0;
    const STATUS_NON_INVENTORIE = 1;
    const STATUS_INVENTORIE = 2;

    const NON_INVENTORIABLE_LABEL = "Non inventoriable";
    const NON_INVENTORIE_LABEL = "Non inventorié";
    const INVENTORIE_LABEL = "Inventorié";

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
        $sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'preinventaire';
        $sql .= ' WHERE rowid = '.$id;

        $resql = $this->db->query($sql);
        if (!$resql) return 0;
        elseif ($this->db->num_rows($resql < 1)) {
            $obj = $this->db->fetch_object($resql);
            $this->id = $obj->rowid;
            $this->valeur = $obj->valeur;
            $this->description = $obj->description;
            $this->inventoriable = $obj->inventoriable;
            $this->amortissable = $obj->amortissable;
            $this->datec = $this->db->jdate($obj->datec);
            $this->fk_source = $obj->fk_source;

            if (!$this->inventoriable) $this->status = PreinventaireLine::STATUS_NON_INVENTORIABLE;
            else {
                // Check if this line has been inventoried
                $sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'materiel WHERE fk_preinventaire = '.$this->id;
                $resql = $this->db->query($sql);
                if ($this->db->num_rows($resql) > 0) {
                    $this->status = PreinventaireLine::STATUS_INVENTORIE;
                } else $this->status = PreinventaireLine::STATUS_NON_INVENTORIE;
            }
            
            return 1;

        } else return 0; // No entry found
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
            case PreinventaireLine::STATUS_NON_INVENTORIABLE:
                $labelStatus = PreinventaireLine::NON_INVENTORIABLE_LABEL;
                $statusType = 'status6';
                break;
            case PreinventaireLine::STATUS_NON_INVENTORIE:
                $labelStatus = PreinventaireLine::NON_INVENTORIE_LABEL;
                $statusType = 'status5';
                break;
            case PreinventaireLine::STATUS_INVENTORIE:
                $labelStatus = PreinventaireLine::INVENTORIE_LABEL;
                $statusType = 'status4';
                break;
        }
		

		return dolGetStatus($labelStatus, '', '', $statusType, $mode);
	}


}