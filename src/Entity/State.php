<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StateRepository")
 */
class State
{
    /**
     * @var array
     */
    public static $types = array(
        'AL'=>'Alabama',
        'AK'=>'Alaska',
        'AZ'=>'Arizona',
        'AR'=>'Arkansas',
        'CA'=>'California',
        'CO'=>'Colorado',
        'CT'=>'Connecticut',
        'DE'=>'Delaware',
        'DC'=>'District of Columbia',
        'FL'=>'Florida',
        'GA'=>'Georgia',
        'HI'=>'Hawaii',
        'ID'=>'Idaho',
        'IL'=>'Illinois',
        'IN'=>'Indiana',
        'IA'=>'Iowa',
        'KS'=>'Kansas',
        'KY'=>'Kentucky',
        'LA'=>'Louisiana',
        'ME'=>'Maine',
        'MD'=>'Maryland',
        'MA'=>'Massachusetts',
        'MI'=>'Michigan',
        'MN'=>'Minnesota',
        'MS'=>'Mississippi',
        'MO'=>'Missouri',
        'MT'=>'Montana',
        'NE'=>'Nebraska',
        'NV'=>'Nevada',
        'NH'=>'New Hampshire',
        'NJ'=>'New Jersey',
        'NM'=>'New Mexico',
        'NY'=>'New York',
        'NC'=>'North Carolina',
        'ND'=>'North Dakota',
        'OH'=>'Ohio',
        'OK'=>'Oklahoma',
        'OR'=>'Oregon',
        'PA'=>'Pennsylvania',
        'RI'=>'Rhode Island',
        'SC'=>'South Carolina',
        'SD'=>'South Dakota',
        'TN'=>'Tennessee',
        'TX'=>'Texas',
        'UT'=>'Utah',
        'VT'=>'Vermont',
        'VA'=>'Virginia',
        'WA'=>'Washington',
        'WV'=>'West Virginia',
        'WI'=>'Wisconsin',
        'WY'=>'Wyoming',
    );

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="string", length=255)
     */
    private $abbreviation;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Region", mappedBy="state")
     */
    private $regions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Experience", mappedBy="state")
     */
    private $experiences;

    public function __construct()
    {
        $this->regions = new ArrayCollection();
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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(string $abbreviation): self
    {
        $this->abbreviation = $abbreviation;

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
            $region->setState($this);
        }

        return $this;
    }

    public function removeRegion(Region $region): self
    {
        if ($this->regions->contains($region)) {
            $this->regions->removeElement($region);
            // set the owning side to null (unless already changed)
            if ($region->getState() === $this) {
                $region->setState(null);
            }
        }

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
            $experience->setState($this);
        }

        return $this;
    }

    public function removeExperience(Experience $experience): self
    {
        if ($this->experiences->contains($experience)) {
            $this->experiences->removeElement($experience);
            // set the owning side to null (unless already changed)
            if ($experience->getState() === $this) {
                $experience->setState(null);
            }
        }

        return $this;
    }
}
