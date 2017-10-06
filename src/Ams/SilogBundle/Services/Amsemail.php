<?php

/**
 * Classe fournissant des méthodes liées à l'envoi d'email
 * @author madelise
 */

namespace Ams\SilogBundle\Services;

use \Symfony\Component\DependencyInjection\ContainerAware;
//use Ams\SilogBundle\Controller\GlobalController;

class Amsemail extends ContainerAware {

    /**
     * Méthode de génération et d'envoi de l'email
     * Le tableau $aDatas permet de configurer le mail avant son envoi. Détail de ses clés:
     * sMailDest -> Le destinataire du courrier
     * sSenderAdress -> L'expéditeur (par défaut LOG-IT-MROAD@proximy.fr) voir EMAIL_SERVICE_DEFAULT_SENDER
     * sSubject -> Le sujet de l'email
     * cc -> Un tableau contenant les adresses à joindre en copie carbone
     * bcc -> Un tableau contenant les adresses à joindre en copie carbone cachée
     * sContentHTML -> Le contenu HTML du courrier à envoyer
     * sContentTxt -> Le contenu text clair du courrier
     * charset -> Encodage du contenu (par défaut UTF-8) voir EMAIL_SERVICE_DEFAULT_CHARSET
     * encoding_bits -> Nombre de bits pour l'encodage (par défaut 8) voir EMAIL_SERVICE_DEFAULT_TRANSFER_ENCODING_BITS
     * aAttachment -> un tableau: array('sFichier' => Chemin_vers_PJ, 'sNomFichier' => Nom_du_fichier_PJ)
     * @param string $sTemplate Le template à charger ex: AmsDistributionBundle:Emails:mail_recap_paie_tournee.mail.twig
     * @param array $aDatas Un tableau de données pour générer l'email : 
     * @param string $sForEnv Permet d'envoyer les e-mails depuis un autre environnement
     * @return bool $bMailSent TRUE si l'email a bien été envoyé
     */
    public function send($sTemplate, $aDatas, $sForEnv = NULL){
        $sMailStr = ''; // La variable contenant le texte
        $bMailSent = FALSE;
        $sEnv = $this->container->get('kernel')->getEnvironment();
        
        if (!empty($aDatas)){
            $view =  $this->container->get('twig');
            
            $aDatas['sRandom'] = $sRandom = md5(date('r', time())); // Valeur aléatoire
            
            // Utilisation ou non des valeurs par défaut
            $aDatas['charset'] = isset($aDatas['charset']) ? $aDatas['charset'] : $this->container->getParameter('EMAIL_SERVICE_DEFAULT_CHARSET');
            $aDatas['encoding_bits'] = isset($aDatas['encoding_bits']) ? $aDatas['encoding_bits'] : $this->container->getParameter('EMAIL_SERVICE_DEFAULT_TRANSFER_ENCODING_BITS');
            $sSenderAdress = isset($aDatas['sSenderAdress']) ? $aDatas['sSenderAdress'] : $this->container->getParameter('EMAIL_SERVICE_DEFAULT_SENDER');
            $sReplyAdress = isset($aDatas['sReplyAdress']) ? $aDatas['sReplyAdress'] : $sSenderAdress;
            
            // Initialisation de l'entête
            $aDatas['sHeaders'] = 'From: '.$sSenderAdress."\r\nReply-To: ".$sReplyAdress;
            $aDatas['sSenderAdress'] = $sSenderAdress;
            
            // CC
            if (isset($aDatas['cc']) && !empty($aDatas['cc'])){
                $aDatas['sHeaders'] .= "\r\n".'Cc:'.implode(',',$aDatas['cc']).'\n';
            }
            
            // Bcc
            if (isset($aDatas['bcc']) && !empty($aDatas['bcc'])){
                $aDatas['sHeaders'] .= "\r\n".'Bcc:'.implode(',',$aDatas['bcc']).'\n';
            }
            
            // Prise en compte d'une PJ
            if (!empty($aDatas['aAttachment']) && ($sEnv != 'prod') ){
                $aDatas['aAttachment']['sAttachedFile'] = chunk_split(base64_encode(file_get_contents($aDatas['aAttachment']['sFichier']))); 
                $aDatas['sHeaders'] .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$aDatas['sRandom']."\""; 
            }
            
            // Contenus texte et HTML du courrier
            if (empty($aDatas['sContentHTML']) || empty($aDatas['sContentTXT'])){
                if (empty($aDatas['sContentTXT'])){
                    $aDatas['sContentTXT'] = strip_tags(preg_replace("/\<br\s*\/?\>/i", "\n", $aDatas['sContentHTML']));
                }
                else{
                    $aDatas['sContentHTML'] =  nl2br($saDatas['sContentHTML']);
                }
            }
            
            switch ($sEnv){
                case 'prod':
                case 'preprod':
                    $sMailStr .= $view->render($sTemplate, array('mailDatas' => $aDatas));
                    $sCmd = 'echo "'.$sMailStr.'" | '.$this->container->getParameter('EMAIL_SERVICE_COMMAND_SENDMAIL');
                    $iCmdStatus = exec($sCmd.' && echo $?');
                    
                    if ($iCmdStatus == 0){
                        $bMailSent = TRUE;
                    }
                    break;
                default:
                    $sMailStr = $view->render($sTemplate, array('mailDatas' => $aDatas));
                    ob_start();
                    echo $sMailStr;
                    $sMessage = ob_get_clean(); 
                    $bMailSent = mail( $aDatas['sMailDest'], $aDatas['sSubject'], $sMessage, $aDatas['sHeaders'] ); 
                    break;
            }
            
        }
        return $bMailSent;
    }

}
