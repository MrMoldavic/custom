-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


CREATE TABLE llx_famille(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	identifiant_famille varchar(255),
	antenne integer,
	enfants varchar(255),
	quotient_familial integer NULL,
	nom_parent_1 varchar(255) NOT NULL, 
	prenom_parent_1 varchar(255) NOT NULL,
	csp_parent_1 integer NULL,
	tel_parent_1 varchar(255) NULL,
	mail_parent_1 varchar(255) NULL,
	adresse_parent_1 varchar(255) NULL,
	nom_parent_2 varchar(255) NULL, 
	prenom_parent_2 varchar(255) NULL,
	csp_parent_2 integer NULL,
	tel_parent_2 varchar(255) NULL,
	mail_parent_2 varchar(255) NULL,
	adresse_parent_2 varchar(255) NULL,
	description text, 
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	last_main_doc varchar(255), 
	model_pdf varchar(255)
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
