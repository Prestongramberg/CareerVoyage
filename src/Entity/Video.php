<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VideoRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"video" = "Video", "companyVideo" = "CompanyVideo", "schoolVideo" = "SchoolVideo", "careerVideo" = "CareerVideo", "professionalVideo" = "ProfessionalVideo", "helpVideo" = "HelpVideo", "educatorVideo" = "EducatorVideo"})
 */
class Video
{
    use Timestampable;

    /**
     * @Groups({"VIDEO"})
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Groups({"VIDEO"})
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @Groups({"VIDEO"})
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $videoId;

    /**
     * @Groups({"VIDEO"})
     * @ORM\Column(type="text", nullable=true)
     */
    protected $tags;

    /**
     * @var boolean
     */
    private $isFavorite = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\VideoFavorite", mappedBy="video", orphanRemoval=true)
     */
    private $videoFavorites;

    public function __construct()
    {
        $this->videoFavorites = new ArrayCollection();
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

    public function getVideoId(): ?string
    {
        return $this->videoId;
    }

    public function setVideoId(string $videoId): self
    {
        if (strpos($videoId, '/') !== false) {
            $videoId = substr($videoId, strrpos($videoId, '/') + 1);
        }

        $this->videoId = $videoId;

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @Groups({"VIDEO"})
     * @return bool
     */
    public function isFavorite()
    {
        return $this->isFavorite;
    }

    /**
     * @param bool $isFavorite
     */
    public function setIsFavorite($isFavorite)
    {
        $this->isFavorite = $isFavorite;
    }


    /**
     * @return Collection|VideoFavorite[]
     */
    public function getVideoFavorites(): Collection
    {
        return $this->videoFavorites;
    }

    public function addVideoFavorite(VideoFavorite $videoFavorite): self
    {
        if (!$this->videoFavorites->contains($videoFavorite)) {
            $this->videoFavorites[] = $videoFavorite;
            $videoFavorite->setVideo($this);
        }

        return $this;
    }

    public function removeVideoFavorite(VideoFavorite $videoFavorite): self
    {
        if ($this->videoFavorites->contains($videoFavorite)) {
            $this->videoFavorites->removeElement($videoFavorite);
            // set the owning side to null (unless already changed)
            if ($videoFavorite->getVideo() === $this) {
                $videoFavorite->setVideo(null);
            }
        }

        return $this;
    }
}
