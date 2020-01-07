# Création champ option type absence
ALTER TABLE `conges_type_absence` ADD `ta_actif` TINYINT(1) NOT NULL DEFAULT '1';
# Retrait option globale congés exceptionnels
DELETE FROM `conges_config` WHERE `conf_nom` = 'gestion_conges_exceptionnels';