<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TagRepository::class)
 */
class Tag
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity=Experience::class, mappedBy="tags")
     */
    private $experiences;

    /**
     * @ORM\Column(type="boolean")
     */
    private $systemDefined = false;

    /**
     * @ORM\OneToOne(targetEntity=SecondaryIndustry::class, cascade={"persist", "remove"})
     */
    private $secondaryIndustry;

    /**
     * @ORM\ManyToOne(targetEntity=Industry::class)
     */
    private $primaryIndustry;

    public function __construct()
    {
        $this->experiences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Experience[]
     */
    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    public function addExperience(Experience $experience): self
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences[] = $experience;
            $experience->addTag($this);
        }

        return $this;
    }

    public function removeExperience(Experience $experience): self
    {
        if ($this->experiences->removeElement($experience)) {
            $experience->removeTag($this);
        }

        return $this;
    }

    public function getSystemDefined(): ?bool
    {
        return $this->systemDefined;
    }

    public function setSystemDefined(bool $systemDefined): self
    {
        $this->systemDefined = $systemDefined;

        return $this;
    }
    
    public function getSecondaryIndustry(): ?SecondaryIndustry
    {
        return $this->secondaryIndustry;
    }

    public function setSecondaryIndustry(?SecondaryIndustry $secondaryIndustry): self
    {
        $this->secondaryIndustry = $secondaryIndustry;

        return $this;
    }

    public function getPrimaryIndustry(): ?Industry
    {
        return $this->primaryIndustry;
    }

    public function setPrimaryIndustry(?Industry $primaryIndustry): self
    {
        $this->primaryIndustry = $primaryIndustry;

        return $this;
    }
}
