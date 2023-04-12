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


CREATE TABLE llx_dispositif (
  rowid int(11) PRIMARY KEY NOT NULL,
  nom varchar(255) NOT NULL,
  fk_etablissement int(11) NOT NULL,
  fk_type_classe int(11) NOT NULL,
  fk_prof int(11) NOT NULL,
  note_public text DEFAULT NULL,
  note_private text DEFAULT NULL,
  date_creation datetime NOT NULL,
  tms timestamp NULL DEFAULT NULL,
  fk_user_creat int(11) NOT NULL,
  fk_user_modif int(11) DEFAULT NULL,
  last_main_doc varchar(255) NOT NULL,
  model_pdf varchar(255) NOT NULL,
  description text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
