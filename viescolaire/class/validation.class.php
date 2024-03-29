<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';

class Validation extends CommonObject
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

	/**
	 * Nettoie une chaine de caractère
	 *
	 * @param string $classType classe à instancier
	 * @param int $id $id à fetch
	 * @param string $errorMessage message de sortie

	 * @return boolean
	 */
	public function validateClass($classType, $id) {

		$classInstance = new $classType($this->db);
		$classInstance->fetch((int)$id);

		if (!$classInstance->id) {
			return false;
		}

		return true;
	}

	public function isValidLength($value, $min, $max)
	{
		$length = strlen((string)$value);
		if ($length < $min || $length > $max) {
			return false;
		}

		return true;
	}


}


