<?php

namespace App\DataFixtures;

use App\Entity\ProfessionalUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $professionalUser = new ProfessionalUser();
        $professionalUser->setFirstName('Josh');
        $professionalUser->setLastName('Crawmer');
        $professionalUser->setPassword($this->passwordEncoder->encodePassword(
            $professionalUser,
            'Iluv2rap!'
        ));
        $professionalUser->setEmail('joshcrawmer4@yahoo.com');
        $professionalUser->setUsername('joshcrawmer4');
        $professionalUser->agreeToTerms();
        $professionalUser->setupAsProfessional();


        $manager->persist($professionalUser);

        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return array(
            CompanyFixtures::class,
        );
    }
}
