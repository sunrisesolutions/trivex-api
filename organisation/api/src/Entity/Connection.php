<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Util\AppUtil;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('ROLE_USER')"},
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ConnectionRepository")
 * @ORM\Table(name="organisation__connection")
 * @ORM\HasLifecycleCallbacks()
 */
class Connection
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PrePersist
     */
    public function initiateUuid()
    {
        if (empty($this->uuid)) {
            $this->uuid = AppUtil::generateUuid(AppUtil::APP_NAME.'_CONN');
        }
    }

    /**
     * @ORM\Column(type="string", length=191)
     * @Groups("read")
     */
    private $uuid;

    /**
     * @var IndividualMember
     * @ORM\ManyToOne(targetEntity="App\Entity\IndividualMember", inversedBy="fromConnections")
     * @ORM\JoinColumn(name="id_from_member", referencedColumnName="id"),
     * @Groups("read")
     */
    private $fromMember;

    /**
     * @var IndividualMember
     * @ORM\ManyToOne(targetEntity="App\Entity\IndividualMember", inversedBy="toConnections")
     * @ORM\JoinColumn(name="id_to_member", referencedColumnName="id"),
     * @Groups({"read", "write"})
     */
    private $toMember;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("read")
     */
    private $createdAt;

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

    public function getFromMember(): ?IndividualMember
    {
        return $this->fromMember;
    }

    public function setFromMember(?IndividualMember $fromMember): self
    {
        $this->fromMember = $fromMember;

        return $this;
    }

    public function getToMember(): ?IndividualMember
    {
        return $this->toMember;
    }

    public function setToMember(?IndividualMember $toMember): self
    {
        $this->toMember = $toMember;

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
}
