<?php

namespace App\Entity;

use App\Util\AppUtil;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoleRepository")
 * @ORM\Table(name="organisation__role")
 * @ORM\HasLifecycleCallbacks()
 */
class Role
{
    /**
     * @var int|null The Event Id
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer",options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\PrePersist
     */
    public function initiateUuid()
    {
        if (empty($this->uuid)) {
            $this->uuid = AppUtil::generateUuid(AppUtil::APP_NAME.'_IM');
        }
    }

    /**
     * @ORM\Column(type="string", length=191)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $member;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\IndividualMember", inversedBy="roles")
     */
    private $individualMember;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMember(): ?string
    {
        return $this->member;
    }

    public function setMember(string $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getIndividualMember(): ?IndividualMember
    {
        return $this->individualMember;
    }

    public function setIndividualMember(?IndividualMember $individualMember): self
    {
        $this->individualMember = $individualMember;

        return $this;
    }
}
