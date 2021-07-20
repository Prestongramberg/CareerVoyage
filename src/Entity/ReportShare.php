<?php

namespace App\Entity;

use App\Repository\ReportShareRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReportShareRepository::class)
 */
class ReportShare
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Report::class, inversedBy="reportShare", cascade={"persist", "remove"})
     */
    private $report;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="reportShares")
     */
    private $users;

    /**
     * @ORM\ManyToMany(targetEntity=School::class, inversedBy="reportShares")
     */
    private $schools;

    /**
     * @ORM\ManyToMany(targetEntity=Region::class, inversedBy="reportShares")
     */
    private $regions;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $userRole;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->schools = new ArrayCollection();
        $this->regions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): self
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

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
        }

        return $this;
    }

    public function removeSchool(School $school): self
    {
        $this->schools->removeElement($school);

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
        }

        return $this;
    }

    public function removeRegion(Region $region): self
    {
        $this->regions->removeElement($region);

        return $this;
    }

    public function getUserRole(): ?string
    {
        return $this->userRole;
    }

    public function setUserRole(?string $userRole): self
    {
        $this->userRole = $userRole;

        return $this;
    }
}
