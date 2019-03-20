 UPDATE conges_solde_user SET su_solde = su_solde - su_reliquat
 DELETE FROM `conges_config` WHERE conf_nom = 'calcul_auto_jours_feries_france';
