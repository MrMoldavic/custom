CREATE TABLE `llx_c_classe_etablissement` (
  `rowid` int(11) NOT NULL,
  `fk_etablissement` int(11) NOT NULL,
  `classe` varchar(255) NOT NULL,
  `prof_principal` varchar(255) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;