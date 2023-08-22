<?php

/**
 * Contient des méthodes relatives aux formulaires du module Materiel
 */
class FormMateriel
{
	/**
     * @var DoliDB Database handler.
     */
    public $db;

	// Cache arrays
	public $cache_warehouses = array();
	public $cache_lot = array();
	public $cache_adherent = array();
	public $cache_type_materiel = array();
	public $cache_materiel = array();
	public $cache_marque = array();
	public $cache_classe = array();


	/**
	 *  Constructor
	 *
	 *  @param  DoliDB  $db     Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	public function strToArray($str) // Converti les listes d'id de la bdd en array (["1", "2", "3"])
	{
	    return json_decode($str, false);
	}


    /* Charge la liste des matériels dans le cache */
    public function loadMateriels($fk_product = 0, $batch = '', $status = '', $sumStock = true, $exclude = '', $orderBy = 'm.rowid', $onlyNoLot = 1)
	{
		global $conf, $langs;

		if (empty($fk_product) && count($this->cache_materiel)) return 0;

		if (is_array($exclude))	$excludeGroups = implode("','", $exclude);



		$sql = "SELECT m.rowid, m.cote, t.indicatif";
		$sql .= " FROM ".MAIN_DB_PREFIX."materiel as m";
        $sql.=" INNER JOIN ".MAIN_DB_PREFIX."c_type_materiel as t ON m.fk_type_materiel=t.rowid";
        $sql .= " ORDER BY ".$orderBy;




		$resql = $this->db->query($sql);

		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);
				$this->cache_materiel[$obj->rowid]['id'] = $obj->rowid;
				$this->cache_materiel[$obj->rowid]['indicatif'] = $obj->indicatif;
				$this->cache_materiel[$obj->rowid]['cote'] = $obj->cote;
				$i++;
			}
			return $num;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}

	}
	public function selectMateriels($selected = '', $htmlname = 'idmateriel', $filterstatus = '', $empty = 0, $disabled = 0, $fk_product = 0, $empty_label = '', $showstock = 0, $forcecombo = 0, $events = array(), $morecss = 'minwidth200', $exclude = '', $showfullpath = 1, $orderBy = 'm.rowid', $onlyNoLot = 1)
	{
		global $conf, $langs, $user;

		$out = '';

		$this->loadMateriels();
		$nbofmat = count($this->cache_materiel);

		if ($conf->use_javascript_ajax && !$forcecombo)
		{
			include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
			$comboenhancement = ajax_combobox($htmlname, $events);
			$out .= $comboenhancement;
		}

		$out .= '<select class="flat'.($morecss ? ' '.$morecss : '').'"'.($disabled ? ' disabled' : '').' id="'.$htmlname.'" name="'.$htmlname.'">';
		if ($empty) $out .= '<option value="-1">'.($empty_label ? $empty_label : '&nbsp;').'</option>';
		foreach ($this->cache_materiel as $id => $arraymateriel)
		{
			$label = '';
			$label .= $arraymateriel['indicatif'];
		    $label .= '-';
		    $label .= $arraymateriel['cote'];
			$out .= '<option value="'.$id.'"';
			if ($selected == $id || ($selected == 'ifone' && $nboftypes == 1)) $out .= ' selected';
			$out .= ' data-html="'.dol_escape_htmltag($label).'"';
			$out .= '>';
			$out .= $label;
			$out .= '</option>';
		}
		$out .= '</select>';
		if ($disabled) $out .= '<input type="hidden" name="'.$htmlname.'" value="'.(($selected > 0) ? $selected : '').'">';

        $parameters = array(
            'selected' => $selected,
            'htmlname' => $htmlname,
            'filterstatus' => $filterstatus,
            'empty' => $empty,
            'disabled ' => $disabled,
            'fk_product' => $fk_product,
            'empty_label' => $empty_label,
            'showstock' => $showstock,
            'forcecombo' => $forcecombo,
            'events' => $events,
            'morecss' => $morecss
        );

		return $out;
	}
	
	public function formSelectMateriels($page, $selected = '', $htmlname = 'materiel_id', $addempty = 0)
    {
        global $langs;
        if ($htmlname != "none") {
            print '<form method="POST" action="'.$page.'">';
            print '<input type="hidden" name="action" value="settypemateriel">';
            print '<input type="hidden" name="token" value="'.newToken().'">';
            print '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
            print '<tr><td>';
            print $this->selectMateriels($selected, $htmlname, '', $addempty);
            print '</td>';
            print '<td class="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
            print '</tr></table></form>';
        }
    }


	/* Charge la liste des marques dans le cache */
public function loadMarques($fk_product = 0, $batch = '', $status = '', $sumStock = true, $exclude = '', $stockMin = false, $orderBy = 'm.rowid')
	{
		global $conf, $langs;

		if (empty($fk_product) && count($this->cache_marque)) return 0;

		if (is_array($exclude))	$excludeGroups = implode("','", $exclude);



		$sql = "SELECT m.rowid, m.marque";

		$sql .= " FROM ".MAIN_DB_PREFIX."c_marque as m";

		if (!empty($exclude)) $sql .= ' AND m.rowid NOT IN('.$this->db->escape(implode(',', $exclude)).')';


        $sql .= " ORDER BY ".$orderBy;
		$resql = $this->db->query($sql);

		if ($resql)
		{

			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);
				$this->cache_marque[$obj->rowid]['id'] = $obj->rowid;
				$this->cache_marque[$obj->rowid]['marque'] = $obj->marque;
				$i++;
			}
			return $num;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}

	}
	public function selectMarques($selected = '', $htmlname = 'idmarque', $filterstatus = '', $empty = 0, $disabled = 0, $fk_product = 0, $empty_label = '', $showstock = 0, $forcecombo = 0, $events = array(), $morecss = 'minwidth200', $exclude = '', $showfullpath = 1, $stockMin = false, $orderBy = 'e.ref')
	{
		global $conf, $langs, $user, $hookmanager;

		$out = '';

		$this->loadMarques();
		$nboftypes = count($this->cache_marque);

		if ($conf->use_javascript_ajax && !$forcecombo)
		{
			include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
			$comboenhancement = ajax_combobox($htmlname, $events);
			$out .= $comboenhancement;
		}

		$out .= '<select class="flat'.($morecss ? ' '.$morecss : '').'"'.($disabled ? ' disabled' : '').' id="'.$htmlname.'" name="'.($htmlname.($disabled ? '_disabled' : '')).'">';
		if ($empty) $out .= '<option value="-1">'.($empty_label ? $empty_label : '&nbsp;').'</option>';
		foreach ($this->cache_marque as $id => $arraymarques)
		{
			$label = '';
			$label .= $arraymarques['marque'];
			$out .= '<option value="'.$id.'"';
			if ($selected == $id || ($selected == 'ifone' && $nboftypes == 1)) $out .= ' selected';
			$out .= ' data-html="'.dol_escape_htmltag($label).'"';
			$out .= '>';
			$out .= $label;
			$out .= '</option>';
		}
		$out .= '</select>';
		if ($disabled) $out .= '<input type="hidden" name="'.$htmlname.'" value="'.(($selected > 0) ? $selected : '').'">';

        $parameters = array(
            'selected' => $selected,
            'htmlname' => $htmlname,
            'filterstatus' => $filterstatus,
            'empty' => $empty,
            'disabled ' => $disabled,
            'fk_product' => $fk_product,
            'empty_label' => $empty_label,
            'showstock' => $showstock,
            'forcecombo' => $forcecombo,
            'events' => $events,
            'morecss' => $morecss
        );

		return $out;

	}
	public function formSelectMarques($page, $selected = '', $htmlname = 'marque_id', $addempty = 0)
    {
        global $langs;
        if ($htmlname != "none") {
            print '<form method="POST" action="'.$page.'">';
            print '<input type="hidden" name="action" value="setmarque">';
            print '<input type="hidden" name="token" value="'.newToken().'">';
            print '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
            print '<tr><td>';
            print $this->selectMarques($selected, $htmlname, '', $addempty);
            print '</td>';
            print '<td class="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
            print '</tr></table></form>';
        }
    }


  	public function selectTypesMateriel($selected = '', $htmlname = 'idtypemateriel', $filterstatus = '', $empty = 0, $disabled = 0, $fk_product = 0, $empty_label = '', $showstock = 0, $forcecombo = 0, $events = array(), $morecss = 'minwidth200', $exclude = '', $showfullpath = 1, $stockMin = false, $orderBy = 'e.ref')
  	{
  		global $conf, $langs, $user, $hookmanager;

  		$out = '';

  		$this->loadTypesMateriel();
  		$nboftypes = count($this->cache_type_materiel);

  		if ($conf->use_javascript_ajax && !$forcecombo)
  		{
  			include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
  			$comboenhancement = ajax_combobox($htmlname, $events);
  			$out .= $comboenhancement;
  		}

  		$out .= '<select class="flat'.($morecss ? ' '.$morecss : '').'"'.($disabled ? ' disabled' : '').' id="'.$htmlname.'" name="'.($htmlname.($disabled ? '_disabled' : '')).'">';
  		if ($empty) $out .= '<option value="-1">'.($empty_label ? $empty_label : '&nbsp;').'</option>';
  		foreach ($this->cache_type_materiel as $id => $arraytypes)
  		{
  			$label = '';
  			$label .= $arraytypes['indicatif'];
  		    $label .= ' - ';
  		    $label .= $arraytypes['type'];
  			$out .= '<option value="'.$id.'"';
  			if ($selected == $id || ($selected == 'ifone' && $nboftypes == 1)) $out .= ' selected';
  			$out .= ' data-html="'.dol_escape_htmltag($label).'"';
  			$out .= '>';
  			$out .= $label;
  			$out .= '</option>';
  		}
  		$out .= '</select>';
  		if ($disabled) $out .= '<input type="hidden" name="'.$htmlname.'" value="'.(($selected > 0) ? $selected : '').'">';

          $parameters = array(
              'selected' => $selected,
              'htmlname' => $htmlname,
              'filterstatus' => $filterstatus,
              'empty' => $empty,
              'disabled ' => $disabled,
              'fk_product' => $fk_product,
              'empty_label' => $empty_label,
              'showstock' => $showstock,
              'forcecombo' => $forcecombo,
              'events' => $events,
              'morecss' => $morecss
          );

  		return $out;

  	}
  	public function formSelectTypesMateriel($page, $selected = '', $htmlname = 'type_materiel_id', $addempty = 0)
      {
          global $langs;
          if ($htmlname != "none") {
              print '<form method="POST" action="'.$page.'">';
              print '<input type="hidden" name="action" value="settypemateriel">';
              print '<input type="hidden" name="token" value="'.newToken().'">';
              print '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
              print '<tr><td>';
              print $this->selectTypesMateriel($selected, $htmlname, '', $addempty);
              print '</td>';
              print '<td class="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
              print '</tr></table></form>';
          }
      }

  	/* Charge la liste des adhérents dans le cache */
  public function loadProprietaires($fk_product = 0, $batch = '', $status = '', $sumStock = true, $exclude = '', $stockMin = false, $orderBy = 'p.rowid')
  	{
  		global $conf, $langs;

  		if (is_array($exclude))	$excludeGroups = implode("','", $exclude);

  		$sql = "SELECT p.rowid, p.proprietaire";
  		$sql .= " FROM ".MAIN_DB_PREFIX."c_proprietaire as p";
  		$sql .= " WHERE p.active = 1";
        $sql .= " ORDER BY ".$orderBy;
  		$resql = $this->db->query($sql);

  		if ($resql)
  		{
  			$num = $this->db->num_rows($resql);
  			$i = 0;
  			while ($i < $num)
  			{
  				$obj = $this->db->fetch_object($resql);
  				$this->cache_proprietaire[$obj->rowid]['id'] = $obj->rowid;
  				$this->cache_proprietaire[$obj->rowid]['proprietaire'] = $obj->proprietaire;
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

  	public function selectProprietaires($selected = '', $htmlname = 'idproprietaire', $filterstatus = '', $empty = 0, $disabled = 0, $fk_product = 0, $empty_label = '', $showstock = 0, $forcecombo = 0, $events = array(), $morecss = 'minwidth200', $exclude = '', $showfullpath = 1, $stockMin = false, $orderBy = 'p.rowid')
  	{
  		global $conf, $langs, $user, $hookmanager;

  		$out = '';

  		$this->loadProprietaires();

  		if ($conf->use_javascript_ajax && !$forcecombo)
  		{
  			include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
  			$comboenhancement = ajax_combobox($htmlname, $events);
  			$out .= $comboenhancement;
  		}

  		$out .= '<select class="flat'.($morecss ? ' '.$morecss : '').'"'.($disabled ? ' disabled' : '').' id="'.$htmlname.'" name="'.($htmlname.($disabled ? '_disabled' : '')).'">';
  		if ($empty) $out .= '<option value="-1">'.($empty_label ? $empty_label : '&nbsp;').'</option>';
  		foreach ($this->cache_proprietaire as $id => $arrayproprietaires)
  		{
  			$label = '';
  			$label .= $arrayproprietaires['proprietaire'];
  			$out .= '<option value="'.$id.'"';
  			if ($selected == $id || ($selected == 'ifone' && $nbofwarehouses == 1)) $out .= ' selected';
  			$out .= ' data-html="'.dol_escape_htmltag($label).'"';
  			$out .= '>';
  			$out .= $label;
  			$out .= '</option>';
  		}
  		$out .= '</select>';
  		if ($disabled) $out .= '<input type="hidden" name="'.$htmlname.'" value="'.(($selected > 0) ? $selected : '').'">';

          $parameters = array(
              'selected' => $selected,
              'htmlname' => $htmlname,
              'filterstatus' => $filterstatus,
              'empty' => $empty,
              'disabled ' => $disabled,
              'fk_product' => $fk_product,
              'empty_label' => $empty_label,
              'showstock' => $showstock,
              'forcecombo' => $forcecombo,
              'events' => $events,
              'morecss' => $morecss
          );

  		return $out;

  	}

  	public function formSelectProprietaires($page, $selected = '', $htmlname = 'proprietaire_id', $addempty = 0)
      {
          global $langs;
          if ($htmlname != "none") {
              print '<form method="POST" action="'.$page.'">';
              print '<input type="hidden" name="action" value="setproprietaire">';
              print '<input type="hidden" name="token" value="'.newToken().'">';
              print '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
              print '<tr><td>';
              print $this->selectProprietaires($selected, $htmlname, '', $addempty);
              print '</td>';
              print '<td class="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
              print '</tr></table></form>';
          }
      }

      /* Charge la liste des types de matériel dans le cache */
      public function loadTypesMateriel($fk_product = 0, $batch = '', $status = '', $sumStock = true, $exclude = '', $stockMin = false, $orderBy = 't.rowid')
      {
        global $conf, $langs;

        if (empty($fk_product) && count($this->cache_type_materiel)) return 0;

        if (is_array($exclude))	$excludeGroups = implode("','", $exclude);



        $sql = "SELECT t.rowid, t.indicatif, t.type";

        $sql .= " FROM ".MAIN_DB_PREFIX."c_type_materiel as t";

        if (!empty($exclude)) $sql .= ' AND t.rowid NOT IN('.$this->db->escape(implode(',', $exclude)).')';


            $sql .= " ORDER BY ".$orderBy;
        $resql = $this->db->query($sql);

        if ($resql)
        {

          $num = $this->db->num_rows($resql);
          $i = 0;
          while ($i < $num)
          {
            $obj = $this->db->fetch_object($resql);
            $this->cache_type_materiel[$obj->rowid]['id'] = $obj->rowid;
            $this->cache_type_materiel[$obj->rowid]['indicatif'] = $obj->indicatif;
            $this->cache_type_materiel[$obj->rowid]['type'] = $obj->type;
            $i++;
          }
          return $num;
        }
        else
        {
          dol_print_error($this->db);
          return -1;
        }

      }

      /*
      * Formulaire de selection de classe de type de materiel
      */

      /* Charge la liste des classe dans le cache */
	public function loadClasses($orderBy = 'c.classe')
	{
		global $conf, $langs;

		if (count($this->cache_classe)) return 0;

		$sql = "SELECT c.rowid, c.classe";
		$sql .= " FROM ".MAIN_DB_PREFIX."c_classe_materiel as c";
		$sql .= " WHERE active=1";
		$sql .= " ORDER BY ".$orderBy;
		$resql = $this->db->query($sql);

		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);
				$this->cache_classe[$obj->rowid]['id'] = $obj->rowid;
				$this->cache_classe[$obj->rowid]['classe'] = $obj->classe;
				$i++;
			}
			return $num;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}

	}
	public function selectClasses($empty = 1, $selected = '', $htmlname = 'idclasse', $disabled = 0, $morecss = 'minwidth200', $orderBy = 'm.rowid')
	{
		global $conf, $langs, $user, $hookmanager;

		$out = '';

		$this->loadClasses();
		$nbofclasse = count($this->cache_classe);

		if ($conf->use_javascript_ajax)
		{
			include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
			$comboenhancement = ajax_combobox($htmlname, $events);
			$out .= $comboenhancement;
		}

		$out .= '<select class="flat'.($morecss ? ' '.$morecss : '').'"'.($disabled ? ' disabled' : '').' id="'.$htmlname.'" name="'.$htmlname.'">';
		if ($empty) $out .= '<option value="-1">'.($empty_label ? $empty_label : '&nbsp;').'</option>';
		foreach ($this->cache_classe as $id => $arrayclasse)
		{
			$label = '';
			$label .= $arrayclasse['classe'];
			$out .= '<option value="'.$id.'"';
			if ($selected == $id || ($selected == 'ifone' && $nbofclasse == 1)) $out .= ' selected';
			$out .= ' data-html="'.dol_escape_htmltag($label).'"';
			$out .= '>';
			$out .= $label;
			$out .= '</option>';
		}
		$out .= '</select>';
		if ($disabled) $out .= '<input type="hidden" name="'.$htmlname.'" value="'.(($selected > 0) ? $selected : '').'">';
		return $out;

	}
	public function formSelectClasses($page, $selected = '', $htmlname = 'classe_id', $addempty = 0)
	{
		global $langs;
		if ($htmlname != "none") {
			print '<form method="POST" action="'.$page.'">';
			print '<input type="hidden" name="action" value="setclasse">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
			print '<tr><td>';
			print $this->selectClasses($selected, $htmlname, '', $addempty);
			print '</td>';
			print '<td class="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
			print '</tr></table></form>';
		}
	}


	public function getPreinventaireLinesForCreation()
	{
		$return_array = array();

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."preinventaire";
		$sql .= " WHERE inventoriable = 1";
		$sql .= " AND rowid NOT IN ". "(SELECT fk_preinventaire FROM ".MAIN_DB_PREFIX."materiel WHERE fk_preinventaire IS NOT NULL) ORDER BY rowid DESC";
		$resql = $this->db->query($sql);
		if ($this->db->num_rows($resql) > 0) {
			$i = 0;
			$num = $this->db->num_rows($resql);
			while ($i < $num) {
				$line = $this->db->fetch_object($resql);

				$return_array[$line->rowid] = $line->description;
				$i++;
			}
		}
		return $return_array;
	}

}
