<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class MailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param User $user
     * @param string $subject
     * @param string $template
     * @param array<string, mixed> $context
     * @param array{path: string, name: string}|null $attachment
     * @return void
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendMail(
        User $user,
        string $subject,
        string $template,
        array $context,
        ?array $attachment = null,
    ): void {
        $userMail = $user->getEmail();

        $email = (new TemplatedEmail())
            ->from('contact@guillaume-gorvel.fr')
            ->to($userMail)
            ->subject($subject)
            ->htmlTemplate('emails/'.$template.'.html.twig')
            ->context($context);
        if ($attachment && file_exists($attachment['path'])) {
            $email->attachFromPath($attachment['path'], $attachment['name']);
        }

        $this->mailer->send($email);
    }
}
