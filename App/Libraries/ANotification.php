<?php

namespace App\Libraries;

/**
 * Objet de gestion des notifications
 * 
 * 
 */
abstract Class ANotification {

    protected $contenuNotification;
    protected $envoiMail;

    /**
     * 
     * implémente les informations de la demande d'heure
     * ainsi que du contenu du mail
     * 
     * @param int $id
     * 
     */
    public function __construct($id) {
        $id = (int)$id;
        if (is_int($id)){
            $this->contenuNotification = $this->getContenu($id);
        } else {
            throw new Exception('erreur id');
        }
    }


    /**
     * 
     * Transmet les notifications par mail
     * 
     * @return boolean
     */
    public function send() {

    $return = [];

        // init du mail
        $mail = new \PHPMailer();

        if (file_exists(CONFIG_PATH . 'config_SMTP.php')) {
            include CONFIG_PATH . 'config_SMTP.php';

            if (!empty($config_SMTP_host)) {
                $mail->IsSMTP();
                $mail->Host = $config_SMTP_host;
                $mail->Port = $config_SMTP_port;

                if (!empty($config_SMTP_user)) {
                    $mail->SMTPAuth = true;
                    $mail->Username = $config_SMTP_user;
                    $mail->Password = $config_SMTP_pwd;
                }
                if (!empty($config_SMTP_sec)) {
                    $mail->SMTPSecure = $config_SMTP_sec;
                } else {
                    $mail->SMTPAutoTLS = false;
                }
            }
        } else {
            if (file_exists('/usr/sbin/sendmail')) {
                $mail->IsSendmail();   // send message using the $Sendmail program
            } elseif (file_exists('/var/qmail/bin/sendmail')) {
                $mail->IsQmail(); // send message using the qmail MTA
            } else {
                $mail->IsMail(); // send message using PHP mail() function
            }
        }
        
        foreach ($this->contenuNotification as $notification){
            if (empty($notification['destinataire'][0])) {
                continue;
            }
            if ($this->canSend($notification['config'])){
                $mail->ClearAddresses();
                $mail->From = $notification['expediteur']['mail'];
                $mail->FromName = $notification['expediteur']['nom'];
                foreach ($notification['destinataire'] as $destinataire) {
                    $mail->AddAddress($destinataire);
                }
                $mail->SetLanguage( 'fr', ROOT_PATH . 'vendor/phpmailer/phpmailer/language/');
        
                $mail->Subject = utf8_decode ( $notification['sujet'] );
                $mail->Body = utf8_decode ( $notification['message'] );

                $return[] = $mail->Send();
            }
        }
        return !in_array(false, $return);
    }

    /**
     * récupère les données de l'évenemment
     * 
     * @todo déplacer la requete vers le protocontroller
     * @param int $id
     * 
     * @return array
     */
    abstract protected function getData($id);

    /**
     * selection du contenu de la notification
     * 
     * @return array
     */
    protected function getContenu($id) {
        $data = $this->getData($id);
        switch ($data['statut']) {
            case \App\Models\AHeure::STATUT_DEMANDE:
                $NotifContent[] = $this->getContenuDemande($data);
                break;
            case \App\Models\AHeure::STATUT_PREMIERE_VALIDATION:
                $NotifContent[] = $this->getContenuEmployePremierValidation($data);
                $NotifContent[] = $this->getContenuGrandResponsablePremiereValidation($data);
                break;
            case \App\Models\AHeure::STATUT_VALIDATION_FINALE:
                $NotifContent[] = $this->getContenuValidationFinale($data);
                break;
            case \App\Models\AHeure::STATUT_REFUS:
                $NotifContent[] = $this->getContenuRefus($data);
                break;
            case \App\Models\AHeure::STATUT_ANNUL:
                $NotifContent[] = $this->getContenuAnnulation($data);
                break;
        }
        return $NotifContent;
    }
    
    /**
     * Controle de l'option d'envoi de mails selon la notification
     * 
     * @param string $optionName
     * @return boolean
     * @throws Exception
     * 
     */
    private function canSend($optionName) {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());

        switch ($optionName) {
            case 'mail_new_demande_alerte_resp':
                return $config->isSendMailDemandeResponsable();
            case 'mail_prem_valid_conges_alerte_user':
                return $config->isSendMailPremierValidationUtilisateur();
            case 'mail_valid_conges_alerte_user':
                return $config->isSendMailValidationUtilisateur();
            case 'mail_supp_demande_alerte_resp':
                return $config->isSendMailSupprimeDemandeResponsable();
            case 'mail_new_demande_alerte_resp':
                return $config->isSendMailDemandeResponsable();
            default:
                return false;
        }
    }

    /**
     * notification d'une nouvelle demande d'heures
     * au responsable du demandeur
     * 
     * @param array $data
     * @return array
     */
    abstract protected function getContenuDemande($data);
    
    /**
     * notification d'une première validation 
     * au demandeur d'heures
     * 
     * @param array $data
     * @return array
     */
    abstract protected function getContenuEmployePremierValidation($data);
    
    /**
     * notification d'une validation finale
     * au demandeur d'heures
     * 
     * @param array $data
     * @return array
     */
    abstract protected function getContenuValidationFinale($data);
    
    /**
     * notification d'un refus
     * au demandeur d'heures
     * 
     * @param array $data
     * @return array
     */
    abstract protected function getContenuRefus($data);
    
    /**
     * notification d'une annulation par le demandeur
     * à son responsable
     * 
     * @param array $data
     * @return array
     */
    abstract protected function getContenuAnnulation($data);
    
    /**
     * notification d'une première validation
     * au grand responsable du demandeur d'heures
     * 
     * @param array $data
     * @return array
     */
    abstract protected function getContenuGrandResponsablePremiereValidation($data);
    
}
