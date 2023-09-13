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


CREATE TABLE llx_creneau(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer(11) AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_dispositif integer(11) NOT NULL, 
	fk_type_classe integer(11) NOT NULL, 
	fk_niveau integer(11) NOT NULL,
	fk_instrument_enseigne integer(11) NOT NULL,
	fk_prof_1 integer(11) NOT NULL,
	fk_prof_2 integer(11) NULL,  
	fk_prof_3 integer(11) NULL,   
	professeurs varchar(255),
	eleves varchar(255),
	nombre_places integer(11) NOT NULL,
	fk_annee_scolaire integer(11) NULL, 
	heure_debut varchar(255) NOT NULL,
	minutes_debut integer(11) NOT NULL,
	heure_fin varchar(255) NOT NULL,
	minutes_fin integer(11) NOT NULL,
	jour varchar(255) NOT NULL,
	fk_salle integer(11) NULL,  
	infos_creneau varchar(255),
	commentaires text NULL, 
	nom_groupe varchar(255) NULL, 
	nom_creneau varchar(255) NOT NULL, 
	note_public text, 
	note_private text,
	date_creation datetime, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	last_main_doc varchar(255), 
	model_pdf varchar(255), 
	status integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
