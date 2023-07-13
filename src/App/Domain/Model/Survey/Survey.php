<?php

namespace App\Domain\Model\Survey;


use App\Infrastructure\Framework\Symfony\Repository\SurveyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SurveyRepository::class)]
#[UniqueEntity(fields: 'name', errorPath: 'name')]
class Survey
{
    public const STATUS_NEW = 'new';
    public const STATUS_LIVE = 'live';
    public const STATUS_CLOSED = 'closed';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private ?UuidInterface $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 32)]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: Answer::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $answers;

    #[ORM\OneToOne(mappedBy: 'survey', cascade: ['persist', 'remove'])]
    #[Ignore]
    private ?Report $report = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $reportEmail = null;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): self
    {
        $this->id = $id;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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
            $this->answers->add($answer);
            $answer->setSurvey($this);
        }

        return $this;
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(Report $report): self
    {
        if ($report->getSurvey() !== $this) {
            $report->setSurvey($this);
        }

        $this->report = $report;

        return $this;
    }

    public function getReportEmail(): string
    {
        return $this->reportEmail;
    }

    public function setReportEmail(string $reportEmail): self
    {
        $this->reportEmail = $reportEmail;

        return $this;
    }
}