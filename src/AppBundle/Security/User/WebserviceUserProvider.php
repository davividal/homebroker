<?php

namespace AppBundle\Security\User;

use Monolog\Logger;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use GuzzleHttp\Client;

class WebserviceUserProvider implements UserProviderInterface
{
    /**
     * @var Client
     */
    private $client;
    private $password;
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Client $client, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function loadUserByUsername($username)
    {
        $userData = $this->client->post(
            '/login',
            [
                'json' => [
                    'login' => $username,
                    'password' => $this->password,
                ],
            ]
        );

        if ($userData) {
            $this->logger->info('Userdata: ' . $userData->getBody());
            $user = json_decode($userData->getBody());

            return new WebserviceUser($user->login, $user->password, null, ['ROLE_USER'], $user->id, $user->name, $user->balance);
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof WebserviceUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $this->password = $user->getPassword();

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'AppBundle\Security\User\WebserviceUser';
    }
}
