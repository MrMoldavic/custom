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


CREATE TABLE llx_parents(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	firstname varchar(255) NOT NULL,
	lastname varchar(255) NOT NULL,
	address varchar(255), 
	zipcode varchar(255), 
	town varchar(255), 
	phone varchar(255), 
	mail varchar(255), 
	csp int(12), 
	quotient_familial int(12),
    fk_type_parent int(12) NOT NULL,
    contact_preferentiel tinyint(1) NOT NULL,
    description text,
	fk_famille int(12),
    fk_adherent int(12),
    fk_tiers int(12),
    date_creation datetime NOT NULL,
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL, 
	fk_user_create integer, 
	fk_user_modif integer, 
	status integer
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
