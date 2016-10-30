<?php

namespace App\Libraries;

/**
 * Objet de gestion des notifications
 * 
 * 
 */
abstract Class ANotification {

    protected $data;
    protected $Notification;
    protected $envoiMail;

    public function __construct($id) {
        $this->data = $this->getData($id);
        $this->Notification = $this->getNotificationContent();
    }


    /**
     * 
     * Transmet la notification par mail
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
        
        foreach ($this->Notification as $notification){
            if($this->canSend($notification['config'])){
                $mail->ClearAddresses();
                $mail->From = $notification['expediteur'];
                foreach ($notification['destinataire'] as $destinataire) {
                    $mail->AddAddress($destinataire);
                }
                $mail->SetLanguage( 'fr', ROOT_PATH . 'vendor/phpmailer/phpmailer/language/');
        
                $mail->Subject = $notification['sujet'];
                $mail->Body = $notification['message'];

                $return[] = $mail->Send();
            }
        }
        return $return;
    }

    abstract protected function getData($id);

    /**
     * construction de la notification
     * @todo isoler l'option d'envoi d'un mail dans une méthode
     * après avoir réduit les différentes options des mails
     * 
     * @param array $data
     * @return array
     */
    protected function getNotificationContent() {
        $notifContent = [];
        switch ($this->data['statut']) {
            case \App\Models\Heure\Additionnelle::STATUT_DEMANDE:
                $NotifContent[] = $this->getNotificationDemande();
                break;
            case \App\Models\Heure\Additionnelle::STATUT_PREMIERE_VALIDATION:
                $NotifContent[] = $this->getNotificationEmployePremiereValidation();
                $NotifContent[] = $this->getNotificationGrandResponsablePremiereValidation();
                break;
            case \App\Models\Heure\Additionnelle::STATUT_VALIDATION_FINALE:
                $NotifContent[] = $this->getNotificationValidationFinale();
                break;
            case \App\Models\Heure\Additionnelle::STATUT_REFUS:
                $NotifContent[] = $this->getNotificationRefus();
                break;
            case \App\Models\Heure\Additionnelle::STATUT_ANNUL:
                $NotifContent[] = $this->getNotificationAnnulation();
                break;
        }
        return $NotifContent;
    }
    
    protected function canSend($optionName) {
        if(isset($_SESSION['config'][$optionName])){
            return $_SESSION['config'][$optionName];
        } else {
            throw new Exception('Option introuvable');
            return false;
        }
    }

}
