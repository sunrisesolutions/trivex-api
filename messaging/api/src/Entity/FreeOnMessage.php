<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Util\AppUtil;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={
 *     "access_control"="is_granted('ROLE_USER')",
 *     "order"={"id": "DESC"}
 * },
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}}
 * )
 * @ApiFilter(DateFilter::class, properties={"readAt"})
 * @ApiFilter(ExistsFilter::class, properties={"readAt"})
 * @ORM\Entity(repositoryClass="App\Repository\FreeOnMessageRepository")
 * @ORM\Table(name="messaging__free_on")
 * @ORM\HasLifecycleCallbacks()
 */
class FreeOnMessage
{
    /**
     * @var int|null
     * @ORM\Id
     * @ORM\Column(type="integer",options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    const DAYS = [
        'SATURDAY',
        'SUNDAY',
        'MONDAY',
        'TUESDAY',
        'WEDNESDAY',
        'THURSDAY',
        'FRIDAY',
        'SATURDAY',
        'SUNDAY',
        'MONDAY',
        'TUESDAY',
        'WEDNESDAY',
        'THURSDAY',
        'FRIDAY',
        'SATURDAY',
        'SUNDAY',
    ];

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getSubject()
    {
        return $this->fromHour.':'.($this->fromMinute ?: '00').' - '.$this->toHour.':'.($this->toMinute ?: '00').'   ';
    }

    /**
     * @ORM\PrePersist
     */
    public function initiateUuid()
    {
        if (empty($this->uuid)) {
            $this->uuid = AppUtil::generateUuid(AppUtil::APP_NAME.'_FREE_ON');
        }
    }

    /**
     * @ORM\Column(type="string", length=191)
     * @Groups("read")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write"})
     */
    private $text;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $fromHour;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $toHour;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $fromMinute;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $toMinute;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $effectiveFrom;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $expireOn;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("read")
     */
    private $timezone = 'Asia/Singapore';

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="freeOnMessages")
     * @ORM\JoinColumn(name="id_organisation", referencedColumnName="id")
     */
    private $organisation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\IndividualMember", inversedBy="freeOnMessages")
     * @ORM\JoinColumn(name="id_sender", referencedColumnName="id")
     */
    private $sender;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $freeOnMondays;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $freeOnTuesdays;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $freeOnWednesdays;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $freeOnThursdays;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $freeOnFridays;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $freeOnSaturdays;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $freeOnSundays;

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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getFromHour(): ?int
    {
        return $this->fromHour;
    }

    public function setFromHour(?int $fromHour): self
    {
        $this->fromHour = $fromHour;

        return $this;
    }

    public function getToHour(): ?int
    {
        return $this->toHour;
    }

    public function setToHour(?int $toHour): self
    {
        $this->toHour = $toHour;

        return $this;
    }

    public function getFromMinute(): ?int
    {
        return $this->fromMinute;
    }

    public function setFromMinute(?int $fromMinute): self
    {
        $this->fromMinute = $fromMinute;

        return $this;
    }

    public function getToMinute(): ?int
    {
        return $this->toMinute;
    }

    public function setToMinute(?int $toMinute): self
    {
        $this->toMinute = $toMinute;

        return $this;
    }


    public function getEffectiveFrom(): ?\DateTimeInterface
    {
        return $this->effectiveFrom;
    }

    public function setEffectiveFrom(?\DateTimeInterface $effectiveFrom): self
    {
        $this->effectiveFrom = $effectiveFrom;

        return $this;
    }

    public function getExpireOn(): ?\DateTimeInterface
    {
        return $this->expireOn;
    }

    public function setExpireOn(?\DateTimeInterface $expireOn): self
    {
        $this->expireOn = $expireOn;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

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

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function getSender(): ?IndividualMember
    {
        return $this->sender;
    }

    public function setSender(?IndividualMember $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getFreeOnMondays(): ?bool
    {
        return $this->freeOnMondays;
    }

    public function setFreeOnMondays(?bool $freeOnMondays): self
    {
        $this->freeOnMondays = $freeOnMondays;

        return $this;
    }

    public function getFreeOnTuesdays(): ?bool
    {
        return $this->freeOnTuesdays;
    }

    public function setFreeOnTuesdays(?bool $freeOnTuesdays): self
    {
        $this->freeOnTuesdays = $freeOnTuesdays;

        return $this;
    }

    public function getFreeOnWednesdays(): ?bool
    {
        return $this->freeOnWednesdays;
    }

    public function setFreeOnWednesdays(?bool $freeOnWednesdays): self
    {
        $this->freeOnWednesdays = $freeOnWednesdays;

        return $this;
    }

    public function getFreeOnThursdays(): ?bool
    {
        return $this->freeOnThursdays;
    }

    public function setFreeOnThursdays(?bool $freeOnThursdays): self
    {
        $this->freeOnThursdays = $freeOnThursdays;

        return $this;
    }

    public function getFreeOnFridays(): ?bool
    {
        return $this->freeOnFridays;
    }

    public function setFreeOnFridays(?bool $freeOnFridays): self
    {
        $this->freeOnFridays = $freeOnFridays;

        return $this;
    }

    public function getFreeOnSaturdays(): ?bool
    {
        return $this->freeOnSaturdays;
    }

    public function setFreeOnSaturdays(?bool $freeOnSaturdays): self
    {
        $this->freeOnSaturdays = $freeOnSaturdays;

        return $this;
    }

    public function getFreeOnSundays(): ?bool
    {
        return $this->freeOnSundays;
    }

    public function setFreeOnSundays(?bool $freeOnSundays): self
    {
        $this->freeOnSundays = $freeOnSundays;

        return $this;
    }
}
