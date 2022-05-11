<?php
namespace Nish\Utils\Mailer;

use Nish\Exceptions\MailerException;
use Nish\PrimitiveBeast;

class FileMailer extends PrimitiveBeast implements IMailer
{
    /**
     * @param $host
     * @param $username
     * @param $password
     * @param array|null $to
     * @param array|null $bcc
     * @param array|null $cc
     * @param null $fromAddr
     * @param string $subject
     * @param string $htmlBody
     * @param string $textBody
     * @param int $port
     * @param string $smtpSecure
     * @param null $replyTo
     * @param array|null $attachments
     * @throws MailerException
     */
    public static function sendSMTPMail($host, $username, $password, ?array $to = null, ?array $bcc = null, ?array $cc = null, $fromAddr = null, $subject = '', $htmlBody = '', $textBody = '', $port = 587, $smtpSecure = 'tls', $replyTo = null, ?array $attachments = null)
    {
        $mail = [
            'Host' => $host,
            'Port' => $port,
            'Username' => $username,
            'Password' => $password,
            'SMTPSecure' => $smtpSecure,
        ];


        if ($fromAddr) {
            $mail['fromAddr'] = $fromAddr;
        }

        if ($replyTo) {
            $mail['replyTo'] = $replyTo;
        }

        if (!empty($to)) {
            $mail['to'] = [];

            foreach ($to as $addr) {
                $mail['to'][] = $addr;
            }
        }

        if (!empty($bcc)) {
            $mail['bcc'] = [];

            foreach ($bcc as $addr) {
                $mail['bcc'][] = $addr;
            }
        }

        if (!empty($cc)) {
            $mail['cc'] = [];

            foreach ($cc as $addr) {
                $mail['cc'][] = $addr;
            }
        }

        if (empty($subject)) {
            $subject = 'Untitled';
        }

        $mail['Subject'] = $subject;
        $mail['Body'] = $htmlBody;

        if ($textBody) {
            $mail['AltBody'] = $textBody;
        }

        if (!empty($attachments)) {
            $mail['attachments'] = [];
            foreach ($attachments as $i => $v) {
                $mail['attachments'][] = "$i => $v";
            }
        }

        try {
            $logger = self::getDefaultLogger();

            if ($logger) {
                $logger->info('Mail Sent!', $mail);
            }
        } catch (\Exception $e) {
            throw new MailerException($e->getMessage(), $e->getCode() );
        }

    }
}