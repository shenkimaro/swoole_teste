<?php

class Mail {

    /**
     *
     * @param string $assunto
     * @param string $para
     * @param string $nome
     * @param string $mensagem
     * @param string $from
     * @param string $fromName
     * @return boolean
     */
    public static function enviarEmail($assunto, $para, $nome, $mensagem, $from = "", $fromName = "", $attachmentList = []) {
        $message = "";
        $message .= $mensagem;
        $mail = self::getEmail($from, $fromName); // set word wrap to 50 characters
        if (is_array($para)) {
            foreach ($para as $var => $value) {
                $mail->AddAddress($value, $nome[$var]);
            }
        } else {
            $mail->AddAddress($para, $nome);
        }
        $mail->Subject = $assunto;
        $mail->Body = $message;
        $mail->AltBody = "";
        foreach ($attachmentList as $attachment) {
            $mail->AddAttachment($attachment);
        }
        if (!$mail->Send()) {
//			die ("Erro: ".$mail->ErrorInfo."");
            return false;
        }
        return true;
    }

    /**
     *
     * @return PHPMailer
     */
    private static function getEmail($from = "", $fromName = "") {
        if ($fromName == "")
            $fromName = "UEG - Universidade Estadual de Goias";
        if ($from == "")
            $from = "ueg-noreply@ueg.br";

        if (!class_exists('PHPMailer')) {
            require 'phpmailer/PHPMailer.php5';
        }
        $mail = new PHPMailer();
        $mail->IsSMTP();
//		$mail->CharSet = "ASCII";
        $mail->SMTPSecure = "ssl";
        $mail->Port = 465;
        $mail->Host = "smtp.gmail.com";  // specify main and backup server
        $mail->SMTPAuth = true;     // turn on SMTP authentication
        $mail->Username = "ueg-noreply@ueg.br";  // SMTP username
        $mail->Password = '#$toILb*8523'; // SMTP password;

        $mail->Priority = 1;
        $mail->FromName = $fromName;
        $mail->From = $from;
        $mail->AddReplyTo($from, $fromName);
        $mail->WordWrap = 50;
        $mail->IsHTML(true);
        return $mail;
    }
}

?>