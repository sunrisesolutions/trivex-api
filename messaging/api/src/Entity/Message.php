<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Util\AppUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Exception\UnsupportedException;

/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('ROLE_USER')"},
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 * @ORM\Table(name="messaging__message")
 * @ORM\HasLifecycleCallbacks()
 */
class Message
{
    const STATUS_DRAFT = 'MESSAGE_DRAFT';
    const STATUS_NEW = 'MESSAGE_NEW';
    const STATUS_DELIVERY_IN_PROGRESS = 'DELIVERY_IN_PROGRESS';
    const STATUS_DELIVERY_SUCCESSFUL = 'DELIVERY_SUCCESSFUL';
    const STATUS_RECEIVED = 'MESSAGE_RECEIVED';
    const STATUS_READ = 'MESSAGE_READ';

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
        $this->status = self::STATUS_DRAFT;
        $this->deliveries = new ArrayCollection();
    }

    public function commitDeliveries()
    {
        $deliveries = [];
        if (in_array($this->status, [self::STATUS_NEW, self::STATUS_DELIVERY_IN_PROGRESS])) {
            if (empty($this->conversation)) {
                $members = $this->organisation->getIndividualMembersByPage();
                if (empty($members)) {
                    return false;
                }
            } else {
                throw new UnsupportedException('Not yet implemented');
//                $members = $this->conversation->getParticipants();
//                $this->status = self::STATUS_DELIVERY_SUCCESSFUL;
            }

            /** @var IndividualMember $member */
            foreach ($members as $member) {
                $recipient = $member;
                $delivery = Delivery::createInstance($this, $recipient);
                $deliveries[] = $delivery;
            }
        } else {
            return false;
        }

        return $deliveries;
    }

    /**
     * @ORM\PrePersist
     */
    public function initiateUuid()
    {
        if (empty($this->uuid)) {
            $this->uuid = AppUtil::generateUuid();
            if (empty($this->code)) {
                $this->code = $this->uuid;
            }
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
     * @var Conversation
     * @ORM\ManyToOne(targetEntity="App\Entity\Conversation", inversedBy="messages")
     * @ORM\JoinColumn(name="id_conversation", referencedColumnName="id")
     * @Groups({"read", "write"})
     */
    private $conversation;

    /**
     * @var Organisation
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="messages")
     * @ORM\JoinColumn(name="id_organisation", referencedColumnName="id")
     * @Groups("read")
     */
    private $organisation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\IndividualMember", inversedBy="messages")
     * @ORM\JoinColumn(name="id_sender", referencedColumnName="id")
     * @Groups("read")
     */
    private $sender;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     */
    private $subject;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read", "write"})
     */
    private $body;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Delivery", mappedBy="message")
     */
    private $deliveries;

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

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): self
    {
        $this->conversation = $conversation;

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

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Delivery[]
     */
    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

    public function addDelivery(Delivery $delivery): self
    {
        if (!$this->deliveries->contains($delivery)) {
            $this->deliveries[] = $delivery;
            $delivery->setMessage($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): self
    {
        if ($this->deliveries->contains($delivery)) {
            $this->deliveries->removeElement($delivery);
            // set the owning side to null (unless already changed)
            if ($delivery->getMessage() === $this) {
                $delivery->setMessage(null);
            }
        }

        return $this;
    }
}
