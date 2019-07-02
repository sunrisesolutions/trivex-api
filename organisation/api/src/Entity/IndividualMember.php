<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Util\AppUtil;
use App\Util\AwsS3Util;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\SendEmailToIndividualMember;

/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('ROLE_USER')"},
 *     collectionOperations={
 *         "get",
 *         "post"={"access_control"="is_granted('ROLE_ORG_ADMIN')"},
 *     },
 *     itemOperations={
 *     "get",
 *     "delete"={"access_control"="is_granted('ROLE_ORG_ADMIN')"},
 *     "put_email"={
 *         "method"="PUT",
 *         "path"="/individual_members/{id}/email",
 *         "controller"=SendEmailToIndividualMember::class,
 *         "normalization_context"={"groups"={"email"}},
 *         "denormalization_context"={"groups"={"email"}},
 *     }
 *     },
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}}
 * )
 * @ApiFilter(SearchFilter::class, properties={"uuid": "exact", "fulltextString": "partial"})
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
     * @var string
     * @Groups({"email"})
     */
    public $emailBody;

    /**
     * @var string
     * @Groups({"email"})
     */
    public $emailSubject;

    /**
     * @Groups({"read"})
     * @return mixed|string
     */
    public function getProfilePicture()
    {
        return AwsS3Util::getInstance()->getObjectReadUrl(sprintf('organisation/individual/profile-picture/ORG_IM-UUID-%d.jpg', $this->id));
    }

    /**
     * @ORM\PrePersist
     */
    public function initiateUuid()
    {
        if (empty($this->uuid)) {
            $this->uuid = AppUtil::generateUuid(sprintf(AppUtil::APP_NAME.'_IM'));
        }
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateFulltextString()
    {
        if (empty($person = $this->person)) {
            $this->fulltextString = '';
        } else {
            $fulltextString = '';
            $fulltextString .= 'name: '.$person->getName().' email: '.$person->getEmail().' employer: '.$person->getEmployerName();
            $this->fulltextString = $fulltextString;
        }
    }

    /**
     * @ORM\PrePersist
     */
    public function initiateAccessToken()
    {
        if (empty($this->accessToken)) {
            $this->accessToken = AppUtil::generateUuid(sprintf(AppUtil::APP_NAME.'_IMT'));
        }
    }

    /**
     * @Groups("write")
     */
    private $organisationUuid;

    /**
     * @Groups("write")
     */
    private $personUuid;

    /**
     * @return array
     * @Groups({"read"})
     */
    public function getPersonData()
    {
        $person = $this->person;

        return [
            'name' => $person->getName(),
            'jobTitle' => $person->getJobTitle(),
            'employerName' => $person->getEmployerName(),
            'dob' => $person->getBirthDate(),
            'nric' => ($nat = $person->getNationality()) ? $nat->getNricNumber() : '',
            'email' => $person->getEmail()
        ];
    }

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Connection", mappedBy="fromMember")
     */
    private $fromConnections;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Connection", mappedBy="toMember")
     */
    private $toConnections;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="individualMembers", cascade={"persist","merge"})
     * @ORM\JoinColumn(name="id_person", referencedColumnName="id", onDelete="CASCADE")
     */
    private $person;

    /**
     * @var Organisation
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="individualMembers", cascade={"persist","merge"})
     * @ORM\JoinColumn(name="id_organisation", referencedColumnName="id", onDelete="CASCADE")
     */
    private $organisation;

    /**
     * @var string
     * @ORM\Column(type="string", length=191)
     */
    private $accessToken;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Role", mappedBy="individualMember")
     */
    private $roles;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fulltextString;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->fromConnections = new ArrayCollection();
        $this->toConnections = new ArrayCollection();
        $this->createdAt = new \DateTime();
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

    /**
     * @return Collection|Connection[]
     */
    public function getFromConnections(): Collection
    {
        return $this->fromConnections;
    }

    public function addFromConnection(Connection $fromConnection): self
    {
        if (!$this->fromConnections->contains($fromConnection)) {
            $this->fromConnections[] = $fromConnection;
            $fromConnection->setFromMember($this);
        }

        return $this;
    }

    public function removeFromConnection(Connection $fromConnection): self
    {
        if ($this->fromConnections->contains($fromConnection)) {
            $this->fromConnections->removeElement($fromConnection);
            // set the owning side to null (unless already changed)
            if ($fromConnection->getFromMember() === $this) {
                $fromConnection->setFromMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Connection[]
     */
    public function getToConnections(): Collection
    {
        return $this->toConnections;
    }

    public function addToConnection(Connection $toConnection): self
    {
        if (!$this->toConnections->contains($toConnection)) {
            $this->toConnections[] = $toConnection;
            $toConnection->setToMember($this);
        }

        return $this;
    }

    public function removeToConnection(Connection $toConnection): self
    {
        if ($this->toConnections->contains($toConnection)) {
            $this->toConnections->removeElement($toConnection);
            // set the owning side to null (unless already changed)
            if ($toConnection->getToMember() === $this) {
                $toConnection->setToMember(null);
            }
        }

        return $this;
    }

    public function getFulltextString(): ?string
    {
        return $this->fulltextString;
    }

    public function setFulltextString(?string $fulltextString): self
    {
        $this->fulltextString = $fulltextString;

        return $this;
    }

    public function getPersonUuid(): ?string
    {
        return $this->personUuid;
    }

    public function setPersonUuid(?string $personUuid): self
    {
        $this->personUuid = $personUuid;

        return $this;
    }

    public function getOrganisationUuid(): ?string
    {
        return $this->organisationUuid;
    }

    public function setOrganisationUuid(?string $organisationUuid): self
    {
        $this->organisationUuid = $organisationUuid;

        return $this;
    }
}
