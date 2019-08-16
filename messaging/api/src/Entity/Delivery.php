<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

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
 * @ApiFilter(SearchFilter::class, properties={"uuid": "exact", "selectedOptions": "partial"})

 * @ORM\Entity(repositoryClass="App\Repository\DeliveryRepository")
 * @ORM\Table(name="messaging__delivery")
 * @ORM\HasLifecycleCallbacks()
 */
class Delivery
{
    /**
     * @var int|null
     * @ORM\Id
     * @ORM\Column(type="integer",options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public static function createInstance(Message $message, IndividualMember $recipient)
    {
        $d = new Delivery();
        $d->message = $message;
        $d->recipient = $recipient;

        return $d;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getUnreadDeliveryCount(){

    }

    /**
     * @ORM\PrePersist
     */
    public function initiateUuid()
    {
        if (empty($this->uuid)) {
            $this->uuid = AppUtil::generateUuid(AppUtil::APP_NAME.'_DELIV');
        }
    }

    /**
     * @ORM\Column(type="string", length=191)
     * @Groups("read")
     */
    private $uuid;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("read")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read", "write"})
     */
    private $readAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("read")
     */
    private $updatedAt;

    /**
     * @var Message
     * @ORM\ManyToOne(targetEntity="App\Entity\Message", inversedBy="deliveries")
     * @ORM\JoinColumn(name="id_message", referencedColumnName="id")
     * @Groups("read")
     */
    private $message;

    /**
     * @var IndividualMember
     * @ORM\ManyToOne(targetEntity="App\Entity\IndividualMember", inversedBy="deliveries")
     * @ORM\JoinColumn(name="id_recipient", referencedColumnName="id")
     * @Groups("read")
     */
    private $recipient;

    /**
     * @var array
     * @ORM\Column(type="magenta_json", nullable=true)
     * @Groups({"read", "write"})
     */
    private $selectedOptions = [];

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

    public function getReadAt(): ?\DateTimeInterface
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeInterface $readAt): self
    {
        $this->readAt = $readAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getRecipient(): ?IndividualMember
    {
        return $this->recipient;
    }

    public function setRecipient(?IndividualMember $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getSelectedOptions()
    {
        return $this->selectedOptions;
    }

    public function setSelectedOptions(array $selectedOptions): self
    {
        $this->selectedOptions = $selectedOptions;

        return $this;
    }
}
