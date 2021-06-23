<?php

namespace App\Report\Serializer;

use App\Entity\Report;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Class ColumnNameConverter
 * @package App\Report\Serializer
 */
class ColumnNameConverter implements NameConverterInterface
{

    /**
     * @var Report $report
     */
    private $report;

    /**
     * ColumnNameConverter constructor.
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function normalize($propertyName)
    {
        foreach($this->report->getReportColumns() as $reportColumn) {

            if($reportColumn->getField() === $propertyName) {
                return $reportColumn->getName();
            }
        }

        return $propertyName;
    }

    public function denormalize($propertyName)
    {
        // do nothing
        return $propertyName;
    }
}