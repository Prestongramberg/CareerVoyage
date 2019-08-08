<?php

namespace App\Mailer;

use App\Entity\SchoolAdministrator;
use App\Entity\User;
use Swift_Attachment;

/**
 * Class ImportMailer
 * @package App\Mailer
 */
class ImportMailer extends AbstractMailer
{

    public function studentImportMailer(SchoolAdministrator $schoolAdministrator, $attachmendFilePath) {

        $message = (new \Swift_Message('Students Imported'))
            ->setFrom('info@pintex.test')
            ->setTo($schoolAdministrator->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/studentImportEmail.html.twig',
                    ['user' => $schoolAdministrator]
                ),
                'text/html'
            );

        $message->attach(
            Swift_Attachment::fromPath($attachmendFilePath)->setFilename('students.csv')
        );

        $this->mailer->send($message);
    }

    public function educatorImportMailer(SchoolAdministrator $schoolAdministrator, $attachmendFilePath) {

        $message = (new \Swift_Message('Educators Imported'))
            ->setFrom('info@pintex.test')
            ->setTo($schoolAdministrator->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/educatorImportEmail.html.twig',
                    ['user' => $schoolAdministrator]
                ),
                'text/html'
            );

        $message->attach(
            Swift_Attachment::fromPath($attachmendFilePath)->setFilename('educators.csv')
        );

        $this->mailer->send($message);
    }
}