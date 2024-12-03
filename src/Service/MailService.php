<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class MailService
{
    private MailerInterface $mailer;

    /**
     * @param MailerInterface $mailer
     */
    public function __construct(MailerInterface $mailer)
    {
    $this->mailer = $mailer;
    }

    public function sendMail(
        User $user,
        string $subject,
        string $template,
        array $context

    ): void
    {
        $userMail = $user->getEmail();

        $email = (new TemplatedEmail())
            ->from('contact@guillaume-gorvel.fr')
            ->to($userMail)
            ->subject($subject)
            ->htmlTemplate('emails/' . $template . '.html.twig')
            ->context($context);

        $this->mailer->send($email);
    }
}