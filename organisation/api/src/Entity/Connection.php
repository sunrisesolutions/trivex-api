<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
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
 * @ApiFilter(SearchFilter::class, properties={"uuid": "exact", "fulltextString": "partial"})
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
     * @ORM\JoinColumn(name="id_from_member", referencedColumnName="id", onDelete="CASCADE"),
     * @Groups("read")
     */
    private $fromMember;

    /**
     * @var IndividualMember
     * @ORM\ManyToOne(targetEntity="App\Entity\IndividualMember", inversedBy="toConnections")
     * @ORM\JoinColumn(name="id_to_member", referencedColumnName="id", onDelete="CASCADE"),
     * @Groups({"read", "write"})
     */
    private $toMember;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("read")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $fulltextString;

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateFulltextString()
    {
        $fromPerson = $this->fromMember->getFulltextString();
        $toPerson = $this->toMember->getFulltextString();
        if (empty($fromPerson) && empty($toPerson)) $this->fulltextString = '';
        else $this->fulltextString = ' createdAt: ' . (string)$this->createdAt->format("Y-m-d H:i:s");

        if (!empty($fromPerson)) {
            $this->fulltextString .= ' from: ' . $fromPerson;
        }
        if (!empty($toPerson)) {
            $this->fulltextString .= ' to: ' . $toPerson;
        }
    }

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

    public function getFulltextString(): ?string
    {
        return $this->fulltextString;
    }

    public function setFulltextString(?string $fulltextString): self
    {
        $this->fulltextString = $fulltextString;

        return $this;
    }
}
