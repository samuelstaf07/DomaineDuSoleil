<?php

namespace App\Services;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MailerService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function sendWelcomeEmail(string $to, string $username): void
    {
        $body = $this->twig->render('emails/register.html.twig', [
            'username' => $username
        ]);

        $email = (new Email())
            ->from('no-reply@domainedusoleil.com')
            ->to($to)
            ->subject('Bienvenue sur MonApp !')
            ->html($body);

        $this->mailer->send($email);
    }
    /**
     * Notification de connexion
     * @throws LoaderError|RuntimeError|SyntaxError|TransportExceptionInterface
     */
    public function sendLoginNotificationEmail(string $to, string $username, \DateTimeInterface $loginTime): void
    {
        $body = $this->twig->render('emails/register.html.twig', [
            'username' => $username,
            'loginTime' => $loginTime,
        ]);

        $email = (new Email())
            ->from('no-reply@domainedusoleil.com')
            ->to($to)
            ->subject('Nouvelle connexion à votre compte')
            ->html($body);

        $this->mailer->send($email);
    }
}