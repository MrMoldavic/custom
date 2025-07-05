-- Copyright (C) 2025 Baptiste Diodati <baptiste.diodati@tousalamusique.com>
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


CREATE TABLE llx_leboncoin_seller(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	firstname varchar(128) NULL,
	lastname varchar(128) NULL,
	pseudo varchar(128) NOT NULL,
	account_url varchar(255) NOT NULL,
	phone varchar(20) NULL,
	email varchar(255) NULL,
	address varchar(255) NULL,
	zip varchar(25) NULL,
	town varchar(50) NULL,
	note_public text NULL, 
	note_private text NULL, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	status integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
