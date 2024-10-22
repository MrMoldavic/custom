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


CREATE TABLE llx_emprunteur(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	nom varchar(255) NOT NULL, 
	prenom varchar(255) NOT NULL, 
	societe varchar(255) NULL, 
	adress varchar(255) NULL, 
	zipcode varchar(255) NULL, 
	town varchar(255) NULL, 
	phone varchar(255) NULL, 
	email varchar(255) NULL, 
	notes varchar(255) NULL, 
	date_creation datetime NOT NULL, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	status integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
