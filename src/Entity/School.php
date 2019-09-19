<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class School
{
    use Timestampable;

    /**
     * @Groups({"ALL_USER_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"ALL_USER_DATA"})
     * @Assert\NotBlank(message="Don't forget a name!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Company", mappedBy="schools")
     */
    private $companies;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ProfessionalUser", mappedBy="schools")
     */
    private $professionalUsers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EducatorUser", mappedBy="school")
     */
    private $educatorUsers;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolAdministratorRequest", mappedBy="school")
     */
    private $schoolAdministratorRequests;

    /**
     * @Assert\NotBlank(message="Don't forget a school email!", groups={"EDIT"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @Assert\NotBlank(message="Don't forget to add an overview and background!", groups={"EDIT"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $overviewAndBackground;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\SchoolAdministrator", inversedBy="schools")
     */
    private $schoolAdministrators;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Region", inversedBy="schools")
     */
    private $region;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolVideo", mappedBy="school", orphanRemoval=true)
     */
    private $schoolVideos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolExperience", mappedBy="school", orphanRemoval=true)
     */
    private $schoolExperiences;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolPhoto", mappedBy="school", orphanRemoval=true)
     */
    private $schoolPhotos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentUser", mappedBy="school")
     */
    private $studentUsers;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $zipcode;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="schools")
     */
    private $site;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\State", inversedBy="schools")
     */
    private $state;

    public function __construct()
    {
        $this->companies = new ArrayCollection();
        $this->professionalUsers = new ArrayCollection();
        $this->educatorUsers = new ArrayCollection();
        $this->schoolAdministratorRequests = new ArrayCollection();
        $this->schoolAdministrators = new ArrayCollection();
        $this->schoolVideos = new ArrayCollection();
        $this->schoolExperiences = new ArrayCollection();
        $this->schoolPhotos = new ArrayCollection();
        $this->studentUsers = new ArrayCollection();
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
     * @return Collection|Company[]
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies[] = $company;
            $company->addSchool($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        if ($this->companies->contains($company)) {
            $this->companies->removeElement($company);
            $company->removeSchool($this);
        }

        return $this;
    }

    /**
     * @return Collection|ProfessionalUser[]
     */
    public function getProfessionalUsers(): Collection
    {
        return $this->professionalUsers;
    }

    public function addProfessionalUser(ProfessionalUser $professionalUser): self
    {
        if (!$this->professionalUsers->contains($professionalUser)) {
            $this->professionalUsers[] = $professionalUser;
            $professionalUser->addSchool($this);
        }

        return $this;
    }

    public function removeProfessionalUser(ProfessionalUser $professionalUser): self
    {
        if ($this->professionalUsers->contains($professionalUser)) {
            $this->professionalUsers->removeElement($professionalUser);
            $professionalUser->removeSchool($this);
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
            $educatorUser->setSchool($this);
        }

        return $this;
    }

    public function removeEducatorUser(EducatorUser $educatorUser): self
    {
        if ($this->educatorUsers->contains($educatorUser)) {
            $this->educatorUsers->removeElement($educatorUser);
            // set the owning side to null (unless already changed)
            if ($educatorUser->getSchool() === $this) {
                $educatorUser->setSchool(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SchoolAdministratorRequest[]
     */
    public function getSchoolAdministratorRequests(): Collection
    {
        return $this->schoolAdministratorRequests;
    }

    public function addSchoolAdministratorRequest(SchoolAdministratorRequest $schoolAdministratorRequest): self
    {
        if (!$this->schoolAdministratorRequests->contains($schoolAdministratorRequest)) {
            $this->schoolAdministratorRequests[] = $schoolAdministratorRequest;
            $schoolAdministratorRequest->setSchool($this);
        }

        return $this;
    }

    public function removeSchoolAdministratorRequest(SchoolAdministratorRequest $schoolAdministratorRequest): self
    {
        if ($this->schoolAdministratorRequests->contains($schoolAdministratorRequest)) {
            $this->schoolAdministratorRequests->removeElement($schoolAdministratorRequest);
            // set the owning side to null (unless already changed)
            if ($schoolAdministratorRequest->getSchool() === $this) {
                $schoolAdministratorRequest->setSchool(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getOverviewAndBackground(): ?string
    {
        return $this->overviewAndBackground;
    }

    public function setOverviewAndBackground(?string $overviewAndBackground): self
    {
        $this->overviewAndBackground = $overviewAndBackground;

        return $this;
    }

    public function isUserSchoolAdministrator(User $user) {

        return ($this->getSchoolAdministrators()->filter(function(SchoolAdministrator $schoolAdministrator) use ($user) {
            return $schoolAdministrator->getId() === $user->getId();
        })->count() > 0);
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
        }

        return $this;
    }

    public function removeSchoolAdministrator(SchoolAdministrator $schoolAdministrator): self
    {
        if ($this->schoolAdministrators->contains($schoolAdministrator)) {
            $this->schoolAdministrators->removeElement($schoolAdministrator);
        }

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Collection|SchoolVideo[]
     */
    public function getSchoolVideos(): Collection
    {
        return $this->schoolVideos;
    }

    public function addSchoolVideo(SchoolVideo $schoolVideo): self
    {
        if (!$this->schoolVideos->contains($schoolVideo)) {
            $this->schoolVideos[] = $schoolVideo;
            $schoolVideo->setSchool($this);
        }

        return $this;
    }

    public function removeSchoolVideo(SchoolVideo $schoolVideo): self
    {
        if ($this->schoolVideos->contains($schoolVideo)) {
            $this->schoolVideos->removeElement($schoolVideo);
            // set the owning side to null (unless already changed)
            if ($schoolVideo->getSchool() === $this) {
                $schoolVideo->setSchool(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SchoolExperience[]
     */
    public function getSchoolExperiences(): Collection
    {
        return $this->schoolExperiences;
    }

    public function addSchoolExperience(SchoolExperience $schoolExperience): self
    {
        if (!$this->schoolExperiences->contains($schoolExperience)) {
            $this->schoolExperiences[] = $schoolExperience;
            $schoolExperience->setSchool($this);
        }

        return $this;
    }

    public function removeSchoolExperience(SchoolExperience $schoolExperience): self
    {
        if ($this->schoolExperiences->contains($schoolExperience)) {
            $this->schoolExperiences->removeElement($schoolExperience);
            // set the owning side to null (unless already changed)
            if ($schoolExperience->getSchool() === $this) {
                $schoolExperience->setSchool(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SchoolPhoto[]
     */
    public function getSchoolPhotos(): Collection
    {
        return $this->schoolPhotos;
    }

    public function addSchoolPhoto(SchoolPhoto $schoolPhoto): self
    {
        if (!$this->schoolPhotos->contains($schoolPhoto)) {
            $this->schoolPhotos[] = $schoolPhoto;
            $schoolPhoto->setSchool($this);
        }

        return $this;
    }

    public function removeSchoolPhoto(SchoolPhoto $schoolPhoto): self
    {
        if ($this->schoolPhotos->contains($schoolPhoto)) {
            $this->schoolPhotos->removeElement($schoolPhoto);
            // set the owning side to null (unless already changed)
            if ($schoolPhoto->getSchool() === $this) {
                $schoolPhoto->setSchool(null);
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
            $studentUser->setSchool($this);
        }

        return $this;
    }

    public function removeStudentUser(StudentUser $studentUser): self
    {
        if ($this->studentUsers->contains($studentUser)) {
            $this->studentUsers->removeElement($studentUser);
            // set the owning side to null (unless already changed)
            if ($studentUser->getSchool() === $this) {
                $studentUser->setSchool(null);
            }
        }

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): self
    {
        $this->zipcode = $zipcode;

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

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

        return $this;
    }
}
