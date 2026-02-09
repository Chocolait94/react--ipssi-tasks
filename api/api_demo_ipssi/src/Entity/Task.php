<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entite Task - Represente une tache dans notre application
 *
 * Cette entite utilise les attributs PHP 8 pour la configuration Doctrine ORM.
 * Chaque propriete est mappee a une colonne de la table "task" en base de donnees.
 */
#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'task')]
#[ORM\HasLifecycleCallbacks]
class Task
{
    // Constantes pour les statuts possibles d'une tache
    public const STATUS_PENDING = 'pending';       // En attente
    public const STATUS_IN_PROGRESS = 'in_progress'; // En cours
    public const STATUS_COMPLETED = 'completed';   // Terminee

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dueDate = null;

    // =====================================================
    // GETTERS ET SETTERS
    // =====================================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        // Validation du statut
        $validStatuses = [self::STATUS_PENDING, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException(
                sprintf('Statut invalide "%s". Valeurs acceptees: %s', $status, implode(', ', $validStatuses))
            );
        }
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    // =====================================================
    // LIFECYCLE CALLBACKS (Evenements Doctrine)
    // =====================================================

    /**
     * Appele automatiquement AVANT l'insertion en base
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Appele automatiquement AVANT chaque mise a jour
     */
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // =====================================================
    // METHODES UTILITAIRES
    // =====================================================

    /**
     * Retourne les statuts valides sous forme de tableau
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
        ];
    }

    /**
     * Convertit l'entite en tableau pour la serialisation JSON
     * Methode utile pour l'API REST
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'createdAt' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'dueDate' => $this->dueDate?->format('Y-m-d'),
        ];
    }
}
