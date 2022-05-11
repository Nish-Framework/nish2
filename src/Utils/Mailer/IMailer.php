<?php
namespace Nish\Utils\Mailer;


use Nish\Exceptions\MailerException;

interface IMailer
{
    /**
     * @param $host
     * @param $username
     * @param $password
     * @param array|null $to
     * @param array|null $bcc
     * @param array|null $cc
     * @param $fromAddr
     * @param $subject
     * @param $htmlBody
     * @param $textBody
     * @param $port
     * @param $smtpSecure
     * @param $replyTo
     * @param array|null $attachments
     * @throws MailerException
     * @return mixed
     */
    public static function sendSMTPMail($host, $username, $password, ?array $to, ?array $bcc, ?array $cc, $fromAddr, $subject, $htmlBody, $textBody, $port, $smtpSecure, $replyTo, ?array $attachments);
}