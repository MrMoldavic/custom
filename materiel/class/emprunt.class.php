<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
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
 * \file        class/emprunt.class.php
 * \ingroup     materiel
 * \brief       This file is a CRUD class file for Emprunt (Create/Read/Update/Delete)
 */





//  ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/materiel/core/lib/functions.lib.php';
// require_once DOL_DOCUMENT_ROOT.'/custom/materiel/class/empruntLine.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for Emprunt
 */
class Emprunt extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'materiel';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'emprunt';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'emprunt';

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
	 * @var string String with name of icon for emprunt. Must be a 'fa-xxx' fontawesome code (or 'fa-xxx_fa_color_size') or 'emprunt@materiel' if picto is file 'img/object_emprunt.png'.
	 */
	public $picto = 'emprunt';

	public $total_ttc;

	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 4;
	const STATUS_CANCELED = 9;


	/**
	 *  'type' field format:
	 *  	'integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter[:Sortfield]]]',
	 *  	'select' (list of values are in 'options'),
	 *  	'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter[:Sortfield]]]]',
	 *  	'chkbxlst:...',
	 *  	'varchar(x)',
	 *  	'text', 'text:none', 'html',
	 *   	'double(24,8)', 'real', 'price',
	 *  	'date', 'datetime', 'timestamp', 'duration',
	 *  	'boolean', 'checkbox', 'radio', 'array',
	 *  	'mail', 'phone', 'url', 'password', 'ip'
	 *		Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM' or '!empty($conf->multicurrency->enabled)' ...)
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
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>4, 'noteditable'=>'1', 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'validate'=>'1', 'comment'=>"Reference of object"),
		'fk_emprunteur' => array('type'=>'integer:Emprunteur:custom/materiel/class/emprunteur.class.php:1', 'label'=>'Emprunteur', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>1, 'searchall'=>1, 'help'=>"Personne laissant à disposition son objet à l'association", 'showoncombobox'=>'2', 'validate'=>'1', 'foreignkey'=>'emprunteur.rowid', 'css'=>'maxwidth200',),
		'montant' => array('type'=>'price', 'label'=>'Montant', 'enabled'=>'1', 'position'=>40, 'notnull'=>1, 'visible'=>2, 'default'=>0, 'isameasure'=>'1', 'help'=>"Valeur en euro des objets cumulés", 'validate'=>'1',),
		'date_emprunt' => array('type'=>'date', 'label'=>'Date de l\'emprunt', 'enabled'=>'1', 'position'=>44, 'notnull'=>1, 'visible'=>1,),

		'description' => array('type'=>'text', 'label'=>'Notes', 'enabled'=>'1', 'position'=>45, 'notnull'=>0, 'visible'=>1, 'default'=>'', 'isameasure'=>'1', 'css'=>'maxwidth75imp', 'help'=>"Help text for quantity", 'validate'=>'1',),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>61, 'notnull'=>0, 'visible'=>0, 'cssview'=>'wordbreak', 'validate'=>'1',),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>62, 'notnull'=>0, 'visible'=>0, 'cssview'=>'wordbreak', 'validate'=>'1',),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'picto'=>'user', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'picto'=>'user', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>'1', 'position'=>2000, 'notnull'=>1, 'visible'=>2, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Brouillon', '1'=>'Valid&eacute;', '9'=>'Annul&eacute;'), 'validate'=>'1',),
	);

	public $rowid;
	public $ref;
	public $fk_emprunteur;
	public $montant;
	public $description;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $status;
	// END MODULEBUILDER PROPERTIES


	// If this object has a subtable with lines

	// /**
	//  * @var string    Name of subtable line
	//  */
	public $table_element_line = 'emprunt_det';

	// /**
	//  * @var string    Field with ID of parent key if this object has a parent
	//  */
	// public $fk_element = 'fk_emprunt';

	// /**
	//  * @var string    Name of subtable class that manage subtable lines
	//  */
	// public $class_element_line = 'Empruntline';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	// protected $childtables = array();

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	// protected $childtablesoncascade = array('materiel_empruntdet');

	// /**
	//  * @var EmpruntLine[]     Array of subtable lines
	//  */
	// public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid']) && !empty($this->fields['ref'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->materiel->emprunt->read) {
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
		$this->montant = 0.00;
		$resultcreate = $this->createCommon($user, $notrigger);

		$emprunteur = "SELECT nom FROM " . MAIN_DB_PREFIX . "emprunteur WHERE rowid = (SELECT fk_emprunteur FROM ".MAIN_DB_PREFIX."emprunt WHERE rowid = ".$resultcreate.")";
		$resqlEmprunteur = $this->db->query($emprunteur);
		$objEmprunteur =  $this->db->fetch_object($resqlEmprunteur);

		
		$sqlRef = "UPDATE " . MAIN_DB_PREFIX . "emprunt SET ref = 'EMP-" . $resultcreate . "-".$objEmprunteur->nom."' WHERE rowid=" . $resultcreate;
		$this->db->query($sqlRef);

		return $resultcreate;
	}

	public function formAddObjectLine()
    {
        global $form;
        //WYSIWYG Editor
        require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

        print '<tr>';

        // Description
        print '<td>';
        $doleditor = new DolEditor('description', GETPOST('description', 'none'), '', 160, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '90%');
        $doleditor->Create();
        print '</td>';

        // Valeur
        print '<td>';
        print '<input type="number" step="0.01" name="valeur" maxlength="255" value="'.GETPOST('valeur', 'float').'">';
        print '</td>';

        // Quantity
        print '<td class="center">';
        print '<input type="number" step="1" name="qty" maxlength="255" value="'.GETPOST('qty', 'int').'">';
        print '</td>';

        // BLANK
        print '<td>';
        print '</td>';

        // Submit button
        print '<td>';
        print '<input type="submit" class="button" value="Ajouter" name="addline" id="addline">';
        print '</td>';

        print '</tr>';
    }


	public function addLine($description, $valeur, $qty)
    {
        // First check if the status is draft
        if ($this->status != 0) return 0;

        $sql = "INSERT INTO ".MAIN_DB_PREFIX."emprunt_det (";
        $sql .= "fk_emprunt, ";
        $sql .= "description, ";
        $sql .= "valeur, ";
        $sql .= "qty";
        $sql .= ") VALUES (";
        $sql .= $this->id . ", ";
        $sql .= "'".str_replace("'","\'",$description)."', ";
        $sql .= $valeur . ", ";
        $sql .= $qty;
        $sql .= ')';


		$sqlMontant = "UPDATE " . MAIN_DB_PREFIX . "emprunt SET montant = montant + ".($valeur*$qty)." WHERE rowid=" . $this->id;
		$this->db->query($sqlMontant);


       $resql = $this->db->query($sql);
        if (!$resql) {
            $this->error = "Database Error";
            return 0;
        } else {
            $this->db->commit();
            return 1;
        }
    }


	public function printObjectLines($action = '', $lineid ='')
    {

        global $conf, $form;
        //WYSIWYG Editor
        require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

        $this->fetch_lines();
        if (empty($this->lines)) return 1; // Stop the function here if there's no line
        else 
        {
            foreach ($this->lines as $line)
            {
                // If this line is edited, we will print input fields instead of text
                $islineedited = (($action == 'editline' && $line->id == $lineid && $this->status == Emprunt::STATUS_DRAFT) ? 1 : 0);

                if ($islineedited) {
                    print '<input type="hidden" name="lineid" value="'. $line->id .'" />';
                }
                
                print '<tr>';

                // Description
                print '<td>';
                if ($islineedited) {  
                    $doleditor = new DolEditor('description', $line->description, '', 160, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '90%');
                    $doleditor->Create();
                }
                else print $line->description;
                print '</td>';

                // Valeur unitaire
                print '<td>';
                if ($islineedited) {  
                    print '<input type="number" step="0.01" name="valeur" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.$line->valeur.'">';
                }
                else print price($line->valeur, 1, '', 0, -1, -1, $conf->currency);
                print '</td>';

                // Quantity
                print '<td class="center">';
                if ($islineedited) {  
                    print '<input type="number" step="1" name="qty" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.$line->qty.'">';
                }
                else print $line->qty;
                print '</td>';

                // Valeur totale
                print '<td>';
                print price($line->valeur * $line->qty, 1, '', 0, -1, -1, $conf->currency);
                print '</td>';

                
                if ($islineedited && $this->status == Emprunt::STATUS_DRAFT) 
                {  
                    print '<td>';
                    print '<input type="submit" class="button" value="Modifier" name="save" id="addline">';
                    print '<input type="submit" class="button" value="Annuler" name="cancel" id="addline">';                
                    print '</td>';
                } 
                elseif ($this->status == Emprunt::STATUS_DRAFT) 
                {
                    // Modify / Remove button
                    print '<td align="center">';
                    print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?action=editline&id='. $this->id .'&lineid='.$line->id.'">'.img_edit().'</a>';
                    print '&nbsp;';
                    print '<a href="'.$_SERVER["PHP_SELF"].'?action=ask_deleteline&id='. $this->id .'&lineid='.$line->id.'">'.img_delete().'</a>';
                    print '</td>';
                } 
                else 
                {
                    print '<td align="center">';
                    print '</td>';
                }

                print '</tr>';
            }
        }
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
					//var_dump($key);
					//var_dump($clonedObj->array_options[$key]); exit;
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
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) {
			$this->fetch_lines();
		}
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch_lines()
    {
        // Reset values
        $this->lines = array();

        $sql = "SELECT ed.rowid";
        $sql .= " FROM ".MAIN_DB_PREFIX."emprunt_det as ed";
        $sql .= " WHERE ed.fk_emprunt = ".$this->id;
        $resql = $this->db->query($sql);
        $num = $this->db->num_rows($resql);

        $this->total_ttc = 0;
        
        if ($num)
        {
            $i = 0;
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);
                $line = new EmpruntLine($this->db);
                $line->fetch($obj->rowid);
                $this->total_ttc += $line->valeur * $line->qty;
                $this->lines[$line->id] = $line;
				
                $i++;
            }
        }
        return 1;
    }

	public function updateLine($lineid, $description, $valeur, $qty)
    {
        // Check if the recufiscal is in draft mode
        if ($this->status != Emprunt::STATUS_DRAFT)
        {
            $this->error = 'Le reçu ne peut pas être modifié si il n\'est pas en brouillon';
            return 0;
        }

        // Check if the line is indeed associatied with this recufiscal
        if (!array_key_exists($lineid, $this->lines)) { 
            // This line doesn't correspond to this recufiscal
            $this->error = 'Ligne de matériel invalide pour ce reçu fiscal';
            return 0;
        } else {

			$line = "SELECT valeur,qty,rowid FROM " . MAIN_DB_PREFIX . "emprunt_det WHERE rowid = ".$lineid;
			$resqlLine = $this->db->query($line);
			$objLine =  $this->db->fetch_object($resqlLine);

			$sqlSubMontant = "UPDATE " . MAIN_DB_PREFIX . "emprunt SET montant = montant - ".($objLine->valeur*$objLine->qty)." WHERE rowid=" . $this->id;
			$this->db->query($sqlSubMontant);


			// prendre la valeur actuelle de la ligne, la soustraire au montant existant dans l'emprunt, et remplacer par la nouvelle valeur
            $sql = "UPDATE ". MAIN_DB_PREFIX ."emprunt_det";
            $sql .= " SET description = '". $description ."', ";
            $sql .= "valeur = ". $valeur .", ";
            $sql .= "qty = ". $qty;
            $sql .= " WHERE rowid = ". $lineid;
            $resql = $this->db->query($sql);


			$sqlAddMontant = "UPDATE " . MAIN_DB_PREFIX . "emprunt SET montant = montant + ".($valeur*$qty)." WHERE rowid=" . $this->id;
			$this->db->query($sqlAddMontant);

            if (!$resql) {
                $this->error = 'Database error';
                return 0;
            } else {
                $this->db->commit();
                return 1;
            }
        }
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
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= " WHERE t.entity IN (".getEntity($this->element).")";
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
		$emprunteur = "SELECT nom FROM " . MAIN_DB_PREFIX . "emprunteur WHERE rowid = ".$this->fk_emprunteur;
		$resqlEmprunteur = $this->db->query($emprunteur);
		$objEmprunteur =  $this->db->fetch_object($resqlEmprunteur);


		$this->ref = 'EMP-'. $this->id . "-".$objEmprunteur->nom;

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
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 *  Delete a line of object in database
	 *
	 *	@param  User	$user       User that delete
	 *  @param	int		$idline		Id of line to delete
	 *  @param 	bool 	$notrigger  false=launch triggers after, true=disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function deleteLine($id)
    {
        // Check if there is no source related to this receipt
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."source ";
        $sql .= "WHERE fk_origine = " . $this->id;
        $sql .= " AND fk_type_source = 3";
        $resql = $this->db->query($sql);
        $num = $this->db->num_rows($resql);
        if ($num > 0)
        {
            // A source has been found related to this receipt
            $this->error = 'Ce reçu fiscal est lié à une source';
            return 0;
        }

        // Check if the line id corresponds to this receipt
        if (!array_key_exists($id, $this->lines))
        {
            $this->error = "Ligne de produit invalide pour ce reçu fiscal";
            return 0;
        }

		$sqlRowDet = "SELECT qty,valeur,rowid FROM ".MAIN_DB_PREFIX."emprunt_det ";
		$sqlRowDet .= "WHERE rowid = ".$id;
        $resqlRowDet = $this->db->query($sqlRowDet);
		$objRowDet =  $this->db->fetch_object($resqlRowDet);

		$sqlMontant = "UPDATE " . MAIN_DB_PREFIX . "emprunt SET montant = montant - ".($objRowDet->valeur*$objRowDet->qty)." WHERE rowid=" . $this->id;
		$this->db->query($sqlMontant);

        // Delete line from database
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."emprunt_det ";
        $sql .= "WHERE rowid = ".$id;
        $resql = $this->db->query($sql);
        if (!$resql) return 0;
        else 
        {
            $this->db->commit();
            return 1;
        }


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

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->materiel->emprunt->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->materiel->emprunt->emprunt_advance->validate))))
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
			$sql .= " SET status = ".self::STATUS_VALIDATED;
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
				$result = $this->call_trigger('EMPRUNT_VALIDATE', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
		}

		// if (!$error) {
		// 	$this->oldref = $this->ref;

		// 	// Rename directory if dir was a temporary ref
		// 	if (preg_match('/^[\(]?PROV/i', $this->ref)) {
		// 		// Now we rename also files into index
		// 		$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'emprunt/".$this->db->escape($this->newref)."'";
		// 		$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'emprunt/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
		// 		$resql = $this->db->query($sql);
		// 		if (!$resql) {
		// 			$error++; $this->error = $this->db->lasterror();
		// 		}

		// 		// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
		// 		$oldref = dol_sanitizeFileName($this->ref);
		// 		$newref = dol_sanitizeFileName($num);
		// 		$dirsource = $conf->materiel->dir_output.'/emprunt/'.$oldref;
		// 		$dirdest = $conf->materiel->dir_output.'/emprunt/'.$newref;
		// 		if (!$error && file_exists($dirsource)) {
		// 			dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

		// 			if (@rename($dirsource, $dirdest)) {
		// 				dol_syslog("Rename ok");
		// 				// Rename docs starting with $oldref with $newref
		// 				$listoffiles = dol_dir_list($conf->materiel->dir_output.'/emprunt/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
		// 				foreach ($listoffiles as $fileentry) {
		// 					$dirsource = $fileentry['name'];
		// 					$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
		// 					$dirsource = $fileentry['path'].'/'.$dirsource;
		// 					$dirdest = $fileentry['path'].'/'.$dirdest;
		// 					@rename($dirsource, $dirdest);
		// 				}
		// 			}
		// 		}
		// 	}
		// }

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

	public function setStatus($statusid)
    {
        $sql = "UPDATE ".MAIN_DB_PREFIX."emprunt ";
        $sql .= "SET status = {$statusid} ";
        $sql .= "WHERE rowid = {$this->id}";
        $resql = $this->db->query($sql);
        if (!$resql)
        {
            $this->error = "Query error";
            return 0;
        }
        else 
        {
            $this->db->commit();
            return 1;
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

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'EMPRUNT_UNVALIDATE');
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

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->materiel->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->materiel->materiel_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'EMPRUNT_CANCEL');
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

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->materiel->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->materiel->materiel_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'EMPRUNT_REOPEN');
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

		$label = img_picto('', $this->picto).' <u>'.$langs->trans("Emprunt").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = dol_buildpath('/materiel/emprunt_card.php', 1).'?id='.$this->id;

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
				$label = $langs->trans("ShowEmprunt");
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
				//$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				$result .= talm_img_object(($notooltip ? '' : $label), 'emprunt', ($notooltip ? 'class="paddingright"'.$style : 'class="paddingright classfortooltip"'.$style), 0, 0, $notooltip ? 0 : 1);
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
		$hookmanager->initHooks(array('empruntdao'));
		$parameters = array('id'=>$this->id, 'getnomurl' => &$result);
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
			//$langs->load("materiel@materiel");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Brouillon');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Validé');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Brouillon');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Validé');
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
		$sql = "SELECT rowid,";
		$sql .= " date_creation as datec, tms as datem,";
		$sql .= " fk_user_creat, fk_user_modif";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);

				$this->id = $obj->rowid;

				$this->user_creation_id = $obj->fk_user_creat;
				$this->user_modification_id = $obj->fk_user_modif;
				if (!empty($obj->fk_user_valid)) {
					$this->user_validation_id = $obj->fk_user_valid;
				}
				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = empty($obj->datem) ? '' : $this->db->jdate($obj->datem);
				if (!empty($obj->datev)) {
					$this->date_validation   = empty($obj->datev) ? '' : $this->db->jdate($obj->datev);
				}
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

		$objectline = new EmpruntLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_emprunt = '.((int) $this->id)));

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
		$langs->load("materiel@materiel");

		if (empty($conf->global->MATERIEL_EMPRUNT_ADDON)) {
			$conf->global->MATERIEL_EMPRUNT_ADDON = 'mod_emprunt_standard';
		}

		if (!empty($conf->global->MATERIEL_EMPRUNT_ADDON)) {
			$mybool = false;

			$file = $conf->global->MATERIEL_EMPRUNT_ADDON.".php";
			$classname = $conf->global->MATERIEL_EMPRUNT_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir) {
				$dir = dol_buildpath($reldir."core/modules/materiel/");

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

		$langs->load("materiel@materiel");

		if (!dol_strlen($modele)) {
			$modele = 'standard_emprunt';

			if (!empty($this->model_pdf)) {
				$modele = $this->model_pdf;
			} elseif (!empty($conf->global->EMPRUNT_ADDON_PDF)) {
				$modele = $conf->global->EMPRUNT_ADDON_PDF;
			}
		}

		$modelpath = "core/modules/materiel/doc/";

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
}
