<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Region;
use App\Entity\Site;
use App\Entity\State;
use App\Repository\SiteRepository;
use App\Repository\StateRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /**
     * @var StateRepository
     */
    private $stateRepository;

    /**
     * @var SiteRepository
     */
    private $siteRepository;

    /**
     * AppFixtures constructor.
     * @param StateRepository $stateRepository
     * @param SiteRepository $siteRepository
     */
    public function __construct(StateRepository $stateRepository, SiteRepository $siteRepository)
    {
        $this->stateRepository = $stateRepository;
        $this->siteRepository = $siteRepository;
    }

    public function load(ObjectManager $manager)
    {
        foreach(State::$types as $abbreviation => $fullName) {
            $stateObject = new State();
            $stateObject->setName($fullName);
            $stateObject->setAbbreviation($abbreviation);
            $manager->persist($stateObject);
        }
        $manager->flush();

        $site = new Site();
        $site->setName('maranatha');
        $site->setBaseUrl('maranatha.org');
        $manager->persist($site);

        $site = new Site();
        $site->setName('future forward');
        $site->setBaseUrl('my.futureforward.org');
        $manager->persist($site);

        $site = new Site();
        $site->setName('pintex');
        $site->setBaseUrl('pintex.org');
        $manager->persist($site);

        $manager->flush();

        $region = new Region();
        $state = $this->stateRepository->findOneBy([
            'name' => 'Minnesota'
        ]);
        $region->setName('Southeast');
        $region->setState($state);
        $region->setSite($this->siteRepository->find(2));
        $manager->persist($region);
        $manager->flush();

    }
}