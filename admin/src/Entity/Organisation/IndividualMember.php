<?php

namespace App\Entity\Organisation;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Util\Organisation\AppUtil;
use App\Util\Organisation\AwsS3Util;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
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
 *     "put"={"access_control"="is_granted('ROLE_ORG_ADMIN')"},
 *     "put_email"={
 *         "method"="PUT",
 *         "path"="/individual_members/{id}/email",
 *         "controller"=SendEmailToIndividualMember::class,
 *         "normalization_context"={"groups"={"email"}},
 *         "denormalization_context"={"groups"={"email"}},
 *     }
 *     },
 *     normalizationContext={"groups"={"read_member"}},
 *     denormalizationContext={"groups"={"write"}}
 * )
 * @ApiFilter(SearchFilter::class, properties={"uuid": "exact", "fulltextString": "partial"})
 * @ORM\Entity(repositoryClass="App\Repository\Organisation\IndividualMemberRepository")
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
     * @var string|null
     * @Groups({"email"})
     */
    public $emailBody;

    /**
     * @var string|null
     * @Groups({"email"})
     */
    public $emailSubject;

    /**
     * @Groups({"read_member"})
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
     * @return bool
     * @Groups("read_member")
     */
    public function isMessageDeliverable(): bool
    {
        return !empty($this->getMessageDeliverable());
    }

    public function getMessageDeliverable()
    {
        $c = Criteria::create();
        $expr = Criteria::expr();
        $c->andWhere($expr->eq('name', 'ROLE_MSG_USER'));
        $this->messageDeliverable = $this->roles->matching($c)->count() > 0;
        return $this->messageDeliverable;
    }

    /**
     * @return bool
     * @Groups("read_member")
     */
    public function isAdmin(): bool
    {
        return !empty($this->getAdmin());
    }

    public function getAdmin(): ?bool
    {
        $c = Criteria::create();
        $expr = Criteria::expr();
        $c->andWhere($expr->eq('name', 'ROLE_ORG_ADMIN'));
        $this->admin = $this->roles->matching($c)->count() > 0;
        return $this->admin;
    }

    /**
     * @return bool
     * @Groups("read_member")
     */
    public function isMessageAdmin(): bool
    {
        return !empty($this->getMessageAdmin());
    }

    public function getMessageAdmin(): ?bool
    {
        $c = Criteria::create();
        $expr = Criteria::expr();
        $c->andWhere($expr->eq('name', 'ROLE_MSG_ADMIN'));
        $this->messageAdmin = $this->roles->matching($c)->count() > 0;
        return $this->messageAdmin;
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
            $fulltextString .= 'name: '.$person->getGivenName().' email: '.$person->getEmail().' employer: '.$person->getEmployerName().' job: '.$person->getJobTitle();
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
     * @var string
     * @Groups({"read_member", "write"})
     */
    private $organisationUuid;

    /**
     * @var string
     * @Groups("write")
     */
    private $personUuid;

    /**
     * @return array
     * @Groups({"read_member"})
     */
    public function getPersonData()
    {
        $person = $this->person;
        if (empty($person)) {
            return [];
        }
        return [
            'uuid' => $person->getUuid(),
            'name' => $person->getGivenName(),
            'jobTitle' => $person->getJobTitle(),
            'employerName' => $person->getEmployerName(),
            'dob' => $person->getBirthDate(),
            'nric' => ($nat = $person->getNationality()) ? $nat->getNricNumber() : '',
            'email' => $person->getEmail(),
            'phone' => $person->getPhoneNumber()
        ];
    }

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Organisation\Connection", mappedBy="fromMember")
     * @ApiSubresource()
     */
    private $fromConnections;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Organisation\Connection", mappedBy="toMember")
     * @ApiSubresource()
     */
    private $toConnections;

    /**
     * @var string
     * @ORM\Column(type="string", length=191)
     * @Groups({"read_member"})
     */
    private $uuid;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Groups({"read_member"})
     */
    private $createdAt;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation\Person", inversedBy="individualMembers", cascade={"persist","merge"})
     * @ORM\JoinColumn(name="id_person", referencedColumnName="id", onDelete="SET NULL")
     */
    private $person;

    /**
     * @var Organisation
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation\Organisation", inversedBy="individualMembers", cascade={"persist","merge"})
     * @ORM\JoinColumn(name="id_organisation", referencedColumnName="id", onDelete="SET NULL")
     */
    private $organisation;

    /**
     * @var string
     * @ORM\Column(type="string", length=191)
     */
    private $accessToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fulltextString;

    /**
     * @var boolean|null
     * @Groups("write")
     */
    public $admin = false;

    /**
     * @var boolean|null
     * @Groups("write")
     */
    public $messageDeliverable = false;

    /**
     * @var boolean|null
     * @Groups("write")
     */
    public $messageAdmin = false;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Organisation\Role", inversedBy="individualMembers")
     * @ORM\JoinTable(name="organisation__individuals_roles",
     *      joinColumns={@ORM\JoinColumn(name="id_individual", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="id_role", referencedColumnName="id")}
     *      )
     */
    private $roles;

    public function __construct()
    {
        $this->fromConnections = new ArrayCollection();
        $this->toConnections = new ArrayCollection();
        $this->createdAt = new \DateTime();
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
        if (empty($this->organisationUuid) && !empty($this->organisation)) {
            $this->organisationUuid = $this->organisation->getUuid();
        }
        return $this->organisationUuid;
    }

    public function setOrganisationUuid(?string $organisationUuid): self
    {
        $this->organisationUuid = $organisationUuid;

        return $this;
    }

    public function setAdmin(?bool $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function setMessageDeliverable(?bool $messageDeliverable): self
    {
        $this->messageDeliverable = $messageDeliverable;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmailBody(): ?string
    {
        return $this->emailBody;
    }

    /**
     * @param string|null $emailBody
     */
    public function setEmailBody(?string $emailBody): void
    {
        $this->emailBody = $emailBody;
    }

    /**
     * @return string|null
     */
    public function getEmailSubject(): ?string
    {
        return $this->emailSubject;
    }

    /**
     * @param string|null $emailSubject
     */
    public function setEmailSubject(?string $emailSubject): void
    {
        $this->emailSubject = $emailSubject;
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
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }

        return $this;
    }

    public function hasRole(string $roleName): bool {
        $c = Criteria::create()->where(Criteria::expr()->eq('name', $roleName));
        return $this->roles->matching($c)->count() > 0;
    }
}
