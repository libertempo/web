ALTER TABLE `conges_type_absence`
    ADD `type_natif` TINYINT(1) NOT NULL DEFAULT 0
;

UPDATE `conges_type_absence` SET type_natif = 1 WHERE ta_short_libelle IN ('cp', 'rtt', 'fo', 'mi', 'ab', 'mal');
