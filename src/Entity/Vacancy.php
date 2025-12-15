<?php

namespace App\Entity;

use App\Repository\VacancyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VacancyRepository::class)]
class Vacancy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nameVacancy = null;

    #[ORM\ManyToOne(inversedBy: 'vacancies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $skills = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameVacancy(): ?string
    {
        return $this->nameVacancy;
    }

    public function setNameVacancy(string $nameVacancy): static
    {
        $this->nameVacancy = $nameVacancy;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

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

    public function getSkills(): ?string
    {
        return $this->skills;
    }

    public function setSkills(?string $skills): static
    {
        $this->skills = $skills;

        return $this;
    }
}
