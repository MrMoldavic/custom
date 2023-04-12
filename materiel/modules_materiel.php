<?php
/* Copyright (C) 2003-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2008 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2005      Regis Houssin        <regis.houssin@inodbox.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 * or see https://www.gnu.org/
 */

/**
 *	    \file       htdocs/core/modules/dons/modules_don.php
 *		\ingroup    donations
 *		\brief      File of class to manage donation document generation
 */
require_once DOL_DOCUMENT_ROOT.'/core/class/commondocgenerator.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/materiel.class.php';




/**
 *	Parent class of subscription templates
 */
abstract class ModeleMateriel extends CommonDocGenerator
{
	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return list of active generation modules
	 *
	 *  @param	DoliDB  $db     			Database handler
	 *  @param  integer $maxfilenamelength  Max length of value to show
	 *  @return	array						List of templates
	 */
	public static function liste_modeles($db, $maxfilenamelength = 0)
	{
		// phpcs:enable
		global $conf;

		$type = 'recu_fiscal';
		$list = array();

		include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
		$list = getListOfModels($db, $type, $maxfilenamelength);

		return $list;
	}
}


/**
 *	Parent class of donation numbering templates
 */
// abstract class ModeleNumRefDons
// {
// 	/**
// 	 * @var string Error code (or message)
// 	 */
// 	public $error = '';

// 	/**
// 	 * 	Return if a module can be used or not
// 	 *
// 	 *  @return		boolean     true if module can be used
// 	 */
// 	public function isEnabled()
// 	{
// 		return true;
// 	}

// 	/**
// 	 * 	Renvoi la description par defaut du modele de numerotation
// 	 *
// 	 *  @return     string      Texte descripif
// 	 */
// 	public function info()
// 	{
// 		global $langs;
// 		$langs->load("bills");
// 		return $langs->trans("NoDescription");
// 	}

// 	/**
// 	 *  Return an example of numbering
// 	 *
// 	 *  @return     string      Example
// 	 */
// 	public function getExample()
// 	{
// 		global $langs;
// 		$langs->load("bills");
// 		return $langs->trans("NoExample");
// 	}

// 	/**
// 	 *  Checks if the numbers already in the database do not
// 	 *  cause conflicts that would prevent this numbering working.
// 	 *
// 	 *  @return     boolean     false if conflict, true if ok
// 	 */
// 	public function canBeActivated()
// 	{
// 		return true;
// 	}

// 	/**
// 	 *  Renvoi prochaine valeur attribuee
// 	 *
// 	 *  @return     string      Valeur
// 	 */
// 	public function getNextValue()
// 	{
// 		global $langs;
// 		return $langs->trans("NotAvailable");
// 	}

// 	/**
// 	 *  Renvoi version du module numerotation
// 	 *
// 	 *  @return     string      Valeur
// 	 */
// 	public function getVersion()
// 	{
// 		global $langs;
// 		$langs->load("admin");

// 		if ($this->version == 'development') {
// 			return $langs->trans("VersionDevelopment");
// 		}
// 		if ($this->version == 'experimental') {
// 			return $langs->trans("VersionExperimental");
// 		}
// 		if ($this->version == 'dolibarr') {
// 			return DOL_VERSION;
// 		}
// 		if ($this->version) {
// 			return $this->version;
// 		}
// 		return $langs->trans("NotAvailable");
// 	}
// }
