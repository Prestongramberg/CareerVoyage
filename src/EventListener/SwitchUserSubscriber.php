<?php

namespace App\EventListener;

use App\Entity\Survey;
use App\Entity\User;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SwitchUserSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $uploadsPath;

    /**
     * SwitchUserSubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param string                 $uploadsPath
     */
    public function __construct(EntityManagerInterface $entityManager, string $uploadsPath)
    {
        $this->entityManager = $entityManager;
        $this->uploadsPath   = $uploadsPath;
    }

    public function onSwitchUser(SwitchUserEvent $event): void
    {
        $request = $event->getRequest();

        $switchUser = $request->query->get('_switch_user', null);

        // login
        if ($switchUser !== '_exit' && $event->getToken() && $event->getToken()->getUser() && $event->getToken()->getUser() instanceof User) {
            /** @var User $user */
            $user = $event->getToken()->getUser();

            if (!$user->getPhoto() && $user->getFirstName() && $user->getLastName()) {

                $avatarUrl    = sprintf("https://ui-avatars.com/api/?name=%s+%s&background=random&size=128", $user->getFirstName(), $user->getLastName());
                $fileContents = file_get_contents($avatarUrl);

                $filesystem  = new Filesystem();
                $destination = $this->uploadsPath . '/' . UploaderHelper::PROFILE_PHOTO;
                $fileName = uniqid('', true) . '.png';
                $filesystem->dumpFile($destination . '/' . $fileName, $fileContents);
                $user->setPhoto($fileName);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // constant for security.switch_user
            SecurityEvents::SWITCH_USER => 'onSwitchUser',
        ];
    }
}