<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Util\AppUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(shortName="acrole")
 * @ORM\Entity(repositoryClass="App\Repository\ACRoleRepository")
 * @ORM\Table(name="authorisation__role")
 * @ORM\HasLifecycleCallbacks()
 */
class ACRole
{
    /**
     * @var int|null The User Id
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer",options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string The Universally Unique Id
     * @ORM\Column(type="string", length=191, unique=true)
     * @Assert\NotBlank()
     */
    private $uuid;

    /**
     * @ORM\PrePersist
     */
    public function initiateUuid()
    {
        if (empty($this->uuid)) {
            $this->uuid = AppUtil::generateUuid();
        }
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ACEntry", mappedBy="role")
     */
    private $entries;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="roles")
     */
    private $organisation;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|ACEntry[]
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function addEntry(ACEntry $entry): self
    {
        if (!$this->entries->contains($entry)) {
            $this->entries[] = $entry;
            $entry->setRole($this);
        }

        return $this;
    }

    public function removeEntry(ACEntry $entry): self
    {
        if ($this->entries->contains($entry)) {
            $this->entries->removeElement($entry);
            // set the owning side to null (unless already changed)
            if ($entry->getRole() === $this) {
                $entry->setRole(null);
            }
        }

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
}
