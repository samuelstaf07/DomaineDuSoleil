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
     * @throws LoaderError|RuntimeError|SyntaxError|TransportExceptionInterface
     */
    public function sendEmailConfirmation(string $to, string $username, string $signedUrl): void
    {
        $body = $this->twig->render('emails/emailconfirmation.html.twig', [
            'username' => $username,
            'confirmationUrl' => $signedUrl,
        ]);

        $email = (new Email())
            ->from('no-reply@domainedusoleil.com')
            ->to($to)
            ->subject('Confirmez votre adresse email')
            ->html($body);

        $this->mailer->send($email);
    }

    public function sendPasswordReset(string $to, string $username, string $signedUrl): void
    {
        $body = $this->twig->render('emails/passwordreset.html.twig', [
            'username' => $username,
            'confirmationUrl' => $signedUrl,
        ]);

        $email = (new Email())
            ->from('no-reply@domainedusoleil.com')
            ->to($to)
            ->subject('Changer votre mot de passe')
            ->html($body);

        $this->mailer->send($email);
    }

    public function sendDeleteAccount(string $to, string $username, string $signedUrl): void
    {
        $body = $this->twig->render('emails/deleteaccount.html.twig', [
            'username' => $username,
            'confirmationUrl' => $signedUrl,
        ]);

        $email = (new Email())
            ->from('no-reply@domainedusoleil.com')
            ->to($to)
            ->subject('Supprimer votre compte')
            ->html($body);

        $this->mailer->send($email);
    }


    public function sendInfoDeletedAccount(string $to, string $username): void
    {
        $body = $this->twig->render('emails/deletedaccount.html.twig', [
            'username' => $username,
        ]);

        $email = (new Email())
            ->from('no-reply@domainedusoleil.com')
            ->to($to)
            ->subject('Compte supprimé')
            ->html($body);

        $this->mailer->send($email);
    }

    public function sendChangeMail(string $to, string $username, string $newEmail): void
    {
        $body = $this->twig->render('emails/changemail.html.twig', [
            'username' => $username,
            'newEmail' => $newEmail,
        ]);

        $email = (new Email())
            ->from('no-reply@domainedusoleil.com')
            ->to($to)
            ->subject('Adresse mail changée')
            ->html($body);

        $this->mailer->send($email);
    }
}