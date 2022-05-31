<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\QuestionRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    collectionOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['get_list']],
        ],
    ],
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['get_detail_question']],
        ],
    ],
    attributes: ["security" => "is_granted('ROLE_USER')"],
    normalizationContext: ['groups' => ['get_list', 'get_detail_question']],
)]
#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["get_list", "get_detail_question"])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["get_list", "get_detail_question"])]
    private $header;

    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    #[Groups("get_detail_question")]
    private $text;

    #[ORM\Column(type: 'string', length: 32)]
    #[Groups(["get_list", "get_detail_question"])]
    private $category;

    #[ORM\Column(type: 'boolean')]
    private $isModerated;

    #[ORM\Column(type: 'datetime')]
    #[Groups(["get_list", "get_detail_question"])]
    private $dateCreated;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Answer::class, orphanRemoval: true)]
    private $answers;

    #[Groups("get_detail_question")]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private $author;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->header . ' ' . $this->category;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeader(): ?string
    {
        return $this->header;
    }

    public function setHeader(string $header): self
    {
        $this->header = $header;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getIsModerated(): ?bool
    {
        return $this->isModerated;
    }

    public function setIsModerated(bool $isModerated): self
    {
        $this->isModerated = $isModerated;

        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    public function getDateCreated(): ?DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }
}
