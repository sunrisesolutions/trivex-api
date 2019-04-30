<?php


namespace App\Security;


use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;

final class JWTUser extends \Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser implements JWTUserInterface
{
    // Your own logic

    private $orgUuid;

    public function __construct($username, array $roles, $org)
    {
        parent::__construct($username, $roles);
        $this->orgUuid = $org;
    }

    public static function createFromPayload($username, array $payload)
    {
        return new self(
            $username,
            $payload['roles'], // Added by default
            $payload['org']  // Custom
        );
    }

    /**
     * @return mixed
     */
    public function getOrgUuid()
    {
        return $this->orgUuid;
    }
}
