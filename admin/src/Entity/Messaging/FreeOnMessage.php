<?php

namespace App\Entity\Messaging;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Util\Messaging\AppUtil;
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
 * @ORM\Entity(repositoryClass="App\Repository\Messaging\FreeOnMessageRepository")
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

    public function getSubject()
    {
        return $this->fromHour.' - '.$this->toHour;
    }

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
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $fromDay;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $toDay;

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

    public function getFromDay(): ?int
    {
        return $this->fromDay;
    }

    public function setFromDay(?int $fromDay): self
    {
        $this->fromDay = $fromDay;

        return $this;
    }

    public function getToDay(): ?int
    {
        return $this->toDay;
    }

    public function setToDay(?int $toDay): self
    {
        $this->toDay = $toDay;

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
}
