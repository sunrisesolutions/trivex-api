<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\IndividualMemberRepository")
 * @ORM\Table(name="event__individual_member")
 * @ORM\HasLifecycleCallbacks()
 */
class IndividualMember
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=191, nullable=true)
     */
    private $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="individualMembers")
     */
    private $organisation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="individualMembers")
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

    public function setUuid(?string $uuid): self
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
