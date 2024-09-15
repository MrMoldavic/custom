<?php


global $conf, $user, $langs, $db;
const DOL_DOCUMENT_ROOT = '/Applications/XAMPP/xamppfiles/htdocs/php/En cours annonces/intranet.tousalamusique.com/';
const MAIN_DB_PREFIX = 'llx_';




require_once dirname(__FILE__).'/../../../../core/class/commonobject.class.php';
require_once dirname(__FILE__).'/../../../../core/db/DoliDB.class.php';
require_once dirname(__FILE__).'/../../class/eleve.class.php';


class SouhaitTest extends PHPUnit\Framework\TestCase
{
	/**
	 * Constructor
	 * We save global variables into local variables
	 *
	 * @return EmpruntTest
	 */
	public function __construct()
	{
		parent::__construct('name',[],'');

		global $db;
		$this->savdb = $db;
	}

    public function testFetchSouhait()
    {
		global $db;
		$eleveClass = new Eleve($db);
		$eleveClass->fetch(2);

        return $this->assertEquals('kjzaefzef',$eleveClass->nom);
    }
}
