<?php

namespace providers\phpmailer;

require_once 'phpmailer/class.phpmailer.php';
require_once 'phpmailer/class.pop3.php';
require_once 'phpmailer/class.smtp.php';

class CPHPMailerConnector extends \cms\emailing\CCMSEMailingConnectorAdapter {

    /**
     * PHPMailer main class
     * @var \PHPMailer
     */
    private $phpmailer;

    public function __construct($protocol) {

        parent::__construct($protocol);

        $this->phpmailer = new \PHPMailer();
        switch ($protocol) {
            case self::SMTP_PROTOCOL_SMTP:
                $this->phpmailer->isSMTP();
                $this->phpmailer->SMTPKeepAlive = true;
                break;
        }
    }

    public function ping() {

        $smtp = $this->phpmailer->getSMTPInstance();
        $smtp->reset();
    }

    public function close() {
        if ($this->phpmailer !== null) {
            $this->phpmailer->smtpClose();
        }
    }

    public function setSMTPParameters($host, $port, $encryption, $auth = false, $username = false, $password = false) {

        parent::setSMTPParameters($host, $port, $encryption, $auth, $username, $password);

        $this->phpmailer->Host = $host;
        $this->phpmailer->Port = $port;
        switch ($encryption) {
            case self::SMTP_SSL_ENCRYPTION: $this->phpmailer->SMTPSecure = 'ssl'; break;
            case self::SMTP_TLS_ENCRYPTION: $this->phpmailer->SMTPSecure = 'tls'; break;
            default: $this->phpmailer->SMTPSecure = '';
        }
        $this->phpmailer->SMTPAuth = $auth;
        if ($auth) {
            $this->phpmailer->Username = $username;
            $this->phpmailer->Password = $password;
        }
    }

    public function send($from, $to, $cc, $subject, $text, $html, $attachments = null, $bcc = null, $reply_to = null) {

        $this->phpmailer->clearAddresses();
        $this->phpmailer->clearAttachments();
        $this->phpmailer->clearBCCs();
        $this->phpmailer->clearCCs();
        $this->phpmailer->clearCustomHeaders();
        $this->phpmailer->clearReplyTos();
        $this->phpmailer->clearAllRecipients();
        $this->phpmailer->MessageDate = \PHPMailer::rfcDate();

        if (is_string($to)) {
            $recipients = preg_split('/,\\s/', $to);
        } else if (is_array($to)) {
            $recipients = $to;
        } else {
            return false;
        }

        foreach ($recipients as $recipient) {
            $this->phpmailer->addAddress($recipient);
        }

        if (is_string($cc)) {
            $recipients = preg_split('/,\\s/', $cc);
        } else if (is_array($cc)) {
            $recipients = $cc;
        } else {
            $recipients = null;
        }

        if (count($recipients) > 0) {
            foreach ($recipients as $recipient) {
                $this->phpmailer->addCC($recipient);
            }
        }

        if (is_string($bcc)) {
            $recipients = preg_split('/,\\s/', $bcc);
        } else if (is_array($bcc)) {
            $recipients = $bcc;
        } else {
            $recipients = null;
        }

        if (count($recipients) > 0) {
            foreach ($recipients as $recipient) {
                $this->phpmailer->addBCC($recipient);
            }
        }

        if (is_string($reply_to)) {
            $this->phpmailer->addReplyTo($reply_to);
        }

        $this->phpmailer->setFrom($from, $from);
        $this->phpmailer->Subject = $subject;

        if (strlen($html) > 0) {
            if (strlen($this->htmlCharset) > 0) {
                $this->phpmailer->CharSet = $this->htmlCharset;
            }
            $this->phpmailer->isHTML(true);
            $this->phpmailer->Body = $html;
            if (strlen($text) > 0) {
                $this->phpmailer->AltBody = $text;
            }
        } else {
            $this->phpmailer->Encoding = $this->textCharset;
            $this->phpmailer->isHTML(false);
            $this->phpmailer->Body = $text;
        }


        if (is_array($attachments) && count($attachments) > 0) {
            foreach($attachments as $attachment) {
                $this->phpmailer->addAttachment($attachment['path'], $attachment['name'], $attachment['encoding'], $attachment['mimetype']);
            }
        }

        if ($this->phpmailer->send()) {
            $this->error = false;
        } else {
            $this->error = $this->phpmailer->ErrorInfo;
        }
    }
}
