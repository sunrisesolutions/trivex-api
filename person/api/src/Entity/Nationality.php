<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('ROLE_USER')"},
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\NationalityRepository")
 * @ORM\Table(name="person__nationality")
 * @ORM\HasLifecycleCallbacks()
 */
class Nationality
{
    /**
     * @var int|null The Nationality Id
     * @ORM\Id()
     * @ORM\Column(type="integer",options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"read","write"})
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write"})
     */
    private $nricNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write"})
     */
    private $passportNumber;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="nationalities")
     * @ORM\JoinColumn(name="id_person", referencedColumnName="id", onDelete="CASCADE")
     * @Groups("read")
     */
    private $person;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getNricNumber(): ?string
    {
        return $this->nricNumber;
    }

    public function setNricNumber(?string $nricNumber): self
    {
        $this->nricNumber = $nricNumber;

        return $this;
    }

    public function getPassportNumber(): ?string
    {
        return $this->passportNumber;
    }

    public function setPassportNumber(?string $passportNumber): self
    {
        $this->passportNumber = $passportNumber;

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
