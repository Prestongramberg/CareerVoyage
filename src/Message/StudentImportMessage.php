<?php

namespace App\Message;


class StudentImportMessage
{
    private $schoolId;
    private $fileName;
    private $siteId;

    /**
     * StudentImportMessage constructor.
     * @param $schoolId
     * @param $fileName
     * @param $siteId
     */
    public function __construct($schoolId, $fileName, $siteId)
    {
        $this->schoolId = $schoolId;
        $this->fileName = $fileName;
        $this->siteId = $siteId;
    }

    /**
     * @return mixed
     */
    public function getSchoolId()
    {
        return $this->schoolId;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return mixed
     */
    public function getSiteId()
    {
        return $this->siteId;
    }
}