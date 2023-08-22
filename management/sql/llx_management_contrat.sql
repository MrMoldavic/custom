-- Copyright (C) 2023 Baptiste Diodati <baptiste.diodati@gmail.com>
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


CREATE TABLE llx_management_contrat(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(255) NOT NULL,
	cote integer NOT NULL,
	fk_agent integer NOT NULL, 
	fk_statut integer NOT NULL, 
	fk_type_contrat integer NOT NULL, 
	date_debut date NOT NULL, 
	date_fin date NULL, 
	duree varchar(255) NOT NULL, 
	nombre_mois integer NULL, 
	salaire_brut varchar(255) NULL, 
	salaire_net varchar(255) NULL, 
	ancienne_ref varchar(255) NULL, 
	alternance tinyint(4) NULL,
	aide_contrat varchar(255) NULL,
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	last_main_doc varchar(255), 
	import_key varchar(14), 
	model_pdf varchar(255), 
	status integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
