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
     * @var array
     */
    public static $states = array(
        'AL'=>'Alabama',
        'AK'=>'Alaska',
        'AZ'=>'Arizona',
        'AR'=>'Arkansas',
        'CA'=>'California',
        'CO'=>'Colorado',
        'CT'=>'Connecticut',
        'DE'=>'Delaware',
        'DC'=>'District of Columbia',
        'FL'=>'Florida',
        'GA'=>'Georgia',
        'HI'=>'Hawaii',
        'ID'=>'Idaho',
        'IL'=>'Illinois',
        'IN'=>'Indiana',
        'IA'=>'Iowa',
        'KS'=>'Kansas',
        'KY'=>'Kentucky',
        'LA'=>'Louisiana',
        'ME'=>'Maine',
        'MD'=>'Maryland',
        'MA'=>'Massachusetts',
        'MI'=>'Michigan',
        'MN'=>'Minnesota',
        'MS'=>'Mississippi',
        'MO'=>'Missouri',
        'MT'=>'Montana',
        'NE'=>'Nebraska',
        'NV'=>'Nevada',
        'NH'=>'New Hampshire',
        'NJ'=>'New Jersey',
        'NM'=>'New Mexico',
        'NY'=>'New York',
        'NC'=>'North Carolina',
        'ND'=>'North Dakota',
        'OH'=>'Ohio',
        'OK'=>'Oklahoma',
        'OR'=>'Oregon',
        'PA'=>'Pennsylvania',
        'RI'=>'Rhode Island',
        'SC'=>'South Carolina',
        'SD'=>'South Dakota',
        'TN'=>'Tennessee',
        'TX'=>'Texas',
        'UT'=>'Utah',
        'VT'=>'Vermont',
        'VA'=>'Virginia',
        'WA'=>'Washington',
        'WV'=>'West Virginia',
        'WI'=>'Wisconsin',
        'WY'=>'Wyoming',
    );

    public static $courses = [
        'Agriculture, Food & Natural Resources',
        'Architecture & Construction',
        'Arts, A/V Technology & Communications',
        'Business Management & Administration',
        'Education & Training',
        'Finance',
        'Government & Public Administration',
        'Health Science',
        'Hospitality & Tourism',
        'Human Services',
        'Information Technology',
        'Law, Public Safety, Corrections & Security',
        'Manufacturing',
        'Marketing',
        'Science, Technology, Engineering & Mathematics',
        'Transportation, Distribution & Logistics'
    ];

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
        foreach(self::$states as $abbreviation => $fullName) {
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

        foreach(self::$courses as $courseTitle) {
            $career = new Career();
            $career->setTitle($courseTitle);
            $manager->persist($career);
        }
        $manager->flush();
    }
}