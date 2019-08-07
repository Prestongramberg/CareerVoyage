<?php

namespace App\DataFixtures;


use App\Entity\Career;
use App\Entity\Course;
use App\Entity\Region;
use App\Entity\State;
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
     * AppFixtures constructor.
     * @param StateRepository $stateRepository
     */
    public function __construct(StateRepository $stateRepository)
    {
        $this->stateRepository = $stateRepository;
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

        $region = new Region();
        $state = $this->stateRepository->findOneBy([
            'name' => 'Minnesota'
        ]);
        $region->setName('Southeast');
        $region->setState($state);
        $manager->persist($region);
        $manager->flush();

        foreach(Career::$types as $careerTitle) {
            $career = new Career();
            $career->setTitle($careerTitle);
            $manager->persist($career);
        }
        $manager->flush();
    }
}