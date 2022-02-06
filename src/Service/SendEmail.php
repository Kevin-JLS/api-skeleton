<?php 

namespace App\Service;

use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SendEmail
{
    private MailerInterface $mailer;

    private string $senderEmail;

    private string $senderName;

    public function __construct(
        MailerInterface $mailer,
        string $senderEmail,
        string $senderName
    )
    {
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    /** @param array<mixed> $arguments */
    public function send(array $arguments): void
    {
        [
            'recipient_email'   => $recipientEmail,
            'subject'           => $subject,
            'html_template'     => $html_template,
            'context'           => $context,
        ] = $arguments;

        $email = new TemplatedEmail();

        $email->from(new Address($this->senderEmail, $this->senderName))
              ->to($recipientEmail)
              ->subject($subject)
              ->htmlTemplate($html_template)
              ->context($context);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $mailerExeption) {
            throw $mailerExeption;
        }
    }
}