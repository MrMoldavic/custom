<?php

require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/kit.class.php';
class FormKit
{
	/**
     * @var DoliDB Database handler.
     */
    public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	// Cache arrays
	public $cache_type_kit = array();
	public $cache_materiel = array();
	public $cache_kit = array();


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



	/**
	 *  Form selection type de kit
	 *
	 * @return mixed null if KO, 1 if OK
	 */

	/* Charge la liste des types de kit dans le cache */
    public function loadKitType($orderBy = 'k.rowid')
	{
		global $conf, $langs;

		if (count($this->cache_type_kit)) return null; // Array already exists

		$sql = "SELECT k.rowid, k.indicatif, k.type";
		$sql .= " FROM ".MAIN_DB_PREFIX."c_type_kit as k";
        $sql .= " ORDER BY ".$orderBy;
		$resql = $this->db->query($sql);

		if (!$resql)
		{
			dol_print_error($this->db);
			return null;
		}

		$num = $this->db->num_rows($resql);
		$i = 0;
		while ($i < $num)
		{
			$obj = $this->db->fetch_object($resql);
			$this->cache_type_kit[$obj->rowid]['id'] = $obj->rowid;
			$this->cache_type_kit[$obj->rowid]['indicatif'] = $obj->indicatif;
			$this->cache_type_kit[$obj->rowid]['type'] = $obj->type;
			$i++;
		}
		return 1;
	}

	/**
	 * Retourne un formulaire de selection de type de kit
	 */
	public function selectTypeKit($selected = '', $htmlname = 'idtypekit', $empty = 0, $disabled = 0, $forcecombo = 0, $morecss = 'minwidth200', $orderBy = 'k.indicatif')
	{
		global $conf, $langs, $user, $hookmanager;
		$out = '';
		$this->loadKitType();
		$nboftypes = count($this->cache_type_kit);

		if ($conf->use_javascript_ajax && !$forcecombo)
		{
			include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
			$comboenhancement = ajax_combobox($htmlname, $events);
			$out .= $comboenhancement;
		}

		$out .= '<select class="flat'.($morecss ? ' '.$morecss : '').'"'.($disabled ? ' disabled' : '').' id="'.$htmlname.'" name="'.($htmlname.($disabled ? '_disabled' : '')).'">';
		if ($empty) $out .= '<option value="-1">'.($empty_label ? $empty_label : '&nbsp;').'</option>';
		foreach ($this->cache_type_kit as $id => $array_types_kit)
		{
			$label = '';
			$label .= $array_types_kit['type'];
			$out .= '<option value="'.$id.'"';
			if ($selected == $id || ($selected == 'ifone' && $nboftypes == 1)) $out .= ' selected';
			$out .= ' data-html="'.dol_escape_htmltag($label).'"';
			$out .= '>';
			$out .= $label;
			$out .= '</option>';
		}
		$out .= '</select>';
		if ($disabled) $out .= '<input type="hidden" name="'.$htmlname.'" value="'.(($selected > 0) ? $selected : '').'">';

		return $out;
	}

	public function formSelectTypesKit($page, $selected = '', $htmlname = 'idtypekit', $addempty = 0)
    {
        global $langs;
        if ($htmlname != "none") {
            print '<form method="POST" action="'.$page.'">';
            print '<input type="hidden" name="action" value="settypekit">';
            print '<input type="hidden" name="token" value="'.newToken().'">';
            print '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
            print '<tr><td>';
            print $this->selectTypeKit($selected, $htmlname, '', $addempty);
            print '</td>';
            print '<td class="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
            print '</tr></table></form>';
        }
    }




	/**
	 *  Form selection kit
	 *
	 *
	 */


	/* Charge la liste des types de kit dans le cache */
    public function loadKit($showAll = 0, $orderBy = 'k.rowid')
	{
		global $conf, $langs;
		$this->cache_kit = array();
		$kit_exclude = array();

        if (!$showAll) { // Si on ne veut que les kits qui ne sont pas en exploitation
            // On récupère la liste des exploitations pour savoir quels kits sont déjà dans une exploitation
    		$sql = "SELECT ec.fk_kit";
    		$sql .= " FROM ".MAIN_DB_PREFIX."exploitation_content as ec";
            $sql .= " WHERE active = 1";
    		$resql = $this->db->query($sql);
    		if (!$resql) return -1;

    		$num = $this->db->num_rows($resql);
    		$i = 0;
    		while ($i < $num) // Création de la liste d'id à exclure
    		{
    			$obj = $this->db->fetch_object($resql);
    			$kit_fk = $obj->fk_kit;
                $kit_exclude[] = ' AND k.rowid<>'.$kit_fk;
    			$i++;
    		}
        }


		$sql = "SELECT k.rowid, k.cote, tk.indicatif";
		$sql .= " FROM ".MAIN_DB_PREFIX."kit as k";
		$sql .= " INNER JOIN ".MAIN_DB_PREFIX."c_type_kit as tk ON k.fk_type_kit=tk.rowid";
		$sql .= " WHERE k.active = 1";
        foreach($kit_exclude as $kit_exclude_) {
            $sql .= $kit_exclude_;
        }
        $sql .= " ORDER BY ".$orderBy;
		$resql = $this->db->query($sql);

		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);
				$this->cache_kit[$obj->rowid]= $obj->indicatif .'-'. $obj->cote;
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
	public function selectKit($selected = '', $htmlname = 'idkit', $empty = 0, $showAll = 0, $isArray = 1,$disabled = 0, $forcecombo = 0, $morecss = 'minwidth200', $orderBy = 'k.indicatif')
	{
		global $conf, $langs, $user, $hookmanager;
		$out = '';
		$this->loadKit($showAll);
		$nbofkits = count($this->cache_kit);

		if ($conf->use_javascript_ajax && !$forcecombo)
		{
			include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
			$comboenhancement = ajax_combobox($htmlname, $events);
			$out .= $comboenhancement;
		}

		$out .= '<select class="flat'.($morecss ? ' '.$morecss : '').'"'.($disabled ? ' disabled' : '').' id="'.$htmlname.'" name="'.($isArray ? "kit[]" : $htmlname).'">';
		if ($empty) $out .= '<option value="-1">'.($empty_label ? $empty_label : '&nbsp;').'</option>';
		foreach ($this->cache_kit as $id => $ref_kit)
		{
		    $kit_tmp = new Kit($this->db);
		    $kit_tmp->fetch($id);
			$label = '<span class="badge  badge-dot badge-status'.$kit_tmp->disponibilite_badge_code.' classfortooltip badge-status" title="'.$kit_tmp->disponibilite.'"></span>&nbsp;&nbsp;';
			$label .= $ref_kit. ' ' . $kit_tmp->type_kit->title . ' ' . $kit_tmp->materiel_object[array_key_first ($kit_tmp->materiel_object)]->marque . ' ' . $kit_tmp->materiel_object[array_key_first ($kit_tmp->materiel_object)]->modele;
			$out .= '<option'.($kit_tmp->disponibilite_badge_code == 8 ? ' disabled' : '').' value="'.$id.'"';
			if ($selected == $id || ($selected == 'ifone' && $nbofkits == 1)) $out .= ' selected';
			$out .= ' data-html="'.dol_escape_htmltag($label).'"';
			$out .= '>';
			$out .= $label;
			$out .= '</option>';
		}
		$out .= '</select>';
		if ($disabled) $out .= '<input type="hidden" name="'.$htmlname.'" value="'.(($selected > 0) ? $selected : '').'">';

		return $out;

	}
	public function formSelectKit($page, $selected = '', $htmlname = 'idkit', $addempty = 0)
    {
        global $langs;
        if ($htmlname != "none") {
            print '<form method="POST" action="'.$page.'">';
            print '<input type="hidden" name="action" value="setkit">';
            print '<input type="hidden" name="token" value="'.newToken().'">';
            print '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
            print '<tr><td>';
            print $this->selectKit($selected, $htmlname, '', $addempty);
            print '</td>';
            print '<td class="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
            print '</tr></table></form>';
        }
    }


	/**
	 *  Charge une liste de matériel pouvant être inclus dans un type de kit
	 *
	 *
	 */
    public function loadMateriels($fk_type_kit, $filterkit = '', $editmode = 0, $orderBy = 'm.rowid')
	{
		global $conf, $langs;
		$mat_exclude = array();
		$type_materiel_to_get = array();
	    $this->cache_materiel = array();

        // On récupère la liste des kits pour savoir quels matériels sont déjà dans un kit
        $sql = "SELECT fk_materiel";
        $sql .= " FROM ".MAIN_DB_PREFIX."kit_content";
        $sql .= " WHERE active = 1";
        if ($filterkit) $sql .= " AND fk_kit<>".$filterkit;
        $resql = $this->db->query($sql);
		$num = $this->db->num_rows($resql);
		$i = 0;
		while ($i < $num) // Création de la liste de matériels déjà dans un kit
		{
			$obj = $this->db->fetch_object($resql);
			$mat_fk = $obj->fk_materiel;
            $mat_exclude[] = ' AND m.rowid<>'.$mat_fk;
			$i++;
		}

   

		$sql = "SELECT m.rowid, m.cote, m.modele, t.indicatif, mq.marque, ex.badge_status_code, ex.exploitabilite";
		$sql .= " FROM ".MAIN_DB_PREFIX."materiel as m";
        $sql.=" INNER JOIN ".MAIN_DB_PREFIX."c_type_materiel as t ON m.fk_type_materiel=t.rowid";
        $sql.=" LEFT JOIN ".MAIN_DB_PREFIX."c_marque as mq ON m.fk_marque=mq.rowid";
        $sql.=" LEFT JOIN ".MAIN_DB_PREFIX."c_exploitabilite as ex ON m.fk_exploitabilite=ex.rowid";
        $sql.=" LEFT JOIN ".MAIN_DB_PREFIX."c_type_kit_det as tkdet ON m.fk_type_materiel = tkdet.fk_type_materiel";
        $sql .= " WHERE m.active = 1";
		$sql .= " AND tkdet.fk_type_kit = " . $fk_type_kit;
        foreach($mat_exclude as $mat_exclude_) {
            $sql .= $mat_exclude_;
        }
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
				$this->cache_materiel[$obj->rowid]['marque'] = $obj->marque;
				$this->cache_materiel[$obj->rowid]['modele'] = $obj->modele;
				$this->cache_materiel[$obj->rowid]['exploitabilite_code'] = $obj->badge_status_code;
				$this->cache_materiel[$obj->rowid]['exploitabilite'] = $obj->exploitabilite;
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



	public function selectMateriels($selected = '', $htmlname = 'idmateriel', $filterstatus = '', $empty = 0, $disabled = 0, $fk_type_kit, $filterkit = 0, $morecss = 'minwidth200', $exclude = '', $showfullpath = 1, $orderBy = 'm.rowid', $onlyNoLot = 0)
	{
		global $conf, $langs, $user, $hookmanager;
		$out = '';

		$this->loadMateriels($fk_type_kit, $filterkit);
		$nbofmat = count($this->cache_materiel);

		if ($conf->use_javascript_ajax && !$forcecombo)
		{
			include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
			$comboenhancement = ajax_combobox($htmlname, $events);
			$out .= $comboenhancement;
		}

		$out .= '<select class="flat'.($morecss ? ' '.$morecss : '').'"'.($disabled ? ' disabled' : '').' id="'.$htmlname.'" name="materiel[]">';
		if ($empty) $out .= '<option value="-1">'.($empty_label ? $empty_label : '&nbsp;').'</option>';
		foreach ($this->cache_materiel as $id => $arraymateriel)
		{
			$label = '<span class="badge  badge-dot badge-status'.$arraymateriel['exploitabilite_code'].' classfortooltip badge-status" title="'.$arraymateriel['exploitabilite'].'"></span>&nbsp;&nbsp;';
			$label .= $arraymateriel['indicatif'];
		    $label .= '-';
		    $label .= $arraymateriel['cote'];
		    $label .= ' '.$arraymateriel['marque'];
		    $label .= ' '.$arraymateriel['modele'];
			$out .= '<option value="'.$id.'"';
			if ($selected == $id || ($selected == 'ifone' && $nboftypes == 1)){
			    $out .= ' selected';
			}
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

    public function kitHistory($id)
    {
        function cmp($a, $b) {
            $a_date = strtotime((array_key_exists ('date_ajout', $a) ? $a['date_ajout'] : $a['date_suppression']));
            $b_date = strtotime((array_key_exists ('date_ajout', $b) ? $b['date_ajout'] : $b['date_suppression']));
            if ($a_date == $b_date) return 0;
            return ($a_date > $b_date) ? -1 : 1;
        }
        //On récupère les dates d'ajout des matériels (actifs et supprimés du kit) et les dates de suppression des matériels désactivés
        if (!$id) return 0;
        $history_data = array(); // Liste d'objets de réponse sql

        $sql = "SELECT kc.date_ajout, kc.fk_materiel, kc.fk_user_author";
        $sql .= " FROM ".MAIN_DB_PREFIX."kit_content as kc";
        $sql .= " WHERE kc.fk_kit = ".$id;
        $sql .= " ORDER BY kc.date_ajout DESC";
    		$resql = $this->db->query($sql);
    		$active_mat_list = mysqli_fetch_all($resql, MYSQLI_ASSOC);
    		$history_data = array_merge($history_data, $active_mat_list);

        $sql = "SELECT kc.date_suppression, kc.fk_materiel, kc.fk_user_delete";
        $sql .= " FROM ".MAIN_DB_PREFIX."kit_content as kc";
        $sql .= " WHERE kc.date_suppression IS NOT NULL AND kc.fk_kit = ".$id;
        $sql .= " ORDER BY kc.date_suppression DESC";
    		$resql = $this->db->query($sql);
    		$inactive_mat_list = mysqli_fetch_all($resql, MYSQLI_ASSOC);
    		$history_data = array_merge($history_data, $inactive_mat_list);

    		usort($history_data, "cmp"); // On trie les historiques par date

    		return $history_data;

    }

    /*
     * Affiche le formulaire de selection de matériel pour la création d'un kit
     */
    public function printKitMaterielSelect($kit = '', $id_type_kit) 
	{
        print '<tr></tr>';
        print '<tr id="first_mat"><td class="titlefieldcreate fieldrequired">Matériel(s) inclus : </td><td>';
        print ' <a href="" class="add_select">Cliquez pour ajouter un matériel';
        print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="Ajouter un matériel"></span>';
        print '</a>';
        print '</td>';
        print '</tr>';
        print '<tr><td></td><td>';
        print 'Le premier materiel correspondra au libellé du kit.';
        print '</td>';
        print '</tr>';
		
        print '<tr id="before_select"></tr>';

		// If the kit parameter is provided, display the list of materiel in it
        if (is_object($kit)) 
		{
        	foreach ($kit->materiel_object as $mat) 
			{
                print '<tr><td style="text-align:right;"><div class="fas fa-angle-up mat-sort-up" style="cursor:pointer; position:absolute;"></div><div class="fas fa-angle-down mat-sort-down" style="cursor:pointer; margin-top:20px;"></div></td><td style="vertical-align:middle; padding-left:20px;"><span class="fas fa-trash delete-select"></span>'.$this->selectMateriels($mat->id, uniqid(), '', 1,0, $id_type_kit, $kit->id) . '</td></tr>';
        	}
        } 
		else 
		{
            print '<tr><td style="text-align:right;"><span class="classfortooltip" title="Ce matériel correspondra au libellé du kit"></span><div class="fas fa-angle-up mat-sort-up" style="cursor:pointer; position:absolute;"></div><div class="fas fa-angle-down mat-sort-down" style="cursor:pointer; margin-top:20px;"></div></td><td style="vertical-align:middle; padding-left:20px;"><span class="fas fa-trash delete-select"></span>'.$this->selectMateriels('', uniqid(), '', 1,0, GETPOST('idtypekit', 'int')) . '</td></tr>';
        }
		print '<tr id="select-delimitor"></tr>';

		print "<script>
        $( \".add_select\" ).click(function(e) {
            e.preventDefault();
                $.ajax({ url: '".DOL_URL_ROOT."/custom/kit/ajax/select_materiel.php?idtypekit=".$id_type_kit . (is_object($kit) ? '&idkit='.$kit->id : '') ."',
                    success: function(output) {
                    var output_ = '<tr><td style=\"text-align:right;\"><span class=\"classfortooltip\" title=\"Ce matériel correspondra au libellé du kit\"></span><div class=\"fas fa-angle-up mat-sort-up\" style=\"cursor:pointer; position:absolute;\"></div><div class=\"fas fa-angle-down mat-sort-down\" style=\"cursor:pointer; margin-top:20px;\"></div></td><td style=\"vertical-align:middle; padding-left:20px;\"><span class=\"fas fa-trash delete-select\"></span>' + output + '</td></tr>';
                    $('[name=\"materiel[]\"]').last().closest('tr').after(output_);
                   $( '[name=\"materiel[]\"]' ).find('option').removeAttr('disabled');
                   $( '[name=\"materiel[]\"]' ).each(function( index ) {
                        var selected = $(this).next('.select2-container').find('.select2-selection__rendered').text();
                        if (selected.trim()) {
                			$( '[name=\"materiel[]\"]' ).not($(this)).find('option:contains(\"'+selected+'\")').attr('disabled', 'disabled');
						}
					});
                  }
            });
        });
        $(document).on('click',  '.delete-select',function(e) {
            if ($('[name=\"materiel[]\"]').length > 1) {
            $(this).closest('tr').remove();

            }
        });
        $(document).ready(function(){
            ". (is_object($kit) ? '$(\'[name="materiel[]"]\').find("option").removeAttr("disabled"),$(\'[name="materiel[]"]\').each(function(e){var t=$(this).next(".select2-container").find(".select2-selection__rendered").text();t.trim()||(t=0),console.log(t),$(\'[name="materiel[]"]\').not($(this)).find(\'option:contains("\'+t+\'")\').attr("disabled","disabled"),$.ajax({url:"http://test-dolibarr.tousalamusique.com/custom/kit/ajax/materiel_status.php?id="+$(this).find(\'option:contains("\'+t+\'")\').attr("value"),context:this,beforeSend:function(){$(this).next(".select2-container").nextAll("span").remove(),$(this).next(".select2-container").after(\'<span id="loader" class="lds-dual-ring"></span>\')},success:function(e){var t=e;$(this).next(".select2-container").nextAll("span").remove(),$(this).next(".select2-container").after(t)}})});' : '')."
            $(document).on(\"click\", \".mat-sort-up,.mat-sort-down\", function () {
                var row = $(this).parents(\"tr:first\");
                if ($(this).is(\".mat-sort-up\")) {
                    if (row.prev().attr(\"id\") != $(\"#before_select\").attr(\"id\")){
                        row.insertBefore(row.prev());
                    }
                } else {
                    if (row.next().attr(\"id\") != $(\"#select-delimitor\").attr(\"id\")){
                        row.insertAfter(row.next());
                    }
                }
            });
        });
        $(document).on('change','[name=\"materiel[]\"]',function(){
           $( '[name=\"materiel[]\"]' ).each(function( index ) {
			$(this).find('option').removeAttr('disabled');
		   });
		   console.log('changed');
           $( '[name=\"materiel[]\"]' ).each(function( index ) {
                var selected = $(this).next('.select2-container').find('.select2-selection__rendered').text();
				if (selected.trim()){
                	$( '[name=\"materiel[]\"]' ).not($(this)).find('option:contains(\"'+selected+'\")').attr('disabled', 'disabled');
				}
            });
                var selected = $(this).next('.select2-container').find('.select2-selection__rendered').text();
               $.ajax({ url: '".DOL_URL_ROOT."/custom/kit/ajax/materiel_status.php?id='+ $(this).find('option:contains(\"'+selected+'\")').attr('value'),
                context:this,
            beforeSend: function () { // Before we send the request, remove the .hidden class from the spinner and default to inline-block.
                    $(this).next('.select2-container').nextAll('span').remove();
                    $(this).next('.select2-container').after('<span id=\"loader\" class=\"lds-dual-ring\"></span>');
            },
                    success: function(output) {
                    var output_ = output;
                    $(this).next('.select2-container').nextAll('span').remove();
                    $(this).next('.select2-container').after(output_);
                  }
            });
        });
        </script>";
        print '<style>
            /*Spinner Styles*/
        .lds-dual-ring {
            display: inline-block;
        }
        .lds-dual-ring:after {
            content: " ";
            display: block;
            width: 0.86em;
            height: 0.86em;
            margin: 5% auto;
            border-radius: 50%;
            border: 0.1em solid #fff;
            border-color: black transparent black transparent;
            animation: lds-dual-ring 1.2s linear infinite;
        }
        @keyframes lds-dual-ring {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        tbody tr:nth-child(5) td:first-child span:before {
            font-family: "Font Awesome 5 Free";
   			content: "\f02b";   
			display: inline-block;
			padding-right: 25px;
			vertical-align: 50%;
			color: red;
			font-weight: 900;
        }
        </style>';
    }

}
