<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';

class Dictionary extends CommonObject
{
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
	}

	public function returnActualyear()
	{
		$sql = "SELECT ";
		$sql .= "rowid, annee, annee_actuelle ";
		$sql .= "FROM ".MAIN_DB_PREFIX."c_annee_scolaire ";
		$sql .= "WHERE annee_actuelle = 1";

		$resql = $this->db->query($sql);
		if($resql)
		{
			$record = $this->db->fetch_object($resql);
			$this->db->free($resql);

			return $record;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}


	/**
	 * Fetch object from the database
	 *
	 * @param string $table dictionary to fetch from
	 * @param array $parameters array of column to fetch
	 * @param int $id id of item requested for direct fetch
	 * @param string $column string column requested for direct fetch
	 * @return int <0 if KO, >0 if OK
	 */
	public function fetchByDictionary(string $table, array $parameters, int $id = 0, string $column = '', string $andWhere = "")
	{
		$sql = "SELECT ";
		for($i=0;$i<count($parameters);$i++)
		{
			$sql .= $this->db->sanitize($this->db->escape($parameters[$i])).', ';
		}
		$sql = substr($sql, 0, -2);
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->db->sanitize($this->db->escape($table));

		if($id)
		{
			$sql .= " WHERE ".$this->db->sanitize($this->db->escape($column))." = ".$this->db->sanitize($this->db->escape($id));
		}
		if($andWhere)
		{
			$sql .= $andWhere;
		}

		$resql = $this->db->query($sql);


		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			if($num == 1)
			{
				$records = $this->db->fetch_object($resql);
			}
			else
			{
				while ($i < ($limit ? min($limit, $num) : $num)) {

					$obj = $this->db->fetch_object($resql);
					//$record = new self($this->db);
					//$record->setVarsFromFetchObj($obj);

					$records[$obj->id ? : $obj->rowid] = $obj;

					$i++;
				}
			}
			$this->db->free($resql);
			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

}


