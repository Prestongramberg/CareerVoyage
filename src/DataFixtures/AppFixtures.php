<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Region;
use App\Entity\Site;
use App\Entity\State;
use App\Repository\SiteRepository;
use App\Repository\StateRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

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
        $site->setName('Future Forward');
        $site->setBaseUrl('my.futureforward.org');
        $site->setChildSite(true);
        $site->setLogo('logo-future-forward.png');
        $site->setLogoEmail('logo-future-forward-email.png');
        $site->setFullyQualifiedBaseUrl('http://my.futureforward.test');
        $site->setSignature('&copy;2019 Future Forward. All Rights Reserved.<br />
210 Wood Lake Drive SE | Rochester, MN 55904 | <a href="tel:1-507-281-6678" style="color: #636466; 
text-decoration: none;">(507) 281-6678</a> | <a href="http://www.futureforward.org/" 
style="color: #636466; text-decoration: none;">www.futureforward.org</a><br />');
        $site->setParentSite(false);
        $manager->persist($site);

        $site = new Site();
        $site->setName('pintex');
        $site->setBaseUrl('pintex.org');
        $site->setLogo('logo-pintex.jpg');
        $site->setLogoEmail('logo-pintex-email.jpg');
        $site->setFullyQualifiedBaseUrl('http://localhost:8000');
        $site->setSignature('&copy;2019 Pintex. All Rights Reserved.<br />
<a href=\http://www.pintex.test/\"" style=\""color: #636466; text-decoration: none;\"">www.pintex.test</a><br />');
        $site->setChildSite(false);
        $site->setParentSite(true);
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