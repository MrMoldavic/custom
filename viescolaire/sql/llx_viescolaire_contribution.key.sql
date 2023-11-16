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


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_viescolaire_contribution ADD INDEX idx_viescolaire_contribution_rowid (rowid);
ALTER TABLE llx_viescolaire_contribution ADD INDEX idx_viescolaire_contribution_ref (ref);
ALTER TABLE llx_viescolaire_contribution ADD INDEX idx_viescolaire_contribution_fk_soc (fk_soc);
ALTER TABLE llx_viescolaire_contribution ADD INDEX idx_viescolaire_contribution_fk_project (fk_project);
ALTER TABLE llx_viescolaire_contribution ADD CONSTRAINT llx_viescolaire_contribution_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_viescolaire_contribution ADD INDEX idx_viescolaire_contribution_status (status);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_viescolaire_contribution ADD UNIQUE INDEX uk_viescolaire_contribution_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_viescolaire_contribution ADD CONSTRAINT llx_viescolaire_contribution_fk_field FOREIGN KEY (fk_field) REFERENCES llx_viescolaire_myotherobject(rowid);
