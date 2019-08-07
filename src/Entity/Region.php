<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RegionRepository")
 */
class Region
{
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
     * @ORM\OneToMany(targetEntity="App\Entity\RegionalCoordinatorRequest", mappedBy="region")
     */
    private $regionalCoordinatorRequests;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\State", inversedBy="regions")
     */
    private $state;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\School", mappedBy="region")
     */
    private $schools;

    public function __construct()
    {
        $this->regionalCoordinators = new ArrayCollection();
        $this->regionalCoordinatorRequests = new ArrayCollection();
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

    /**
     * @return Collection|RegionalCoordinatorRequest[]
     */
    public function getRegionalCoordinatorRequests(): Collection
    {
        return $this->regionalCoordinatorRequests;
    }

    public function addRegionalCoordinatorRequest(RegionalCoordinatorRequest $regionalCoordinatorRequest): self
    {
        if (!$this->regionalCoordinatorRequests->contains($regionalCoordinatorRequest)) {
            $this->regionalCoordinatorRequests[] = $regionalCoordinatorRequest;
            $regionalCoordinatorRequest->setRegion($this);
        }

        return $this;
    }

    public function removeRegionalCoordinatorRequest(RegionalCoordinatorRequest $regionalCoordinatorRequest): self
    {
        if ($this->regionalCoordinatorRequests->contains($regionalCoordinatorRequest)) {
            $this->regionalCoordinatorRequests->removeElement($regionalCoordinatorRequest);
            // set the owning side to null (unless already changed)
            if ($regionalCoordinatorRequest->getRegion() === $this) {
                $regionalCoordinatorRequest->setRegion(null);
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
}
