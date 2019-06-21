<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('ROLE_USER') and object.uuid == user.imUuid"},
 *     collectionOperations={
 *     },
 *     itemOperations={
 *     "get"={},
 *     },
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}}
 * )
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
        $this->conversations = new ArrayCollection();
        $this->notifSubscriptions = new ArrayCollection();
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
     * @ORM\OneToMany(targetEntity="App\Entity\Delivery", mappedBy="recipient")
     */
    private $deliveries;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Conversation", mappedBy="participants")
     */
    private $conversations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\NotifSubscription", mappedBy="individualMember")
     */
    private $notifSubscriptions;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="individualMembers")
     * @ORM\JoinColumn(name="id_person", referencedColumnName="id")
     */
    private $person;

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

    /**
     * @return Collection|Conversation[]
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): self
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations[] = $conversation;
            $conversation->addParticipant($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): self
    {
        if ($this->conversations->contains($conversation)) {
            $this->conversations->removeElement($conversation);
            $conversation->removeParticipant($this);
        }

        return $this;
    }

    /**
     * @return Collection|NotifSubscription[]
     */
    public function getNotifSubscriptions(): Collection
    {
        return $this->notifSubscriptions;
    }

    public function addNotifSubscription(NotifSubscription $notifSubscription): self
    {
        if (!$this->notifSubscriptions->contains($notifSubscription)) {
            $this->notifSubscriptions[] = $notifSubscription;
            $notifSubscription->setIndividualMember($this);
        }

        return $this;
    }

    public function removeNotifSubscription(NotifSubscription $notifSubscription): self
    {
        if ($this->notifSubscriptions->contains($notifSubscription)) {
            $this->notifSubscriptions->removeElement($notifSubscription);
            // set the owning side to null (unless already changed)
            if ($notifSubscription->getIndividualMember() === $this) {
                $notifSubscription->setIndividualMember(null);
            }
        }

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
}
