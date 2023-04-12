<?php

/**
 * Contient des méthodes relatives aux formulaires du module Préinventaire
 */
class FormPreinventaire
{
	/**
     * @var DoliDB Database handler.
     */
    public $db;

	// Cache arrays
	public $cache_factures = array();
	public $cache_sources = array();


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
     * Charge la liste des factures disponibles (brouillons)
     */
    public function loadFactures($orderBy = 'f.rowid')
    {
        global $conf, $langs;

        // Load draft invoices
        $sql = "SELECT f.rowid, f.ref_supplier";
        $sql .= " FROM ".MAIN_DB_PREFIX."facture_fourn as f";
        $sql .= " WHERE f.fk_statut = 0";
        $sql .= " ORDER BY ".$orderBy;
        $resql = $this->db->query($sql);

        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);
                $this->cache_factures[$obj->rowid]['ref'] = $obj->ref_supplier;
                $i++;
            }
            return $num;
        }
        else
        {
            dol_print_error($this->db);
            return 0;
        }

    }

    public function selectFactures($selected = '', $htmlname = 'sourceid', $empty = 1, $disabled = 0, $empty_label = '', $morecss = 'minwidth200')
    {
        global $conf, $langs, $user, $hookmanager;

        $out = '';

        $this->loadFactures();

        if ($conf->use_javascript_ajax && !$forcecombo)
        {
            include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
            $comboenhancement = ajax_combobox($htmlname);
            $out .= $comboenhancement;
        }

        $out .= '<select class="flat'.($morecss ? ' '.$morecss : '').'"'.($disabled ? ' disabled' : '').' id="'.$htmlname.'" name="'.($htmlname.($disabled ? '_disabled' : '')).'">';
        if ($empty) $out .= '<option value="-1">'.($empty_label ? $empty_label : '&nbsp;').'</option>';
        foreach ($this->cache_factures as $id => $arrayfacture)
        {
            $label = '';
            $label .= $arrayfacture['ref'];
            $out .= '<option value="'.$id.'"';
            if ($selected == $id) $out .= ' selected';
            $out .= ' data-html="'.dol_escape_htmltag($label).'"';
            $out .= '>';
            $out .= $label;
            $out .= '</option>';
        }
        $out .= '</select>';
        if ($disabled) $out .= '<input type="hidden" name="'.$htmlname.'" value="'.(($selected > 0) ? $selected : '').'">';

        return $out;

    }

    public function loadSources()
    {
        // Fetch factures, recu fiscaux and emprunt that are treated (meaning they are in the 'source' table)
        $sql = "SELECT f.rowid, f.ref FROM ".MAIN_DB_PREFIX."facture_fourn as f";
        $sql .= " WHERE f.rowid IN (SELECT fk_source FROM ".MAIN_DB_PREFIX."source WHERE fk_type_source = 1 AND inventoriable = 1)";
        $sql .= " UNION ";
        $sql .= "SELECT r.rowid, r.ref FROM ".MAIN_DB_PREFIX."recu_fiscal as r";
        $sql .= " WHERE r.rowid IN (SELECT fk_source FROM ".MAIN_DB_PREFIX."source WHERE fk_type_source = 2 AND inventoriable = 1)";
        // $sql .= " UNION ";
        // $sql = "SELECT e.rowid, e.ref FROM ".MAIN_DB_PREFIX."emprunt as e";
        // $sql .= " WHERE e.rowid  IN (SELECT fk_source FROM ".MAIN_DB_PREFIX."source WHERE fk_type_source = 3 AND inventoriable = 1)";

        $resql = $this->db->query($sql);
        $num = $this->db->num_rows($resql);
        if ($num > 0) {
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($resql);
                $this->cache_sources[] = $obj->ref;
                $i++;
            }
        }
    }

    public function selectSources ($selected = '', $htmlname = 'sourceid', $empty = 1, $disabled = 0, $empty_label = '', $morecss = 'minwidth200')
    {
        global $conf, $langs, $user, $hookmanager;

        $out = '';

        $this->loadSources();

        if ($conf->use_javascript_ajax && !$forcecombo)
        {
            include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
            $comboenhancement = ajax_combobox($htmlname);
            $out .= $comboenhancement;
        }

        $out .= '<select class="flat'.($morecss ? ' '.$morecss : '').'"'.($disabled ? ' disabled' : '').' id="'.$htmlname.'" name="'.($htmlname.($disabled ? '_disabled' : '')).'">';
        if ($empty) $out .= '<option value="-1" selected="selected">'.($empty_label ? $empty_label : '&nbsp;').'</option>';
        foreach ($this->cache_sources as $sourceref)
        {
            $label = '';
            $label .= $sourceref;
            $out .= '<option value="'.$sourceref.'"';
            if ($selected == $sourceref) $out .= ' selected';
            $out .= ' data-html="'.dol_escape_htmltag($label).'"';
            $out .= '>';
            $out .= $label;
            $out .= '</option>';
        }
        $out .= '</select>';
        if ($disabled) $out .= '<input type="hidden" name="'.$htmlname.'" value="'.(($selected > 0) ? $selected : '').'">';

        return $out;

    }

}
