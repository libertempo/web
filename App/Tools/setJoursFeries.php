#!/usr/bin/env php
<?php
require_once 'libraries';


function isJoursFeriesSaisi($annee) : bool
{
    $db = \includes\SQL::singleton();
    $sql="SELECT EXISTS ("
            . "SELECT jf_date FROM conges_jours_feries "
            . "WHERE jf_date LIKE '" . $annee . "%'); ";
    $reqlog = $db->query($sql);
    return 0 < (int) $reqlog->fetch_array()[0];
}

display('insertion des jours fériés français');

$annee = $argv[1] ?? null;
$force = $argv[2] ?? null;

if (preg_match('/^[0-9]{4}/', $annee)) {
    $annee = date('Y');
}

displayInfo('Calcul des jours fériés pour l\'année ' . $annee);
$joursFeriesFrance = \hr\Fonctions::getJoursFeriesFrance($annee);

if ('O' === $force) {
    \hr\Fonctions::supprimeFeriesAnnee($annee);
}

if(isJoursFeriesSaisi($annee)) {
    displayInfo('Des jours fériés ont déja été saisi pour l\'année ' . $annee);
    $input = getValue('Ecraser et continuer ? (O/N) :');

    if ($input !== 'O') {
        displayError('Annulation de l\'insertion des jours fériés.');
    }
}

display('Mise à jour de la base de donnée');
\hr\Fonctions::supprimeFeriesAnnee($annee);
\hr\Fonctions::insereFeriesAnnee($joursFeriesFrance);
display('import des jours fériés terminés.');