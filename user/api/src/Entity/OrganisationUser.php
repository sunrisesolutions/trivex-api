<?php

namespace App\Entity;

use App\Util\AppUtil;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user__organisations_users")
 */
class OrganisationUser
{
    /**
     * @var int|null The User Id
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer",options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @var Organisation|null
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="organisationUsers")
     * @ORM\JoinColumn(name="id_organisation", referencedColumnName="id", onDelete="CASCADE")
     */
    private $organisation;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="User", inversedBy="organisationUsers")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=191, nullable=true)
     */
    private $uuid;

    /**
     * @return Organisation|null
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param mixed $organisation
     */
    public function setOrganisation($organisation): void
    {
        $this->organisation = $organisation;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
}
