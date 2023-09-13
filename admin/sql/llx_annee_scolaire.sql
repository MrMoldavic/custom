CREATE TABLE llx_c_annee_scolaire(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	annee varchar(255) NOT NULL, 
    annee_actuelle smallint NOT NULL,
	active smallint NOT NULL

	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;