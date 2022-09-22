<?php

namespace App\Entity;

use App\Repository\RaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RaceRepository::class)]
class Race
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $raceName = null;

    #[ORM\Column(length: 255)]
    private ?string $date = null;

    #[ORM\Column(length: 255)]
    private ?string $filePath = null;

    #[ORM\OneToMany(mappedBy: 'race', targetEntity: Result::class)]
    private Collection $result;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaceName(): ?string
    {
        return $this->raceName;
    }

    public function setRaceName(string $raceName): self
    {
        $this->raceName = $raceName;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @return Collection<int, Result>
     */
    public function getResult(): Collection
    {
        return $this->result;
    }

    public function addResult(Result $result): self
    {
        if (!$this->result->contains($result)) {
            $this->result->add($result);
            $result->setRace($this);
        }

        return $this;
    }

    public function removeResult(Result $result): self
    {
        if ($this->result->removeElement($result)) {
            // set the owning side to null (unless already changed)
            if ($result->getRace() === $this) {
                $result->setRace(null);
            }
        }

        return $this;
    }

}
