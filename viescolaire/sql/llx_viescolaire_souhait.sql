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


CREATE TABLE llx_souhait(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	souhait varchar(255) NOT NULL,
  	fk_eleve int(11) NOT NULL,
  	fk_type_classe int(11) NOT NULL,
  	fk_instru_enseigne int(11) NOT NULL,
	fk_niveau int(11) NOT NULL,
	fk_annee_scolaire int(11) NOT NULL,
  	details text DEFAULT NULL,
  	disponibilite text NOT NULL,
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	last_main_doc varchar(255), 
	model_pdf varchar(255),
	status int(11) DEFAULT 0
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
