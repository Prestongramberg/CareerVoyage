<?php

namespace App\Form;

use App\Entity\Career;
use App\Entity\Company;
use App\Entity\Course;
use App\Entity\Experience;
use App\Entity\Grade;
use App\Entity\Industry;
use App\Entity\Lesson;
use App\Entity\ProfessionalUser;
use App\Entity\State;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;

class NewExperienceType extends AbstractType
{

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


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Company $company */
        $company = $options['company'];

        $builder
            ->add('title', TextType::class, [])
            ->add('briefDescription', TextType::class, [])
            ->add('about', TextareaType::class, [])
            ->add('type', ChoiceType::class, [
                'choices'  => Experience::$types
            ])
            ->add('careers', EntityType::class, [
                'class' => Career::class,
                'choice_label' => 'title',
                'expanded'  => true,
                'multiple'  => true,
            ])
            ->add('availableSpaces', NumberType::class, [])
            ->add('payment', TextType::class, [])
            ->add('paymentShownIsPer', ChoiceType::class, [
                'choices'  => [
                    'Per Person And Per Visit' => 'PER_PERSON_AND_PER_VISIT',
                    'Hour' => 'HOUR',
                    'Day' => 'DAY',
                    'Week' => 'WEEK',
                    'Month' => 'MONTH',
                    'Year' => 'YEAR',
                ],
                'expanded'  => false,
                'multiple'  => false,
                'required' => false
            ])
            ->add('employeeContact', EntityType::class, [
                'class' => ProfessionalUser::class,
                'choice_label' => 'fullName',
                'expanded'  => false,
                'multiple'  => false,
                'query_builder' => function (EntityRepository $er) use ($company) {
                    return $er->createQueryBuilder('p')
                        ->where('p.company = :company')
                        ->setParameter('company', $company);
                },
            ])
            ->add('email', TextType::class, [])
            ->add('street', TextType::class, [])
            ->add('city', TextType::class, [])
            ->add('state', EntityType::class, [
                'class' => State::class,
                'choice_label' => 'name',
                'expanded'  => false,
                'multiple'  => false,
            ])
            ->add('zipcode', TextType::class, [])
            ->add('startDateAndTime', DateType::class, [
                'widget' => 'single_text',

                // prevents rendering it as type="date", to avoid HTML5 date pickers
                'html5' => false,

                // adds a class that can be selected in JavaScript
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('endDateAndTime', DateType::class, [
                'widget' => 'single_text',

                // prevents rendering it as type="date", to avoid HTML5 date pickers
                'html5' => false,

                // adds a class that can be selected in JavaScript
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('length', NumberType::class, []);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Experience::class,
            'validation_groups' => ['CREATE'],
        ]);

        $resolver->setRequired(['company']);

    }
}
