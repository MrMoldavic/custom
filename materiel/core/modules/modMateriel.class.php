<?php

/**
 * 	\defgroup   materiel     Module Materiel
 *  \brief      Materiel module descriptor.
 *
 *  \file       htdocs/materiel/core/modules/modMateriel.class.php
 *  \ingroup    materiel
 *  \brief      Description and activation file for module Materiel
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module Materiel
 */
class modMateriel extends DolibarrModules
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
		$this->numero = 510000; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'materiel';
		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "TALM";
		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '92';
		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleMaterielName' not found (Materiel is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description, used if translation string 'ModuleMaterielDesc' not found (Materiel is name of module).
		$this->description = "Module de gestion des matériels";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "Permet la gestion des matériels de l'association";
		$this->editor_name = 'Etienne Pommier';
		$this->editor_url = '';
		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = 'development';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where MATERIEL is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto = 'materiel';
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
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				//    '/materiel/css/materiel.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/materiel/js/materiel.js.php',
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
		// Example: this->dirs = array("/materiel/temp","/materiel/subdir");
		$this->dirs = array("/materiel/temp","/materiel/subdir");
		// Config pages. Put here list of php page, stored into materiel/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@materiel");
		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
		$this->depends = array();
		$this->requiredby = array(); // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array(); // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)
		$this->langfiles = array("materiel@materiel");
		$this->phpmin = array(5, 5); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(11, -3); // Minimum version of Dolibarr required by module
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		//$this->automatic_activation = array('FR'=>'MaterielWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('MATERIEL_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('MATERIEL_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array();

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isset($conf->materiel) || !isset($conf->materiel->enabled)) {
			$conf->materiel = new stdClass();
			$conf->materiel->enabled = 0;
		}

		// Array to add new pages in new tabs
		$this->tabs = array();

		// Dictionaries
		$this->dictionaries=array(
			'langs'=>'materiel@materiel',
			// List of tables we want to see into dictonnary editor
			'tabname'=>array(MAIN_DB_PREFIX."c_classe_materiel",
							 MAIN_DB_PREFIX."c_etat_materiel",
							 MAIN_DB_PREFIX."c_etat_etiquette",
			                 MAIN_DB_PREFIX."c_destination",
			                 MAIN_DB_PREFIX."c_origine_materiel",
			                 MAIN_DB_PREFIX."c_marque",
			                 MAIN_DB_PREFIX."c_proprietaire",
			                 MAIN_DB_PREFIX."c_exploitabilite",
			                 MAIN_DB_PREFIX."c_type_source"),
			// Label of tables
			'tablib'=>array("Classe de matériel","État du materiel", "État de l'étiquette du matériel","Destination (usage) du matériel", "Origine du matériel", "Marque du matériel", "Propriétaire", "Exploitabilité", "Type de source"),
			// Request to select fields
			'tabsql'=>array('SELECT f.rowid as rowid, f.classe, f.active FROM '.MAIN_DB_PREFIX.'c_classe_materiel as f',
							'SELECT f.rowid as rowid, f.indicatif, f.etat, f.badge_status_code, f.active FROM '.MAIN_DB_PREFIX.'c_etat_materiel as f',
							'SELECT f.rowid as rowid, f.indicatif, f.etat, f.badge_code, f.active FROM '.MAIN_DB_PREFIX.'c_etat_etiquette as f',
			                'SELECT f.rowid as rowid, f.indicatif, f.destination, f.active FROM '.MAIN_DB_PREFIX.'c_destination as f',
			                'SELECT f.rowid as rowid, f.origine, f.active FROM '.MAIN_DB_PREFIX.'c_origine_materiel as f',
			                'SELECT f.rowid as rowid, f.marque, f.active FROM '.MAIN_DB_PREFIX.'c_marque as f',
			                'SELECT f.rowid as rowid, f.proprietaire, f.active FROM '.MAIN_DB_PREFIX.'c_proprietaire as f',
			                'SELECT f.rowid as rowid, f.indicatif, f.exploitabilite, f.badge_status_code, f.active FROM '.MAIN_DB_PREFIX.'c_exploitabilite as f',
			                'SELECT f.rowid as rowid, f.type, f.table_name, f.active FROM '.MAIN_DB_PREFIX.'c_type_source as f'),
			// Sort order
			'tabsqlsort'=>array("rowid ASC", "rowid ASC", "rowid ASC", "rowid ASC", "rowid ASC", "rowid ASC", "rowid ASC", "rowid ASC", "rowid ASC"),
			// List of fields (result of select to show dictionary)
			'tabfield'=>array("classe", "indicatif,etat,badge_status_code", "indicatif,etat,badge_code", "indicatif,destination", "origine", "marque", "proprietaire", "indicatif,exploitabilite,badge_status_code", "type,table_name"),
			// List of fields (list of fields to edit a record)
			'tabfieldvalue'=>array("classe", "indicatif,etat,badge_status_code", "indicatif,etat,badge_code", "indicatif,destination", "origine", "marque", "proprietaire", "indicatif,exploitabilite,badge_status_code", "type,table_name"),
			// List of fields (list of fields for insert)
			'tabfieldinsert'=>array("classe", "indicatif,etat,badge_status_code", "indicatif,etat,badge_code", "indicatif,destination", "origine", "marque", "proprietaire", "indicatif,exploitabilite,badge_status_code", "type,table_name"),
			// Name of columns with primary key (try to always name it 'rowid')
			'tabrowid'=>array("rowid", "rowid", "rowid", "rowid", "rowid", "rowid", "rowid", "rowid", "rowid"),
			// Condition to show each dictionary
			'tabcond'=>array($conf->materiel->enabled, $conf->materiel->enabled, $conf->materiel->enabled, $conf->materiel->enabled, $conf->materiel->enabled, $conf->materiel->enabled, $conf->materiel->enabled, $conf->materiel->enabled, $conf->materiel->enabled)
		);


		// Boxes/Widgets
		// Add here list of php file(s) stored in materiel/core/boxes that contains a class to show a widget.
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'materielwidget1.php@materiel',
			//      'note' => 'Widget provided by Materiel',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/materiel/class/emprunt.class.php',
			//      'objectname' => 'Emprunt',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => '$conf->materiel->enabled',
			//      'priority' => 50,
			//  ),
		);
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->materiel->enabled', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->materiel->enabled', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Lire les données des matériels'; // Permission label
		$this->rights[$r][2] = 'r'; // Permission label
		$this->rights[$r][3] = 0; // Permission label
		$this->rights[$r][4] = 'read'; // Permission label
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Créer/mettre à jour les matériels'; // Permission label
		$this->rights[$r][2] = 'c'; // Permission label
		$this->rights[$r][3] = 0; // Permission label
		$this->rights[$r][4] = 'create'; // Permission label
        $r++;

		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Supprimer un matériel'; // Permission label
		$this->rights[$r][2] = 'd'; // Permission label
		$this->rights[$r][3] = 0; // Permission label
		$this->rights[$r][4] = 'delete'; // In php code, permission will be checked by test if ($user->rights->materiel->level1->level2)
		$r++;

		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Gérer les types de matériel'; // Permission label
		$this->rights[$r][2] = 'mt'; // Permission label
		$this->rights[$r][3] = 0; // Permission label
		$this->rights[$r][4] = 'modifytype'; // In php code, permission will be checked by test if ($user->rights->materiel->level1->level2)
		$r++;

		/* END MODULEBUILDER PERMISSIONS */

		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		$this->menu[$r++] = array(
			'fk_menu'=>'',
			'type'=>'top',
			'titre'=>'ModuleMaterielName',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'materiel',
			
			'leftmenu'=>'',
			'url'=>'/materiel/materielindex.php',
			'langs'=>'materiel@materiel',
			'position'=>1000 + $r,
			'enabled'=>'$conf->materiel->enabled',
			'perms'=>'$user->rights->materiel->read',
			'target'=>'',
			'user'=>0
		);

		$r++;
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=materiel',
			'type'=>'left',
			'titre'=>'Pré-inventaire',
			'mainmenu'=>'materiel',
			'leftmenu'=>'preinventaire',
			'url'=>'',
			'langs'=>'materiel@materiel',
			'position'=>12,
			'enabled'=>'$conf->materiel->enabled',
			'perms'=>'$user->rights->materiel->read',
			'target'=>'',
			'user'=>0
		);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=preinventaire',
		'type'=>'left',
		'titre'=>'Liste du préinventaire',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/materiel/preinventaire/list.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->create',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=preinventaire',
		'type'=>'left',
		'titre'=>'<strong>Sources</strong>',
		'mainmenu'=>'materiel',
		'leftmenu'=>'preinv_source',
		'url'=>'',
		'langs'=>'materiel@materiel',
		'position'=>15,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->create',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=preinv_source',
		'type'=>'left',
		'titre'=>'Liste des sources',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/materiel/preinventaire/source/list.php',
		'langs'=>'materiel@materiel',
		'position'=>16,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->create',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=preinv_source',
		'type'=>'left',
		'titre'=>'Gestion / Traitement',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/materiel/preinventaire/source/manage.php',
		'langs'=>'materiel@materiel',
		'position'=>17,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->create',
		'target'=>'',
		'user'=>0);

		$r++;
		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel',
		'type'=>'left',
		'titre'=>'Matériel',
		'mainmenu'=>'materiel',
		'leftmenu'=>'mat',
		'url'=>'/materiel/materielindex.php',
		'langs'=>'materiel@materiel',
		'position'=>13,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->read',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=mat',
		'type'=>'left',
		'titre'=>'Nouveau matériel',
		'mainmenu'=>'materiel',
		'leftmenu'=>'nouveau_mat',
		'url'=>'/materiel/card.php?action=create',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->create',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=nouveau_mat',
		'type'=>'left',
		'titre'=>'Gérer les types de matériel',
		'mainmenu'=>'materiel',
		'leftmenu'=>'aa',
		'url'=>'/materiel/typemat/card.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->modifytype',
		'target'=>'',
		'user'=>0);


		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=mat',
		'type'=>'left',
		'titre'=>'Liste du matériel',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/materiel/list.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->read',
		'target'=>'',
		'user'=>0);

		$r++;
		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel',
		'type'=>'left',
		'titre'=>'Reçu fiscaux',
		'mainmenu'=>'materiel',
		'leftmenu'=>'recufiscaux',
		'url'=>'/recufiscal/index.php',
		'langs'=>'materiel@materiel',
		'position'=>13,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->read',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=recufiscaux',
		'type'=>'left',
		'titre'=>'Nouveau reçu fiscal',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/recufiscal/card.php?action=create',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->create',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=recufiscaux',
		'type'=>'left',
		'titre'=>'Liste reçus fiscaux',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/recufiscal/list.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->create',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=recufiscaux',
		'type'=>'left',
		'titre'=>'Donateurs',
		'mainmenu'=>'materiel',
		'leftmenu'=>'listedonateurs',
		'url'=>'/recufiscal/donateur/list.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->create',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=listedonateurs',
		'type'=>'left',
		'titre'=>'Nouveau donateur',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/recufiscal/donateur/card.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->create',
		'target'=>'',
		'user'=>0);
		$r++;
		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel',
		'type'=>'left',
		'titre'=>'Emprunts',
		'mainmenu'=>'materiel',
		'leftmenu'=>'entretiens',
		'url'=>'',
		'langs'=>'materiel@materiel',
		'position'=>13,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->read',
		'target'=>'',
		'user'=>0);
	
		
		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=entretiens',
		'type'=>'left',
		'titre'=>'Nouvel emprunt',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/materiel/emprunt_card.php?action=create',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->create',
		'target'=>'',
		'user'=>0);
		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=entretiens',
		'type'=>'left',
		'titre'=>'Liste des emprunts',
		'mainmenu'=>'materiel',
		'leftmenu'=>'emprunteur',
		'url'=>'/materiel/emprunt_list.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->read',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=emprunteur',
		'type'=>'left',
		'titre'=>'Nouvel emprunteur',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/materiel/emprunteur_card.php?action=create',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->create',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=emprunteur',
		'type'=>'left',
		'titre'=>'Liste des emprunteurs',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/materiel/emprunteur_list.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->read',
		'target'=>'',
		'user'=>0);
		
			
			
		

		$r++;

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel',
		'type'=>'left',
		'titre'=>'Kit',
		'mainmenu'=>'materiel',
		'leftmenu'=>'kit',
		'url'=>'/kit/index.php',
		'langs'=>'materiel@materiel',
		'position'=>13,
		'enabled'=>'1',
		'perms'=>'$user->rights->kit->read',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=kit',
		'type'=>'left',
		'titre'=>'Nouveau kit',
		'mainmenu'=>'materiel',
		'leftmenu'=>'nouveau_kit',
		'url'=>'/kit/card.php?action=create',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->kit->create',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=nouveau_kit',
		'type'=>'left',
		'titre'=>'Nouveau type de kit',
		'mainmenu'=>'materiel',
		'leftmenu'=>'dd',
		'url'=>'/kit/typekit/card.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->kit->managekittype',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=nouveau_kit',
		'type'=>'left',
		'titre'=>'Liste des types de kit',
		'mainmenu'=>'materiel',
		'leftmenu'=>'dd',
		'url'=>'/kit/typekit/list.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->kit->managekittype',
		'target'=>'',
		'user'=>0);


		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=kit',
		'type'=>'left',
		'titre'=>'Liste des kits',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/kit/list.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->kit->read',
		'target'=>'',
		'user'=>0);

		$r++;
		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel',
		'type'=>'left',
		'titre'=>'Exploitation',
		'mainmenu'=>'materiel',
		'leftmenu'=>'exploitation',
		'url'=>'/exploitation/index.php',
		'langs'=>'materiel@materiel',
		'position'=>13,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->read',
		'target'=>'',
		'user'=>0);


		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=exploitation',
		'type'=>'left',
		'titre'=>'Nouvelle exploitation',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/exploitation/card.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->create',
		'target'=>'',
		'user'=>0);


		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=exploitation',
		'type'=>'left',
		'titre'=>'Liste des exploitations',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/exploitation/list.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->materiel->read',
		'target'=>'',
		'user'=>0);

		$r++;
		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel',
		'type'=>'left',
		'titre'=>'Entretien',
		'mainmenu'=>'materiel',
		'leftmenu'=>'entretien',
		'url'=>'/entretien/index.php',
		'langs'=>'materiel@materiel',
		'position'=>13,
		'enabled'=>'1',
		'perms'=>'$user->rights->entretien->read',
		'target'=>'',
		'user'=>0);


		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=entretien',
		'type'=>'left',
		'titre'=>'Nouvel entretien',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/entretien/card.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->entretien->create',
		'target'=>'',
		'user'=>0);

		$this->menu[]=array(
		'fk_menu'=>'fk_mainmenu=materiel,fk_leftmenu=entretien',
		'type'=>'left',
		'titre'=>'Liste des entretiens',
		'mainmenu'=>'materiel',
		'leftmenu'=>'',
		'url'=>'/entretien/list.php',
		'langs'=>'materiel@materiel',
		'position'=>14,
		'enabled'=>'1',
		'perms'=>'$user->rights->entretien->read',
		'target'=>'',
		'user'=>0);
		$r++;

		


		// Exports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER EXPORT EMPRUNT */
		/*
		$langs->load("materiel@materiel");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='EmpruntLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='emprunt@materiel';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'Emprunt'; $keyforclassfile='/materiel/class/emprunt.class.php'; $keyforelement='emprunt@materiel';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'EmpruntLine'; $keyforclassfile='/materiel/class/emprunt.class.php'; $keyforelement='empruntline@materiel'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='emprunt'; $keyforaliasextra='extra'; $keyforelement='emprunt@materiel';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='empruntline'; $keyforaliasextra='extraline'; $keyforelement='empruntline@materiel';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('empruntline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'emprunt as t';
		//$this->export_sql_end[$r]  =' LEFT JOIN '.MAIN_DB_PREFIX.'emprunt_line as tl ON tl.fk_emprunt = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('emprunt').')';
		$r++; */
		/* END MODULEBUILDER EXPORT EMPRUNT */

		// Imports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER IMPORT EMPRUNT */
		/*
		 $langs->load("materiel@materiel");
		 $this->export_code[$r]=$this->rights_class.'_'.$r;
		 $this->export_label[$r]='EmpruntLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		 $this->export_icon[$r]='emprunt@materiel';
		 $keyforclass = 'Emprunt'; $keyforclassfile='/materiel/class/emprunt.class.php'; $keyforelement='emprunt@materiel';
		 include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		 $keyforselect='emprunt'; $keyforaliasextra='extra'; $keyforelement='emprunt@materiel';
		 include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		 //$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		 $this->export_sql_start[$r]='SELECT DISTINCT ';
		 $this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'emprunt as t';
		 $this->export_sql_end[$r] .=' WHERE 1 = 1';
		 $this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('emprunt').')';
		 $r++; */
		/* END MODULEBUILDER IMPORT EMPRUNT */
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

		$result = $this->_load_tables('/materiel/sql/');
		if ($result < 0) return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')

		// Permissions
		$this->remove($options);

		$sql = array();

		// Document templates
		$moduledir = 'materiel';
		$myTmpObjects = array();
		$myTmpObjects['Emprunt']=array('includerefgeneration'=>0, 'includedocgeneration'=>0);

		foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
			if ($myTmpObjectKey == 'Emprunt') continue;
			if ($myTmpObjectArray['includerefgeneration']) {
				$src=DOL_DOCUMENT_ROOT.'/install/doctemplates/materiel/template_emprunts.odt';
				$dirodt=DOL_DATA_ROOT.'/doctemplates/materiel';
				$dest=$dirodt.'/template_emprunts.odt';

				if (file_exists($src) && ! file_exists($dest))
				{
					require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
					dol_mkdir($dirodt);
					$result=dol_copy($src, $dest, 0, 0);
					if ($result < 0)
					{
						$langs->load("errors");
						$this->error=$langs->trans('ErrorFailToCopyFile', $src, $dest);
						return 0;
					}
				}

				$sql = array_merge($sql, array(
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."','".strtolower($myTmpObjectKey)."',".$conf->entity.")",
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".strtolower($myTmpObjectKey)."', ".$conf->entity.")"
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
