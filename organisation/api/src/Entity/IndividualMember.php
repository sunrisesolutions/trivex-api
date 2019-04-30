<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Util\AppUtil;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('ROLE_USER')"},
 *     collectionOperations={
 *         "get"={"access_control"="is_granted('ROLE_ADMIN')"},
 *         "post"={"access_control"="is_granted('ROLE_ADMIN')"}
 *     },
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\IndividualMemberRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class IndividualMember
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
            $this->uuid = AppUtil::generateUuid();
        }
    }

    /**
     * @ORM\PrePersist
     */
    public function initiateAccessToken()
    {
        if (empty($this->uuid)) {
            $this->uuid = AppUtil::generateUuid(sprintf('IMT_%s_%s', $this->organisation->getId(), $this->person->getId()));
        }
    }

    /**
     * @var string
     * @ORM\Column(type="string", length=191)
     * @Groups({"read"})
     */
    private $uuid;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Groups({"read"})
     */
    private $createdAt;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="individualMembers")
     */
    private $person;

    /**
     * @var Organisation
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="individualMembers")
     */
    private $organisation;

    /**
     * @var string
     * @ORM\Column(type="string", length=191)
     * @Groups({"read"})
     */
    private $accessToken;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}
