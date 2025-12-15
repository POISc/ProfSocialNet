<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    private ?string $nameCompany = null;

    /**
     * @var Collection<int, Vacancy>
     */
    #[ORM\OneToMany(targetEntity: Vacancy::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $vacancies;

    public function __construct()
    {
        $this->vacancies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameCompany(): ?string
    {
        return $this->nameCompany;
    }

    public function setNameCompany(string $nameCompany): static
    {
        $this->nameCompany = $nameCompany;

        return $this;
    }

    /**
     * @return Collection<int, Vacancy>
     */
    public function getVacancies(): Collection
    {
        return $this->vacancies;
    }

    public function addVacancy(Vacancy $vacancy): static
    {
        if (!$this->vacancies->contains($vacancy)) {
            $this->vacancies->add($vacancy);
            $vacancy->setCompany($this);
        }

        return $this;
    }

    public function removeVacancy(Vacancy $vacancy): static
    {
        if ($this->vacancies->removeElement($vacancy)) {
            // set the owning side to null (unless already changed)
            if ($vacancy->getCompany() === $this) {
                $vacancy->setCompany(null);
            }
        }

        return $this;
    }
}
