<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Util\AppUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('ROLE_USER')"},
 *     collectionOperations={
 *         "get",
 *         "post"={"access_control"="is_granted('ROLE_ADMIN')"}
 *     },
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\IndividualMemberRepository")
 * @ORM\Table(name="organisation__individual_member")
 * @ORM\HasLifecycleCallbacks()
 */
class IndividualMember
{
    const TYPE_SUBSCRIPTION = 'SUBSCRIPTION';

    /**
     * @var int|null The Event Id
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer",options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\PrePersist
     */
    public function initiateUuid()
    {
        if (empty($this->uuid)) {
            $this->uuid = AppUtil::generateUuid(sprintf(AppUtil::APP_NAME.'_IM_%s_%s', $this->organisation->getId(), $this->person->getId()));
        }
    }

    /**
     * @ORM\PrePersist
     */
    public function initiateAccessToken()
    {
        if (empty($this->uuid)) {
            $this->uuid = AppUtil::generateUuid(sprintf(AppUtil::APP_NAME.'_IMT_%s_%s', $this->organisation->getId(), $this->person->getId()));
        }
    }

    /**
     * @return array
     * @Groups({"read"})
     */
    public function getPersonData()
    {
        $person = $this->person;

        return ['name' => $person->getName(), 'jobTitle' => $person->getJobTitle(), 'employerName' => $person->getEmployerName()];
    }

    /**
     * @var string
     * @ORM\Column(type="string", length=191)
     * @Groups({"read"})
     */
    private $uuid;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Groups({"read"})
     */
    private $createdAt;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="individualMembers")
     */
    private $person;

    /**
     * @var Organisation
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="individualMembers")
     */
    private $organisation;

    /**
     * @var string
     * @ORM\Column(type="string", length=191)
     * @Groups({"read"})
     */
    private $accessToken;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Role", mappedBy="individualMember")
     */
    private $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

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

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return Collection|Role[]
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->setIndividualMember($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
            // set the owning side to null (unless already changed)
            if ($role->getIndividualMember() === $this) {
                $role->setIndividualMember(null);
            }
        }

        return $this;
    }
}
