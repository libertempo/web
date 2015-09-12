<?php

$local_scripts=curPage();

if($local_scripts[0] == "hr_index.php")
 {
    $pattern="&onglet=";
    $i = 0;
    $which_onglet=explode($pattern,$local_scripts[1]);
    echo '<LINK href="'.ROOT_PATH.'include/plugins/cet/css/bulles.css" title="bulles" rel="stylesheet" type="text/css">';
    if(empty($which_onglet) || $which_onglet[1] == "page_principale" || $which_onglet[0] == $local_scripts[1])
        {
        $select_all_cet = "SELECT u_nom,u_prenom,pc_jours_demandes,pc_u_login,pc_requested_date,pc_comments FROM conges_users,conges_plugin_cet WHERE `conges_users`.`u_login`=`conges_plugin_cet`.`pc_u_login`";
        $exec_all_cet = \includes\SQL::query($select_all_cet);
        echo "<script>
        $(document).ready(function(){
            $('th:last-child').after('<th>CET</th>');";
        if($exec_all_cet->num_rows !=0)
            {
            while($user_cet=$exec_all_cet->fetch_array())
                {
                echo "
                var tableRow = $('tr:has(td:contains(\"".$user_cet['u_nom']."\")):has(td:contains(\"".$user_cet['u_prenom']."\"))');
                tableRow.css('color','blue');
                var jours_demandes = Math.round(".$user_cet['pc_jours_demandes'].")
                tableRow.append('<td class=\"cet\" id=\"cet_".$user_cet['pc_u_login']."_".$i."\"><b>'+jours_demandes+'</b><span class=\"cet_detail\">details : ".$user_cet['pc_requested_date'].". ".$user_cet['pc_comments']."</span></td>');
                addHover('".$user_cet['pc_u_login']."_".$i."');
                ";
                $i++;
                }
            }

        echo "
        function addHover(uid) {
        var td_id = '#cet_'+uid;
            $(td_id).mouseover(function(){
                $(this).children('span').show();
              }).mouseout(function(){
                $(this).children('span').hide();
              });
        }
        });
        </script>";
        }
 }


?>
