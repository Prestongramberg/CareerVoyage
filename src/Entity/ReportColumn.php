<?php

namespace App\Entity;

use App\Repository\ReportColumnRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReportColumnRepository::class)
 */
class ReportColumn
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $defaultName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $field;

    /**
     * @ORM\ManyToOne(targetEntity=Report::class, inversedBy="reportColumns")
     */
    private $report;

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

    public function getDefaultName(): ?string
    {
        return $this->defaultName;
    }

    public function setDefaultName(?string $defaultName): self
    {
        $this->defaultName = $defaultName;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): self
    {
        $this->field = $field;

        return $this;
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
}
