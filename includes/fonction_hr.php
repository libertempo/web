<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les 
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE, 
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation 
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps 
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation, 
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*************************************************************************************************
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either 
version 2 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*************************************************************************************************/


defined( '_PHP_CONGES' ) or die( 'Restricted access' );


// renvoit un tableau de tableau contenant les informations de tous les users dont $login est HR responsable
function recup_infos_all_users_du_hr($login, $DEBUG=FALSE)
{
    $tab=array();

    $list_groupes_double_validation=get_list_groupes_double_valid($DEBUG);
    if( $DEBUG ) { echo "list_groupes_double_validation :<br>\n"; print_r($list_groupes_double_validation); echo "<br><br>\n";}

    //$sql = "SELECT u_login FROM conges_users WHERE u_login!='conges' AND u_login!='admin' ORDER BY u_login";
    $sql1 = "SELECT u_login FROM conges_users WHERE u_login!='conges' AND u_login!='admin' ORDER BY u_nom";

    $ReqLog = \includes\SQL::query($sql1) ;

    while ($resultat = $ReqLog->fetch_array())
    {
        $tab_user=array();
        $sql_login=$resultat["u_login"];

        $tab[$sql_login] = recup_infos_du_user($sql_login, $list_groupes_double_validation, $DEBUG);
    }

    return $tab ;
}


// recup de la liste de TOUS les users pour le responsable RH
// renvoit une liste de login entre quotes et séparés par des virgules
function get_list_all_users_du_hr($resp_login, $DEBUG=FALSE)
{

	$list_users="";

	$sql1="SELECT DISTINCT(u_login) FROM conges_users WHERE u_login!='conges' AND u_login!='admin'  ORDER BY u_nom  ";

	$ReqLog1 = \includes\SQL::query($sql1);

	while ($resultat1 = $ReqLog1->fetch_array())
	{
		$current_login=$resultat1["u_login"];
		if($list_users=="")
			$list_users="'$current_login'";
		else
			$list_users=$list_users.", '$current_login'";
	}
	

	if( $DEBUG ) { echo "list_users = $list_users<br>\n" ;}

	return $list_users;
}
