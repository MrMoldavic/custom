<?php

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';
class Salle extends CommonObject
{
    /**
     * @var string ID to identify managed object
     */
    public $element = 'salle';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'salles';


    public $picto = 'thirdparty';

    public $regeximgext = '\.gif|\.jpg|\.jpeg|\.png|\.bmp|\.webp|\.xpm|\.xbm'; // See also into images.lib.php

    public $id;
    public $ref;

    public $indicatif_batiment;
    public $batiment;
    public $salle;

    public $active;

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


    public function fetch($id = '')
    {

        global $langs, $conf, $fields;

        // Check parameters
        if (!$id) {
            $this->error = 'ErrorWrongParameters';
            dol_syslog(get_class($this)."::fetch ".$this->error);
            return -1;
        }

        $sql = "SELECT s.rowid, s.indicatif_batiment, s.salle, s.active";
        $sql .= " , b.batiment";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_salle as s ";
        $sql.="INNER JOIN ".MAIN_DB_PREFIX."c_batiment as b ON s.indicatif_batiment=b.indicatif ";
        $sql .= " WHERE s.rowid = ".(int) $id;

        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql) > 0) {
                $obj = $this->db->fetch_object($resql);

                $this->id                           = $obj->rowid;
                $this->active                       = $obj->active;
                $this->indicatif_batiment           = $obj->indicatif_batiment;
                $this->salle                        = $obj->salle;
                $this->batiment                     = $obj->batiment;
                $this->ref                          = $obj->indicatif_batiment . '-' . $obj->salle;
            }
        }
        else
        {
            dol_print_error($this->db);
            return -1;
        }

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

        $label = '<u>Salle</u>';
        $label .= '<br><b>bâtiment : </b> '.$this->batiment;
        $label .= '<br><b>Salle : </b> '.$this->salle;

        $linkclose = '';

        if (empty($notooltip)) {
            if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
                $label = $langs->trans("ShowProduct");
                $linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
            }

            $linkclose .= ' title="'.dol_escape_htmltag($label, 1, 1).'"';
            $linkclose .= ' class="nowraponall classfortooltip"';
        }
        $linkstart = '<a '.$style;
        $linkstart .= $linkclose.'>';
        $linkend = '</a>';

        $result = $linkstart;

        $result .= (img_object(($notooltip ? '' : $label), 'company', ($notooltip ? 'class="paddingright"'.$style : 'class="paddingright classfortooltip"'.$style), 0, 0, $notooltip ? 0 : 1));

        $result .= $this->ref;
        $result .= $linkend;


        return $result;
    }




}
?>
