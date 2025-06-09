<?php

namespace App\Services;

use App\Entity\Bills;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Symfony\Component\Mime\Address;

class MailerService extends AbstractController
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
            ->from(new Address('no-reply@domainedusoleil.com', 'Domaine du Soleil'))
            ->to($to)
            ->subject('Confirmez votre adresse email')
            ->html($body);

        $this->mailer->send($email);
    }

    public function sendWelcomeForGoogleAccount(string $to, string $username): void
    {
        $body = $this->twig->render('emails/welcomeForGoogleAccount.html.twig', [
            'username' => $username,
        ]);

        $email = (new Email())
            ->from(new Address('no-reply@domainedusoleil.com', 'Domaine du Soleil'))
            ->to($to)
            ->subject('Bienvenu sur le Domaine du Soleil !')
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
            ->from(new Address('no-reply@domainedusoleil.com', 'Domaine du Soleil'))
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
            ->from(new Address('no-reply@domainedusoleil.com', 'Domaine du Soleil'))
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
            ->from(new Address('no-reply@domainedusoleil.com', 'Domaine du Soleil'))
            ->to($to)
            ->subject('Compte supprimé')
            ->html($body);

        $this->mailer->send($email);
    }

    public function changeMail(string $to, string $username, string $newEmail): void
    {
        $body = $this->twig->render('emails/changemail.html.twig', [
            'username' => $username,
            'newEmail' => $newEmail,
        ]);

        $email = (new Email())
            ->from(new Address('no-reply@domainedusoleil.com', 'Domaine du Soleil'))
            ->to($to)
            ->subject('Adresse mail changée')
            ->html($body);

        $this->mailer->send($email);
    }

    public function sendChangeMail(string $to, string $username, string $newEmail, string $signedUrl): void
    {
        $body = $this->twig->render('emails/sendChangeMail.html.twig', [
            'username' => $username,
            'newEmail' => $newEmail,
            'confirmationUrl' => $signedUrl,
        ]);

        $email = (new Email())
            ->from(new Address('no-reply@domainedusoleil.com', 'Domaine du Soleil'))
            ->to($to)
            ->subject('Demande de changement de mail')
            ->html($body);

        $this->mailer->send($email);
    }

    public function sendCommand(string $to, string $username, Bills $bill, $resEvents, $resRentals): void
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        $html = $this->renderView('pdf/bill.html.twig', [
            'bill' => $bill,
            'user' => $bill->getUser(),
            'events' => $resEvents,
            'rentals' => $resRentals,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        $body = $this->twig->render('emails/sendCommand.html.twig', [
            'username' => $username,
            'bill' => $bill,
        ]);

        $email = (new Email())
            ->from(new Address('no-reply@domainedusoleil.com', 'Domaine du Soleil'))
            ->to($to)
            ->subject('Merci pour votre commande !')
            ->html($body)
            ->attach($pdfOutput, 'facture.pdf', 'application/pdf');

        $this->mailer->send($email);
    }
}