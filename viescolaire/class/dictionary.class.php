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

	public function fetchByDictionary(string $table, array $parameters,$id = null, $column = null, string $andWhere = '')
	{
		// Construction de la requête SQL
		$column ??= '';
		$id ??= 0;
		$sanitizedTable = MAIN_DB_PREFIX . $this->db->sanitize($this->db->escape($table));
		$sanitizedParams = array_map(fn($param) => $this->db->sanitize($this->db->escape($param)), $parameters);
		$sql = 'SELECT ' . implode(', ', $sanitizedParams) . ' FROM ' . $sanitizedTable;

		// Ajout de conditions WHERE si nécessaire
		$conditions = [];
		if ($id && $column) {
			$conditions[] = $this->db->sanitize($this->db->escape($column)) . ' = ' . $this->db->sanitize($this->db->escape($id));
		}
		if ($andWhere) {
			$conditions[] = $andWhere;
		}
		if (!empty($conditions)) {
			$sql .= ' WHERE ' . implode(' AND ', $conditions);
		}

		// Exécution de la requête SQL
		$resql = $this->db->query($sql);

		if (!$resql) {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . implode(',', $this->errors), LOG_ERR);
			return -1;
		}

		// Traitement des résultats
		$num = $this->db->num_rows($resql);
		$records = [];

		while ($obj = $this->db->fetch_object($resql)) {
			// Utiliser une clé unique pour chaque objet, par exemple `id` ou `rowid`.
			$key = property_exists($obj, 'id') ? $obj->id : (property_exists($obj, 'rowid') ? $obj->rowid : null);
			if ($key !== null) {
				$records[$key] = $obj;
			} else {
				// Ajouter un objet sans clé spécifique si 'id' ou 'rowid' n'existent pas.
				$records[] = $obj;
			}
		}

		$this->db->free($resql);
		return $num === 1 ? reset($records) : $records;
	}


}


