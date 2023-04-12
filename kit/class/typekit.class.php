<?php

class TypeKit
{
    public $id;
    public $indicatif;
    public $title;
    public $allowed_materiel_type_ids = array();
    public $db_table_name;
    

    /**
     *  Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->db_table_name = 'c_type_kit';
    }

	/**
	 *  Récupération des données du type de kit à partir de l'id
     *
     *  @return int    0 if KO, 1 if OK
	 */

    public function fetch($id)
    {
        $sql = "SELECT * FROM ".MAIN_DB_PREFIX.$this->db_table_name." as tk";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_kit_det as tkdet on tk.rowid = tkdet.fk_type_kit";
        $sql .= " WHERE tk.rowid = ".(int) $id;

        $resql = $this->db->query($sql);
        $num = $this->db->num_rows($resql);

        if ($resql && $num) {    
            // Loop through result, creating array of allowed materiel type
            $i = 0;
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);
                $materiel_type_id = $obj->fk_type_materiel;
                $this->allowed_materiel_type_ids[] = $materiel_type_id;
                $this->id = $id;
                $this->title = $obj->type;
                $this->indicatif = $obj->indicatif;
                $this->active = $obj->active;
                
                $i++;
            }
            return 1;
        }
        else return 0; // No entry found
    }

    /**
     * Get allowed materiel type info
     * 
     * @return array Allowed materiel type info (indicatif, titre...)
     */
    public function getAllowedMaterielTypesInfo()
    {
        if (empty($this->allowed_materiel_type_ids)) return 0;

        $return_array = array();

        $sql = "SELECT fk_type_materiel, fk_type_kit, tm.* FROM ".MAIN_DB_PREFIX."c_type_kit_det as tkdet ";
        $sql .= "LEFT JOIN ".MAIN_DB_PREFIX."c_type_materiel as tm ON tm.rowid = tkdet.fk_type_materiel ";
        $sql .= "WHERE tkdet.fk_type_kit = ". (int)$this->id;
        $resql = $this->db->query($sql);
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);
                // Looping through results
                $return_array[$obj->rowid]['indicatif'] = $obj->indicatif;
                $return_array[$obj->rowid]['title'] = $obj->type;
                $return_array[$obj->rowid]['class_id'] = $obj->fk_classe;  
                $i++;
            }
            return $return_array;
        }
        else return 0;
    }

}
?>
