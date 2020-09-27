<?php

namespace App\Service;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhpSpreadsheetHelper
{
    /**
     * @var UploaderHelper
     */
    private $uploadHelper;

    /**
     * PhpSpreadsheetHelper constructor.
     * @param UploaderHelper $uploadHelper
     */
    public function __construct(UploaderHelper $uploadHelper)
    {
        $this->uploadHelper = $uploadHelper;
    }

    /**
     * @param File $uploadedFile
     * @return array
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public function getColumns(File $uploadedFile) {

        $fileExtension = $this->uploadHelper->guessExtension($uploadedFile);
        $path = $uploadedFile->getRealPath();

        $columns = [];

        switch ($fileExtension) {
            case 'xlsx':
                $reader = ReaderEntityFactory::createXLSXReader();
                break;
            case 'ods':
                $reader = ReaderEntityFactory::createODSReader();
                break;
            case 'csv':
                $reader = ReaderEntityFactory::createCSVReader();
                break;
            default:
                throw new \Exception("Error reading file. Make sure file is a valid CSV, ODD, or XLSX. File extension %s being used.", $fileExtension);
                break;
        }

        $reader->open($path);

        /** @var \Box\Spout\Reader\SheetInterface $sheet */
        foreach ($reader->getSheetIterator() as $sheet) {
            /** @var \Box\Spout\Common\Entity\Row $row */
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $columns = $row->toArray();
                break 2;
            }
        }

        $reader->close();

        return $columns;
    }

    /**
     * @param File $uploadedFile
     * @return \Box\Spout\Reader\CSV\Reader|\Box\Spout\Reader\ODS\Reader|\Box\Spout\Reader\XLSX\Reader
     * @throws \Exception
     */
    public function getReader(File $uploadedFile) {
        $fileExtension = $this->uploadHelper->guessExtension($uploadedFile);
        $path = $uploadedFile->getRealPath();
        switch ($fileExtension) {
            case 'xlsx':
                $reader = ReaderEntityFactory::createXLSXReader();
                break;
            case 'ods':
                $reader = ReaderEntityFactory::createODSReader();
                break;
            case 'csv':
                $reader = ReaderEntityFactory::createCSVReader();
                break;
            default:
                throw new \Exception("Reader could not be created from file extension: %s", $fileExtension);
                break;
        }

        $reader->open($path);

        return $reader;
    }

    /**
     * @deprecated Using box/sprout due to performance issues with phpoffice
     * Return the column names from the CSV
     * @param UploadedFile $uploadedFile
     * @return array|bool
     */
    public function getColumnNames(UploadedFile $uploadedFile) {
        $tempPathName = $uploadedFile->getRealPath();
        $fileExtension = $uploadedFile->getClientOriginalExtension();
        $error = false;
        $reader = false;
        $spreadsheet = false;
        $columns = false;
        switch ($fileExtension) {
            case 'xlsx':
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                break;
            case 'xls':
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                break;
            case 'csv':
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                break;
        }
        if($reader) {
            try {
                $chunkFilter = new ChunkReadFilter();
                $chunkFilter->setRows(1,1);
                $reader->setReadFilter($chunkFilter);
                $spreadsheet = $reader->load($tempPathName);
            } catch(\PhpOffice\PhpSpreadsheet\Reader\Exception $exception) {
                $error = 'Error reading in file: ' . $exception->getMessage();
            }
        }
        if($spreadsheet && !$error) {
            try {
                $columns = $spreadsheet->getActiveSheet()->toArray();
            } catch (\PhpOffice\PhpSpreadsheet\Exception $exception) {
                $error = 'Error converting spreadsheet to an array: ' . $exception->getMessage();
            }
        }
        if(!$error && !empty($columns[0])) {
            // remove empty or null values from array
            return array_filter($columns[0]);
        }
        return false;
    }

    /**
     * @deprecated Using box/sprout due to performance issues with phpoffice
     * Return all the spreadsheet rows
     * @param File $uploadedFile
     * @return array|bool
     */
    public function getAllRows(File $uploadedFile) {
        $fileExtension = $this->uploadHelper->guessExtension($uploadedFile);
        $path = $uploadedFile->getRealPath();
        $error = false;
        $reader = false;
        $spreadsheet = false;
        $rows = false;
        switch ($fileExtension) {
            case 'xlsx':
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                break;
            case 'xls':
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                break;
            case 'csv':
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                break;
        }
        if($reader) {
            try {
                $spreadsheet = $reader->load($path);
            } catch(\PhpOffice\PhpSpreadsheet\Reader\Exception $exception) {
                $error = 'Error reading in file: ' . $exception->getMessage();
            }
        }
        if($spreadsheet && !$error) {
            try {
                $rows = $spreadsheet->getActiveSheet()->toArray();
            } catch (\PhpOffice\PhpSpreadsheet\Exception $exception) {
                $error = 'Error converting spreadsheet to an array: ' . $exception->getMessage();
            }
        }
        if(!$error && !empty($rows)) {
            // remove empty or null values from array
            return $rows;
        }
        return false;
    }

    /**
     * @param $columns
     * @return string|string[]|null
     */
    public function formFriendly($columns) {
        $columns = !is_array($columns) ? [$columns] : $columns;
        $columns = preg_replace('/[^a-zA-Z0-9_ ]/', '', $columns);
        $columns = array_map('strtolower', $columns);
        $columns = array_map(
            function($str) {
                return str_replace(' ', '_', $str);
            },
            $columns
        );
        return $columns;
    }

    /**
     * @param $columns
     * @return string|string[]|null
     */
    public function choicesForForm($columns) {
        $columns = !is_array($columns) ? [$columns] : $columns;
        $choices = [];
        foreach($columns as $column) {
            $choices[$column] = $this->formFriendly($column)[0];
        }
        return $choices;
    }
}