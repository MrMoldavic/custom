<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2023 Baptiste Diodati <baptiste.diodati@gmail.com>
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
 */

/**
 * 	\defgroup   organisation     Module Organisation
 *  \brief      Organisation module descriptor.
 *
 *  \file       htdocs/organisation/core/modules/modOrganisation.class.php
 *  \ingroup    organisation
 *  \brief      Description and activation file for module Organisation
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module Organisation
 */
class modOrganisation extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 500100; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'organisation';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "other";

		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';

		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleOrganisationName' not found (Organisation is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// Module description, used if translation string 'ModuleOrganisationDesc' not found (Organisation is name of module).
		$this->description = "OrganisationDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "OrganisationDescription";

		// Author
		$this->editor_name = 'Editor name';
		$this->editor_url = 'https://www.example.com';

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where ORGANISATION is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		// To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
		$this->picto = 'generic';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 0,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 1,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				//    '/organisation/css/organisation.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/organisation/js/organisation.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			'hooks' => array(
				//   'data' => array(
				//       'hookcontext1',
				//       'hookcontext2',
				//   ),
				//   'entity' => '0',
			),
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/organisation/temp","/organisation/subdir");
		$this->dirs = array("/organisation/temp");

		// Config pages. Put here list of php page, stored into organisation/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@organisation");

		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
		$this->depends = array();
		$this->requiredby = array(); // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array(); // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)

		// The language file dedicated to your module
		$this->langfiles = array("organisation@organisation");

		// Prerequisites
		$this->phpmin = array(5, 6); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(11, -3); // Minimum version of Dolibarr required by module

		// Messages at activation
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		//$this->automatic_activation = array('FR'=>'OrganisationWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('ORGANISATION_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('ORGANISATION_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array();

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isset($conf->organisation) || !isset($conf->organisation->enabled)) {
			$conf->organisation = new stdClass();
			$conf->organisation->enabled = 0;
		}

		// Array to add new pages in new tabs
		$this->tabs = array();
		// Example:
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@organisation:$user->rights->organisation->read:/organisation/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@organisation:$user->rights->othermodule->read:/organisation/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
		//
		// Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view

		// Dictionaries
		$this->dictionaries = array(
			'langs' => 'organisation@organisation',
			// List of tables we want to see into dictonnary editor
			'tabname' => array(MAIN_DB_PREFIX . "organisation_c_type_evenement", 
			MAIN_DB_PREFIX . "organisation_c_etat_evenement",
			MAIN_DB_PREFIX . "organisation_c_type_participation",
			MAIN_DB_PREFIX . "organisation_c_type_poste",
			MAIN_DB_PREFIX . "organisation_c_etat_convocation"
			),
			// Label of tables
			'tablib' => array("Type des Evenements", "Etat des Evenements", "Type de participation", "Type de poste", "Etat des convocations"),
			// Request to select fieldsc
			'tabsql' => array('SELECT t.rowid as rowid, t.type, t.active FROM ' . MAIN_DB_PREFIX . 'organisation_c_type_evenement as t',
				'SELECT e.rowid as rowid, e.etat, e.active FROM ' . MAIN_DB_PREFIX . 'organisation_c_etat_evenement as e',
				'SELECT t.rowid as rowid, t.type, t.active FROM ' . MAIN_DB_PREFIX . 'organisation_c_type_participation as t',
				'SELECT p.rowid as rowid, p.poste, p.active FROM ' . MAIN_DB_PREFIX . 'organisation_c_type_poste as p',
				'SELECT e.rowid as rowid, e.etat, e.active FROM ' . MAIN_DB_PREFIX . 'organisation_c_etat_convocation as e',
				),
			// Sort order
			'tabsqlsort' => array("type ASC","etat ASC","type ASC", "poste ASC", "etat ASC"),
			// List of fields (result of select to show dictionary)
			'tabfield' => array("type", "etat", "type", "poste", "etat"),
			// List of fields (list of fields to edit a record)
			'tabfieldvalue' => array("type", "etat","type", "poste", "etat"),
			// List of fields (list of fields for insert)
			'tabfieldinsert' => array("type", "etat", "type", "poste", "etat"),
			// Name of columns with primary key (try to always name it 'rowid')
			'tabrowid' => array("rowid", "rowid","rowid", "rowid", "rowid"),
			// Condition to show each dictionary
			'tabcond' => array($conf->organisation->enabled, $conf->organisation->enabled, $conf->organisation->enabled, $conf->organisation->enabled, $conf->organisation->enabled)
		);

		// Boxes/Widgets
		// Add here list of php file(s) stored in organisation/core/boxes that contains a class to show a widget.
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'organisationwidget1.php@organisation',
			//      'note' => 'Widget provided by Organisation',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			0 => array(
				'label' => 'Test du job',
				'jobtype' => 'method',
				'class' => '/organisation/class/engagement.class.php',
				'objectname' => 'Engagement',
				'method' => 'doScheduledJob',
				'parameters' => '',
				'comment' => 'Comment',
				'frequency' => 2,
				'unitfrequency' => 60,
				'status' => 0,
				'test' => '$conf->organisation->enabled',
				'priority' => 50,
			),
		);
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->organisation->enabled', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->organisation->enabled', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of Organisation'; // Permission label
		$this->rights[$r][4] = 'engagement';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->organisation->engagement->read)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update objects of Organisation'; // Permission label
		$this->rights[$r][4] = 'engagement';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->organisation->engagement->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of Organisation'; // Permission label
		$this->rights[$r][4] = 'engagement';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->organisation->engagement->delete)
		$r++;
		/* END MODULEBUILDER PERMISSIONS */

		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		// Add here entries to declare new menus
		/* BEGIN MODULEBUILDER TOPMENU */
		$this->menu[$r++] = array(
			'fk_menu'=>'', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'top', // This is a Top menu entry
			'titre'=>'ModuleOrganisationName',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'organisation',
			'leftmenu'=>'',
			'url'=>'/organisation/organisationindex.php',
			'langs'=>'organisation@organisation', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$conf->organisation->enabled', // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled.
			'perms'=>'1', // Use 'perms'=>'$user->rights->organisation->engagement->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		/* END MODULEBUILDER TOPMENU */
		/* BEGIN MODULEBUILDER LEFTMENU ENGAGEMENT */
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=organisation',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Left menu entry
			'titre'=>'Groupes',
			'mainmenu'=>'organisation',
			'leftmenu'=>'groupe',
			'url'=>'/organisation/organisationindex.php',
			'langs'=>'organisation@organisation',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->organisation->enabled',  // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=groupe',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',			                // This is a Left menu entry
			'titre'=>'Liste des groupes',
			'mainmenu'=>'organisation',
			'leftmenu'=>'groupe',
			'url'=>'/organisation/groupe_list.php',
			'langs'=>'organisation@organisation',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->organisation->enabled',  // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=groupe',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',			                // This is a Left menu entry
			'titre'=>'Nouveau groupe',
			'mainmenu'=>'organisation',
			'leftmenu'=>'groupe',
			'url'=>'/organisation/groupe_card.php?action=create',
			'langs'=>'organisation@organisation',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->organisation->enabled',  // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		/* */

		// $this->menu[$r++]=array(
		// 	'fk_menu'=>'fk_mainmenu=organisation',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		// 	'type'=>'left',                          // This is a Left menu entry
		// 	'titre'=>'Engagements',
		// 	'mainmenu'=>'organisation',
		// 	'leftmenu'=>'engagement',
		// 	'url'=>'/organisation/organisationindex.php',
		// 	'langs'=>'organisation@organisation',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		// 	'position'=>1000+$r,
		// 	'enabled'=>'$conf->organisation->enabled',  // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled.
		// 	'perms'=>'1',			                // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
		// 	'target'=>'',
		// 	'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		// );

        // $this->menu[$r++]=array(
        //     // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
        //     'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=engagement',
        //     // This is a Left menu entry
        //     'type'=>'left',
        //     'titre'=>'Liste des engagements',
        //     'mainmenu'=>'organisation',
        //     'leftmenu'=>'engagement',
        //     'url'=>'/organisation/engagement_list.php',
        //     // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
        //     'langs'=>'organisation@organisation',
        //     'position'=>1100+$r,
        //     // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
        //     'enabled'=>'$conf->organisation->enabled',
        //     // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
        //     'perms'=>'1',
        //     'target'=>'',
        //     // 0=Menu for internal users, 1=external users, 2=both
        //     'user'=>2,
        // );

        // $this->menu[$r++]=array(
        //     // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
        //     'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=engagement',
        //     // This is a Left menu entry
        //     'type'=>'left',
        //     'titre'=>'Nouvel engagement',
        //     'mainmenu'=>'organisation',
        //     'leftmenu'=>'engagement',
        //     'url'=>'/organisation/engagement_card.php?action=create',
        //     // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
        //     'langs'=>'organisation@organisation',
        //     'position'=>1100+$r,
        //     // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
        //     'enabled'=>'$conf->organisation->enabled',
        //     // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
        //     'perms'=>'1',
        //     'target'=>'',
        //     // 0=Menu for internal users, 1=external users, 2=both
        //     'user'=>2
        // );



		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=organisation',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Left menu entry
			'titre'=>'Événement',
			'mainmenu'=>'organisation',
			'leftmenu'=>'evenement',
			'url'=>'/organisation/organisationindex.php',
			'langs'=>'organisation@organisation',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->organisation->enabled',  // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);

        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=evenement',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'Liste des événements',
            'mainmenu'=>'organisation',
            'leftmenu'=>'evenement',
            'url'=>'/organisation/evenement_list.php',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'organisation@organisation',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->organisation->enabled',
            // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
            'perms'=>'1',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2,
        );

        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=evenement',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'Nouvel événement',
            'mainmenu'=>'organisation',
            'leftmenu'=>'evenement',
            'url'=>'/organisation/evenement_card.php?action=create',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'organisation@organisation',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->organisation->enabled',
            // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
            'perms'=>'1',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=organisation',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Left menu entry
			'titre'=>'Artistes et Morceaux',
			'mainmenu'=>'organisation',
			'leftmenu'=>'artiste',
			'url'=>'/organisation/organisationindex.php',
			'langs'=>'organisation@organisation',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->organisation->enabled',  // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);

        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=artiste',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'Liste des artistes',
            'mainmenu'=>'organisation',
            'leftmenu'=>'artiste2',
            'url'=>'/organisation/artiste_list.php',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'organisation@organisation',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->organisation->enabled',
            // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
            'perms'=>'1',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2,
        );

        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=artiste2',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'Nouvel artiste',
            'mainmenu'=>'organisation',
            'leftmenu'=>'',
            'url'=>'/organisation/artiste_card.php?action=create',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'organisation@organisation',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->organisation->enabled',
            // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
            'perms'=>'1',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );

		$this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=artiste',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'Liste des morceaux',
            'mainmenu'=>'organisation',
            'leftmenu'=>'morceau',
            'url'=>'/organisation/morceau_list.php',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'organisation@organisation',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->organisation->enabled',
            // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
            'perms'=>'1',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2,
        );

        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=morceau',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'Nouveau morceau',
            'mainmenu'=>'organisation',
            'leftmenu'=>'',
            'url'=>'/organisation/morceau_card.php?action=create',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'organisation@organisation',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->organisation->enabled',
            // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
            'perms'=>'1',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=organisation',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Left menu entry
			'titre'=>'Instruments',
			'mainmenu'=>'organisation',
			'leftmenu'=>'instrument',
			'url'=>'/organisation/organisationindex.php',
			'langs'=>'organisation@organisation',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->organisation->enabled',  // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);

        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=instrument',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'Liste des instrument',
            'mainmenu'=>'organisation',
            'leftmenu'=>'instrument2',
            'url'=>'/organisation/instrument_list.php',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'organisation@organisation',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->organisation->enabled',
            // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
            'perms'=>'1',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2,
        );

        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=instrument2',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'Nouvel instrument',
            'mainmenu'=>'organisation',
            'leftmenu'=>'',
            'url'=>'/organisation/instrument_card.php?action=create',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'organisation@organisation',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->organisation->enabled',
            // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
            'perms'=>'1',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );

		$this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=instrument',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'Liste des types d\'instruments',
            'mainmenu'=>'organisation',
            'leftmenu'=>'instrument',
            'url'=>'/organisation/typeinstrument_list.php',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'organisation@organisation',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->organisation->enabled',
            // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
            'perms'=>'1',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2,
        );

        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=instrument',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'Nouveau type d\'instrument',
            'mainmenu'=>'organisation',
            'leftmenu'=>'',
            'url'=>'/organisation/typeinstrument_card.php?action=create',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'organisation@organisation',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->organisation->enabled',
            // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
            'perms'=>'1',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );


		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=organisation',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Left menu entry
			'titre'=>'Interprétation',
			'mainmenu'=>'organisation',
			'leftmenu'=>'interpretation',
			'url'=>'/organisation/organisationindex.php',
			'langs'=>'organisation@organisation',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->organisation->enabled',  // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);

        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=interpretation',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'Liste des interprétations',
            'mainmenu'=>'organisation',
            'leftmenu'=>'interpretation',
            'url'=>'/organisation/interpretation_list.php',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'organisation@organisation',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->organisation->enabled',
            // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
            'perms'=>'1',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2,
        );

        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=interpretation',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'Nouvelle interprétation',
            'mainmenu'=>'organisation',
            'leftmenu'=>'',
            'url'=>'/organisation/interpretation_card.php?action=create',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'organisation@organisation',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->organisation->enabled',
            // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
            'perms'=>'1',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );


		// $this->menu[$r++]=array(
		// 	'fk_menu'=>'fk_mainmenu=organisation',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		// 	'type'=>'left',                          // This is a Left menu entry
		// 	'titre'=>'Proposition',
		// 	'mainmenu'=>'organisation',
		// 	'leftmenu'=>'proposition',
		// 	'url'=>'/organisation/organisationindex.php',
		// 	'langs'=>'organisation@organisation',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		// 	'position'=>1000+$r,
		// 	'enabled'=>'$conf->organisation->enabled',  // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled.
		// 	'perms'=>'1',			                // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
		// 	'target'=>'',
		// 	'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		// );

        // $this->menu[$r++]=array(
        //     // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
        //     'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=proposition',
        //     // This is a Left menu entry
        //     'type'=>'left',
        //     'titre'=>'Liste des proposition',
        //     'mainmenu'=>'organisation',
        //     'leftmenu'=>'proposition',
        //     'url'=>'/organisation/proposition_list.php',
        //     // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
        //     'langs'=>'organisation@organisation',
        //     'position'=>1100+$r,
        //     // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
        //     'enabled'=>'$conf->organisation->enabled',
        //     // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
        //     'perms'=>'1',
        //     'target'=>'',
        //     // 0=Menu for internal users, 1=external users, 2=both
        //     'user'=>2,
        // );

        // $this->menu[$r++]=array(
        //     // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
        //     'fk_menu'=>'fk_mainmenu=organisation,fk_leftmenu=proposition',
        //     // This is a Left menu entry
        //     'type'=>'left',
        //     'titre'=>'Nouvelle proposition',
        //     'mainmenu'=>'organisation',
        //     'leftmenu'=>'',
        //     'url'=>'/organisation/proposition_card.php?action=create',
        //     // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
        //     'langs'=>'organisation@organisation',
        //     'position'=>1100+$r,
        //     // Define condition to show or hide menu entry. Use '$conf->organisation->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
        //     'enabled'=>'$conf->organisation->enabled',
        //     // Use 'perms'=>'$user->rights->organisation->level1->level2' if you want your menu with a permission rules
        //     'perms'=>'1',
        //     'target'=>'',
        //     // 0=Menu for internal users, 1=external users, 2=both
        //     'user'=>2
        // );





		/* END MODULEBUILDER LEFTMENU ENGAGEMENT */
		// Exports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER EXPORT ENGAGEMENT */
		/*
		$langs->load("organisation@organisation");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='EngagementLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='engagement@organisation';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'Engagement'; $keyforclassfile='/organisation/class/engagement.class.php'; $keyforelement='engagement@organisation';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'EngagementLine'; $keyforclassfile='/organisation/class/engagement.class.php'; $keyforelement='engagementline@organisation'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='engagement'; $keyforaliasextra='extra'; $keyforelement='engagement@organisation';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='engagementline'; $keyforaliasextra='extraline'; $keyforelement='engagementline@organisation';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('engagementline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'engagement as t';
		//$this->export_sql_end[$r]  =' LEFT JOIN '.MAIN_DB_PREFIX.'engagement_line as tl ON tl.fk_engagement = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('engagement').')';
		$r++; */
		/* END MODULEBUILDER EXPORT ENGAGEMENT */

		// Imports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER IMPORT ENGAGEMENT */
		/*
		$langs->load("organisation@organisation");
		$this->import_code[$r]=$this->rights_class.'_'.$r;
		$this->import_label[$r]='EngagementLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->import_icon[$r]='engagement@organisation';
		$this->import_tables_array[$r] = array('t' => MAIN_DB_PREFIX.'organisation_engagement', 'extra' => MAIN_DB_PREFIX.'organisation_engagement_extrafields');
		$this->import_tables_creator_array[$r] = array('t' => 'fk_user_author'); // Fields to store import user id
		$import_sample = array();
		$keyforclass = 'Engagement'; $keyforclassfile='/organisation/class/engagement.class.php'; $keyforelement='engagement@organisation';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinimport.inc.php';
		$import_extrafield_sample = array();
		$keyforselect='engagement'; $keyforaliasextra='extra'; $keyforelement='engagement@organisation';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinimport.inc.php';
		$this->import_fieldshidden_array[$r] = array('extra.fk_object' => 'lastrowid-'.MAIN_DB_PREFIX.'organisation_engagement');
		$this->import_regex_array[$r] = array();
		$this->import_examplevalues_array[$r] = array_merge($import_sample, $import_extrafield_sample);
		$this->import_updatekeys_array[$r] = array('t.ref' => 'Ref');
		$this->import_convertvalue_array[$r] = array(
			't.ref' => array(
				'rule'=>'getrefifauto',
				'class'=>(empty($conf->global->ORGANISATION_ENGAGEMENT_ADDON) ? 'mod_engagement_standard' : $conf->global->ORGANISATION_ENGAGEMENT_ADDON),
				'path'=>"/core/modules/commande/".(empty($conf->global->ORGANISATION_ENGAGEMENT_ADDON) ? 'mod_engagement_standard' : $conf->global->ORGANISATION_ENGAGEMENT_ADDON).'.php'
				'classobject'=>'Engagement',
				'pathobject'=>'/organisation/class/engagement.class.php',
			),
			't.fk_soc' => array('rule' => 'fetchidfromref', 'file' => '/societe/class/societe.class.php', 'class' => 'Societe', 'method' => 'fetch', 'element' => 'ThirdParty'),
			't.fk_user_valid' => array('rule' => 'fetchidfromref', 'file' => '/user/class/user.class.php', 'class' => 'User', 'method' => 'fetch', 'element' => 'user'),
			't.fk_mode_reglement' => array('rule' => 'fetchidfromcodeorlabel', 'file' => '/compta/paiement/class/cpaiement.class.php', 'class' => 'Cpaiement', 'method' => 'fetch', 'element' => 'cpayment'),
		);
		$r++; */
		/* END MODULEBUILDER IMPORT ENGAGEMENT */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		//$result = $this->_load_tables('/install/mysql/', 'organisation');
		$result = $this->_load_tables('/organisation/sql/');
		if ($result < 0) {
			return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
		}

		// Create extrafields during init
		//include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		//$extrafields = new ExtraFields($this->db);
		//$result1=$extrafields->addExtraField('organisation_myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', 0, 0, '', '', 'organisation@organisation', '$conf->organisation->enabled');
		//$result2=$extrafields->addExtraField('organisation_myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'organisation@organisation', '$conf->organisation->enabled');
		//$result3=$extrafields->addExtraField('organisation_myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'organisation@organisation', '$conf->organisation->enabled');
		//$result4=$extrafields->addExtraField('organisation_myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1,'', 0, 0, '', '', 'organisation@organisation', '$conf->organisation->enabled');
		//$result5=$extrafields->addExtraField('organisation_myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'organisation@organisation', '$conf->organisation->enabled');

		// Permissions
		$this->remove($options);

		$sql = array();

		// Document templates
		$moduledir = dol_sanitizeFileName('organisation');
		$myTmpObjects = array();
		$myTmpObjects['Engagement'] = array('includerefgeneration'=>0, 'includedocgeneration'=>0);

		foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
			if ($myTmpObjectKey == 'Engagement') {
				continue;
			}
			if ($myTmpObjectArray['includerefgeneration']) {
				$src = DOL_DOCUMENT_ROOT.'/install/doctemplates/'.$moduledir.'/template_engagements.odt';
				$dirodt = DOL_DATA_ROOT.'/doctemplates/'.$moduledir;
				$dest = $dirodt.'/template_engagements.odt';

				if (file_exists($src) && !file_exists($dest)) {
					require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
					dol_mkdir($dirodt);
					$result = dol_copy($src, $dest, 0, 0);
					if ($result < 0) {
						$langs->load("errors");
						$this->error = $langs->trans('ErrorFailToCopyFile', $src, $dest);
						return 0;
					}
				}

				$sql = array_merge($sql, array(
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")",
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")"
				));
			}
		}

		return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string	$options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}
