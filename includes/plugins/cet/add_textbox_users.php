<?php

$local_scripts = curPage();

if($local_scripts[0] == "user_index.php")
 {
    $pattern="&onglet=";
    $which_onglet=explode($pattern,$local_scripts[1]);
    if(empty($which_onglet) || ( array_key_exists(1,$which_onglet) AND $which_onglet[1] == "nouvelle_absence") || $which_onglet[0] == $local_scripts[1])
        {
        $nb_days_cet  = getpost_variable("nb_days_cet");
        $comments_cet = getpost_variable("comments_cet");
        $user_login   = $_SESSION['userlogin'];
        if(!empty($nb_days_cet))
            {
            $text='Merci. Votre demande de '.$nb_days_cet.' jours va &ecirc;tre transmise.';
            alerte_mail($user_login, ":responsable:", "0", "cet_demande", FALSE);
            $query_insert_cet = 'INSERT INTO conges_plugin_cet(pc_jours_demandes,pc_comments,pc_u_login) VALUES("'.$nb_days_cet.'","'.$comments_cet.'","'.$user_login.'")';
            $exec_query_cet = \includes\SQL::query($query_insert_cet);
            }
        else
            { $text='<br /><form method="POST" name="form2">Nombre de jours demand&eacute;s pour alimenter votre CET : <input type="text" name="nb_days_cet"> Commentaires <input type="text" name="comments_cet"><input type="submit" /></form>'; }

        echo "<script>
        $(document).ready(function(){
            $('.nouvelle_absence').before('".$text."');
        });
        </script>";
        }
 }




