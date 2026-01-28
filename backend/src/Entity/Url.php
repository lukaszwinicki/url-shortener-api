<?php

namespace App\Entity;

use App\Enum\Visibility;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'urls')]
#[ORM\Index(columns: ['short_code'], name: 'idx_short_code')]
#[ORM\Index(columns: ['session_id', 'deleted_at'], name: 'idx_session_deleted')]
class Url
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 8, unique: true)]
    private ?string $shortCode = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $originalUrl = null;

    #[ORM\Column(type: 'string', enumType: Visibility::class)]
    private Visibility $visibility = Visibility::Public;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: Types::STRING, length: 8, nullable: true, unique: true)]
    private ?string $customAlias = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'urls')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Session $session = null;

    /**
     * @var Collection<int, UrlLog>
     */
    #[ORM\OneToMany(targetEntity: UrlLog::class, mappedBy: 'url', cascade: ['persist', 'remove'])]
    private Collection $urlLogs;

    #[ORM\Column(type: Types::INTEGER)]
    private int $clickCount = 0;

    public function __construct()
    {
        $this->urlLogs = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShortCode(): ?string
    {
        return $this->shortCode;
    }

    public function setShortCode(string $shortCode): self
    {
        $this->shortCode = $shortCode;
        return $this;
    }

    public function getOriginalUrl(): ?string
    {
        return $this->originalUrl;
    }

    public function setOriginalUrl(?string $originalUrl): self
    {
        $this->originalUrl = $originalUrl;
        return $this;
    }

    public function getVisibility(): Visibility
    {
        return $this->visibility;
    }

    public function setVisibility(Visibility $visibility): self
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getCustomAlias(): ?string
    {
        return $this->customAlias;
    }

    public function setCustomAlias(?string $customAlias): self
    {
        $this->customAlias = $customAlias;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return Collection<int, UrlLog>
     */
    public function getUrlLogs(): Collection
    {
        return $this->urlLogs;
    }

    public function addUrlLog(UrlLog $urlLog): self
    {
        if (!$this->urlLogs->contains($urlLog)) {
            $this->urlLogs->add($urlLog);
            $urlLog->setUrl($this);
        }

        return $this;
    }

    public function removeUrlLog(UrlLog $urlLog): self
    {
        if ($this->urlLogs->removeElement($urlLog)) {
            if ($urlLog->getUrl() === $this) {
                $urlLog->setUrl(null);
            }
        }

        return $this;
    }

    public function getClickCount(): int
    {
        return $this->clickCount;
    }

    public function setClickCount(int $clickCount): self
    {
        $this->clickCount = $clickCount;
        return $this;
    }

    public function incrementClickCount(): self
    {
        $this->clickCount++;
        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt !== null && $this->expiresAt < new \DateTimeImmutable();
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}
