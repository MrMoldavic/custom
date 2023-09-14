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
 * \file        class/creneau.class.php
 * \ingroup     scolarite
 * \brief       This file is a CRUD class file for Creneau (Create/Read/Update/Delete)
 */

//   ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for Creneau
 */
class Creneau extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'scolarite';

	public $place_restantes_cours = 0;

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'creneau';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'creneau';

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
	 * @var string String with name of icon for creneau. Must be the part after the 'object_' into object_creneau.png
	 */
	public $picto = 'creneau@scolarite';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 4;
	const STATUS_CANCELED = 9;


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
		'rowid' => array('type'=>'integer(11) ', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>2, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
		'fk_dispositif' => array('type'=>'integer:Dispositif:custom/scolarite/class/dispositif.class.php:1', 'label'=>'Dispositif', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'searchall'=>1, 'css'=>'maxwidth300', 'validate'=>'1',),
		'fk_type_classe' => array('type'=>'sellist:type_classe:type', 'label'=>'Type de classe', 'enabled'=>'1', 'position'=>2, 'notnull'=>1, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth300', 'validate'=>'1',),
		'fk_instrument_enseigne' => array('type'=>'sellist:c_instrument_enseigne:instrument', 'label'=>'Instrument enseigné', 'enabled'=>'1', 'position'=>1, 'notnull'=>0, 'visible'=>1, 'help'=>"Laissez vide si groupe",),
		'fk_niveau' => array('type'=>'sellist:c_niveaux:niveau', 'label'=>'Niveau du cours/groupe', 'enabled'=>'1', 'position'=>2, 'notnull'=>1, 'visible'=>3, 'help'=>"Niveau des élèves dans le groupe",),
		'fk_prof_1' => array('type'=>'integer:Agent:custom/management/class/agent.class.php:1:(t.status!=9)', 'label'=>'Professeur n°1', 'enabled'=>'1', 'position'=>5, 'notnull'=>0, 'visible'=>1, 'css'=>'maxwidth200',),
		'fk_prof_2' => array('type'=>'integer:Agent:custom/management/class/agent.class.php:1:(t.status!=9)', 'label'=>'Professeur n°2', 'enabled'=>'1', 'position'=>6, 'notnull'=>0, 'visible'=>1, 'css'=>'maxwidth200',),
		'fk_prof_3' => array('type'=>'integer:Agent:custom/management/class/agent.class.php:1:(t.status!=9)', 'label'=>'Professeur n°3', 'enabled'=>'1', 'position'=>7, 'notnull'=>0, 'visible'=>1, 'css'=>'maxwidth200',),
		'professeurs' => array('type'=>'varchar(255)', 'label'=>'Professeurs', 'enabled'=>'1', 'position'=>4, 'notnull'=>0, 'visible'=>2, 'css'=>'minwidth200', 'validate'=>'1',),
		'eleves' => array('type'=>'varchar(255)', 'label'=>'Éleves', 'enabled'=>'1', 'position'=>4, 'notnull'=>0, 'visible'=>2, 'css'=>'minwidth200', 'validate'=>'1',),
		'infos_creneau' => array('type'=>'varchar(255)', 'label'=>'Infos créneau', 'enabled'=>'1', 'position'=>3, 'notnull'=>0, 'visible'=>2, 'searchall'=>1, 'css'=>'minwidth300', 'validate'=>'1',),
		'nombre_places' => array('type'=>'integer', 'label'=>'Nb élèves', 'enabled'=>'1', 'position'=>3, 'notnull'=>1, 'visible'=>1, 'css'=>'maxwidth400', 'validate'=>'1',),
		'fk_annee_scolaire' => array('type'=>'sellist:c_annee_scolaire:annee', 'label'=>'Année Scolaire', 'enabled'=>'1', 'position'=>7, 'notnull'=>1, 'visible'=>-1,),
		'heure_debut' => array('type'=>'sellist:c_heure:heure', 'label'=>'Heure de début', 'enabled'=>'1', 'position'=>40, 'notnull'=>1, 'visible'=>1, 'css'=>'minwidth100', 'help'=>"Format : 12:00 / 08:30 ", 'validate'=>'1',),
		'minutes_debut' => array('type'=>'varchar(255)', 'label'=>'Minutes de début', 'enabled'=>'1', 'position'=>41, 'notnull'=>1, 'visible'=>3, 'css'=>'minwidth100', 'help'=>"Laissez vide si heure pile", 'arrayofkeyval'=>array('00'=>'00', '15'=>'15', '30'=>'30', '45'=>'45'), 'validate'=>'1',),
		'heure_fin' => array('type'=>'sellist:c_heure:heure', 'label'=>'Heure de fin', 'enabled'=>'1', 'position'=>42, 'notnull'=>1, 'visible'=>1, 'css'=>'minwidth100', 'help'=>"Format : 12:00 / 08:30 ", 'validate'=>'1',),
		'minutes_fin' => array('type'=>'varchar(255)', 'label'=>'Minutes de fin', 'enabled'=>'1', 'position'=>43, 'notnull'=>1, 'visible'=>3, 'css'=>'minwidth100', 'help'=>"Laissez vide si heure pile", 'arrayofkeyval'=>array('00'=>'00', '15'=>'15', '30'=>'30', '45'=>'45'), 'validate'=>'1',),
		'jour' => array('type'=>'sellist:c_jour:jour:rowid::(active=1):rowid', 'label'=>'Jour de la semaine', 'enabled'=>'1', 'position'=>39, 'notnull'=>1, 'visible'=>1, 'searchall'=>1, 'css'=>'maxwidth300', 'cssview'=>'wordbreak', 'validate'=>'1',),
		'fk_salle' => array('type'=>'integer:Salle:custom/scolarite/class/salle.class.php:1', 'label'=>'Salle', 'enabled'=>'1', 'position'=>44, 'notnull'=>0, 'visible'=>3, 'searchall'=>1, 'css'=>'maxwidth200', 'cssview'=>'wordbreak',),
		'commentaires' => array('type'=>'text', 'label'=>'Commentaires', 'enabled'=>'1', 'position'=>45, 'notnull'=>0, 'visible'=>3, 'isameasure'=>'1', 'css'=>'maxwidth400', 'help'=>"Help text for quantity", 'validate'=>'1',),
		'nom_groupe' => array('type'=>'varchar(255)', 'label'=>'Nom du groupe', 'enabled'=>'1', 'position'=>1, 'notnull'=>0, 'visible'=>3, 'css'=>'maxwidth400', 'help'=>"Écriture : Nom pour un groupe seulement (nom de cours généré automatiquement)", 'validate'=>'1',),
		'nom_creneau' => array('type'=>'varchar(255)', 'label'=>'Nom du créneau', 'enabled'=>'1', 'position'=>0, 'notnull'=>0, 'visible'=>0, 'css'=>'maxwidth400', 'showoncombobox'=>'1', 'validate'=>'1',),
		'note_public' => array('type'=>'text', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>61, 'notnull'=>0, 'visible'=>0, 'cssview'=>'maxwidth400', 'validate'=>'1',),
		'note_private' => array('type'=>'text', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>62, 'notnull'=>0, 'visible'=>0, 'cssview'=>'maxwidth400', 'validate'=>'1',),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'last_main_doc' => array('type'=>'varchar(255)', 'label'=>'LastMainDoc', 'enabled'=>'1', 'position'=>600, 'notnull'=>0, 'visible'=>0,),
		'model_pdf' => array('type'=>'varchar(255)', 'label'=>'Model pdf', 'enabled'=>'1', 'position'=>1010, 'notnull'=>-1, 'visible'=>0,),
		'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>'1', 'position'=>2000, 'notnull'=>1, 'visible'=>2, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Brouillon', '1'=>'Valid&eacute;', '9'=>'Annul&eacute;'), 'validate'=>'1',),
	);
	public $rowid;
	public $fk_dispositif;
	public $fk_type_classe;
	public $fk_instrument_enseigne;
	public $fk_niveau;
	public $fk_prof_1;
	public $fk_prof_2;
	public $fk_prof_3;
	public $professeurs;
	public $eleves;
	public $infos_creneau;
	public $nombre_places;
	public $heure_debut;
	public $minutes_debut;
	public $heure_fin;
	public $minutes_fin;
	public $jour;
	public $fk_salle;
	public $commentaires;
	public $nom_groupe;
	public $nom_creneau;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $last_main_doc;
	public $model_pdf;
	public $status;
	public $fk_annee_scolaire;
	// END MODULEBUILDER PROPERTIES


	// If this object has a subtable with lines

	// /**
	//  * @var string    Name of subtable line
	//  */
	// public $table_element_line = 'scolarite_creneauline';

	// /**
	//  * @var string    Field with ID of parent key if this object has a parent
	//  */
	// public $fk_element = 'fk_creneau';

	// /**
	//  * @var string    Name of subtable class that manage subtable lines
	//  */
	// public $class_element_line = 'Creneauline';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	// protected $childtables = array();

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	// protected $childtablesoncascade = array('scolarite_creneaudet');

	// /**
	//  * @var CreneauLine[]     Array of subtable lines
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

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->scolarite->creneau->read) {
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
		if($this->fk_salle)
		{
			
			$existingCreneau = "SELECT rowid FROM ".MAIN_DB_PREFIX."creneau WHERE fk_salle=".$this->fk_salle." AND heure_debut=".$this->heure_debut." AND fk_annee_scolaire =".$this->fk_annee_scolaire." AND jour=".$this->jour;
			$resqlExistingCreneau = $this->db->query($existingCreneau);
			if($resqlExistingCreneau->num_rows > 0)
			{
				return setEventMessage('Désolé, cette salle est déjà utilisée à cet horaire.','errors');
			}
		}

		if($this->fk_prof_1 != NULL)
		{
			$profCours = "SELECT c.fk_prof_1,c.fk_prof_2,c.fk_prof_3,c.rowid FROM ".MAIN_DB_PREFIX."creneau as c WHERE c.heure_debut =".$this->heure_debut." AND c.jour=".$this->jour." AND fk_annee_scolaire =".$this->fk_annee_scolaire;
			$profCours .= " AND ((c.fk_prof_1=".$this->fk_prof_1." OR c.fk_prof_2=".$this->fk_prof_1." OR c.fk_prof_3=".$this->fk_prof_1.")";
			if($this->fk_prof_2 != NULL) $profCours .= " OR (c.fk_prof_1=".$this->fk_prof_2." OR c.fk_prof_2=".$this->fk_prof_2." OR c.fk_prof_3=".$this->fk_prof_2.")";
			if($this->fk_prof_3 != NULL) $profCours .= " OR (c.fk_prof_1=".$this->fk_prof_3." OR c.fk_prof_2=".$this->fk_prof_3." OR c.fk_prof_3=".$this->fk_prof_3.")";
			$profCours .= ")";
			$resqlProfsCours = $this->db->query($profCours);
	
			if($resqlProfsCours->num_rows > 0)
			{
				return setEventMessage('Désolé, un des professeur séléctionné à déjà cours à cette heure-ci.','errors');
			}
		}

		



		if(!$this->nom_groupe && !$this->fk_instrument_enseigne)
		{
			return setEventMessage('Nom de groupe ou un instrument enseigné obligatoire.','errors');
		}
		elseif(intval($this->nombre_places) == 0)
		{
			return setEventMessage('Veuillez choisir un nombre de place valide.','errors');
		}
		elseif(intval($this->fk_annee_scolaire) == 0)
		{
			return setEventMessage('Veuillez renseigner l\'année scolaire concernée.','errors');
		}
		elseif($this->nom_groupe && $this->fk_instrument_enseigne)
		{
			return setEventMessage('Veuillez choisir entre nom de groupe et instrument.','errors');
		}
		elseif(!$this->fk_niveau || !$this->heure_debut || !$this->heure_fin || !$this->jour || !$this->fk_type_classe)
		{
			return setEventMessage('Des données sont manquantes.','errors');
		}
		else
		{

		
			if(!$this->nom_groupe)
			{		
				$diminutif = "SELECT d.diminutif FROM ".MAIN_DB_PREFIX."etablissement as d WHERE d.rowid=".("(SELECT i.fk_etablissement FROM ".MAIN_DB_PREFIX."dispositif as i WHERE i.rowid =".$this->fk_dispositif.")");
				$resql = $this->db->query($diminutif);
				$object = $this->db->fetch_object($resql);
	
				$this->nom_creneau = $object->diminutif . '-';
	
				$instrument_enseigne = "SELECT i.instrument FROM ".MAIN_DB_PREFIX."c_instrument_enseigne as i WHERE i.rowid =".$this->fk_instrument_enseigne;
				$resql = $this->db->query($instrument_enseigne);
				$object = $this->db->fetch_object($resql);
	
				$this->nom_creneau .= $object->instrument . '-';
	
				$niveau = "SELECT n.niveau FROM ".MAIN_DB_PREFIX."c_niveaux as n WHERE n.rowid =".$this->fk_niveau;
				$resql = $this->db->query($niveau);
				$object = $this->db->fetch_object($resql);
	
				$this->nom_creneau .= $object->niveau . '-';
	
				$jour = "SELECT j.jour FROM ".MAIN_DB_PREFIX."c_jour as j WHERE j.rowid =".$this->jour;
				$resql = $this->db->query($jour);
				$object = $this->db->fetch_object($resql);
	
				$this->nom_creneau .= $object->jour . '-';
	
				$heure = "SELECT h.heure FROM ".MAIN_DB_PREFIX."c_heure as h WHERE h.rowid =".$this->heure_debut;
				$resql = $this->db->query($heure);
				$object = $this->db->fetch_object($resql);
	
				
				if(!$this->minutes_debut)
				{
					$this->nom_creneau .= $object->heure. 'h00-';
					$this->minutes_debut = "00";
				}
				else
				{
					$this->nom_creneau .= $object->heure. 'h'.$this->minutes_debut.'-';
				}

				if(!$this->minutes_fin)
				{
					$this->minutes_fin = "00";
				}
				
				if($this->fk_prof_1 != NULL)
				{
					$prof = "SELECT p.nom FROM ".MAIN_DB_PREFIX."management_agent as p WHERE p.rowid =".$this->fk_prof_1;
					$resql = $this->db->query($prof);
					$object = $this->db->fetch_object($resql);
				
					$this->nom_creneau .= $object->nom;
				}

				$this->status = 4;
			
			}
			else
			{
				
				$diminutif = "SELECT d.diminutif FROM ".MAIN_DB_PREFIX."etablissement as d WHERE d.rowid=".("(SELECT i.fk_etablissement FROM ".MAIN_DB_PREFIX."dispositif as i WHERE i.rowid =".$this->fk_dispositif.")");
				$resql = $this->db->query($diminutif);
				$object = $this->db->fetch_object($resql);
	
				$this->nom_creneau = $object->diminutif . '-'.$this->nom_groupe.'-';
	
				$niveau = "SELECT n.niveau FROM ".MAIN_DB_PREFIX."c_niveaux as n WHERE n.rowid =".$this->fk_niveau;
				$resql = $this->db->query($niveau);
				$object = $this->db->fetch_object($resql);
	
				$this->nom_creneau .= $object->niveau . '-';
	
				$jour = "SELECT j.jour FROM ".MAIN_DB_PREFIX."c_jour as j WHERE j.rowid =".$this->jour;
				$resql = $this->db->query($jour);
				$object = $this->db->fetch_object($resql);
	
				$this->nom_creneau .= $object->jour . '-';
	
				$heure = "SELECT h.heure FROM ".MAIN_DB_PREFIX."c_heure as h WHERE h.rowid =".$this->heure_debut;
				$resql = $this->db->query($heure);
				$object = $this->db->fetch_object($resql);

				if(!$this->minutes_debut)
				{
					$this->nom_creneau .= $object->heure. 'h00-';
					$this->minutes_debut = "00";
				}
				else
				{
					$this->nom_creneau .= $object->heure. 'h'.$this->minutes_debut.'-';
				}

				if(!$this->minutes_fin)
				{
					$this->minutes_fin = "00";
				}

				if($this->fk_prof_1 != NULL)
				{
					$prof = "SELECT p.nom FROM ".MAIN_DB_PREFIX."management_agent as p WHERE p.rowid =".$this->fk_prof_1;
					$resql = $this->db->query($prof);
					$object = $this->db->fetch_object($resql);
					
					$this->nom_creneau .= $object->nom;
				}
			}

			$affectation = "SELECT s.fk_souhait FROM ".MAIN_DB_PREFIX."affectation as s WHERE s.fk_creneau=".$this->id." AND date_fin IS NULL";
			$resqlAffectation = $this->db->query($affectation);

			foreach($resqlAffectation as $val)
			{
				$eleve = "SELECT e.nom,e.prenom,e.rowid FROM ".MAIN_DB_PREFIX."eleve as e WHERE e.rowid=".("(SELECT s.fk_eleve FROM ".MAIN_DB_PREFIX."souhait as s WHERE s.rowid =".$val['fk_souhait'].")");
				$resqlEleve = $this->db->query($eleve);

				foreach($resqlEleve as $res)
				{
					$this->eleves .= $res['rowid']."\n";
				}
			}
	
			if($this->fk_salle)
			{
				$salle = "SELECT s.salle FROM ".MAIN_DB_PREFIX."salles as s WHERE s.rowid =".$this->fk_salle;
				$resql = $this->db->query($salle);
				$object = $this->db->fetch_object($resql);
						
				$this->nom_creneau .= "-".$object->salle;
			}
			

			$professeur = "";

			if($this->fk_prof_1 != NULL)
			{
				$sqlProf1 = "SELECT prenom,nom,rowid FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid= ".$this->fk_prof_1;
				$resqlProf1 = $this->db->query($sqlProf1);
				$objProf1 = $this->db->fetch_object($resqlProf1);

				$professeur .= $objProf1->prenom.' '.$objProf1->nom.' ';
			}

			if($this->fk_prof_2 != NULL)
			{
				$sqlProf2 = "SELECT prenom,nom,rowid FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid= ".$this->fk_prof_2;
				$resqlProf2 = $this->db->query($sqlProf2);
				$objProf2 = $this->db->fetch_object($resqlProf2);

				$professeur .= $objProf2->prenom.' '.$objProf2->nom.' ';
			}
			
			if($this->fk_prof_3 != NULL)
			{
				$sqlProf3 = "SELECT prenom,nom,rowid FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid= ".$this->fk_prof_3;
				$resqlProf3 = $this->db->query($sqlProf3);
				$objProf3 = $this->db->fetch_object($resqlProf3);

				$professeur .= $objProf3->prenom.' '.$objProf3->nom;
			}

			$this->professeurs = $professeur;

			$this->status = 4;
		
			$resultcreate = $this->createCommon($user, $notrigger);
	
			// $resultvalidate = $this->validate($user, $notrigger);
	
			return $resultcreate;
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
					//var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
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
			$this->fetchLines();
		}

		$sql = "SELECT COUNT(*) as total FROM ".MAIN_DB_PREFIX."affectation WHERE fk_creneau=".$id;
		$sql .= " AND status = 4 AND DATE(NOW()) >= DATE(date_debut) AND (DATE(NOW()) <= DATE(date_fin) OR ISNULL(date_fin))";

		$resql = $this->db->query($sql);
		$object = $this->db->fetch_object($resql);

		$place_restantes_cours = $this->nombre_places - $object->total;
		
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
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
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

			if($this->fk_salle)
			{
				$existingCreneau = "SELECT rowid FROM ".MAIN_DB_PREFIX."creneau WHERE fk_salle=".$this->fk_salle." AND heure_debut=".$this->heure_debut." AND rowid!=".$this->id." AND fk_annee_scolaire =".$this->fk_annee_scolaire;
				$resqlExistingCreneau = $this->db->query($existingCreneau);
				if($resqlExistingCreneau->num_rows > 0)
				{
					return setEventMessage('Désolé, cette salle est déjà utilisée à cet horaire.','errors');
				}
			}

			if($this->fk_prof_1)
			{
				$profCours = "SELECT c.fk_prof_1,c.fk_prof_2,c.fk_prof_3,c.rowid FROM ".MAIN_DB_PREFIX."creneau as c WHERE c.heure_debut =".$this->heure_debut." AND c.jour=".$this->jour." AND fk_annee_scolaire =".$this->fk_annee_scolaire." AND rowid !=".$this->id;
				$profCours .= " AND ((c.fk_prof_1=".$this->fk_prof_1." OR c.fk_prof_2=".$this->fk_prof_1." OR c.fk_prof_3=".$this->fk_prof_1.")";
				if($this->fk_prof_2 != NULL) $profCours .= " OR (c.fk_prof_1=".$this->fk_prof_2." OR c.fk_prof_2=".$this->fk_prof_2." OR c.fk_prof_3=".$this->fk_prof_2.")";
				if($this->fk_prof_3 != NULL) $profCours .= " OR (c.fk_prof_1=".$this->fk_prof_3." OR c.fk_prof_2=".$this->fk_prof_3." OR c.fk_prof_3=".$this->fk_prof_3.")";
				$profCours .= ")";
				$resqlProfsCours = $this->db->query($profCours);
	
				if($resqlProfsCours->num_rows > 0)
				{
					return setEventMessage('Désolé, un des professeur séléctionné à déjà cours à cette heure-ci.','errors');
				}
			}
			

			if(!$this->nom_groupe && !$this->fk_instrument_enseigne)
			{
				return setEventMessage('Nom de groupe ou un instrument enseigné obligatoire.','errors');
			}
			elseif($this->nom_groupe && $this->fk_instrument_enseigne)
			{
				return setEventMessage('Veuillez choisir entre nom de groupe et instrument.','errors');
			}
			elseif(!$this->fk_niveau || !$this->heure_debut || !$this->heure_fin || !$this->jour || !$this->fk_type_classe)
			{
				return setEventMessage('Des données sont manquantes.','errors');
			}

			$affectation = "SELECT s.fk_souhait FROM ".MAIN_DB_PREFIX."affectation as s WHERE s.fk_creneau=".$this->id." AND date_fin IS NULL";
			$resqlAffectation = $this->db->query($affectation);

			$this->eleves = "";
			foreach($resqlAffectation as $val)
			{
				$eleve = "SELECT e.nom,e.prenom,e.rowid FROM ".MAIN_DB_PREFIX."eleve as e WHERE e.rowid=".("(SELECT s.fk_eleve FROM ".MAIN_DB_PREFIX."souhait as s WHERE s.rowid =".$val['fk_souhait'].")");
				$resqlEleve = $this->db->query($eleve);

				foreach($resqlEleve as $res)
				{
					$this->eleves .= $res['prenom'].' '.$res['nom'].' ';
				}
				
			}

			if($this->fk_instrument_enseigne == "0")
			{
				$this->nom_creneau = "";

				$diminutif = "SELECT d.diminutif FROM ".MAIN_DB_PREFIX."etablissement as d WHERE d.rowid=".("(SELECT i.fk_etablissement FROM ".MAIN_DB_PREFIX."dispositif as i WHERE i.rowid =".$this->fk_dispositif.")");
				$resql = $this->db->query($diminutif);
				$object = $this->db->fetch_object($resql);
	
				$this->nom_creneau .= $object->diminutif . '-' . $this->nom_groupe . '-';
					
			}
			else
			{
		
				$this->nom_creneau = "";

				$diminutif = "SELECT d.diminutif FROM ".MAIN_DB_PREFIX."etablissement as d WHERE d.rowid=".("(SELECT i.fk_etablissement FROM ".MAIN_DB_PREFIX."dispositif as i WHERE i.rowid =".$this->fk_dispositif.")");
				$resql = $this->db->query($diminutif);
				$object = $this->db->fetch_object($resql);
		
				$this->nom_creneau .= $object->diminutif . '-';

				$instrument_enseigne = "SELECT i.instrument FROM ".MAIN_DB_PREFIX."c_instrument_enseigne as i WHERE i.rowid =".$this->fk_instrument_enseigne;
				$resqlinstru = $this->db->query($instrument_enseigne);
				$object = $this->db->fetch_object($resqlinstru);
		
				$this->nom_creneau .= $object->instrument . '-';
				
			}	

				$this->professeurs = "";

				$niveau = "SELECT n.niveau FROM ".MAIN_DB_PREFIX."c_niveaux as n WHERE n.rowid =".$this->fk_niveau;
				$resql = $this->db->query($niveau);
				$object = $this->db->fetch_object($resql);

				$this->nom_creneau .= $object->niveau . '-';

				$jour = "SELECT j.jour FROM ".MAIN_DB_PREFIX."c_jour as j WHERE j.rowid =".$this->jour;
				$resql = $this->db->query($jour);
				$object = $this->db->fetch_object($resql);

				$this->nom_creneau .= $object->jour . '-';

				$heure = "SELECT h.heure FROM ".MAIN_DB_PREFIX."c_heure as h WHERE h.rowid =".$this->heure_debut;
				$resql = $this->db->query($heure);
				$object = $this->db->fetch_object($resql);


				if(!$this->minutes_debut)
				{
					$this->nom_creneau .= $object->heure. 'h00-';
					$this->minutes_debut = "00";
				}
				else
				{
					$this->nom_creneau .= $object->heure. 'h'.$this->minutes_debut;
				}

				if(!$this->minutes_fin)
				{
					$this->minutes_fin = "00";
				}

				if($this->fk_prof_1 != NULL)
				{
					$prof = "SELECT p.nom,p.prenom FROM ".MAIN_DB_PREFIX."management_agent as p WHERE p.rowid =".$this->fk_prof_1;
					$resql = $this->db->query($prof);
					$object = $this->db->fetch_object($resql);
					
					$this->nom_creneau .= "-".$object->nom;
					$professeur .= $object->prenom.' '.$object->nom.' ';
				}
				

				if($this->fk_salle)
				{
					$salle = "SELECT s.salle FROM ".MAIN_DB_PREFIX."salles as s WHERE s.rowid =".$this->fk_salle;
					$resql = $this->db->query($salle);
					$object = $this->db->fetch_object($resql);
							
					$this->nom_creneau .= "-".$object->salle;
				}


				if($this->fk_prof_2 != NULL)
				{
					$sqlProf2 = "SELECT prenom,nom,rowid FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid= ".$this->fk_prof_2;
					$resqlProf2 = $this->db->query($sqlProf2);
					$objProf2 = $this->db->fetch_object($resqlProf2);

					$professeur .= $objProf2->prenom.' '.$objProf2->nom.' ';
				}
			
				if($this->fk_prof_3 != NULL)
				{
					$sqlProf3 = "SELECT prenom,nom,rowid FROM ".MAIN_DB_PREFIX."management_agent WHERE rowid= ".$this->fk_prof_3;
					$resqlProf3 = $this->db->query($sqlProf3);
					$objProf3 = $this->db->fetch_object($resqlProf3);

					$professeur .= $objProf3->prenom.' '.$objProf3->nom;
				}
			
			
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

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scolarite->creneau->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scolarite->creneau->creneau_advance->validate))))
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
				$result = $this->call_trigger('CRENEAU_VALIDATE', $user);
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
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'creneau/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'creneau/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) {
					$error++; $this->error = $this->db->lasterror();
				}

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->scolarite->dir_output.'/creneau/'.$oldref;
				$dirdest = $conf->scolarite->dir_output.'/creneau/'.$newref;
				if (!$error && file_exists($dirsource)) {
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest)) {
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->scolarite->dir_output.'/creneau/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
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

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scolarite->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scolarite->scolarite_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'CRENEAU_UNVALIDATE');
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

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scolarite->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scolarite->scolarite_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'CRENEAU_CANCEL');
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

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scolarite->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scolarite->scolarite_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'CRENEAU_REOPEN');
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

		// $label = img_picto('', $this->picto).' <u>'.$langs->trans("Creneau").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		// $label .= '<br>';
		// $label .= '<b>'.$langs->trans('Ref').':</b> '.$this->nom_groupe;

		$url = dol_buildpath('/scolarite/creneau_card.php', 1).'?id='.$this->id;

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
				$label = $langs->trans("ShowCreneau");
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
			$result .= $this->nom_creneau;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('creneaudao'));
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
			//$langs->load("scolarite@scolarite");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Creneau actif');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Creneau actif');
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

		$objectline = new CreneauLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_creneau = '.((int) $this->id)));

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
		$langs->load("scolarite@scolarite");

		if (empty($conf->global->SCOLARITE_CRENEAU_ADDON)) {
			$conf->global->SCOLARITE_CRENEAU_ADDON = 'mod_creneau_standard';
		}

		if (!empty($conf->global->SCOLARITE_CRENEAU_ADDON)) {
			$mybool = false;

			$file = $conf->global->SCOLARITE_CRENEAU_ADDON.".php";
			$classname = $conf->global->SCOLARITE_CRENEAU_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir) {
				$dir = dol_buildpath($reldir."core/modules/scolarite/");

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

		$langs->load("scolarite@scolarite");

		if (!dol_strlen($modele)) {
			$modele = 'standard_creneau';

			if (!empty($this->model_pdf)) {
				$modele = $this->model_pdf;
			} elseif (!empty($conf->global->CRENEAU_ADDON_PDF)) {
				$modele = $conf->global->CRENEAU_ADDON_PDF;
			}
		}

		$modelpath = "core/modules/scolarite/doc/";

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


require_once DOL_DOCUMENT_ROOT.'/core/class/commonobjectline.class.php';

/**
 * Class CreneauLine. You can also remove this and generate a CRUD class for lines objects.
 */
class CreneauLine extends CommonObjectLine
{
	// To complete with content of an object CreneauLine
	// We should have a field rowid, fk_creneau and position

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
