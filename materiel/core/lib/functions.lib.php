<?php
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/exploitation.lib.php';

/*
 * Copie de functions.lib.php de Dolibarr pour assurer une compatibilité avec les mises à jour
 * en evitant de modifier le code d'origine
 */

 ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


 /**
  *  Show tab footer of a card.
  *  Note: $object->next_prev_filter can be set to restrict select to find next or previous record by $form->showrefnav.
  *
  *  @param	Object	$object			Object to show
  *  @param	string	$paramid   		Name of parameter to use to name the id into the URL next/previous link
  *  @param	string	$morehtml  		More html content to output just before the nav bar
  *  @param	int		$shownav	  	Show Condition (navigation is shown if value is 1)
  *  @param	string	$fieldid   		Nom du champ en base a utiliser pour select next et previous (we make the select max and min on this field). Use 'none' for no prev/next search.
  *  @param	string	$fieldref   	Nom du champ objet ref (object->ref) a utiliser pour select next et previous
  *  @param	string	$morehtmlref  	More html to show after ref
  *  @param	string	$moreparam  	More param to add in nav link url.
  *	@param	int		$nodbprefix		Do not include DB prefix to forge table name
  *	@param	string	$morehtmlleft	More html code to show before ref
  *	@param	string	$morehtmlstatus	More html code to show under navigation arrows
  *  @param  int     $onlybanner     Put this to 1, if the card will contains only a banner (this add css 'arearefnobottom' on div)
  *	@param	string	$morehtmlright	More html code to show before navigation arrows
  *  @return	void
  */
function talm_banner_tab($object, $paramid, $morehtml = '', $shownav = 1, $fieldid = 'rowid', $fieldref = 'ref', $morehtmlref = '', $moreparam = '', $nodbprefix = 0, $morehtmlleft = '', $morehtmlstatus = '', $onlybanner = 0, $morehtmlright = '')
{
	global $conf, $form, $user, $langs;

	$error = 0;
	$maxvisiblephotos = 1;
	$showimage = 1;
	$entity = (empty($object->entity) ? $conf->entity : $object->entity);
	$modulepart = 'unknown';

  if($object->element == 'materiel')
  {
    $width = 80; $cssclass = 'photoref';
    $showimage = $object->is_photo_available($conf->materiel->multidir_output[1]);
    $maxvisiblephotos = 5;
    if ($conf->browser->layout == 'phone') $maxvisiblephotos = 1;
    if ($showimage) $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref">'.$object->show_photos('materiel', $conf->materiel->multidir_output[1], 'small', $maxvisiblephotos, 0, 0, 0, $width, 0).'</div>';
    else
    {
      $nophoto = '/public/theme/common/nophoto.png';
      $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photo'.$modulepart.($cssclass ? ' '.$cssclass : '').'" alt="No photo"'.($width ? ' style="width: '.$width.'px"' : '').' src="'.DOL_URL_ROOT.$nophoto.'"></div>';
    }
  }
  elseif($object->element == 'kit')
  {
    $width = 80; $cssclass = 'photoref';
    $showimage = $object->is_photo_available($conf->kit->multidir_output[1]);
    $maxvisiblephotos = 5;
    if ($conf->browser->layout == 'phone') $maxvisiblephotos = 1;
    if ($showimage) $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref">'.$object->show_photos('kit', $conf->kit->multidir_output[1], 'small', $maxvisiblephotos, 0, 0, 0, $width, 0).'</div>';
    else
    {
      $nophoto = '/public/theme/common/nophoto.png';
      $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photo'.$modulepart.($cssclass ? ' '.$cssclass : '').'" alt="No photo"'.($width ? ' style="width: '.$width.'px"' : '').' src="'.DOL_URL_ROOT.$nophoto.'"></div>';
    }
  }
  elseif($object->element == 'exploitation')
  {
    $width = 80; $cssclass = 'photoref';
    $showimage = $object->is_photo_available($conf->exploitation->multidir_output[1]);
    $maxvisiblephotos = 5;
    if ($conf->browser->layout == 'phone') $maxvisiblephotos = 1;
    if ($showimage) $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref">'.$object->show_photos('exploitation', $conf->exploitation->multidir_output[1], 'small', $maxvisiblephotos, 0, 0, 0, $width, 0).'</div>';
    else
    {
      $nophoto = '/public/theme/common/nophoto.png';
      $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photo'.$modulepart.($cssclass ? ' '.$cssclass : '').'" alt="No photo"'.($width ? ' style="width: '.$width.'px"' : '').' src="'.DOL_URL_ROOT.$nophoto.'"></div>';
    }
  }
  elseif($object->element == 'entretien')
  {
    $width = 80; $cssclass = 'photoref';
    $showimage = $object->is_photo_available($conf->entretien->multidir_output[1]);
    $maxvisiblephotos = 5;
    if ($conf->browser->layout == 'phone') $maxvisiblephotos = 1;
    if ($showimage) $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref">'.$object->show_photos('entretien', $conf->entretien->multidir_output[1], 'small', $maxvisiblephotos, 0, 0, 0, $width, 0).'</div>';
    else
    {
      $nophoto = '/public/theme/common/nophoto.png';
      $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photo'.$modulepart.($cssclass ? ' '.$cssclass : '').'" alt="No photo"'.($width ? ' style="width: '.$width.'px"' : '').' src="'.DOL_URL_ROOT.$nophoto.'"></div>';
    }
  }
  elseif($object->element == 'source')
  {
    $width = 80; $cssclass = 'photoref';
    $showimage = $object->is_photo_available($conf->source->multidir_output[1]);
    $maxvisiblephotos = 5;
    if ($conf->browser->layout == 'phone') $maxvisiblephotos = 1;
    if ($showimage) $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref">'.$object->show_photos('source', $conf->source->multidir_output[1], 'small', $maxvisiblephotos, 0, 0, 0, $width, 0).'</div>';
    else
    {
      $nophoto = '/public/theme/common/nophoto.png';
      $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photo'.$modulepart.($cssclass ? ' '.$cssclass : '').'" alt="No photo"'.($width ? ' style="width: '.$width.'px"' : '').' src="'.DOL_URL_ROOT.$nophoto.'"></div>';
    }
  }
  elseif($object->element == 'recufiscal')
  {
    $width = 80; $cssclass = 'photoref';
    $showimage = $object->is_photo_available($conf->recufiscal->multidir_output[1]);
    $maxvisiblephotos = 5;
    if ($conf->browser->layout == 'phone') $maxvisiblephotos = 1;
    if ($showimage) $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref">'.$object->show_photos('recufiscal', $conf->recufiscal->multidir_output[1], 'small', $maxvisiblephotos, 0, 0, 0, $width, 0).'</div>';
    else
    {
      $nophoto = '/public/theme/common/nophoto.png';
      $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photo'.$modulepart.($cssclass ? ' '.$cssclass : '').'" alt="No photo"'.($width ? ' style="width: '.$width.'px"' : '').' src="'.DOL_URL_ROOT.$nophoto.'"></div>';
    }
  }
  elseif($object->element == 'donateur')
  {
    $width = 80; $cssclass = 'photoref';
    $showimage = $object->is_photo_available($conf->donateur->multidir_output[1]);
    $maxvisiblephotos = 5;
    if ($conf->browser->layout == 'phone') $maxvisiblephotos = 1;
    if ($showimage) $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref">'.$object->show_photos('donateur', $conf->donateur->multidir_output[1], 'small', $maxvisiblephotos, 0, 0, 0, $width, 0).'</div>';
    else
    {
      $nophoto = '/public/theme/common/nophoto.png';
      $morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photo'.$modulepart.($cssclass ? ' '.$cssclass : '').'" alt="No photo"'.($width ? ' style="width: '.$width.'px"' : '').' src="'.DOL_URL_ROOT.$nophoto.'"></div>';
    }
  }

  if ($object->element == 'materiel')
	{
		global $langs, $conf, $db;

		if (!empty($conf->use_javascript_ajax)) {

			$entretien_id = isMaterielInEntretien($object->id);
            if ($entretien_id) {
                $entretien = new Entretien($db);
                $entretien->fetch($entretien_id);
				$morehtmlstatus .= '<span class="classfortooltip badge badge-status3 badge-status">En entretien : '.$entretien->getNomUrl().'</span>';
            }

			$morehtmlstatus .= ' &nbsp; ';
			$morehtmlstatus .= '<span class="classfortooltip badge badge-status'.$object->etat_badge_code.' badge-status" title="État du matériel">'.$object->etat.'</span>';
		$morehtmlstatus .= ' &nbsp; ';
			$morehtmlstatus .= '<span class="classfortooltip badge badge-status'.$object->exploitabilite_badge_code.' badge-status" title="Exploitabilité du matériel">'.($object->exploitabilite == "OK" ? "Exploitable" : $object->exploitabilite).'</span>';
		} else {
			$morehtmlstatus .= '<span>'.$object->etat.'</span>';
		}
		$morehtmlstatus .= ' &nbsp; ';

		if (!empty($conf->use_javascript_ajax)) {
		    if ($object->fk_kit) $morehtmlstatus .= '<span class="badge  badge-status4 badge-status">En kit</span>';
		    else $morehtmlstatus .= '<span class="badge  badge-status5 badge-status">Hors kit</span>';
		} else {
			$morehtmlstatus .= '<span>Disponible</span>';
		}

	}
	elseif ($object->element == 'kit')
	{
			if ($object->active){
				$morehtmlstatus .= '<span class="badge  badge-status'.$object->c_disponibilite[$object->fk_disponibilite]['badge_code'].' badge-status">'.$object->c_disponibilite[$object->fk_disponibilite]['disponibilite'].'</span>';
			}
	}
	elseif ($object->element == 'exploitation')
	{
        	$morehtmlstatus .= exploitation_banner_menu($object);
			$morehtmlstatus .= ' &nbsp;  &nbsp; ';
			$morehtmlstatus .= '<span class="badge badge-status'.$object->c_etat[$object->etat]['badge_code'].' classfortooltip badge-status" style="background-color:'.$object->c_etat[$object->etat]['color'].';" title="'.$object->c_etat[$object->etat]['etat'].'">'.$object->c_etat[$object->etat]['etat'].'</span>';

    }
	elseif ($object->element == 'entretien')
	{
			$morehtmlstatus .= '<span class="badge badge-status'.$object->etat_array[$object->fk_etat]['badge_code'].' classfortooltip badge-status" title="'.$object->etat_array[$object->fk_etat]['label'].'">'.$object->etat_array[$object->fk_etat]['label'].'</span>';
    }
	elseif ($object->element == 'source')
	{
			$morehtmlstatus .= $object->LibStatus($object->fk_status, 6);

			if ($object->fk_status != $object::STATUS_INVENTORIED)
				$morehtmlstatus .= '</div><div class="statusref statusrefbis"><span class="opacitymedium">Pas encore inventorié</span>';
    }
	elseif ($object->element == 'recufiscal')
	{
		$morehtmlstatus .= $object->LibStatus($object->fk_statut, 6);
    }
	elseif ($object->element == 'donateur')
	{
			$moreaddress = $object->getBannerAddress('refaddress', $object);
			if ($moreaddress) {
				$morehtmlref .= '<div class="refidno">';
				$morehtmlref .= $moreaddress;
				$morehtmlref .= '</div>';
			}
    }

  	// Add label
  	if (in_array($object->element, array('materiel', 'kit', 'exploitation')))
  	{
  		if (!empty($object->label)) $morehtmlref .= '<div class="refidno">'.$object->label.'</div>';


		if($object->element == 'materiel')
		{
			$marque = "SELECT marque FROM ".MAIN_DB_PREFIX."c_marque WHERE rowid =".$object->fk_marque;
			$resqlMarque = $db->query($marque);
			$objectMarque = $db->fetch_object($resqlMarque);
			$morehtmlref .= '<div class="refidno">'.$objectMarque->marque.' '.$object->modele.'</div>';
		}
		
  	}

  	print '<div class="'.($onlybanner ? 'arearefnobottom ' : 'arearef ').'heightref valignmiddle centpercent">';
  	print $form->showrefnav($object, $paramid, $morehtml, $shownav, $fieldid, $fieldref, $morehtmlref, $moreparam, $nodbprefix, $morehtmlleft, $morehtmlstatus, $morehtmlright);
  	print '</div>';
  	print '<div class="underrefbanner clearboth"></div>';
}


/**
 *	Show picto whatever it's its name (generic function)
 *
 *	@param      string		$titlealt         		Text on title tag for tooltip. Not used if param notitle is set to 1.
 *	@param      string		$picto       			Name of image file to show ('filenew', ...)
 *													If no extension provided, we use '.png'. Image must be stored into theme/xxx/img directory.
 *                                  				Example: picto.png                  if picto.png is stored into htdocs/theme/mytheme/img
 *                                  				Example: picto.png@mymodule         if picto.png is stored into htdocs/mymodule/img
 *                                  				Example: /mydir/mysubdir/picto.png  if picto.png is stored into htdocs/mydir/mysubdir (pictoisfullpath must be set to 1)
 *	@param		string		$moreatt				Add more attribute on img tag (For example 'style="float: right"')
 *	@param		boolean|int	$pictoisfullpath		If true or 1, image path is a full path
 *	@param		int			$srconly				Return only content of the src attribute of img.
 *  @param		int			$notitle				1=Disable tag title. Use it if you add js tooltip, to avoid duplicate tooltip.
 *  @param		string		$alt					Force alt for bind people
 *  @param		string		$morecss				Add more class css on img tag (For example 'myclascss').
 *  @param		string		$marginleftonlyshort	1 = Add a short left margin on picto, 2 = Add a larger left margin on picto, 0 = No margin left. Works for fontawesome picto only.
 *  @return     string       				    	Return img tag
 *  @see        img_object(), img_picto_common()
 */
function talm_img_picto($titlealt, $picto, $moreatt = '', $pictoisfullpath = false, $srconly = 0, $notitle = 0, $alt = '', $morecss = '', $marginleftonlyshort = 2)
{
	global $conf, $langs;

	// We forge fullpathpicto for image to $path/img/$picto. By default, we take DOL_URL_ROOT/theme/$conf->theme/img/$picto
	$url = DOL_URL_ROOT;
	$theme = $conf->theme;
	$path = 'theme/'.$theme;

	// Define fullpathpicto to use into src
	if ($pictoisfullpath) {
		// Clean parameters
		if (!preg_match('/(\.png|\.gif|\.svg)$/i', $picto)) {
			$picto .= '.png';
		}
		$fullpathpicto = $picto;
		$reg = array();
		if (preg_match('/class="([^"]+)"/', $moreatt, $reg)) {
		    $morecss .= ($morecss ? ' ' : '').$reg[1];
		    $moreatt = str_replace('class="'.$reg[1].'"', '', $moreatt);
		}
	} else {
		$pictowithouttext = preg_replace('/(\.png|\.gif|\.svg)$/', '', $picto);
        if (empty($srconly) && in_array($pictowithouttext, array('materiel', 'object_materiel', 'title_materiel', 'kit', 'object_kit', 
																 'exploitation', 'object_exploitation', 'entretien', 'object_entretien', 
																 'source', 'object_source', 'recufiscal', 'object_recufiscal', 'supplier_invoice', 
																 'object_supplier_invoice', 'donateur', 'object_donateur', 'salle', 'object_salle')
		)) {
			$fakey = $pictowithouttext;
			$facolor = ''; $fasize = '';
			$fa = 'fas';
			$pictowithouttext = str_replace('object_', '', $pictowithouttext);

		    $arrayconvpictotofa = array(
		    	'materiel'=>'guitar', 'kit'=>'boxes', 'exploitation'=>'truck-loading', 'entretien'=>'tools', 'source'=>'file-contract', 
				'recufiscal'=>'hand-holding-usd', 'supplier_invoice'=>'file-invoice-dollar', 'donateur'=>'user-tie', 'salle'=>'chalkboard'
		    );
			if (!empty($arrayconvpictotofa[$pictowithouttext]))
			{
				$fakey = 'fa-'.$arrayconvpictotofa[$pictowithouttext];
			}
			else {
				$fakey = 'fa-'.$pictowithouttext;
			}

			// Define $marginleftonlyshort
			$arrayconvpictotomarginleftonly = array(
				'bank', 'check', 'delete', 'generic', 'grip', 'grip_title', 'jabber',
				'grip_title', 'grip', 'listlight', 'note', 'on', 'off', 'playdisabled', 'printer', 'resize', 'sign-out', 'stats', 'switch_on', 'switch_off',
				'uparrow', '1uparrow', '1downarrow', '1leftarrow', '1rightarrow', '1uparrow_selected', '1downarrow_selected', '1leftarrow_selected', '1rightarrow_selected'
			);
			if (!isset($arrayconvpictotomarginleftonly[$pictowithouttext])) {
				$marginleftonlyshort = 0;
			}

			// Add CSS
			$arrayconvpictotomorcess = array(
				'action'=>'infobox-action', 'account'=>'infobox-bank_account', 'accountancy'=>'infobox-bank_account',
				'bank_account'=>'bg-infobox-bank_account');
			if (!empty($arrayconvpictotomorcess[$pictowithouttext])) {
				$morecss .= ($morecss ? ' ' : '').$arrayconvpictotomorcess[$pictowithouttext];
			}

			// Define $color
			$arrayconvpictotocolor = array(
				'kit'=>'#a69944','exploitation'=>'#a69944', 'materiel'=>'#a69944', 'entretien'=>'#a69944', 'source'=>'#a69944', 
				'recufiscal'=>'#a69944', 'supplier_invoice'=>'#a69944', 'donateur'=>'#a69944', 'salle'=>'#a69944'
			);
			if (isset($arrayconvpictotocolor[$pictowithouttext])) {
				$facolor = $arrayconvpictotocolor[$pictowithouttext];
			}

			// This snippet only needed since function img_edit accepts only one additional parameter: no separate one for css only.
            // class/style need to be extracted to avoid duplicate class/style validation errors when $moreatt is added to the end of the attributes.
            $reg = array();
			if (preg_match('/class="([^"]+)"/', $moreatt, $reg)) {
                $morecss .= ($morecss ? ' ' : '').$reg[1];
                $moreatt = str_replace('class="'.$reg[1].'"', '', $moreatt);
            }
            if (preg_match('/style="([^"]+)"/', $moreatt, $reg)) {
                $morestyle = ' '.$reg[1];
                $moreatt = str_replace('style="'.$reg[1].'"', '', $moreatt);
            }
            $moreatt = trim($moreatt);

            $enabledisablehtml = '<span class="'.$fa.' '.$fakey.($marginleftonlyshort ? ($marginleftonlyshort == 1 ? ' marginleftonlyshort' : ' marginleftonly') : '');
            $enabledisablehtml .= ($morecss ? ' '.$morecss : '').'" style="'.($fasize ? ('font-size: '.$fasize.';') : '').($facolor ? (' color: '.$facolor.';') : '').($morestyle ? ' '.$morestyle : '').'"'.(($notitle || empty($titlealt)) ? '' : ' title="'.dol_escape_htmltag($titlealt).'"').($moreatt ? ' '.$moreatt : '').'>';
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$enabledisablehtml .= $titlealt;
			}
			$enabledisablehtml .= '</span>';

			return $enabledisablehtml;
		}

		if (!empty($conf->global->MAIN_OVERWRITE_THEME_PATH)) {
			$path = $conf->global->MAIN_OVERWRITE_THEME_PATH.'/theme/'.$theme; // If the theme does not have the same name as the module
		}
		elseif (!empty($conf->global->MAIN_OVERWRITE_THEME_RES)) {
			$path = $conf->global->MAIN_OVERWRITE_THEME_RES.'/theme/'.$conf->global->MAIN_OVERWRITE_THEME_RES; // To allow an external module to overwrite image resources whatever is activated theme
		}
		elseif (!empty($conf->modules_parts['theme']) && array_key_exists($theme, $conf->modules_parts['theme'])) {
			$path = $theme.'/theme/'.$theme; // If the theme have the same name as the module
		}

		// If we ask an image into $url/$mymodule/img (instead of default path)
		$regs = array();
		if (preg_match('/^([^@]+)@([^@]+)$/i', $picto, $regs)) {
			$picto = $regs[1];
			$path = $regs[2]; // $path is $mymodule
		}

		// Clean parameters
		if (!preg_match('/(\.png|\.gif|\.svg)$/i', $picto)) {
			$picto .= '.png';
		}
		// If alt path are defined, define url where img file is, according to physical path
		// ex: array(["main"]=>"/home/maindir/htdocs", ["alt0"]=>"/home/moddir0/htdocs", ...)
		foreach ($conf->file->dol_document_root as $type => $dirroot) {
			if ($type == 'main') {
				continue;
			}
			// This need a lot of time, that's why enabling alternative dir like "custom" dir is not recommanded
			if (file_exists($dirroot.'/'.$path.'/img/'.$picto)) {
				$url = DOL_URL_ROOT.$conf->file->dol_url_root[$type];
				break;
			}
		}

		// $url is '' or '/custom', $path is current theme or
		$fullpathpicto = $url.'/'.$path.'/img/'.$picto;
	}

	if ($srconly) {
		return $fullpathpicto;
	}
		// tag title is used for tooltip on <a>, tag alt can be used with very simple text on image for blind people
    return '<img src="'.$fullpathpicto.'" alt="'.dol_escape_htmltag($alt).'"'.(($notitle || empty($titlealt)) ? '' : ' title="'.dol_escape_htmltag($titlealt).'"').($moreatt ? ' '.$moreatt.($morecss ? ' class="'.$morecss.'"' : '') : ' class="inline-block'.($morecss ? ' '.$morecss : '').'"').'>'; // Alt is used for accessibility, title for popup
}


function talm_img_object($titlealt, $picto, $moreatt = '', $pictoisfullpath = false, $srconly = 0, $notitle = 0)
{
 if (strpos($picto, '^') === 0) return talm_img_picto($titlealt, str_replace('^', '', $picto), $moreatt, $pictoisfullpath, $srconly, $notitle);
 else return talm_img_picto($titlealt, 'object_'.$picto, $moreatt, $pictoisfullpath, $srconly, $notitle);
}




/**
 *	Show tab header of a card
 *
 *	@param	array	$links				Array of tabs. Currently initialized by calling a function xxx_admin_prepare_head
 *	@param	string	$active     		Active tab name (document', 'info', 'ldap', ....)
 *	@param  string	$title      		Title
 *	@param  int		$notab				-1 or 0=Add tab header, 1=no tab header (if you set this to 1, using dol_fiche_end() to close tab is not required), -2=Add tab header with no seaparation under tab (to start a tab just after)
 * 	@param	string	$picto				Add a picto on tab title
 *	@param	int		$pictoisfullpath	If 1, image path is a full path. If you set this to 1, you can use url returned by dol_buildpath('/mymodyle/img/myimg.png',1) for $picto.
 *  @param	string	$morehtmlright		Add more html content on right of tabs title
 *  @param	string	$morecss			More Css
 *  @param	int		$limittoshow		Limit number of tabs to show. Use 0 to use automatic default value.
 * 	@return	void
 */
function talm_fiche_head($links = array(), $active = '0', $title = '', $notab = 0, $picto = '', $pictoisfullpath = 0, $morehtmlright = '', $morecss = '', $limittoshow = 0)
{
	print talm_get_fiche_head($links, $active, $title, $notab, $picto, $pictoisfullpath, $morehtmlright, $morecss, $limittoshow);
}


/**
 *  Show tabs of a record
 *
 *	@param	array	$links				Array of tabs
 *	@param	string	$active     		Active tab name
 *	@param  string	$title      		Title
 *	@param  int		$notab				-1 or 0=Add tab header, 1=no tab header (if you set this to 1, using dol_fiche_end() to close tab is not required), -2=Add tab header with no seaparation under tab (to start a tab just after)
 * 	@param	string	$picto				Add a picto on tab title
 *	@param	int		$pictoisfullpath	If 1, image path is a full path. If you set this to 1, you can use url returned by dol_buildpath('/mymodyle/img/myimg.png',1) for $picto.
 *  @param	string	$morehtmlright		Add more html content on right of tabs title
 *  @param	string	$morecss			More Css
 *  @param	int		$limittoshow		Limit number of tabs to show. Use 0 to use automatic default value.
 * 	@return	string
 */
function talm_get_fiche_head($links = array(), $active = '', $title = '', $notab = 0, $picto = '', $pictoisfullpath = 0, $morehtmlright = '', $morecss = '', $limittoshow = 0)
{
	global $conf, $langs, $hookmanager;

	// Show title
	$showtitle = 1;
	if (!empty($conf->dol_optimize_smallscreen)) $showtitle = 0;

	$out = "\n".'<!-- dol_get_fiche_head -->';

	if ((!empty($title) && $showtitle) || $morehtmlright || !empty($links)) {
		$out .= '<div class="tabs'.($picto ? '' : ' nopaddingleft').'" data-role="controlgroup" data-type="horizontal">'."\n";
	}

	// Show right part
	if ($morehtmlright) $out .= '<div class="inline-block floatright tabsElem">'.$morehtmlright.'</div>'; // Output right area first so when space is missing, text is in front of tabs and not under.

	// Show title
	if (!empty($title) && $showtitle)
	{
		$limittitle = 30;
		$out .= '<a class="tabTitle">';
		if ($picto && empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) $out .= talm_img_picto($title, ($pictoisfullpath ? '' : 'object_').$picto, '', $pictoisfullpath, 0, 0, '', 'imgTabTitle').' ';
		$out .= '<span class="tabTitleText">'.dol_trunc($title, $limittitle).'</span>';
		$out .= '</a>';
	}

	// Show tabs

	// Define max of key (max may be higher than sizeof because of hole due to module disabling some tabs).
	$maxkey = -1;
	if (is_array($links) && !empty($links))
	{
		$keys = array_keys($links);
		if (count($keys)) $maxkey = max($keys);
	}

	// Show tabs
	// if =0 we don't use the feature
	if (empty($limittoshow)) {
		$limittoshow = (empty($conf->global->MAIN_MAXTABS_IN_CARD) ? 99 : $conf->global->MAIN_MAXTABS_IN_CARD);
	}
	if (!empty($conf->dol_optimize_smallscreen)) $limittoshow = 2;

	$displaytab = 0;
	$nbintab = 0;
	$popuptab = 0;
	$outmore = '';
	for ($i = 0; $i <= $maxkey; $i++)
	{
		if ((is_numeric($active) && $i == $active) || (!empty($links[$i][2]) && !is_numeric($active) && $active == $links[$i][2])) {
			// If active tab is already present
			if ($i >= $limittoshow) $limittoshow--;
		}
	}

	for ($i = 0; $i <= $maxkey; $i++)
	{
		if ((is_numeric($active) && $i == $active) || (!empty($links[$i][2]) && !is_numeric($active) && $active == $links[$i][2])) {
			$isactive = true;
		}
		else {
			$isactive = false;
		}

		if ($i < $limittoshow || $isactive)
		{
			$out .= '<div class="inline-block tabsElem'.($isactive ? ' tabsElemActive' : '').((!$isactive && !empty($conf->global->MAIN_HIDE_INACTIVETAB_ON_PRINT)) ? ' hideonprint' : '').'"><!-- id tab = '.(empty($links[$i][2]) ? '' : $links[$i][2]).' -->';
			if (isset($links[$i][2]) && $links[$i][2] == 'image')
			{
				if (!empty($links[$i][0]))
				{
					$out .= '<a class="tabimage'.($morecss ? ' '.$morecss : '').'" href="'.$links[$i][0].'">'.$links[$i][1].'</a>'."\n";
				}
				else
				{
					$out .= '<span class="tabspan">'.$links[$i][1].'</span>'."\n";
				}
			}
			elseif (!empty($links[$i][1]))
			{
				//print "x $i $active ".$links[$i][2]." z";
				if ($isactive)
				{
					$out .= '<a'.(!empty($links[$i][2]) ? ' id="'.$links[$i][2].'"' : '').' class="tabactive tab inline-block'.($morecss ? ' '.$morecss : '').'" href="'.$links[$i][0].'">';
					$out .= $links[$i][1];
					$out .= '</a>'."\n";
				}
				else
				{
					$out .= '<a'.(!empty($links[$i][2]) ? ' id="'.$links[$i][2].'"' : '').' class="tabunactive tab inline-block'.($morecss ? ' '.$morecss : '').'" href="'.$links[$i][0].'">';
					$out .= $links[$i][1];
					$out .= '</a>'."\n";
				}
			}
			$out .= '</div>';
		}
		else
		{
			// The popup with the other tabs
			if (!$popuptab)
			{
				$popuptab = 1;
				$outmore .= '<div class="popuptabset wordwrap">'; // The css used to hide/show popup
			}
			$outmore .= '<div class="popuptab wordwrap" style="display:inherit;">';
			if (isset($links[$i][2]) && $links[$i][2] == 'image')
			{
				if (!empty($links[$i][0]))
					$outmore .= '<a class="tabimage'.($morecss ? ' '.$morecss : '').'" href="'.$links[$i][0].'">'.$links[$i][1].'</a>'."\n";
				else
					$outmore .= '<span class="tabspan">'.$links[$i][1].'</span>'."\n";
			}
			elseif (!empty($links[$i][1]))
			{
				$outmore .= '<a'.(!empty($links[$i][2]) ? ' id="'.$links[$i][2].'"' : '').' class="wordwrap inline-block'.($morecss ? ' '.$morecss : '').'" href="'.$links[$i][0].'">';
				$outmore .= preg_replace('/([a-z])\/([a-z])/i', '\\1 / \\2', $links[$i][1]); // Replace x/y with x / y to allow wrap on long composed texts.
				$outmore .= '</a>'."\n";
			}
			$outmore .= '</div>';

			$nbintab++;
		}
		$displaytab = $i;
	}
	if ($popuptab) $outmore .= '</div>';

	if ($popuptab)	// If there is some tabs not shown
	{
		$left = ($langs->trans("DIRECTION") == 'rtl' ? 'right' : 'left');
		$right = ($langs->trans("DIRECTION") == 'rtl' ? 'left' : 'right');

		$tabsname = str_replace("@", "", $picto);
		$out .= '<div id="moretabs'.$tabsname.'" class="inline-block tabsElem">';
		$out .= '<a href="#" class="tab moretab inline-block tabunactive reposition">'.$langs->trans("More").'... ('.$nbintab.')</a>';
		$out .= '<div id="moretabsList'.$tabsname.'" style="position: absolute; '.$left.': -999em; text-align: '.$left.'; margin:0px; padding:2px; z-index:10;">';
		$out .= $outmore;
		$out .= '</div>';
		$out .= '<div></div>';
		$out .= "</div>\n";

		$out .= "<script>";
		$out .= "$('#moretabs".$tabsname."').mouseenter( function() { console.log('mouseenter ".$left."'); $('#moretabsList".$tabsname."').css('".$left."','auto');});";
		$out .= "$('#moretabs".$tabsname."').mouseleave( function() { console.log('mouseleave ".$left."'); $('#moretabsList".$tabsname."').css('".$left."','-999em');});";
		$out .= "</script>";
	}

	if ((!empty($title) && $showtitle) || $morehtmlright || !empty($links)) {
		$out .= "</div>\n";
	}

	if (!$notab || $notab == -1 || $notab == -2) $out .= "\n".'<div class="tabBar'.($notab == -1 ? '' : ($notab == -2 ? ' tabBarNoTop' : ' tabBarWithBottom')).'">'."\n";

	$parameters = array('tabname' => $active, 'out' => $out);
	$reshook = $hookmanager->executeHooks('printTabsHead', $parameters); // This hook usage is called just before output the head of tabs. Take also a look at "completeTabsHead"
	if ($reshook > 0)
	{
		$out = $hookmanager->resPrint;
	}

	return $out;
}





/**
 *	Print a title with navigation controls for pagination
 *
 *	@param	string	    $titre				Title to show (required)
 *	@param	int   	    $page				Numero of page to show in navigation links (required)
 *	@param	string	    $file				Url of page (required)
 *	@param	string	    $options         	More parameters for links ('' by default, does not include sortfield neither sortorder). Value must be 'urlencoded' before calling function.
 *	@param	string    	$sortfield       	Field to sort on ('' by default)
 *	@param	string	    $sortorder       	Order to sort ('' by default)
 *	@param	string	    $morehtmlcenter     String in the middle ('' by default). We often find here string $massaction comming from $form->selectMassAction()
 *	@param	int		    $num				Number of records found by select with limit+1
 *	@param	int|string  $totalnboflines		Total number of records/lines for all pages (if known). Use a negative value of number to not show number. Use '' if unknown.
 *	@param	string	    $picto				Icon to use before title (should be a 32x32 transparent png file)
 *	@param	int		    $pictoisfullpath	1=Icon name is a full absolute url of image
 *  @param	string	    $morehtmlright		More html to show
 *  @param  string      $morecss            More css to the table
 *  @param  int         $limit              Max number of lines (-1 = use default, 0 = no limit, > 0 = limit).
 *  @param  int         $hideselectlimit    Force to hide select limit
 *  @param  int         $hidenavigation     Force to hide all navigation tools
 *  @param  int			$pagenavastextinput 1=Do not suggest list of pages to navigate but suggest the page number into an input field.
 *	@return	void
 */
function talm_print_barre_liste($titre, $page, $file, $options = '', $sortfield = '', $sortorder = '', $morehtmlcenter = '', $num = -1, $totalnboflines = '', $picto = 'generic', $pictoisfullpath = 0, $morehtmlright = '', $morecss = '', $limit = -1, $hideselectlimit = 0, $hidenavigation = 0, $pagenavastextinput = 0)
{
	global $conf, $langs;

	$savlimit = $limit;
	$savtotalnboflines = $totalnboflines;
	$totalnboflines = abs($totalnboflines);

	if ($picto == 'setup') $picto='title_setup.png';
    if (($conf->browser->name == 'ie') && $picto=='title_generic.png') $picto='title.gif';
	if ($limit < 0) $limit = $conf->liste_limit;
	if ($savlimit != 0 && (($num > $limit) || ($num == -1) || ($limit == 0)))
	{
	$nextpage = 1;
	}
	else
	{
	$nextpage = 0;
	}
	//print 'totalnboflines='.$totalnboflines.'-savlimit='.$savlimit.'-limit='.$limit.'-num='.$num.'-nextpage='.$nextpage;

	print "\n";
	print "<!-- Begin title '".$titre."' -->\n";
	print '<table class="centpercent notopnoleftnoright table-fiche-title'.($morecss ? ' '.$morecss : '').'"><tr>'; // maring bottom must be same than into load_fiche_tire

	// Left

	if ($picto && $titre) print '<td class="nobordernopadding widthpictotitle valignmiddle col-picto">'.talm_img_picto('', $picto, 'class="valignmiddle pictotitle widthpictotitle"', $pictoisfullpath).'</td>';
	print '<td class="nobordernopadding valignmiddle col-title">';
	print '<div class="titre inline-block">'.$titre;
	if (!empty($titre) && $savtotalnboflines >= 0 && (string) $savtotalnboflines != '') print '<span class="opacitymedium colorblack paddingleft">('.$totalnboflines.')</span>';
	print '</div></td>';

	// Center
	if ($morehtmlcenter)
	{
		print '<td class="nobordernopadding center valignmiddle">'.$morehtmlcenter.'</td>';
	}

	// Right
	print '<td class="nobordernopadding valignmiddle right">';
	
	if ($sortfield) $options .= "&sortfield=".urlencode($sortfield);
	if ($sortorder) $options .= "&sortorder=".urlencode($sortorder);
	// Show navigation bar
	$pagelist = '';

	if ($savlimit != 0 && ($page > 0 || $num > $limit))
	{
		
		if ($totalnboflines)    // If we know total nb of lines
		{
			// Define nb of extra page links before and after selected page + ... + first or last
			$maxnbofpage=(empty($conf->dol_optimize_smallscreen) ? 4 : 1);

			if ($limit > 0) $nbpages=ceil($totalnboflines/$limit);
			else $nbpages=1;
			$cpt=($page-$maxnbofpage);
			if ($cpt < 0) { $cpt=0; }
	
			if ($cpt>=1)
			{
				$pagelist.= '<li'.(($conf->dol_use_jmobile != 4)?' class="pagination"':'').'><a href="'.$file.'?page=0'.$options.'">1</a></li>';
				if ($cpt > 2) $pagelist.='<li'.(($conf->dol_use_jmobile != 4)?' class="pagination"':'').'><span '.(($conf->dol_use_jmobile != 4)?'class="inactive"':'').'>...</span></li>';
				elseif ($cpt == 2) $pagelist.='<li'.(($conf->dol_use_jmobile != 4)?' class="pagination"':'').'><a href="'.$file.'?page=1'.$options.'">2</a></li>';
			}
			do
			{
				if ($cpt==$page)
				{
					$pagelist.= '<li'.(($conf->dol_use_jmobile != 4)?' class="pagination"':'').'><span '.(($conf->dol_use_jmobile != 4)?'class="active"':'').'>'.($page+1).'</span></li>';
				}
				else
				{
					$pagelist.= '<li'.(($conf->dol_use_jmobile != 4)?' class="pagination"':'').'><a href="'.$file.'?page='.$cpt.$options.'">'.($cpt+1).'</a></li>';
				}
				$cpt++;
			}
			while ($cpt < $nbpages && $cpt<=$page+$maxnbofpage);

			if ($cpt<$nbpages)
			{
				if ($cpt<$nbpages-2) $pagelist.= '<li'.(($conf->dol_use_jmobile != 4)?' class="pagination"':'').'><span '.(($conf->dol_use_jmobile != 4)?'class="inactive"':'').'>...</span></li>';
				elseif ($cpt == $nbpages-2) $pagelist.= '<li'.(($conf->dol_use_jmobile != 4)?' class="pagination"':'').'><a href="'.$file.'?page='.($nbpages-2).$options.'">'.($nbpages - 1).'</a></li>';
				$pagelist.= '<li'.(($conf->dol_use_jmobile != 4)?' class="pagination"':'').'><a href="'.$file.'?page='.($nbpages-1).$options.'">'.$nbpages.'</a></li>';
			}
		}
		else
		{
			$pagelist.= '<li'.(($conf->dol_use_jmobile != 4)?' class="pagination"':'').'><span '.(($conf->dol_use_jmobile != 4)?'class="active"':'').'>'.($page+1)."</li>";
		}
	}


	if ($savlimit || $morehtmlright) {
		print_fleche_navigation($page, $file, $options, $nextpage, $pagelist, $morehtmlright, $savlimit, $totalnboflines, $hideselectlimit); // output the div and ul for previous/last completed with page numbers into $pagelist
	}

	// js to autoselect page field on focus
	if ($pagenavastextinput) {
		print ajax_autoselect('.pageplusone');
	}

	print '</td>';

	print '</tr></table>'."\n";
	print "<!-- End title -->\n\n";
}

/**
 *	Load a title with picto
 *
 *	@param	string	$titre				Title to show
 *	@param	string	$morehtmlright		Added message to show on right
 *	@param	string	$picto				Icon to use before title (should be a 32x32 transparent png file)
 *	@param	int		$pictoisfullpath	1=Icon name is a full absolute url of image
 * 	@param	string	$id					To force an id on html objects
 *  @param  string  $morecssontable     More css on table
 *	@param	string	$morehtmlcenter		Added message to show on center
 * 	@return	string
 *  @see print_barre_liste()
 */
function talm_load_fiche_titre($titre, $morehtmlright = '', $picto = 'generic', $pictoisfullpath = 0, $id = '', $morecssontable = '', $morehtmlcenter = '')
{
	global $conf;

	$return = '';

	if ($picto == 'setup') $picto = 'generic';

	$return .= "\n";
	$return .= '<table '.($id ? 'id="'.$id.'" ' : '').'class="centpercent notopnoleftnoright table-fiche-title'.($morecssontable ? ' '.$morecssontable : '').'">'; // maring bottom must be same than into print_barre_list
	$return .= '<tr class="titre">';
	if ($picto) $return .= '<td class="nobordernopadding widthpictotitle valignmiddle col-picto">'.talm_img_picto('', $picto, 'class="valignmiddle widthpictotitle pictotitle"', $pictoisfullpath).'</td>';
	$return .= '<td class="nobordernopadding valignmiddle col-title">';
	$return .= '<div class="titre inline-block">'.$titre.'</div>';
	$return .= '</td>';
	if (dol_strlen($morehtmlcenter))
	{
		$return .= '<td class="nobordernopadding center valignmiddle">'.$morehtmlcenter.'</td>';
	}
	if (dol_strlen($morehtmlright))
	{
		$return .= '<td class="nobordernopadding titre_right wordbreakimp right valignmiddle">'.$morehtmlright.'</td>';
	}
	$return .= '</tr></table>'."\n";

	return $return;
}


// fonction permettant de rechercher  une valeur dans un array multidimensionnel
function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}



function getClasseDict()
{
	$array_classe = array();

	global $langs, $conf, $db;

	$sql = "SELECT rowid, classe";
	$sql .= " FROM ".MAIN_DB_PREFIX."c_classe_materiel";

	$resql = $db->query($sql);

	$num = $db->num_rows($resql);
	$i = 0;
	while ($i < $num)
	{
		$obj = $db->fetch_object($resql);
		$array_classe[$obj->rowid] = $obj->classe;
		$i++;
	}
	return $array_classe;
}

?>
