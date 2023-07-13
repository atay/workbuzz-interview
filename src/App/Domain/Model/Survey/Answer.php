<?php

namespace App\Domain\Model\Survey;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
class Answer
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private ?UuidInterface $id = null;

    #[ORM\Column]
    #[Assert\Range(min: -2, max: 2)]
    private ?int $quality = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'answer')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?Survey $survey = null;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getQuality(): ?int
    {
        return $this->quality;
    }

    public function setQuality(int $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): self
    {
        $this->survey = $survey;

        return $this;
    }

    #[Assert\Callback]
    public function validateComment(ExecutionContextInterface $context, $payload): void
    {
        // require comment when negative recommendation
        if ($this->getComment() === null && in_array($this->getQuality(), [-2, -1], true)) {
            $context
                ->buildViolation('Comment is required for poor quality')
                ->atPath('comment')
                ->addViolation();
        } else if ($this->getComment() !== null && in_array($this->getQuality(), [0, 1, 2], true)) {
            $context
                ->buildViolation('There should be no comment when good quality')
                ->atPath('comment')
                ->addViolation();
        }
    }
}