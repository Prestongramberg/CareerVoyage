<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RegionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Region
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RegionalCoordinator", mappedBy="region")
     */
    private $regionalCoordinators;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\State", inversedBy="regions")
     */
    private $state;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\School", mappedBy="region")
     */
    private $schools;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="regions")
     */
    private $site;

    public function __construct()
    {
        $this->regionalCoordinators = new ArrayCollection();
        $this->schools = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|RegionalCoordinator[]
     */
    public function getRegionalCoordinators(): Collection
    {
        return $this->regionalCoordinators;
    }

    public function addRegionalCoordinator(RegionalCoordinator $regionalCoordinator): self
    {
        if (!$this->regionalCoordinators->contains($regionalCoordinator)) {
            $this->regionalCoordinators[] = $regionalCoordinator;
            $regionalCoordinator->setRegion($this);
        }

        return $this;
    }

    public function removeRegionalCoordinator(RegionalCoordinator $regionalCoordinator): self
    {
        if ($this->regionalCoordinators->contains($regionalCoordinator)) {
            $this->regionalCoordinators->removeElement($regionalCoordinator);
            // set the owning side to null (unless already changed)
            if ($regionalCoordinator->getRegion() === $this) {
                $regionalCoordinator->setRegion(null);
            }
        }

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Collection|School[]
     */
    public function getSchools(): Collection
    {
        return $this->schools;
    }

    public function addSchool(School $school): self
    {
        if (!$this->schools->contains($school)) {
            $this->schools[] = $school;
            $school->setRegion($this);
        }

        return $this;
    }

    public function removeSchool(School $school): self
    {
        if ($this->schools->contains($school)) {
            $this->schools->removeElement($school);
            // set the owning side to null (unless already changed)
            if ($school->getRegion() === $this) {
                $school->setRegion(null);
            }
        }

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }
}
