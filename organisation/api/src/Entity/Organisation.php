<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Util\AppUtil;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 * )
 * @ORM\Entity(repositoryClass="App\Repository\OrganisationRepository")
 * @ORM\Table(name="organisation__organisation")
 * @ORM\HasLifecycleCallbacks()
 */
class Organisation
{
    const TYPE_COMPANY = 'COMPANY';

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
            $this->uuid = AppUtil::generateUuid();
        }
    }

    public function getLogoUrlFromAws()
    {
        if (empty($this->hasLogo)) {
            return null;
        }

        $accessKey = getenv('S3_ACCESS_KEY');
        $secretKey = getenv('S3_SECRET_KEY');
        $region = getenv('S3_REGION');
        $bucket = getenv('S3_BUCKET');
        $directory = getenv('SE_DIRECTORY');
        $version = AppUtil::SDK_VERSION;

        $credentials = new Credentials($accessKey, $secretKey);

        $path = $directory.'/'.strtolower(AppUtil::APP_NAME).'/logo/'.$this->uuid;

        //Creating a presigned request
        $s3Client = new S3Client([
//            'profile' => 'default',
            'region' => $region,
            'version' => $version,
            'credentials' => $credentials,
        ]);

        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $path,
        ]);

        $request = $s3Client->createPresignedRequest($cmd, '+7 days');
        $url = (string) $request->getUri();

        return $url;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uuid;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $foundedOn;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $registrationNumber;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Organisation", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $hasLogo = false;

    public function __construct()
    {
        $this->children = new ArrayCollection();
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

    public function getFoundedOn(): ?\DateTimeInterface
    {
        return $this->foundedOn;
    }

    public function setFoundedOn(?\DateTimeInterface $foundedOn): self
    {
        $this->foundedOn = $foundedOn;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

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

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): self
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getHasLogo(): ?bool
    {
        return $this->hasLogo;
    }

    public function setHasLogo(bool $hasLogo): self
    {
        $this->hasLogo = $hasLogo;

        return $this;
    }
}
