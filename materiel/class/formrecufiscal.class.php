<?php

//require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/recufiscal.class.php';

/**
 * Contient des méthodes relatives aux formulaires du module de reçu fiscaux
 */
class FormRecuFiscal
{
	/**
     * @var DoliDB Database handler.
     */
    public $db;


	/**
	 *  Constructor
	 *
	 *  @param  DoliDB  $db     Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


    /**
     * Return array of donateur for select field
     */
    public function getDonateurArray()
    {
        $donateur_array = array();

        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."donateur ";
        $sql .= "WHERE active = 1";
        $resql = $this->db->query($sql);
        $num = $this->db->num_rows($resql);
        $i = 0;
        while ($i < $num)
        {
            // Looping through results
            $obj = $this->db->fetch_object($resql);
            $label = (!empty($obj->prenom) ? $obj->prenom . ' ' . $obj->nom : $obj->societe);
            $donateur_array[$obj->rowid] = $label;
            $i++;
        }
        return $donateur_array;
    }

}
