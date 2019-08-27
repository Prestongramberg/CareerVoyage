<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SiteRepository")
 */
class Site
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
     * @ORM\OneToMany(targetEntity="App\Entity\SiteAdminUser", mappedBy="site")
     */
    private $siteAdminUsers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SiteAdminRequest", mappedBy="site")
     */
    private $siteAdminRequests;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $baseUrl;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StateCoordinator", mappedBy="site")
     */
    private $stateCoordinators;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RegionalCoordinator", mappedBy="site")
     */
    private $regionalCoordinators;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Region", mappedBy="site")
     */
    private $regions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolAdministrator", mappedBy="site")
     */
    private $schoolAdministrators;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentUser", mappedBy="site")
     */
    private $studentUsers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EducatorUser", mappedBy="site")
     */
    private $educatorUsers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\School", mappedBy="site")
     */
    private $schools;

    public function __construct()
    {
        $this->siteAdminUsers = new ArrayCollection();
        $this->siteAdminRequests = new ArrayCollection();
        $this->stateCoordinators = new ArrayCollection();
        $this->regionalCoordinators = new ArrayCollection();
        $this->regions = new ArrayCollection();
        $this->schoolAdministrators = new ArrayCollection();
        $this->studentUsers = new ArrayCollection();
        $this->educatorUsers = new ArrayCollection();
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
     * @return Collection|SiteAdminUser[]
     */
    public function getSiteAdminUsers(): Collection
    {
        return $this->siteAdminUsers;
    }

    public function addSiteAdminUser(SiteAdminUser $siteAdminUser): self
    {
        if (!$this->siteAdminUsers->contains($siteAdminUser)) {
            $this->siteAdminUsers[] = $siteAdminUser;
            $siteAdminUser->setSite($this);
        }

        return $this;
    }

    public function removeSiteAdminUser(SiteAdminUser $siteAdminUser): self
    {
        if ($this->siteAdminUsers->contains($siteAdminUser)) {
            $this->siteAdminUsers->removeElement($siteAdminUser);
            // set the owning side to null (unless already changed)
            if ($siteAdminUser->getSite() === $this) {
                $siteAdminUser->setSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SiteAdminRequest[]
     */
    public function getSiteAdminRequests(): Collection
    {
        return $this->siteAdminRequests;
    }

    public function addSiteAdminRequest(SiteAdminRequest $siteAdminRequest): self
    {
        if (!$this->siteAdminRequests->contains($siteAdminRequest)) {
            $this->siteAdminRequests[] = $siteAdminRequest;
            $siteAdminRequest->setSite($this);
        }

        return $this;
    }

    public function removeSiteAdminRequest(SiteAdminRequest $siteAdminRequest): self
    {
        if ($this->siteAdminRequests->contains($siteAdminRequest)) {
            $this->siteAdminRequests->removeElement($siteAdminRequest);
            // set the owning side to null (unless already changed)
            if ($siteAdminRequest->getSite() === $this) {
                $siteAdminRequest->setSite(null);
            }
        }

        return $this;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @return Collection|StateCoordinator[]
     */
    public function getStateCoordinators(): Collection
    {
        return $this->stateCoordinators;
    }

    public function addStateCoordinator(StateCoordinator $stateCoordinator): self
    {
        if (!$this->stateCoordinators->contains($stateCoordinator)) {
            $this->stateCoordinators[] = $stateCoordinator;
            $stateCoordinator->setSite($this);
        }

        return $this;
    }

    public function removeStateCoordinator(StateCoordinator $stateCoordinator): self
    {
        if ($this->stateCoordinators->contains($stateCoordinator)) {
            $this->stateCoordinators->removeElement($stateCoordinator);
            // set the owning side to null (unless already changed)
            if ($stateCoordinator->getSite() === $this) {
                $stateCoordinator->setSite(null);
            }
        }

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
            $regionalCoordinator->setSite($this);
        }

        return $this;
    }

    public function removeRegionalCoordinator(RegionalCoordinator $regionalCoordinator): self
    {
        if ($this->regionalCoordinators->contains($regionalCoordinator)) {
            $this->regionalCoordinators->removeElement($regionalCoordinator);
            // set the owning side to null (unless already changed)
            if ($regionalCoordinator->getSite() === $this) {
                $regionalCoordinator->setSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Region[]
     */
    public function getRegions(): Collection
    {
        return $this->regions;
    }

    public function addRegion(Region $region): self
    {
        if (!$this->regions->contains($region)) {
            $this->regions[] = $region;
            $region->setSite($this);
        }

        return $this;
    }

    public function removeRegion(Region $region): self
    {
        if ($this->regions->contains($region)) {
            $this->regions->removeElement($region);
            // set the owning side to null (unless already changed)
            if ($region->getSite() === $this) {
                $region->setSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SchoolAdministrator[]
     */
    public function getSchoolAdministrators(): Collection
    {
        return $this->schoolAdministrators;
    }

    public function addSchoolAdministrator(SchoolAdministrator $schoolAdministrator): self
    {
        if (!$this->schoolAdministrators->contains($schoolAdministrator)) {
            $this->schoolAdministrators[] = $schoolAdministrator;
            $schoolAdministrator->setSite($this);
        }

        return $this;
    }

    public function removeSchoolAdministrator(SchoolAdministrator $schoolAdministrator): self
    {
        if ($this->schoolAdministrators->contains($schoolAdministrator)) {
            $this->schoolAdministrators->removeElement($schoolAdministrator);
            // set the owning side to null (unless already changed)
            if ($schoolAdministrator->getSite() === $this) {
                $schoolAdministrator->setSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StudentUser[]
     */
    public function getStudentUsers(): Collection
    {
        return $this->studentUsers;
    }

    public function addStudentUser(StudentUser $studentUser): self
    {
        if (!$this->studentUsers->contains($studentUser)) {
            $this->studentUsers[] = $studentUser;
            $studentUser->setSite($this);
        }

        return $this;
    }

    public function removeStudentUser(StudentUser $studentUser): self
    {
        if ($this->studentUsers->contains($studentUser)) {
            $this->studentUsers->removeElement($studentUser);
            // set the owning side to null (unless already changed)
            if ($studentUser->getSite() === $this) {
                $studentUser->setSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EducatorUser[]
     */
    public function getEducatorUsers(): Collection
    {
        return $this->educatorUsers;
    }

    public function addEducatorUser(EducatorUser $educatorUser): self
    {
        if (!$this->educatorUsers->contains($educatorUser)) {
            $this->educatorUsers[] = $educatorUser;
            $educatorUser->setSite($this);
        }

        return $this;
    }

    public function removeEducatorUser(EducatorUser $educatorUser): self
    {
        if ($this->educatorUsers->contains($educatorUser)) {
            $this->educatorUsers->removeElement($educatorUser);
            // set the owning side to null (unless already changed)
            if ($educatorUser->getSite() === $this) {
                $educatorUser->setSite(null);
            }
        }

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
            $school->setSite($this);
        }

        return $this;
    }

    public function removeSchool(School $school): self
    {
        if ($this->schools->contains($school)) {
            $this->schools->removeElement($school);
            // set the owning side to null (unless already changed)
            if ($school->getSite() === $this) {
                $school->setSite(null);
            }
        }

        return $this;
    }
}
