# suppression des droits de conges
DELETE FROM conges_groupe_resp WHERE gr_login = 'conges';
DELETE FROM conges_groupe_grd_resp WHERE ggr_login = 'conges';
UPDATE conges_users SET u_resp_login = NULL WHERE u_login = 'conges';

# suppression des artt de conges
DELETE FROM conges_artt WHERE a_login = 'conges';

# suppression du user conges
DELETE FROM conges_users WHERE u_login = 'conges';
