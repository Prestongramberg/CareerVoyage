<?php

namespace App\Util;


use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FileHelper
{

    public function getOriginalName(UploadedFile $uploadedFile) {
        return pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
    }

    public function getSafeFileName(UploadedFile $uploadedFile) {
        return transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $this->getOriginalName($uploadedFile));
    }

    public function newFileName(UploadedFile $uploadedFile) {
        return $this->getSafeFileName($uploadedFile) . '-'.uniqid().'.'.$uploadedFile->guessExtension();
    }
}