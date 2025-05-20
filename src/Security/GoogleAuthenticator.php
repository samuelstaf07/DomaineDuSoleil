<?php

namespace App\Security;

use App\Entity\Images;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class GoogleAuthenticator implements AuthenticatorInterface
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $client->getAccessToken();
        /** @var GoogleUser $googleUser */
        $googleUser = $client->fetchUserFromToken($accessToken);

        $email = $googleUser->getEmail();

        return new SelfValidatingPassport(
            new UserBadge($email, function() use ($email, $googleUser) {
                $user = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);

                if (!$user) {
                    $image = new Images();
                    $image->setId(1)->setAlt('Image de profil de l\'utilisateur')->setSrc('null');
                    $image->setIsHomePage(0);

                    $user = new Users();
                    $user->setEmail($email);
                    $user->setGoogleAuthenticatorSecret($googleUser->getId());
                    $user->setFirstname($googleUser->getFirstName());
                    $user->setLastname($googleUser->getLastName());
                    $user->setPassword(bin2hex(random_bytes(10)));
                    $user->setCreatedAt(new \DateTimeImmutable('now'));
                    $user->setLastLogAt(new \DateTimeImmutable('now'));
                    $user->setUpdatedAt(new \DateTimeImmutable('now'));
                    $user->setRoles(['ROLE_USER']);
                    $user->setIsActive(true);
                    $user->setIsEmailAuthentificated(true);
                    $user->setImage($image);
                    $user->setBirthDate(new \DateTimeImmutable('now'));

                    $this->entityManager->persist($user);
                }

                $this->entityManager->flush();
                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $user = $passport->getUser();
        return new UsernamePasswordToken($user, $firewallName, $user->getRoles());
    }
}
