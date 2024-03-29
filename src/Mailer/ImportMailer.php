<?php

namespace App\Mailer;

use App\Entity\EmailLog;
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
            ->setFrom($this->siteFromEmail)
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

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Students Imported');
        $log->setToEmail($schoolAdministrator->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function educatorImportMailer(SchoolAdministrator $schoolAdministrator, $attachmendFilePath) {

        $message = (new \Swift_Message('Educators Imported'))
            ->setFrom($this->siteFromEmail)
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

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Educators Imported');
        $log->setToEmail($schoolAdministrator->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}