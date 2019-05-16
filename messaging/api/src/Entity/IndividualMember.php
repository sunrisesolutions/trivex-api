<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\IndividualMemberRepository")
 * @ORM\Table(name="messaging__individual_member")
 * @ORM\HasLifecycleCallbacks()
 */
class IndividualMember
{
    private $messageDeliveryCache = [];

    /**
     * @var int|null
     * @ORM\Id
     * @ORM\Column(type="integer",options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->deliveries = new ArrayCollection();
    }

    public function isMessageDelivered(Message $message)
    {
        if (empty($this->getMessageDelivery($message))) {
            return false;
        }

        return true;
    }

    public function getMessageDelivery(Message $message)
    {
        if (array_key_exists($message->getId(), $this->messageDeliveryCache)) {
            if ($this->messageDeliveryCache[$message->getId()]) {
                return $this->messageDeliveryCache[$message->getId()];
            }
        }
        $c = Criteria::create();
        $expr = Criteria::expr();

        $c->where($expr->eq('message', $message));
        $deliveries = $this->deliveries->matching($c);
        if ($deliveries->count() > 0) {
            return $this->messageDeliveryCache[$message->getId()] = $deliveries->first();
        }

        return null;
    }

    /**
     * @ORM\Column(type="string", length=191)
     */
    private $uuid;

    /**
     * @var Organisation
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="individualMembers")
     * @ORM\JoinColumn(name="id_organisation", referencedColumnName="id")
     */
    private $organisation;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="sender")
     */
    private $messages;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Conversation", inversedBy="participants")
     * @ORM\JoinColumn(name="id_conversation", referencedColumnName="id")
     */
    private $conversation;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Delivery", mappedBy="recipient")
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

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setSender($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

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
            $delivery->setRecipient($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): self
    {
        if ($this->deliveries->contains($delivery)) {
            $this->deliveries->removeElement($delivery);
            // set the owning side to null (unless already changed)
            if ($delivery->getRecipient() === $this) {
                $delivery->setRecipient(null);
            }
        }

        return $this;
    }
}
