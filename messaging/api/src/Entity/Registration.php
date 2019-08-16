<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RegistrationRepository")
 */
class Registration
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\IndividualMember", inversedBy="registrations")
     */
    private $individualMember;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIndividualMember(): ?IndividualMember
    {
        return $this->individualMember;
    }

    public function setIndividualMember(?IndividualMember $individualMember): self
    {
        $this->individualMember = $individualMember;

        return $this;
    }
}
