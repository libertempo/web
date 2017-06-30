<?php

define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';

include_once ROOT_PATH . 'fonctions_conges.php';
include_once INCLUDE_PATH . 'fonction.php';

// SERVER
$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

// récupération des valeurs par défaut
if (!empty($_POST)) {
    $nombreUtilisateurs = $_POST['nombreUtilisateurs'];
    $nombreGroupes = $_POST['nombreGroupes'];
    $nombreConges = $_POST['nombreConges'];
} else {
    $nombreUtilisateurs = 10;
    $nombreGroupes = 1;
    $nombreConges = 0;
}
$selectedNombreGroupes = array_fill(1, 3, '');
$selectedNombreGroupes[$nombreGroupes] = 'selected="selected"';

$selectedNombreConges = array_fill(0, 3, '');
$selectedNombreConges[$nombreConges] = 'selected="selected"';

$listeNoms = ["abel", "absolon", "achille", "adam", "adelaide", "adele", "adeline", "adolphe", "adrien", "adrienne", "agathe", "agnes", "aime", "aimee", "alain", "albert", "albertine", "alexandre", "alexandrie", "alexis", "alfred", "alice", "aline", "alison", "alphonse", "alphonsine", "amarante", "amaury", "ambre", "ambroise", "amedee", "amelie", "anais", "anastasie", "anatole", "andre", "andree", "angele", "angeline", "angelique", "anne", "annette", "anselme", "antoine", "antoinette", "apollinaire", "apolline", "ariane", "arianne", "arienne", "aristide", "arlette", "armand", "armel", "armelle", "arnaud", "arnaude", "aude", "auguste", "augustin", "aurele", "aurelie", "aurelien", "aurore", "avril", "axelle", "baptiste", "barbara", "barnabe", "barthelemy", "basile", "bastien", "baudouin", "beatrice", "benedicte", "benjamin", "benjamine", "benoit", "benoite", "bernadette", "bernard", "bernardine", "berthe", "bertrand", "blaise", "blanche", "boniface", "brice", "brigitte", "bruno", "camille", "carine", "carole", "caroline", "catherine", "cecile", "celeste", "celestin", "celestine", "celine", "cerise", "cesaire", "cesar", "chantal", "chante", "charles", "charline", "charlot", "charlotte", "chloe", "christelle", "christian", "christiane", "christianne", "christine", "christophe", "claire", "clarice", "clarisse", "claude", "claudette", "claudine", "clemence", "clement", "clementine", "clothilde", "colette", "colombain", "colombe", "constance", "constant", "constantin", "corentin", "corin", "corinne", "cosette", "cunegonde", "cyrille", "damien", "daniel", "daniele", "danielle", "david", "delphine", "denis", "denise", "dennis", "desire", "desiree", "diane", "dianne", "didier", "dieudonne", "dieudonnee", "dimitri", "diodore", "dion", "dominique", "donat", "donatien", "donatienne", "doriane", "dorothee", "edgar", "edgard", "edith", "edmond", "edouard", "edwige", "eleonore", "eliane", "elisabeth", "elise", "elodie", "eloi", "eloise", "emeline", "emile", "emilie", "emilien", "emma", "emmanuel", "emmanuelle", "eric", "ermenegilde", "esme", "esmee", "esther", "etienne", "eugene", "eugenie", "eulalie", "eustache", "evariste", "eve", "evette", "evrard", "fabien", "fabienne", "fabiola", "fabrice", "faustine", "felicie", "felicien", "felicienne", "felix", "ferdinand", "fernand", "fernande", "fiacre", "fifi", "firmin", "flavie", "florence", "florentin", "florette", "florian", "florianne", "francine", "franck", "françois", "françoise", "frederic", "frederique", "gabriel", "gabrielle", "gaetan", "gaetane", "gaspard", "gaston", "gautier", "genevieve", "geoffroi", "georges", "georgette", "georgine", "gerald", "gerard", "geraud", "germain", "germaine", "gervais", "gervaise", "ghislain", "ghislaine", "gigi", "gilbert", "gilberte", "gilles", "gisele", "giselle", "gisselle", "godelieve", "gratien", "gregoire", "guillaume", "gustave", "guy", "gwenaelle", "hannah", "hector", "helene", "heloise", "henri", "henriette", "herbert", "hercule", "hermine", "herve", "hilaire", "hippolyte", "honore", "honorine", "horace", "hortense", "hubert", "hugues", "humbert", "hyacinthe", "ignace", "ines", "irene", "irene", "irenee", "isabel", "isabelle", "isidore", "jacinthe", "jacqueline", "jacques", "jean", "jean", "jeanine", "jean", "jeanne", "jeannette", "jeannine", "jeannot", "jeremie", "jerome", "joachim", "joceline", "joel", "joelle", "jolie", "josee", "joseph", "josephe", "josephine", "josette", "josiane", "josue", "jourdain", "judith", "jules", "juliane", "julie", "julien", "julienne", "juliette", "juste", "justin", "justine", "lambert", "laure", "laurence", "laurent", "laurentine", "laurette", "lazare", "lea", "leandre", "leon", "leonard", "leonce", "leonie", "leonne", "leontine", "leopold", "liane", "lionel", "lisette", "loic", "lothaire", "louis", "louise", "loup", "luc", "lucas", "luce", "lucie", "lucien", "lucienne", "lucile", "lucille", "lucinde", "lucrece", "lunete", "lydie", "madeleine", "madeline", "manon", "marc", "marcel", "marceline", "marcelle", "marcellette", "marcellin", "marcelline", "margot", "marguerite", "marianne", "marie", "marielle", "mariette", "marin", "marine", "marise", "marius", "marthe", "martin", "martine", "mathieu", "mathilde", "mathis", "matthieu", "maurice", "maxime", "maximilien", "maximilienne", "melanie", "melissa", "michel", "michele", "micheline", "michelle", "mignon", "mirabelle", "mireille", "modeste", "modestine", "monique", "morgaine", "morgane", "muriel", "myriam", "nadia", "nadine", "narcisse", "natalie", "nathalie", "nazaire", "nicholas", "nicodeme", "nicolas", "nicole", "nicolette", "nina", "ninette", "ninon", "noe", "noel", "noella", "noelle", "noemie", "oceane", "odette", "odile", "odilon", "olivie", "olivier", "olympe", "onesime", "oriane", "orianne", "osanne", "ouida", "ozanne", "papillion", "pascal", "pascale", "pascaline", "paschal", "patrice", "patrick", "paul", "paule", "paulette", "pauline", "penelope", "perceval", "perrine", "philbert", "philibert", "philippe", "philippine", "pierre", "pierrick", "placide", "pons", "prosper", "quentin", "rachel", "rainier", "raoul", "raphael", "raphael", "raymond", "raymonde", "rebecca", "regine", "regis", "reine", "remi", "remy", "renard", "renaud", "rene", "renee", "reynaud", "richard", "robert", "roch", "rochelle", "rodolph", "rodolphe", "rodrigue", "roger", "roland", "rolande", "romain", "romaine", "rosalie", "rose", "roselle", "rosemonde", "rosette", "rosine", "roxane", "roxanne", "sabine", "sacha", "said", "salome", "samuel", "sandrine", "sarah", "sebastien", "sebastienne", "seraphine", "serge", "severin", "severine", "sibylle", "sidonie", "simon", "simone", "solange", "sophie", "stephane", "stephanie", "suzanne", "suzette", "sybille", "sylvain", "sylvaine", "sylvestre", "sylviane", "sylvianne", "sylvie", "tatienne", "telesphore", "theirn", "theo", "theodore", "theophile", "therese", "thibault", "thierry", "thomas", "timothee", "toinette", "toussaint", "tristan", "ulrich", "urbain", "valentin", "valentine", "valere", "valerie", "valery", "veronique", "vespasien", "victoire", "victor", "victorine", "vienne", "vincent", "violette", "virginie", "vivien", "vivienne", "xavier", "yanick", "yann", "yannic", "yannick", "yolande", "yseult", "yves", "yvette", "yvonne", "zacharie", "zephyrine", "zoe"];

$listeGroupes = [
    ["RH", "Ressources Humaines"],
    ["reseau", "Reseau"],
    ["logistique", "Logistique"],
    ["informatique", "Informatique"],
    ["devinfo", "Developpement Informatique"],
    ["comm ext", "Communication"],
    ["comm int", "Communication Interne"],
    ["marketing", "Marketing"],
    ["ventes", "Ventes"],
    ["formation", "Formation"],
    ["infirmerie", "Infirmerie"],
    ["commercial", "Commercial"],
    ["administration", "Administration"],
    ["direction", "Direction"],
    ["secretariat", "Secretariat"],
    ["entretien", "Service de menage"],
];
echo '
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Script génération jeu de données SQL</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="apple-touch-icon" href="../Public/Assets/Img/Favicons/apple-touch-icon.png">
        <link rel="apple-touch-icon" sizes="57x57" href="../Public/Assets/Img/Favicons/apple-touch-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="../Public/Assets/Img/Favicons/apple-touch-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="../Public/Assets/Img/Favicons/apple-touch-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="../Public/Assets/Img/Favicons/apple-touch-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="../Public/Assets/Img/Favicons/apple-touch-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="../Public/Assets/Img/Favicons/apple-touch-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="../Public/Assets/Img/Favicons/apple-touch-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="../Public/Assets/Img/Favicons/apple-touch-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="../Public/Assets/Img/Favicons/apple-touch-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="16x16" href="../Public/Assets/Img/Favicons/favicon-16x16.png">
        <link rel="icon" type="image/png" sizes="32x32" href="../Public/Assets/Img/Favicons/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="../Public/Assets/Img/Favicons/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="192x192" href="../Public/Assets/Img/Favicons/android-chrome-192x192.png">
        <link rel="manifest" href="../Public/Assets/Img/Favicons/manifest.json">
        <link rel="mask-icon" href="../Public/Assets/Img/Favicons/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#da532c">
        <meta name="msapplication-TileImage" ../contenPublic/Assets/Img/Favicons/mstile-144x144.png">
        <meta name="theme-color" content="#ffffff">

        <link type="text/css" href="../Public/Assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen,print">
        <link href="../Public/Assets/font-awesome/css/font-awesome.css" rel="stylesheet">
        <link type="text/css" href="../Public/Assets/Css/reboot.css" rel="stylesheet" media="screen,print">

        <script type="text/javascript" src="../Public/Assets/jquery/js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="../Public/Assets/Js/reboot.js"></script>
    </head>
    <body id="top" class="hbox connected">
        <aside id="toolbar">
            <section>
                <header class="main-header">
                    <i class="icon-ellipsis-vertical toolbar-toggle"></i>
                    <h2 class="brand"><a href="" title="Accueil"><img src="<../Public/Assets/Img/Libertempo64.png" alt="Libertempo"></a></h2>
                </header>
                <div class="tools">
                    <div class="profil-info">
                        <i class="fa fa-smile-o"></i>
                        <div class="wrapper">
                            <div class="user-info">
                                <div class="user-login">Admin</div>
                                <div class="user-name">
                                    <span class="firstname">Script</span>
                                    <span class="name">SQL</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
        <section id="content">
            <section class="vbox">
                <section id="scrollable">
                    <div class="wrapper bg-white">';

$return = '';
$return .= "<h2>Génération de jeu de données SQL</h2>";
$return .= "<form method='post' class='form-inline'>";
$return .= "<p><label for='nombreUtilisateurs'>Nombre d'utilisateurs: </label>";
$return .= "<input type='number' class='form-control' name='nombreUtilisateurs' id='nombreUtilisateurs' required value='$nombreUtilisateurs' min='3' max='" . count($listeNoms) . "' /></p>";

$return .= "<p><label for='nombreGroupes'>Nombre de groupes: </label>";
$return .= "<select class='form-control' name='nombreGroupes' id='nombreGroupes'>";
$return .= "<option value='1' $selectedNombreGroupes[1]>Peu</option>";
$return .= "<option value='2' $selectedNombreGroupes[2]>Moyen</option>";
$return .= "<option value='3' $selectedNombreGroupes[3]>Beaucoup</option>";
$return .= "</select></p>";

$return .= "<p><label for='nombreConges'>Nombre de congés: </label>";
$return .= "<select class='form-control' name='nombreConges' id='nombreConges'>";
$return .= "<option value='0' $selectedNombreConges[0]>Peu</option>";
$return .= "<option value='1' $selectedNombreConges[1]>Moyen</option>";
$return .= "<option value='2' $selectedNombreConges[2]>Beaucoup</option>";
$return .= "</select></p>";

$return .= "<input type='submit' class='btn btn-default' value='Générer' />";
$return .= "</form>";

echo $return;

function returnFloatWithDot($number, $decimals = 1)
{
    return number_format($number, $decimals, '.', '');
}

if (!empty($_POST)) {
    $nombreUtilisateurs = $_POST['nombreUtilisateurs'];
    $nombreGroupes = $_POST['nombreGroupes'];
    $nombreConges = $_POST['nombreConges'];
    $responsableGroupe = array(); // liste des responsable de groupe et des responsable

    // Empêche le nombre d'utilisateurs d'être supérieur au nombre de prénoms disponible
    if ($nombreUtilisateurs >= count($listeNoms)) {
        $nombreUtilisateurs = count($listeNoms);
    }
    $nombreGroupes = ceil($nombreUtilisateurs * $nombreGroupes / 10);

    if ($nombreGroupes >= count($listeGroupes)) {
        $nombreGroupes = count($listeGroupes);
    }

    $randKeysNom = array_rand($listeNoms, $nombreUtilisateurs);

    $sqlGroupe = "-- Contenu de la table `conges_groupe`\n\n";
    for ($i = 1; $i <= $nombreGroupes; $i++) {
        $doubleValidation = rand(0, 1);
        if ($doubleValidation) {
            $doubleValidation = 'Y';
        } else {
            $doubleValidation = 'N';
        }
        $sqlGroupe .= "INSERT INTO `conges_groupe` VALUES (" . $i . ", '" . $listeGroupes[$i - 1][0] . "', '" . $listeGroupes[$i - 1][1] . "', '" . $doubleValidation . "');\n";
    }

    $sqlGroupeUsers = "-- Contenu de la table `conges_groupe_users`\n\n";
    $currentGroupe = 1;
    for ($i = 0; $i < $nombreUtilisateurs; $i++) {
        $prenom = $listeNoms[$randKeysNom[$i]];
        $numeroGroupe = floor($i / ($nombreUtilisateurs / $nombreGroupes)) + 1;
        // Le premier utilisateur qui rentrera dans un nouveau groupe sera le responsable de ce groupe
        if ($currentGroupe !== $numeroGroupe) {
            $responsableGroupe[] = $prenom;
            $currentGroupe = $numeroGroupe;
        }
        $sqlGroupeUsers .= "INSERT INTO `conges_groupe_users` VALUES (" . $numeroGroupe . ", '" . $prenom . "');\n";
    }

    /*
    $sqlGroupeGrandResponsable = "-- Contenu de la table `conges_groupe_grd_resp`\n\n";
    INSERT INTO `conges_groupe_grd_resp` VALUES (2, 'pierre');
     */

    $sqlGroupeResponsable = "-- Contenu de la table `conges_groupe_resp`\n\n";
    for ($i = 0; $i < count($responsableGroupe); $i++) {
        $sqlGroupeResponsable .= "INSERT INTO `conges_groupe_resp` VALUES (" . $i . ", '" . $responsableGroupe[$i] . "');\n";
    }

    $sqlCongesUser = "-- Contenu de la table `conges_users`\n\n";
    $responsable = 'pierre';
    for ($i = 0; $i < $nombreUtilisateurs; $i++) {
        $prenom = $listeNoms[$randKeysNom[$i]];
        $quotite = 100;
        $isQuotite = rand(0, 1);
        if ($isQuotite) {
            $tabQuotite = [90, 80, 70, 60, 50];
            $quotite = $tabQuotite[array_rand($tabQuotite, 1)];
        }
        $heureSolde = 0;
        $isHeureSolde = rand(0, 1);
        if ($isHeureSolde) {
            $heureSolde = rand(0, 400);
        }

        $password = md5($prenom);

        $isHr = 'N';
        $isActive = 'Y';
        $isAdmin = 'N';
        $isResponsable = 'N';
        if (is_int(array_search($prenom, $responsableGroupe))) {
            $isResponsable = 'Y';
            $responsable = 'pierre';
        }

        $sqlCongesUser .= "INSERT INTO `conges_users` VALUES ('" . $prenom . "', '" . $prenom . "', '" . $prenom . "', '" . $isResponsable . "', '" . $responsable . "', '" . $isAdmin . "', '" . $isHr . "', '" . $isActive . "', 'N', '" . $password . "', " . $quotite . ", '', 0, 7, " . $heureSolde . ");\n";

        if (is_int(array_search($prenom, $responsableGroupe))) {
            // Le prochain utilisateur aura pour responsable la personne courante si celle ci est responsable de son groupe
            $responsable = $prenom;
        }
    }
    // A la fin on ajoute l'utilisateur Pierre qui est RH, il est aussi le responsable des responsables (n+2 des autres utilisateurs)
    $sqlCongesUser .= "INSERT INTO `conges_users` VALUES('pierre', 'point', 'pierre', 'Y', 'conges', 'N', 'Y', 'Y', 'N', '84675f2baf7140037b8f5afe54eef841', 100, '', 0, 7, 0);";

    $sqlCongesSoldeUser = "-- Contenu de la table `conges_solde_user`\n\n";
    for ($i = 0; $i < $nombreUtilisateurs; $i++) {
        $prenom = $listeNoms[$randKeysNom[$i]];
        $nb_anCP = returnFloatWithDot(rand(0, 100) / 2); // allow to get x.5 numbers
        $soldeCP = returnFloatWithDot(rand(0, $nb_anCP * 2) / 2); // allow to get x.5 numbers
        $reliquatCP = returnFloatWithDot(rand(0, 20) / 2); // allow to get x.5 numbers
        $sqlCongesSoldeUser .= "INSERT INTO `conges_solde_user` VALUES ('" . $prenom . "', 1, " . $nb_anCP . ", " . $soldeCP . ", " . $reliquatCP . ");\n";

        $nb_anRTT = returnFloatWithDot(rand(0, 40) / 2); // allow to get x.5 numbers
        $soldeRTT = returnFloatWithDot(rand(0, $nb_anRTT * 2) / 2); // allow to get x.5 numbers
        $reliquatRTT = returnFloatWithDot(rand(0, 10) / 2); // allow to get x.5 numbers
        $sqlCongesSoldeUser .= "INSERT INTO `conges_solde_user` VALUES ('" . $prenom . "', 2, " . $nb_anRTT . ", " . $soldeRTT . ", " . $reliquatRTT . ");\n";
    }

    $sqlCongesPeriode = "-- Contenu de la table `conges_periode`\n\n";
    $nbDemande = 1;
    for ($i = 0; $i < $nombreUtilisateurs; $i++) {
        $prenom = $listeNoms[$randKeysNom[$i]];
        $nbConges = rand(0, 5); // Option "Peu" de congés
        if (1 == $nombreConges) {
            // Option "Moyen" de congés
            $nbConges = rand(0, 15);
        }
        if (2 == $nombreConges) {
            // Option "Beaucoup" de congés
            $nbConges = rand(5, 30);
        }
        for ($j = 0; $j < $nbConges; $j++) {
            $randDebut = rand(-30, 90);
            $randFin = floor(log(rand(1, 10)) * rand(0, 10)); // Répartition pseudo-logarithmique des congés pour être plus réaliste
            $randDemande = rand(6, 60);
            $randTraitement = rand(0, 5);
            $dateDemande = $randDebut + $randDemande;
            $sqlDemande = 'NOW() - INTERVAL ' . $dateDemande . ' DAY';
            $dateTraitement = 'NOW() - INTERVAL ' . ($dateDemande - $randTraitement) . ' DAY';
            $dateDebut = 'CURDATE() - INTERVAL ' . $randDebut . ' DAY';
            $dateFin = 'CURDATE() - INTERVAL ' . ($randDebut - $randFin) . ' DAY';

            $demiJourDeb = rand(0, 1);
            $demiJourFin = rand(0, 1);
            // On cherche le rare cas où on tombe sur le même jour avec la date de début l'après midi et la date de fin le matin
            if (0 == $randFin) {
                if (1 == $demiJourDeb && 0 == $demiJourFin) {
                    $demiJourFin = 1; // dans ce cas là met le congés seulement sur l'après midi
                }
            }
            // on transform le 0 en 'am et 1 en 'pm'
            if (0 == $demiJourDeb) {
                $demiJourDeb = 'am';
            } else {
                $demiJourDeb = 'pm';
            }
            if (0 == $demiJourFin) {
                $demiJourFin = 'am';
            } else {
                $demiJourFin = 'pm';
            }
            // calcul du nombre de jours pris
            $nbJours = $randFin;
            if (0 == $randFin) {
                if ($demiJourFin === $demiJourDeb) {
                    $nbJours = 0.5;
                } else {
                    $nbJours = 1;
                }
            } else {
                if ('pm' === $demiJourDeb) {
                    $nbJours -= 0.5;
                }
                if ('am' === $demiJourFin) {
                    $nbJours += 0.5;
                } else {
                    $nbJours += 1;
                }
            }
            $nbJours = returnFloatWithDot($nbJours);

            // une chance sur 4 d'avoir une absence
            $isAbsence = rand(0, 4);
            if (4 === $isAbsence) {
                $typeConges = rand(3, 6);
            } else {
                $typeConges = rand(1, 2);
            }
            $tabEtat = ['ok', 'demande', 'refus'];
            $etat = $tabEtat[rand(0, count($tabEtat) - 1)];
            $commentaireRefus = '';
            if ('refus' == $etat) {
                $tabCommentaireRefus = ['', '', '', 'impossible', 'non', 'demande trop tardive'];
                $commentaireRefus = $tabCommentaireRefus[rand(0, count($tabCommentaireRefus) - 1)];
            }if ('demande' == $etat) {
                $dateTraitement = "null";
            }
            $sqlCongesPeriode .= "INSERT INTO `conges_periode` VALUES ('" . $prenom . "', " . $dateDebut . ", '" . $demiJourDeb . "', " . $dateFin . ", '" . $demiJourFin . "', " . $nbJours . ", '', " . $typeConges . ", '" . $etat . "', null, '" . $commentaireRefus . "', " . $sqlDemande . ", " . $dateTraitement . ", null, " . $nbDemande . ");\n";
            $nbDemande++;
        }
    }

    $sqlJoursFeries = "-- Contenu de la table `conges_jours_feries`\n";
    $sqlJoursFeries .= "INSERT INTO `conges_jours_feries` VALUES (CONCAT(YEAR(NOW()), '-01-01'));\n";
    $sqlJoursFeries .= "INSERT INTO `conges_jours_feries` VALUES (CONCAT(YEAR(NOW()), '-04-06'));\n";
    $sqlJoursFeries .= "INSERT INTO `conges_jours_feries` VALUES (CONCAT(YEAR(NOW()), '-05-01'));\n";
    $sqlJoursFeries .= "INSERT INTO `conges_jours_feries` VALUES (CONCAT(YEAR(NOW()), '-05-08'));\n";
    $sqlJoursFeries .= "INSERT INTO `conges_jours_feries` VALUES (CONCAT(YEAR(NOW()), '-05-14'));\n";
    $sqlJoursFeries .= "INSERT INTO `conges_jours_feries` VALUES (CONCAT(YEAR(NOW()), '-07-14'));\n";
    $sqlJoursFeries .= "INSERT INTO `conges_jours_feries` VALUES (CONCAT(YEAR(NOW()), '-08-15'));\n";
    $sqlJoursFeries .= "INSERT INTO `conges_jours_feries` VALUES (CONCAT(YEAR(NOW()), '-11-01'));\n";
    $sqlJoursFeries .= "INSERT INTO `conges_jours_feries` VALUES (CONCAT(YEAR(NOW()), '-11-11'));\n";
    $sqlJoursFeries .= "INSERT INTO `conges_jours_feries` VALUES (CONCAT(YEAR(NOW()), '-12-25'));\n";

    $sqlPlanning = "-- Contenu de la table `planning`\n";
    $sqlPlanning .= 'INSERT INTO planning (planning_id, name, status) VALUES (7, "planning_type", 1), (8, "planning_sans_creneau", 1);';

    $sqlPlanningCreneau = "-- Contenu de la table`planning_creneau`\n";
    $sqlPlanningCreneau .= 'INSERT INTO planning_creneau (creneau_id, planning_id, jour_id, type_semaine, type_periode, debut, fin) VALUES ("", 7, 1, 1, 1, "28800", "45000"), ("", 7, 1, 1, 2, "50400", "59400"), ("", 7, 1, 1, 2, "64800", "72000"), ("", 7, 2, 1, 1, "28800", "45000"), ("", 7, 2, 1, 2, "50400", "59400"), ("", 7, 2, 1, 2, "64800", "72000"), ("", 7, 3, 1, 1, "28800", "45000"), ("", 7, 3, 1, 2, "50400", "59400"), ("", 7, 3, 1, 2, "64800", "72000"), ("", 7, 4, 1, 1, "28800", "45000"), ("", 7, 4, 1, 2, "50400", "59400"), ("", 7, 4, 1, 2, "64800", "72000"), ("", 7, 5, 1, 1, "28800", "45000"), ("", 7, 5, 1, 2, "50400", "59400"), ("", 7, 5, 1, 2, "64800", "72000");';

    $sql = '';
    $sql .= $sqlGroupe;
    $sql .= "\n\n";
    $sql .= $sqlGroupeUsers;
    $sql .= "\n\n";
    $sql .= $sqlGroupeResponsable;
    $sql .= "\n\n";
    $sql .= $sqlCongesUser;
    $sql .= "\n\n";
    $sql .= $sqlCongesSoldeUser;
    $sql .= "\n\n";
    $sql .= $sqlCongesPeriode;
    $sql .= "\n\n";
    $sql .= $sqlPlanning;
    $sql .= "\n\n";
    $sql .= $sqlPlanningCreneau;
    $sql .= "\n\n";
    $sql .= $sqlJoursFeries;

    echo "<hr/> ";
    echo "<h2>Jeu de données SQL</h2>";
    echo "<p>Pour chaque utilisateur le mot de passe correspond au nom de l'utilisateur par exemple<br />login = pierre,<br /> mot de passe = pierre</p>";
    echo "Liste des utilisateurs crée :";
    echo "<ul>";
    echo "<li>pierre (rôle de RH et N+2)</li>";
    for ($i = 0; $i < $nombreUtilisateurs; $i++) {
        $prenom = $listeNoms[$randKeysNom[$i]];
        if (is_int(array_search($prenom, $responsableGroupe))) {
            echo "<li>$prenom (rôle de responsable, responsable du groupe " . $listeGroupes[array_search($prenom, $responsableGroupe)][1] . ")</li>";
        } else {
            echo "<li>$prenom</li>";
        }
    }
    echo "</ul>";
    echo "<textarea class='form-control' rows='20'>$sql</textarea>";
}

echo '</div></section></section></section></body></html>';
