<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 * \file        class/appel.class.php
 * \ingroup     viescolaire
 * \brief       This file is a CRUD class file for Appel (Create/Read/Update/Delete)
 */

/*ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/scolarite/class/creneau.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/viescolaire/class/assignation.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/management/class/agent.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for Appel
 */
class Appel extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'viescolaire';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'appel';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'appel';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for appel. Must be the part after the 'object_' into object_appel.png
	 */
	public $picto = 'appel@viescolaire';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 4;
	const STATUS_CANCELED = 8;


	/**
	 *  'type' field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter[:Sortfield]]]', 'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter[:Sortfield]]]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'text:none', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 or 2 if field can be used for measure. Field type must be summable like integer or double(24,8). Use 1 in most cases, or 2 if you don't want to see the column total into list (for example for percentage)
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
	 *  'help' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arrayofkeyval' to set a list of values if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel"). Note that type can be 'integer' or 'varchar'
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *	'validate' is 1 if need to validate with $this->validateField()
	 *  'copytoclipboard' is 1 or 2 to allow to add a picto to copy value into clipboard (1=picto after label, 2=picto after value)
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
		'fk_etablissement' => array('type'=>'integer:Etablissement:custom/scolarite/class/etablissement.class.php:1', 'label'=>'Etablissements', 'foreignkey'=>'etablissement.rowid','enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'validate'=>'1', 'comment'=>"Reference of object",'css'=>'maxwidth300',),
		'fk_creneau' => array('type'=>'integer:Creneau:custom/scolarite/class/creneau.class.php:1:(t.nombre_places > (SELECT COUNT(*) FROM '.MAIN_DB_PREFIX.'affectation as c WHERE c.fk_creneau=t.rowid AND c.status = 4 AND DATE(NOW()) >= DATE(c.date_debut) AND (DATE(NOW()) <= DATE(c.date_fin) OR ISNULL(c.date_fin))))', 'label'=>'Creneau', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'default'=>'(PROV)', 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'validate'=>'1', 'comment'=>"Reference of object"),
		'fk_eleve' => array('type'=>'integer(11)', 'label'=>'Résultat', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>0, 'searchall'=>1, 'css'=>'minwidth300', 'cssview'=>'wordbreak', 'help'=>"Help text", 'showoncombobox'=>'2', 'validate'=>'1', 'arrayofkeyval'=>array("0"=>"Présent(e)","1"=>"En retard","2"=>"Absence justifée","3"=>"Absence non-justifée")),
		'fk_user' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'Amount', 'enabled'=>'1', 'position'=>40, 'notnull'=>0, 'visible'=>0, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help text for amount", 'validate'=>'1',),		'justification' => array('type'=>'integer(11)', 'label'=>'Description', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>0, 'validate'=>'1',),
		'action_faite' => array('type'=>'integer(11)', 'label'=>'Description', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>0, 'validate'=>'1',),
		'justification' => array('type'=>'text', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>61, 'notnull'=>0, 'visible'=>0, 'cssview'=>'wordbreak', 'validate'=>'1',),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>61, 'notnull'=>0, 'visible'=>0, 'cssview'=>'wordbreak', 'validate'=>'1',),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>62, 'notnull'=>0, 'visible'=>0, 'cssview'=>'wordbreak', 'validate'=>'1',),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'status' => array('type'=>'varchar(20)', 'label'=>'Status', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>0, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Brouillon', '1'=>'Valid&eacute;', '9'=>'Annul&eacute;'), 'validate'=>'1',),
		'treated' => array('type'=>'integer', 'label'=>'treated', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>0, 'index'=>1, 'validate'=>'1',),

	);
	public $rowid;
	public $ref;
	public $label;
	public $amount;
	public $qty;
	public $fk_soc;
	public $fk_project;
	public $description;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $last_main_doc;
	public $import_key;
	public $model_pdf;
	public $status;
	public $treated;
	// END MODULEBUILDER PROPERTIES


	// If this object has a subtable with lines

	// /**
	//  * @var string    Name of subtable line
	//  */
	// public $table_element_line = 'viescolaire_appelline';

	// /**
	//  * @var string    Field with ID of parent key if this object has a parent
	//  */
	// public $fk_element = 'fk_appel';

	// /**
	//  * @var string    Name of subtable class that manage subtable lines
	//  */
	// public $class_element_line = 'Appelline';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	// protected $childtables = array();

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	// protected $childtablesoncascade = array('viescolaire_appeldet');

	// /**
	//  * @var AppelLine[]     Array of subtable lines
	//  */
	// public $lines = array();
	public $justification;


	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->viescolaire->appel->read) {
			$this->fields['myfield']['visible'] = 1;
			$this->fields['myfield']['noteditable'] = 0;
		}*/

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		$resultcreate = $this->createCommon($user, $notrigger);

		//$resultvalidate = $this->validate($user, $notrigger);

		return $resultcreate;
	}

	/**
	 * Clone an object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $langs, $extrafields;
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

		$this->db->begin();

		// Load source object
		$result = $object->fetchCommon($fromid);
		if ($result > 0 && !empty($object->table_element_line)) {
			$object->fetchLines();
		}

		// get lines so they will be clone
		//foreach($this->lines as $line)
		//	$line->fetch_optionals();

		// Reset some properties
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->import_key);

		// Clear fields
		if (property_exists($object, 'ref')) {
			$object->ref = empty($this->fields['ref']['default']) ? "Copy_Of_".$object->ref : $this->fields['ref']['default'];
		}
		if (property_exists($object, 'label')) {
			$object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
		}
		if (property_exists($object, 'status')) {
			$object->status = self::STATUS_DRAFT;
		}
		if (property_exists($object, 'date_creation')) {
			$object->date_creation = dol_now();
		}
		if (property_exists($object, 'date_modification')) {
			$object->date_modification = null;
		}
		// ...
		// Clear extrafields that are unique
		if (is_array($object->array_options) && count($object->array_options) > 0) {
			$extrafields->fetch_name_optionals_label($this->table_element);
			foreach ($object->array_options as $key => $option) {
				$shortkey = preg_replace('/options_/', '', $key);
				if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey])) {
					unset($object->array_options[$key]);
				}
			}
		}

		// Create clone
		$object->context['createfromclone'] = 'createfromclone';
		$result = $object->createCommon($user);
		if ($result < 0) {
			$error++;
			$this->error = $object->error;
			$this->errors = $object->errors;
		}

		if (!$error) {
			// copy internal contacts
			if ($this->copy_linked_contact($object, 'internal') < 0) {
				$error++;
			}
		}

		if (!$error) {
			// copy external contacts if same company
			if (!empty($object->socid) && property_exists($this, 'fk_soc') && $this->fk_soc == $object->socid) {
				if ($this->copy_linked_contact($object, 'external') < 0) {
					$error++;
				}
			}
		}

		unset($object->context['createfromclone']);

		// End
		if (!$error) {
			$this->db->commit();
			return $object;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null, $moresql = null)
	{
		$result = $this->fetchCommon($id, $ref, $moresql);
		if ($result > 0 && !empty($this->table_element_line)) {
			$this->fetchLines();
		}
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines = array();

		$result = $this->fetchLinesCommon();
		return $result;
	}


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND', $moreSql = null)
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		if($moreSql)
		{
			$sql .= $moreSql;
		}
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= " WHERE t.entity IN (".getEntity($this->table_element).")";
		} else {
			$sql .= " WHERE 1 = 1";
		}

		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key." = ".((int) $value);
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key." = '".$this->db->idate($value)."'";
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key." IN (".$this->db->sanitize($this->db->escape($value)).")";
				} else {
					$sqlwhere[] = $key." LIKE '%".$this->db->escape($value)."%'";
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= " AND (".implode(" ".$filtermode." ", $sqlwhere).")";
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 *  Delete a line of object in database
	 *
	 *	@param  User	$user       User that delete
	 *  @param	int		$idline		Id of line to delete
	 *  @param 	bool 	$notrigger  false=launch triggers after, true=disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = false)
	{
		if ($this->status < 0) {
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}


	/**
	 *	Validate object
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf, $langs;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED) {
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->viescolaire->appel->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->viescolaire->appel->appel_advance->validate))))
		 {
		 $this->error='NotEnoughPermissions';
		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
		 return -1;
		 }*/

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) { // empty should not happened, but when it occurs, the test save life
			$num = $this->getNextNumRef();
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ref = '".$this->db->escape($num)."',";
			$sql .= " status = ".self::STATUS_VALIDATED;
			if (!empty($this->fields['date_validation'])) {
				$sql .= ", date_validation = '".$this->db->idate($now)."'";
			}
			if (!empty($this->fields['fk_user_valid'])) {
				$sql .= ", fk_user_valid = ".((int) $user->id);
			}
			$sql .= " WHERE rowid = ".((int) $this->id);

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql) {
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger) {
				// Call trigger
				$result = $this->call_trigger('APPEL_VALIDATE', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
		}

		if (!$error) {
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref)) {
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'appel/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'appel/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) {
					$error++; $this->error = $this->db->lasterror();
				}

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->viescolaire->dir_output.'/appel/'.$oldref;
				$dirdest = $conf->viescolaire->dir_output.'/appel/'.$newref;
				if (!$error && file_exists($dirsource)) {
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest)) {
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->viescolaire->dir_output.'/appel/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry) {
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
							$dirsource = $fileentry['path'].'/'.$dirsource;
							$dirdest = $fileentry['path'].'/'.$dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		// Set new ref and current status
		if (!$error) {
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error) {
			$this->db->commit();
			return 1;
		} else {
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Set draft status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->viescolaire->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->viescolaire->viescolaire_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'APPEL_UNVALIDATE');
	}

	/**
	 *	Set cancel status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function cancel($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_VALIDATED) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->viescolaire->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->viescolaire->viescolaire_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'APPEL_CANCEL');
	}

	/**
	 *	Set back to validated status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function reopen($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_CANCELED) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->viescolaire->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->viescolaire->viescolaire_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'APPEL_REOPEN');
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';

		$label = img_picto('', $this->picto).' <u>'.$langs->trans("Appel").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = dol_buildpath('/viescolaire/appel_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($url && $add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowAppel");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink' || empty($url)) {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink' || empty($url)) {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) {
				$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
			}
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) {
			$result .= $this->ref;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('appeldao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLabelStatus($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			//$langs->load("viescolaire@viescolaire");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
		}

		$statusType = 'status'.$status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) {
			$statusType = 'status6';
		}

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = "SELECT rowid, date_creation as datec, tms as datem,";
		$sql .= " fk_user_creat, fk_user_modif";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if (!empty($obj->fk_user_author)) {
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation = $cuser;
				}

				if (!empty($obj->fk_user_valid)) {
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if (!empty($obj->fk_user_cloture)) {
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		// Set here init that are not commonf fields
		// $this->property1 = ...
		// $this->property2 = ...

		$this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new AppelLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_appel = '.((int) $this->id)));

		if (is_numeric($result)) {
			$this->error = $objectline->error;
			$this->errors = $objectline->errors;
			return $result;
		} else {
			$this->lines = $result;
			return $this->lines;
		}
	}

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("viescolaire@viescolaire");

		if (empty($conf->global->VIESCOLAIRE_APPEL_ADDON)) {
			$conf->global->VIESCOLAIRE_APPEL_ADDON = 'mod_appel_standard';
		}

		if (!empty($conf->global->VIESCOLAIRE_APPEL_ADDON)) {
			$mybool = false;

			$file = $conf->global->VIESCOLAIRE_APPEL_ADDON.".php";
			$classname = $conf->global->VIESCOLAIRE_APPEL_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir) {
				$dir = dol_buildpath($reldir."core/modules/viescolaire/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false) {
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1') {
					return $numref;
				} else {
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		} else {
			print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
			return "";
		}
	}

	/**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $conf, $langs;

		$result = 0;
		$includedocgeneration = 1;

		$langs->load("viescolaire@viescolaire");

		if (!dol_strlen($modele)) {
			$modele = 'standard_appel';

			if (!empty($this->model_pdf)) {
				$modele = $this->model_pdf;
			} elseif (!empty($conf->global->APPEL_ADDON_PDF)) {
				$modele = $conf->global->APPEL_ADDON_PDF;
			}
		}

		$modelpath = "core/modules/viescolaire/doc/";

		if ($includedocgeneration && !empty($modele)) {
			$result = $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
		}

		return $result;
	}

	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 * Use public function doScheduledJob($param1, $param2, ...) to get parameters
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}

	public function printStatsIndex()
	{
		global $conf;

		if ($conf->use_javascript_ajax) {
			$totalEleves = 0;
			$dataseries = array();

			$etablissementClass = new Etablissement($this->db);
			$etablissements = $etablissementClass->fetchAll('','',0,0,array(),0);

			$eleveClass = new Eleve($this->db);

			foreach ($etablissements as $value) {
				$eleves = $eleveClass->fetchAll('','',0,0,array('customsql'=>" fk_etablissement=$value->id AND status !=".Eleve::STATUS_CANCELED),'AND');

				$totalEleves .= count($eleves);
				array_push($dataseries, [$value->nom, count($eleves)]);
			}

			print '<div class="div-table-responsive-no-min">';
			print '<table class="noborder centpercent">';
			print '<tr class="liste_titre"><th>Statistiques nombre d\'élèves</th></tr>';
			print '<tr><td class="center nopaddingleftimp nopaddingrightimp">';


			$dataval = array();
			$datalabels = array();
			$i = 0;

			include_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';
			$dolgraph = new DolGraph();
			$dolgraph->SetData($dataseries);
			$dolgraph->setShowLegend(2);
			$dolgraph->setShowPercent(1);
			$dolgraph->SetType(array('pie'));
			$dolgraph->setHeight('200');
			$dolgraph->draw('idgraphstatus');
			print $dolgraph->show($totalEleves ? 0 : 1);

			print '</td></tr>';
			print '</table>';
			print '</div>';
		}
	}

	public function printAbsencesIndex()
	{

		require_once DOL_DOCUMENT_ROOT.'/custom/viescolaire/class/eleve.class.php';
		require_once DOL_DOCUMENT_ROOT.'/custom/scolarite/class/creneau.class.php';

		$date = date('Y-m-d');
		$tomorrow = date('Y-m-d', strtotime($date . ' + 1 day'));

		// requête pour aller chercher les absences selon l'établissement
		$absence = 'SELECT DISTINCT a.rowid, a.justification,a.fk_eleve,a.fk_creneau,a.status ';
		$absence .= 'FROM ' . MAIN_DB_PREFIX . 'appel as a';
		// Si selection d'un établissement spécifique, on lie la table établissement dans notre requête
		$absence .= ' INNER JOIN '.MAIN_DB_PREFIX.'creneau as c ON c.rowid=a.fk_creneau';
		if ($_SESSION['etablissementid'] != 0) {
			$absence .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'etablissement as e ON a.fk_etablissement=' . $_SESSION['etablissementid'];
		}
		$absence .= " WHERE a.date_creation >= '" . $date . "' ";
		$absence .= "AND a.date_creation < '" . $tomorrow . "' ";
		$absence .= 'AND a.treated=1 ';
		$absence .= "AND a.fk_eleve != '' ";
		$absence .= "AND a.status !='present' ";
		$absence .= 'ORDER BY c.heure_debut ASC';

		$resqlAbsenceDuJour = $this->db->query($absence);

		// Si il y a un nombre d'absence positif
		if ($resqlAbsenceDuJour->num_rows > 0) {
			print load_fiche_titre("Absences connues aujourd'hui <span class='badge badge-status4 badge-status'>{$resqlAbsenceDuJour->num_rows}</span>", '', 'fa-warning');
			print '<table class="tagtable liste">';
			print '<tbody>';
			print '<tr class="liste_titre">
			<th class="wrapcolumntitle liste_titre">Élève</th>
			<th class="wrapcolumntitle liste_titre">Justification</th>
			<th class="wrapcolumntitle liste_titre">Créneau</th>
			<th class="wrapcolumntitle liste_titre">Status</th>
			</tr>';
			foreach ($resqlAbsenceDuJour as $value) {
				// Fetch de l'élève
				$eleveClass = new Eleve($this->db);
				$eleveClass->fetch($value['fk_eleve']);
				// Fetch du créneau
				$creneauClass = new Creneau($this->db);
				$creneauClass->fetch($value['fk_creneau']);

				print "<tr class='oddeven'>";
				print "<td style='width:20%'>$eleveClass->prenom $eleveClass->nom</td>";
				print "<td style='width:20%'>{$value['justification']}</td>";
				print "<td style='width:45%'><a href=" . DOL_URL_ROOT . "/custom/scolarite/creneau_card.php?id=$creneauClass->rowid>" . $creneauClass->nom_creneau . '</a></td>';
				print '<td>' . '<span class="badge  badge-status' . ($value['status'] == 'retard' ? '1' : ($value['status'] == 'absenceJ' ? '7' : ($value['status'] == 'present' ? '4' : '8'))) . ' badge-status" style="color:white;">' . $value['status'] . '</span>' . '</td>';

				print '</tr>';
			}
			print '</tbody>';
			print '</table>';
			// Sinon affichage d'un message par défaut
		} else {
			print load_fiche_titre("Absences connues aujourd'hui <span class='badge badge-status4 badge-status'>0</span>", '', 'fa-warning');
			print "Aucune absence connue pour aujourd'hui!";
		}
	}

	/**
	 *  Affiche le formulaire de changement d'heure de l'appel
	 *
	 *  @return string      		Formulaire
	 */
	public function printChangeHourFormAppel(int $antenneId, string $heureActuelle, string $selectedDay)
	{
		$form = new Form($this->db);

		$out = '<form method="POST" action='.$_SERVER['PHP_SELF'].'>';
		$out .= '<input type="hidden" name="antenneId" value='.$antenneId.'>';
		$out .= '<input type="hidden" name="token" value='.newToken().'>';
		$out .= '<input type="hidden" name="action" value="create">';
		$out .= dol_get_fiche_head(array(), '');
		$out .= '<table class="border centpercent ">'."\n";

		$out .= '<div class="center">';
		$out .= '<label>Selectionnez l\'heure désirée : </label>';

		$out .= '<input type="string" name="selectedDay" value='.$selectedDay.' hidden>';
		$out .= '<input type="time" name="heureActuelle" value="'.$heureActuelle.':00" >';
		$out .= '</div>';
		$out .= '</table>'."\n";

		$out .= dol_get_fiche_end();

		$out .= $form->buttonsSaveCancel('Valider','','','','hourButton');

		$out .= '</form>';

		return $out;
	}

	public function returnAllAppelInfos(int $creneauId, string $dateAppel, array $eleves, array $professeurs)
	{
		$isComplete = true;
		$countAppelInj = 0;
		$treated = true;
		$appelScolaAgent = 0;

		$conditions = " AND fk_creneau={$creneauId} AND date_creation LIKE '{$dateAppel}%' ORDER BY rowid DESC";

		foreach ([$eleves, $professeurs] as $type)
		{
			foreach ($type as $personne)
			{
				$appelClass = new self($this->db);
				$appelClass->fetch('', '', " AND fk_{$personne->element_appel}={$personne->id}" . $conditions);

				if (!$appelClass->id) {
					$isComplete = false;
				}

				if ($appelClass->status == 'absenceI') {
					$countAppelInj++;
					if ($appelClass->justification == '') {
						$treated = false;
					}
				}

				if($appelScolaAgent != $appelClass->fk_user_creat) {
					$appelScolaAgent = $appelClass->fk_user_creat;
				}
			}
		}

		$userClass = new User($this->db);
		$userClass->fetch($appelScolaAgent);

		return [$isComplete,$countAppelInj, $treated, $userClass];
	}


	/**
	 *  Insert ou update tout les appels envoyé depuis un formulaire pour les élèves
	 *
	 *  @return int
	 */
	public function InsertOrUpdateAppelEleves(array $eleves,int $creneauId,string $dateCreation)
	{
		global $user;
		$resFunction = 0;
		// pour chaque élève, ajout ou update de l'appel
		foreach ($eleves as $eleve)
		{
			// Fetch d'un potentiel appel déjà éxistant
			$appelClass = new self($this->db);
			$appelClass->fetch('',''," AND fk_creneau={$creneauId} AND fk_eleve=$eleve->id AND treated=1 AND date_creation LIKE '{$dateCreation}%' ORDER BY rowid DESC");

			// Si un appel éxiste déjà on l'update
			if($appelClass->id)
			{
				$appelClass->status = GETPOST('presence' . $eleve->id , 'alpha');
				$appelClass->justification = str_replace("'",'`',GETPOST('infos' . $eleve->id, 'alpha'));
				$appelClass->treated = (GETPOST('presence' . $eleve->id, 'alpha') == 'absenceI' && !empty($appelClass->justification)) || GETPOST('presence' . $eleve->id, 'alpha') != 'absenceI' ? 1 : 0;
				$resUpdateAppel = $appelClass->update($user);

				if($resUpdateAppel < 0) $resFunction--;
			} else {
				// Sinon on en crée un
				$appelClass = new self($this->db);
				$appelClass->fk_creneau = $creneauId;
				$appelClass->fk_eleve = $eleve->id;
				$appelClass->justification = str_replace("'",'`',GETPOST('infos' . $eleve->id, 'alpha'));
				$appelClass->date_creation = $dateCreation;
				$appelClass->status = GETPOST('presence' . $eleve->id, 'alpha');
				$appelClass->treated = (GETPOST('presence' . $eleve->id, 'alpha') == 'absenceI' && !empty($appelClass->justification)) || GETPOST('presence' . $eleve->id, 'alpha') != 'absenceI' ? 1 : 0;

				$resultInsertAppel = $appelClass->create($user);

				if($resultInsertAppel < 0) $resFunction--;
			}
		}

		// Return 0 pour OK et négatif si erreur
		return $resFunction;
	}


	/**
	 *  Insert ou update tout les appels envoyé depuis un formulaire pour les professeurs
	 *
	 *  @return int
	 */
	public function InsertOrUpdateAppelProfesseurs(array $professeurs,int $creneauId,string $dateCreation)
	{
		global $user;
		$resFunction = 0;

		// pour chaque élève, ajout ou update de l'appel
		foreach ($professeurs as $professeur)
		{
			$appelClass = new self($this->db);
			$appelClass->fetch('',''," AND fk_creneau={$creneauId} AND fk_user=$professeur->id AND treated=1 AND date_creation LIKE '{$dateCreation}%' ORDER BY rowid DESC");

			// Si un appel éxiste déjà on l'update
			if($appelClass->id)
			{
				$appelClass->status = GETPOST('prof' . $professeur->id, 'alpha');
				$appelClass->justification = str_replace("'",'`',GETPOST('infos' . $professeur->id, 'alpha'));
				$appelClass->treated = (GETPOST('prof' . $professeur->id, 'alpha') == 'absenceI' && !empty($appelClass->justification)) || GETPOST('prof' . $professeur->id, 'alpha') != 'absenceI' ? 1 : 0;
				$resUpdateAppel = $appelClass->update($user);

				if($resUpdateAppel < 0) $resFunction--;
			} else {

				$appelClass = new self($this->db);
				$appelClass->fk_creneau = $creneauId;
				$appelClass->fk_user = $professeur->id;
				$appelClass->justification = str_replace("'",'`',GETPOST('infos' . $professeur->id, 'alpha'));
				$appelClass->date_creation = $dateCreation;
				$appelClass->status =  GETPOST('prof' . $professeur->id, 'alpha');
				$appelClass->treated = (GETPOST('prof' . $professeur->id, 'alpha') == 'absenceI' && !empty($appelClass->justification)) || GETPOST('prof' . $professeur->id, 'alpha') != 'absenceI' ? 1 : 0;


				$resultInsertAppel = $appelClass->create($user);

				if($resultInsertAppel < 0) $resFunction--;
			}
		}

		return $resFunction;
	}

	/**
	 * Vérifie si tous les appels ont bien été envoyés dans le formulaire
	 *
	 * @return bool Booléen
	 */
	public function checkIfAllAppelSent(int $creneauId)
	{
		$eleveClass = new Eleve($this->db);
		$eleves = $eleveClass->fetchAll('', '', 0, 0,
			array('a.fk_creneau' => $creneauId, 'a.status' => Affectation::STATUS_VALIDATED), 'AND',
			' INNER JOIN ' . MAIN_DB_PREFIX . 'souhait as s ON s.fk_eleve = t.rowid INNER JOIN ' . MAIN_DB_PREFIX . 'affectation as a ON a.fk_souhait=s.rowid'
		);

		$agentClass = new Agent($this->db);
		$professeurs = $agentClass->fetchAll('', '', 0, 0,
			array('a.fk_creneau' => $creneauId, 'a.status' => Assignation::STATUS_VALIDATED), 'AND',
			' INNER JOIN ' . MAIN_DB_PREFIX . 'assignation as a ON a.fk_agent=t.rowid'
		);

		return $this->checkAppelsSent($eleves, 'presence') && $this->checkAppelsSent($professeurs, 'prof');
	}

	/**
	 * Vérifie si tous les appels ont été envoyés pour une liste spécifique (éléves ou professeurs)
	 *
	 * @param array  $liste Liste d'éléments à vérifier
	 * @param string $prefix Préfixe utilisé pour le nom des variables POST
	 * @return bool Booléen
	 */
	private function checkAppelsSent(array $liste, string $prefix)
	{
		foreach ($liste as $element) {
			$variableName = $prefix . $element->id;
			if (!GETPOST($variableName, 'alpha')) {
				return false; // Si un appel n'est pas envoyé, retourne false immédiatement
			}
		}

		return true; // Tous les appels sont envoyés
	}
}


require_once DOL_DOCUMENT_ROOT.'/core/class/commonobjectline.class.php';

/**
 * Class AppelLine. You can also remove this and generate a CRUD class for lines objects.
 */
class AppelLine extends CommonObjectLine
{
	// To complete with content of an object AppelLine
	// We should have a field rowid, fk_appel and position

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}
}
