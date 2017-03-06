<?php

namespace edition;

class PDF extends \TCPDF
{
    public function Header()
    {
        /**************************************/
        /* affichage du texte en haut de page */
        /**************************************/
        $this->SetFont('Times', '', 10);
        $this->Cell(0, 3, $_SESSION['config']['texte_haut_edition_papier'], 0, 1, 'C');
        $this->Ln(10);
    }

    public function Footer()
    {
        /**************************************/
        /* affichage du texte de bas de page */
        /**************************************/
        $this->SetFont('Times', '', 10);
        //$pdf->Cell(0,6, 'texte_haut_edition_papier',0,1,'C');
        $this->Cell(0, 3, $_SESSION['config']['texte_bas_edition_papier'], 0, 1, 'C');
        $this->Ln(10);
    }
}
