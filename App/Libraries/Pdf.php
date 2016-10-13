<?php
namespace App\Libraries;

/**
 * Gestion des PDF
 *
 * Ne doit contacter personne
 * Ne doit être contacté que par \App\ProtoControllers\Calendrier
 * Doit être immuable
 */
final class Pdf
{
    /**
     * @var string Orientations du fichier
     */
    const ORIENTATION_PAYSAGE = 'L';
    const ORIENTATION_PORTRAIT = 'P';

    /**
     * @var string Orientation du fichier
     */
    private $orientation;

    /**
     * @var string Css à intégrer dans le PDF
     */
    private $css;

    /**
     * @var string Corps de texte
     */
    private $contenu;

    /**
     * @var string
     */
    private $nomFichier;

    /**
     * @var string Titre du fichier
     */
    private $titre;

    /**
     * @var \TCPDF Instance de la librairie de construction du PDF
     */
    private $pdf;

    public function setOrientationPaysage()
    {
        $this->setOrientation(static::ORIENTATION_PAYSAGE);
    }

    public function setOrientationPortrait()
    {
        $this->setOrientation(static::ORIENTATION_PORTRAIT);
    }

    private function setOrientation($orientation)
    {
        $this->orientation = (string) $orientation;
    }

    public function setCss($css)
    {
        $this->css = $css;
    }

    public function setContenu($contenu)
    {
        $this->contenu = $contenu;
    }

    public function setNomFichier($nomFichier)
    {
        $this->nomFichier = $nomFichier;
    }

    public function setTitre($titre)
    {
        $this->titre = $titre;
    }

    /**
     * Déclenche l'affichage du fichier
     */
    public function display()
    {
        $this->output('I');
    }

    /**
     * Déclenche le téléchargement du fichier
     */
    public function download()
    {
        $this->output('D');
    }

    /**
     * Envoie le fichier à destination
     *
     * @param string $destination Destination de la sortie
     */
    private function output($destination)
    {
        if (!($this->pdf instanceof \TCPDF)) {
            $this->buildFile();
        }

        $this->pdf->Output($this->nomFichier, $destination);
    }

    /**
     * Construit le fichier avec les informations de l'objet
     */
    private function buildFile()
    {
        $this->pdf = new \TCPDF($this->orientation);
        $this->pdf->SetCreator('Libertempo');
        $this->pdf->SetAuthor('Libertempo');
        $this->pdf->SetTitle($this->titre);
        $this->pdf->SetSubject($this->titre);
        $this->pdf->SetHeaderData('', '', $this->titre);

        $this->pdf->AddPage();

        $contenuComplet = '';
        if (!empty($this->css)) {
            $contenuComplet .= '<style>' . $this->css . '</style>';
        }
        $contenuComplet .= $this->contenu;
        $this->pdf->writeHTML($contenuComplet);
    }
}
