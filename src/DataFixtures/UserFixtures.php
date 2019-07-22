<?php

namespace App\DataFixtures;

use App\Entity\AdminUser;
use App\Entity\ProfessionalUser;
use App\Repository\CompanyRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * UserFixtures constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param CompanyRepository $companyRepository
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, CompanyRepository $companyRepository)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->companyRepository = $companyRepository;
    }

    public function load(ObjectManager $manager)
    {
        $professionalUser = new ProfessionalUser();
        $professionalUser->setFirstName('Josh');
        $professionalUser->setLastName('Crawmer');
        $professionalUser->setPassword($this->passwordEncoder->encodePassword(
            $professionalUser,
            'Pintex123!'
        ));
        $professionalUser->setEmail('joshcrawmer4@yahoo.com');
        $professionalUser->setUsername('joshcrawmer4');
        $professionalUser->agreeToTerms();
        $professionalUser->setupAsProfessional();
        $company = $this->getReference('company1');
        $company->setOwner($professionalUser);
        $professionalUser->setCompany($company);
        $manager->persist($company);
        $manager->persist($professionalUser);
        $this->setReference('user1', $professionalUser);

        $professionalUser = new ProfessionalUser();
        $professionalUser->setFirstName('Travis');
        $professionalUser->setLastName('Hoglund');
        $professionalUser->setPassword($this->passwordEncoder->encodePassword(
            $professionalUser,
            'TEST1234'
        ));
        $professionalUser->setEmail('travis@travishoglund.com');
        $professionalUser->setUsername('travishoglund');
        $professionalUser->agreeToTerms();
        $professionalUser->setupAsProfessional();
        $company = $this->getReference('company2');
        $professionalUser->setCompany($company);
        $company->setOwner($professionalUser);
        $professionalUser->setCompany($company);
        $manager->persist($company);
        $manager->persist($professionalUser);
        $this->setReference('user2', $professionalUser);



        $professionalUser = new ProfessionalUser();
        $professionalUser->setFirstName('Tom');
        $professionalUser->setLastName('Brady');
        $professionalUser->setPassword($this->passwordEncoder->encodePassword(
            $professionalUser,
            'Pintex123!'
        ));
        $professionalUser->setEmail('tombrady@yahoo.com');
        $professionalUser->setUsername('tombrady4');
        $professionalUser->agreeToTerms();
        $professionalUser->setupAsProfessional();
        $company = $this->getReference('company1');
        $professionalUser->setCompany($company);
        $manager->persist($professionalUser);

        $professionalUser = new ProfessionalUser();
        $professionalUser->setFirstName('Jill');
        $professionalUser->setLastName('House');
        $professionalUser->setPassword($this->passwordEncoder->encodePassword(
            $professionalUser,
            'Pintex123!'
        ));
        $professionalUser->setEmail('jillhouse@yahoo.com');
        $professionalUser->setUsername('jillhouse44');
        $professionalUser->agreeToTerms();
        $professionalUser->setupAsProfessional();
        $company = $this->getReference('company2');
        $professionalUser->setCompany($company);
        $manager->persist($professionalUser);

        $professionalUser = new ProfessionalUser();
        $professionalUser->setFirstName('Jeff');
        $professionalUser->setLastName('Mason');
        $professionalUser->setPassword($this->passwordEncoder->encodePassword(
            $professionalUser,
            'Pintex123!'
        ));
        $professionalUser->setEmail('jeffreymason@gmail.com');
        $professionalUser->setUsername('jeffreymason33');
        $professionalUser->agreeToTerms();
        $professionalUser->setupAsProfessional();
        $company = $this->getReference('company1');
        $professionalUser->setCompany($company);
        $manager->persist($professionalUser);

        $professionalUser = new ProfessionalUser();
        $professionalUser->setFirstName('Nora');
        $professionalUser->setLastName('Jones');
        $professionalUser->setPassword($this->passwordEncoder->encodePassword(
            $professionalUser,
            'Pintex123!'
        ));
        $professionalUser->setEmail('norajones@gmail.com');
        $professionalUser->setUsername('norajones55');
        $professionalUser->agreeToTerms();
        $professionalUser->setupAsProfessional();
        $company = $this->getReference('company2');
        $professionalUser->setCompany($company);
        $manager->persist($professionalUser);


        $manager->flush();

        $adminUser = new AdminUser();
        $adminUser->setFirstName('adminFirstName');
        $adminUser->setLastName('adminLastName');
        $adminUser->setPassword($this->passwordEncoder->encodePassword(
            $adminUser,
            'Admin123!'
        ));
        $adminUser->setEmail('admin@pintex.com');
        $adminUser->setUsername('pintexAdmin');
        $adminUser->agreeToTerms();
        $adminUser->setupAsAdmin();
        $manager->persist($adminUser);

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
