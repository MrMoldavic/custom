CREATE TABLE llx_salles (
  rowid int(11) NOT NULL PRIMARY KEY NOT NULL,
  salle varchar(255) NOT NULL,
  fk_college int(11) NOT NULL,
  nom_complet varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

