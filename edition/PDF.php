<?php

namespace edition;

class PDF extends \TCPDF
{
    function Header()
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        /**************************************/
        /* affichage du texte en haut de page */
        /**************************************/
        $this->SetFont('Times','',10);
        $this->Cell(0,3, html_entity_decode($config->getTextHaut(), ENT_QUOTES),0,1,'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        /**************************************/
        /* affichage du texte de bas de page */
        /**************************************/
        $this->SetFont('Times','',10);
        //$pdf->Cell(0,6, 'texte_haut_edition_papier',0,1,'C');
        $this->Cell(0,3, html_entity_decode($config->getTextBas(), ENT_QUOTES),0,1,'C');
        $this->Ln(10);
    }
}
