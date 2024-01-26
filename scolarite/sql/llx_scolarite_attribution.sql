-- Copyright (C) 2022 Baptiste Diodati
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


CREATE TABLE llx_scolarite_attribution(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_cle integer NOT NULL,
	fk_user_pret integer NOT NULL,
	fk_annee_scolaire integer NOT NULL,
	commentaire text, 
	date_debut_pret datetime NOT NULL, 
	date_fin_pret datetime, 
	numero_contrat varchar(255),
	etat_contrat varchar(255),
	note_public text, 
	note_private text, 
	date_creation datetime, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	last_main_doc varchar(255), 
	import_key varchar(14), 
	model_pdf varchar(255), 
	status integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
