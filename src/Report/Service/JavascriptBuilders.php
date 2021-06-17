<?php

namespace App\Report\Service;

use App\Entity\AdminUser;
use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\CompanyFavorite;
use App\Entity\Course;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Grade;
use App\Entity\Lesson;
use App\Entity\LessonFavorite;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\RegionalCoordinator;
use App\Entity\Registration;
use App\Entity\Report;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\Share;
use App\Entity\SiteAdminUser;
use App\Entity\StudentToMeetProfessionalExperience;
use App\Entity\StudentUser;
use App\Entity\TeachLessonExperience;
use App\Entity\User;
use App\Report\Model\Builder\Builder;
use App\Report\Model\Builder\ResultColumn;
use App\Report\Model\Filter\FilterInput;
use App\Report\Model\Filter\FilterOperators;
use App\Report\Model\Filter\FilterValueCollection;
use App\Report\Util\Validator\BuildersToMappings;
use App\Util\StringHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class JavascriptBuilders
 *
 * @package App\Report\Service
 */
class JavascriptBuilders
{
    use StringHelper;

    /**
     * Keys should equal @see Builder::$builderId.
     *
     * @var Builder[]
     */
    protected $builders;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * JavascriptBuilders constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param EntityManagerInterface   $entityManager
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                EntityManagerInterface $entityManager
    ) {
        $this->dispatcher    = $dispatcher;
        $this->entityManager = $entityManager;
    }

    public function getFilterData(Report $report)
    {
        $rules   = json_decode($report->getReportRules(), true);
        $filters = [];

        $result = [
            'metadata' => [],
            'column_ids' => [],
            'filter_ids' => [],
            'filter_ids_flat' => [],
        ];

        // by default add everything from base entity 
        if ($className = $report->getReportEntityClassName()) {
            $x                              = json_decode($this->get($className)->getJsonString(), true);
            $result['metadata'][$className] = [
                'filters' => array_combine(
                    array_map(
                        function ($e) {
                            return $e['id'];
                        }, $x['filters']
                    ), $x['filters']
                ),
                'related_entities' => array_combine(
                    array_map(
                        function ($e) {
                            return $e['column_machine_name'];
                        }, $x['related_entities']
                    ), $x['related_entities']
                ),
                'pretty_class_name' => $x['pretty_class_name'],
            ];
        }


        foreach ($this->searchRulesForFilters($rules) as $rule) {
            $parts                             = explode('.', $rule['id']);
            $result['filter_ids_flat'][]       = $rule['id'];
            $result['filter_ids'][$rule['id']] = $rule['data']['class_name'];

            // load metadata for intermediary joins (FILTERS)
            if (count($parts) > 1) {
                $currentEntity = $report->getReportEntityClassName();
                for ($i = 0; $i < count($parts) - 1; $i++) {
                    $associationName = $parts[$i];

                    // traverse it
                    $currentEntity = $result['metadata'][$currentEntity]['related_entities'][$associationName]['association_class'];

                    if (!array_key_exists($currentEntity, $result['metadata'])) {
                        // load it
                        $x                                  = json_decode($this->get($currentEntity)->getJsonString(), true);
                        $result['metadata'][$currentEntity] = [
                            'filters' => array_combine(
                                array_map(
                                    function ($e) {
                                        return $e['id'];
                                    }, $x['filters']
                                ), $x['filters']
                            ),
                            'related_entities' => array_combine(
                                array_map(
                                    function ($e) {
                                        return $e['column_machine_name'];
                                    }, $x['related_entities']
                                ), $x['related_entities']
                            ),
                            'pretty_class_name' => $x['pretty_class_name'],
                        ];
                    }
                }
            }
        }

        foreach ($report->getReportColumns() as $column) {
            $x                      = json_decode($column->getField(), true);
            $id                     = $x['column'];
            $result['column_ids'][] = $id;
            $parts                  = explode('.', $id);
            // load metadata for intermediary joins (COLUMNS)
            if (count($parts) > 1) {
                $currentEntity = $report->getReportEntityClassName();
                for ($i = 0; $i < count($parts) - 1; $i++) {
                    $associationName = $parts[$i];

                    // traverse it
                    $currentEntity = $result['metadata'][$currentEntity]['related_entities'][$associationName]['association_class'];

                    if (!array_key_exists($currentEntity, $result['metadata'])) {
                        // load it
                        $x                                  = json_decode($this->get($currentEntity)->getJsonString(), true);
                        $result['metadata'][$currentEntity] = [
                            'filters' => array_combine(
                                array_map(
                                    function ($e) {
                                        return $e['id'];
                                    }, $x['filters']
                                ), $x['filters']
                            ),
                            'related_entities' => array_combine(
                                array_map(
                                    function ($e) {
                                        return $e['column_machine_name'];
                                    }, $x['related_entities']
                                ), $x['related_entities']
                            ),
                            'pretty_class_name' => $x['pretty_class_name'],
                        ];
                    }
                }
            }
        }


        // apply overrides/operators
        foreach ($result['metadata'] as $entityName => $data) {
            $result['metadata'][$entityName]['filters'] = $this->filtersDefaultOperators($result['metadata'][$entityName]['filters']);
            $result['metadata'][$entityName]['filters'] = $this->filtersOverrides($result['metadata'][$entityName]['filters']);
            $result['metadata'][$entityName]['filters'] = $this->filtersBooleanOverride($result['metadata'][$entityName]['filters']);
            $result['metadata'][$entityName]['filters'] = $this->filtersDateOverrides($result['metadata'][$entityName]['filters']);
        }

        return $result;
    }

    // recursive/nested definition
    public function searchRulesForFilters($rules)
    {
        if (is_array($rules)) {
            if (array_key_exists('rules', $rules)) {
                foreach ($rules['rules'] as $rule) {
                    yield from $this->searchRulesForFilters($rule);
                }
            } else {
                yield $rules;
            }
        }
    }

    public function getFilter($fieldMapping, $targetEntityName = null)
    {
        $fieldName      = $fieldMapping['fieldName'];
        $fieldNameArray = preg_split('/(?=[A-Z])/', $fieldName);
        $label          = ucwords(implode(" ", $fieldNameArray));

        $context = [
            'id' => $fieldMapping['fieldName'],
            'label' => $label,
            'data' => [
                'class_name' => $targetEntityName,
            ],
        ];

        // todo pass the type in here from the class metadata
        switch ($fieldMapping['type']) {

            // todo account for boolean attributes
            case 'string':

                $context['type']  = 'string';
                $context['input'] = 'text';

                break;
            /*           case self::SELECT_FIELD:

                           $context['type'] = 'string';
                           $context['input'] = 'select';
                           $context['values'] = $this->getOptionsWithValuesAsKeys();

                           break;

                       case self::RADIO_FIELD:

                           $context['type'] = 'string';
                           $context['input'] = 'radio';
                           $context['values'] = $this->getOptionsWithValuesAsKeys();

                           break;*/

            /*     case self::CHECKBOX_FIELD:

                     $context['type'] = 'string';
                     $context['input'] = 'checkbox';
                     $context['values'] = $this->getOptionsWithValuesAsKeys();

                     break;*/

            case 'datetime':

                $context['type']  = 'date';
                $context['input'] = 'text';

                /* $context['type']       = 'date';
                 $context['plugin']     = 'datepicker';
                 $context['datepicker'] = [
                     'format' => 'MM/DD/YYYY',
                     'todayBtn' => 'linked',
                     'todayHighlight' => true,
                     'autoclose' => true,
                 ];*/

                break;
            case 'integer':

                $context['type']  = 'integer';
                $context['input'] = 'number';

                break;
            case 'boolean':

                $context['type']      = 'boolean';
                $context['input']     = 'select';
                $context['values']    = [
                    '0' => 'no',
                    '1' => 'yes',
                ];
                $context['operators'] = [
                    'equal',
                    'not_equal',
                    'is_null',
                    'is_not_null',
                ];

                break;
            default:
                // default to string/text to cover the edge cases
                $context['type']  = 'string';
                $context['input'] = 'text';
                break;
        }

        $context = $this->contextOverride($fieldName, $targetEntityName, $context);

        return $context;
    }

    /**
     * @param string $targetEntityName
     *
     * @return Builder
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function get(string $targetEntityName)
    {

        // todo we need some type of indicator here right to determine if we are fetching from a join?
        $classMetadata = $this->entityManager->getClassMetadata($targetEntityName);

        $buildersConfig[$classMetadata->getTableName()] = [
            'class' => $classMetadata->name,
            'human_readable_name' => sprintf("%s Report Builder", $targetEntityName),
            'result_columns' => [],
            'filters' => [],
            'related_entities' => [],
        ];

        $classesAndMappings = [
            $classMetadata->getTableName() => [
                'class' => $classMetadata->name,
                'properties' => [],
                'association_classes' => [],
                // We don't use embeddables in this app so the 4 arrays below we can ignore
                // https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/tutorials/embeddables.html
                'embeddables_properties' => [],
                'embeddables_inside_embeddables_properties' => [],
                'embeddables_association_classes' => [],
                'embeddables_embeddable_classes' => [],
            ],
        ];

        $typesToInclude = [
            \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_ONE,
            \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE,
            \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY,
            //\Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY, // TODO MAYBE ADD??
        ];

        $bannedProperties = [];

        foreach ($classMetadata->getAssociationMappings() as $associationMapping) {
            $fieldName = $associationMapping['fieldName'];

            // todo build in here
            if ($this->shouldExcludeAssociation($fieldName, $targetEntityName)) {
                continue;
            }

            if (in_array($associationMapping['type'], $typesToInclude) && !in_array($fieldName, $bannedProperties)) {

                $format = [
                              "1:1 %s",
                              "N:1 %s",
                              "1:N %s",
                              "N:N %s",
                          ][log($associationMapping['type'], 2)];

                $fieldNameArray = preg_split('/(?=[A-Z])/', $fieldName);
                $label          = ucwords(implode(" ", $fieldNameArray));
                $label          = sprintf($format, $label);

                $label = $this->associationLabelOverride($label, $targetEntityName);

                $buildersConfig[$classMetadata->getTableName()]['related_entities'][] = [
                    'column_machine_name' => $fieldName,
                    'column_human_readable_name' => $label,
                    'association_class' => $associationMapping['targetEntity'],
                ];
            }
        }

        foreach ($classMetadata->getFieldNames() as $fieldName) {

            if ($this->shouldExcludeField($fieldName, $targetEntityName)) {
                continue;
            }

            $fieldMapping = $classMetadata->getFieldMapping($fieldName);

            if (!($filterContext = $this->getFilter($fieldMapping, $targetEntityName))) {
                continue;
            }

            $filterContext['label']  = $this->fieldLabelOverride($filterContext['label'], $targetEntityName);

            $buildersConfig[$classMetadata->getTableName()]['result_columns'][] = [
                'column_machine_name' => $fieldName,
                'column_human_readable_name' => $filterContext['label'],
            ];

            $buildersConfig[$classMetadata->getTableName()]['filters'][]                  = $filterContext;
            $classesAndMappings[$classMetadata->getTableName()]['properties'][$fieldName] = '~';
        }

        usort(
            $buildersConfig[$classMetadata->getTableName()]['filters'], function ($a, $b) {
            return $a['label'] <=> $b['label'];
        }
        );
        // TODO change the column human readable name maybe to something more user friendly?
        usort(
            $buildersConfig[$classMetadata->getTableName()]['related_entities'], function ($a, $b) {
            return $a['column_human_readable_name'] <=> $b['column_human_readable_name'];
        }
        );

        BuildersToMappings::validate($buildersConfig, $classesAndMappings);
        foreach ($buildersConfig as $builderId => $config) {
            $config['id']                = strval($builderId); // necessary for jQuery Query Builder
            $config['pretty_class_name'] = str_replace($classMetadata->namespace . '\\', '', $classMetadata->name);
            $config['filters']           = $this->filtersDefaultOperators($config['filters']);
            $config['filters']           = $this->filtersOverrides($config['filters']);
            $config['filters']           = $this->filtersBooleanOverride($config['filters']); // override all booleans to display the same!
            $config['filters']           = $this->filtersDateOverrides($config['filters']); // override all dates to display the same!
            $builder                     = new Builder();
            $builder
                ->setBuilderId($builderId)
                ->setClassName($config['class'])
                ->setHumanReadableName($config['human_readable_name']);
            unset($config['class']);
            unset($config['human_readable_name']);

            $builder->setJsonString(json_encode($config, JSON_FORCE_OBJECT));

            foreach ($config['result_columns'] as $column) {
                $builder->addResultColumn(new ResultColumn($column['column_human_readable_name'], $column['column_machine_name']));
            }

            $this->builders[$builderId] = $builder;
        }

        // for now we are just returning the builder designated for this building block
        return $this->builders[$classMetadata->getTableName()];
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    private function filtersDefaultOperators(array $filters): array
    {
        foreach ($filters as $key => $filter) {
            // give the filter default operators, according to its type
            if (
                (!array_key_exists('operators', $filter)) ||
                (empty($filter['operators']))
            ) {
                $builderType = $filter['type'];

                switch ($builderType) {
                    case 'json':
                    case 'string':
                    case 'text':
                        $filter['operators'] = [
                            'equal',
                            'not_equal',
                            'is_null',
                            'is_not_null',
                            'begins_with',
                            'not_begins_with',
                            'contains',
                            'not_contains',
                            'ends_with',
                            'not_ends_with',
                            'is_empty',
                            'is_not_empty', // specific to strings
                        ];

                        break;
                    case 'integer':
                    case 'double':
                    case 'date':
                    case 'time':
                    case 'datetime':
                        $filter['operators'] = [
                            'equal',
                            'not_equal',
                            'is_null',
                            'is_not_null',
                            'less',
                            'less_or_equal',
                            'greater',
                            'greater_or_equal',
                            'between',
                            'not_between', // specific to numbers and dates
                        ];

                        break;
                    case 'boolean':
                        $filter['operators'] = [
                            'equal',
                            'not_equal',
                            'is_null',
                            'is_not_null',
                        ];

                        break;
                }
            }
            $filters[$key] = $filter;
        }

        return $filters;
    }

    /**
     * @param array  $filters
     * @param string $builderId
     *
     * @return array
     *
     * @throws \LogicException
     */
    private function filtersOverrides(array $filters): array
    {
        foreach ($filters as $key => $filter) {

            $filterOperators = new FilterOperators();
            foreach ($filter['operators'] as $operator) {
                $filterOperators->addOperator($operator);
            }

            $filters[$key]['values']    = $filter['values'] ?? [];
            $filters[$key]['input']     = $filter['input'] ?? (count($filters[$key]['values']) ? FilterInput::INPUT_TYPE_SELECT : FilterInput::INPUT_TYPE_TEXT);
            $filters[$key]['operators'] = $filterOperators->getOperators();
        }

        return $filters;
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    private function filtersBooleanOverride(array $filters): array
    {
        foreach ($filters as $key => $filter) {
            $builderType = $filter['type'];

            switch ($builderType) {
                case 'boolean':
                    $filter['values'] = [
                        1 => 'Yes',
                        0 => 'No',
                    ];
                    $filter['input']  = 'select';
                    $filter['colors'] = [
                        1 => 'success',
                        0 => 'danger',
                    ];

                    break;
            }

            $filters[$key] = $filter;
        }

        return $filters;
    }

    /**
     * Use with @link https://eonasdan.github.io/bootstrap-datetimepicker/
     * Also make sure to account for @link https://github.com/mistic100/jQuery-QueryBuilder/issues/176.
     *
     * @param array $filters
     *
     * @return array
     */
    private function filtersDateOverrides(array $filters): array
    {
        foreach ($filters as $key => $filter) {
            $builderType = $filter['type'];

            switch ($builderType) {
                case 'datetime':
                case 'date':
                    $filter['validation'] = [
                        'format' => 'MM/DD/YYYY',
                    ];

                    /* $filter['plugin']        = 'datepicker';
                     $filter['plugin_config'] = [
                         'format' => 'MM/DD/YYYY',
                     ];*/

                    break;
                case 'datetime':
                    /* $filter['validation']    = [
                         'format' => 'MM/DD/YYYY HH:mm',
                     ];
                     $filter['plugin']        = 'datetimepicker'; // not found
                     $filter['plugin_config'] = [
                         'format' => 'MM/DD/YYYY HH:mm',
                     ];*/

                    break;
                case 'time':
                    $filter['validation']    = [
                        'format' => 'HH:mm',
                    ];
                    $filter['plugin']        = 'timepicker';
                    $filter['plugin_config'] = [
                        'showPeriod' => true,
                        'showLeadingZero' => true,
                        'defaultTime' => '',
                        'minTime' => [
                            'hour' => 0,
                            'minute' => 0,
                        ],
                        'maxTime' => [
                            'hour' => 23,
                            'minute' => 59,
                        ],
                    ];

                    break;
            }

            $filters[$key] = $filter;
        }

        return $filters;
    }

    /**
     * @param FilterValueCollection $collection
     * @param FilterInput           $input
     * @param string                $filterId
     * @param string                $builderId
     *
     * @throws \LogicException
     */
    private function validateValueCollectionAgainstInput(
        FilterValueCollection $collection, FilterInput $input, string $filterId, string $builderId
    ) {
        if (
            in_array($input->getInputType(), FilterInput::INPUT_TYPES_REQUIRE_NO_VALUES) &&
            0 !== $collection->getFilterValues()->count()
        ) {
            throw new \LogicException(sprintf('Too many values found, While building, Builder with ID %s and Filter with ID %s.', $builderId, $filterId));
        }
        if (
            in_array($input->getInputType(), FilterInput::INPUT_TYPES_REQUIRE_MULTIPLE_VALUES) &&
            0 === $collection->getFilterValues()->count()
        ) {
            throw new \LogicException(sprintf('Not enough values found, While building, Builder with ID %s and Filter with ID %s.', $builderId, $filterId));
        }
    }

    private function shouldExcludeField($fieldName, $entityName)
    {
        switch ($entityName) {

            case User::class:

                $excludedFields = [
                    'password',
                    'passwordResetToken',
                    'passwordResetTokenTimestamp',
                    'notificationPreferenceMask',
                    'photo',
                    'dashboardOrder',
                    'invitationCode',
                    'activationCode',
                    'tempPassword',
                    'temporarySecurityToken',
                    'updatedAt',
                ];

                break;

            case Lesson::class:

                $excludedFields = [
                    'featuredImage',
                    'thumbnailImage',
                    'updatedAt',
                ];

                break;

            case SiteAdminUser::class:

                $excludedFields = [
                    'password',
                    'passwordResetToken',
                    'passwordResetTokenTimestamp',
                    'notificationPreferenceMask',
                    'photo',
                    'dashboardOrder',
                    'invitationCode',
                    'activationCode',
                    'tempPassword',
                    'temporarySecurityToken',
                    'updatedAt',
                    'roles',
                ];

                break;

            case StudentUser::class:

                $excludedFields = [
                    'password',
                    'passwordResetToken',
                    'passwordResetTokenTimestamp',
                    'notificationPreferenceMask',
                    'photo',
                    'dashboardOrder',
                    'invitationCode',
                    'activationCode',
                    'tempPassword',
                    'temporarySecurityToken',
                    'updatedAt',
                    'roles',
                ];

                break;

            case SchoolAdministrator::class:

                $excludedFields = [
                    'password',
                    'passwordResetToken',
                    'passwordResetTokenTimestamp',
                    'notificationPreferenceMask',
                    'photo',
                    'dashboardOrder',
                    'invitationCode',
                    'activationCode',
                    'tempPassword',
                    'temporarySecurityToken',
                    'updatedAt',
                    'roles',
                ];

                break;

            case ProfessionalUser::class:

                $excludedFields = [
                    'password',
                    'passwordResetToken',
                    'passwordResetTokenTimestamp',
                    'notificationPreferenceMask',
                    'photo',
                    'dashboardOrder',
                    'invitationCode',
                    'activationCode',
                    'tempPassword',
                    'temporarySecurityToken',
                    'updatedAt',
                    'roles',
                    'latitude',
                    'longitude'
                ];

                break;

            case Share::class:

                $excludedFields = [
                    'updatedAt',
                ];

                break;

            case School::class:

                $excludedFields = [
                    'updatedAt',
                    'latitude',
                    'longitude'
                ];

                break;

            case RegionalCoordinator::class:

                $excludedFields = [
                    'password',
                    'passwordResetToken',
                    'passwordResetTokenTimestamp',
                    'notificationPreferenceMask',
                    'photo',
                    'dashboardOrder',
                    'invitationCode',
                    'activationCode',
                    'tempPassword',
                    'temporarySecurityToken',
                    'updatedAt',
                    'roles',
                ];

                break;

            case EducatorUser::class:

                $excludedFields = [
                    'password',
                    'passwordResetToken',
                    'passwordResetTokenTimestamp',
                    'notificationPreferenceMask',
                    'photo',
                    'dashboardOrder',
                    'invitationCode',
                    'activationCode',
                    'tempPassword',
                    'temporarySecurityToken',
                    'updatedAt',
                    'roles',
                ];

                break;

            case AdminUser::class:

                $excludedFields = [
                    'password',
                    'passwordResetToken',
                    'passwordResetTokenTimestamp',
                    'notificationPreferenceMask',
                    'photo',
                    'dashboardOrder',
                    'invitationCode',
                    'activationCode',
                    'tempPassword',
                    'temporarySecurityToken',
                    'updatedAt',
                    'roles',
                ];

                break;

            case Registration::class:

                $excludedFields = [
                    'updatedAt',
                ];

                break;

            case Region::class:

                $excludedFields = [
                    'updatedAt',
                ];

                break;

            case LessonFavorite::class:

                $excludedFields = [
                    'updatedAt',
                ];

                break;

            case LessonTeachable::class:

                $excludedFields = [
                    'updatedAt',
                ];

                break;

            case Experience::class:

                $excludedFields = [
                    'updatedAt',
                    'latitude',
                    'longitude'
                ];

                break;

            case Grade::class:

                $excludedFields = [
                    'updatedAt',
                ];

                break;

            case Company::class:

                $excludedFields = [
                    'updatedAt',
                    'latitude',
                    'longitude'
                ];

                break;

            case CompanyFavorite::class:

                $excludedFields = [
                    'updatedAt',
                ];

                break;

            case Course::class:

                $excludedFields = [
                    'updatedAt',
                ];

                break;

            case Chat::class:

                $excludedFields = [
                    'updatedAt',
                ];

                break;

            case ChatMessage::class:

                $excludedFields = [
                    'updatedAt',
                ];

                break;

            case StudentToMeetProfessionalExperience::class:

                $excludedFields = [
                    'updatedAt',
                ];

                break;

            case SchoolExperience::class:

                $excludedFields = [
                    'updatedAt',
                    'latitude',
                    'longitude'
                ];

                break;

            case TeachLessonExperience::class:

                $excludedFields = [
                    'updatedAt',
                    'latitude',
                    'longitude'
                ];

                break;

            case CompanyExperience::class:

                $excludedFields = [
                    'updatedAt',
                    'latitude',
                    'longitude'
                ];

                break;

            default:
                $excludedFields = [];
                break;

        }

        if (in_array($fieldName, $excludedFields, true)) {
            return true;
        }

        return false;
    }

    private function shouldExcludeAssociation($fieldName, $entityName)
    {
        switch ($entityName) {

            case User::class:

                $excludedAssociations = [
                    'chatMessages',
                    'companyViews',
                    'requests',
                    'requestPossibleApprovers',
                    'requestsThatNeedMyApproval',
                    'sentFromShares',
                    'sentToShares',
                    'userRegisterForSchoolExperienceRequests',
                    'videoFavorites',
                ];

                break;

            case Lesson::class:

                $excludedAssociations = [
                    'lessonResources',
                    'teachLessonRequests'
                ];

                break;

            case SiteAdminUser::class:

                $excludedAssociations = [
                    'chatMessages',
                    'companyFavorites',
                    'companyViews',
                    'feedback',
                    'lessonFavorites',
                    'lessonTeachables',
                    'lessons',
                    'registrations',
                    'requestPossibleApprovers',
                    'requests',
                    'requestsThatNeedMyApproval',
                    'schoolExperiences',
                    'sentFromShares',
                    'sentToShares',
                    'userRegisterForSchoolExperienceRequests',
                    'videoFavorites',
                    'site'
                ];

                break;

            case StudentUser::class:

                $excludedAssociations = [
                    'site',
                    'videoFavorites',
                    'userRegisterForSchoolExperienceRequests',
                    'studentToMeetProfessionalRequests',
                    'studentReviewTeachLessonExperienceFeedback',
                    'studentReviewExperienceFeedback',
                    'sentToShares',
                    'sentFromShares',
                    'requests',
                    'requestsThatNeedMyApproval',
                    'requestPossibleApprovers',
                    'lessonTeachables',
                    'educatorRegisterStudentForCompanyExperienceRequests',
                    'chatMessages',
                    'allowedCommunications'
                ];

                break;

            case SchoolAdministrator::class:

                $excludedAssociations = [
                    'site',
                    'videoFavorites',
                    'userRegisterForSchoolExperienceRequests',
                    'sentToShares',
                    'sentFromShares',
                    'requests',
                    'requestsThatNeedMyApproval',
                    'requestPossibleApprovers',
                    'lessonTeachables',
                    'chatMessages',
                    'schoolAdminRegisterSAForCompanyExperienceRequests'
                ];

                break;

            case ProfessionalUser::class:

                $excludedAssociations = [
                    'allowedCommunications',
                    'chatMessages',
                    'registrations',
                    'requestPossibleApprovers',
                    'requests',
                    'requestsThatNeedMyApproval',
                    'professionalVideos',
                    'sentToShares',
                    'sentFromShares',
                    'studentToMeetProfessionalRequests',
                    'userRegisterForSchoolExperienceRequests',
                    'videoFavorites'
                ];

                break;

            case Share::class:

                $excludedAssociations = [
                ];

                break;

            case School::class:

                $excludedAssociations = [
                    'featuredImage',
                    'thumbnailImage',
                    'schoolPhotos',
                    'schoolVideos',
                    'schoolResources',
                    'teachLessonRequests',
                    'site'
                ];

                break;

            case RegionalCoordinator::class:

                $excludedAssociations = [
                    'site',
                    'videoFavorites',
                    'userRegisterForSchoolExperienceRequests',
                    'sentToShares',
                    'sentFromShares',
                    'requests',
                    'requestsThatNeedMyApproval',
                    'requestPossibleApprovers',
                    'lessonTeachables',
                    'chatMessages',
                ];

                break;

            case EducatorUser::class:

                $excludedAssociations = [
                    'site',
                    'videoFavorites',
                    'sentToShares',
                    'sentFromShares',
                    'requests',
                    'educatorVideos',
                    'requestsThatNeedMyApproval',
                    'requestPossibleApprovers',
                    'chatMessages',
                    'educatorRegisterEducatorForCompanyExperienceRequests',
                    'userRegisterForSchoolExperienceRequests'
                ];

                break;

            case AdminUser::class:

                $excludedAssociations = [
                    'chatMessages',
                    'companyFavorites',
                    'companyViews',
                    'feedback',
                    'lessonFavorites',
                    'lessonTeachables',
                    'lessons',
                    'registrations',
                    'requestPossibleApprovers',
                    'requests',
                    'requestsThatNeedMyApproval',
                    'schoolExperiences',
                    'sentFromShares',
                    'sentToShares',
                    'userRegisterForSchoolExperienceRequests',
                    'videoFavorites',
                    'site'
                ];

                break;

            case Registration::class:

                $excludedAssociations = [
                ];

                break;

            case Region::class:

                $excludedAssociations = [
                    'site'
                ];

                break;

            case LessonFavorite::class:

                $excludedAssociations = [
                ];

                break;

            case LessonTeachable::class:

                $excludedAssociations = [
                ];

                break;

            case Experience::class:

                $excludedAssociations = [
                    'experienceFiles',
                    'shares'
                ];

                break;

            case Grade::class:

                $excludedAssociations = [
                ];

                break;

            case Company::class:

                $excludedAssociations = [
                    'featuredImage',
                    'newCompanyRequest',
                    'thumbnailImage',
                    'companyPhotos',
                    'companyVideos',
                    'companyResources',
                    'joinCompanyRequests'
                ];

                break;

            case CompanyFavorite::class:

                $excludedAssociations = [
                ];

                break;

            case Course::class:

                $excludedAssociations = [
                ];

                break;

            case Chat::class:

                $excludedAssociations = [
                    'messages'
                ];

                break;

            case ChatMessage::class:

                $excludedAssociations = [
                ];

                break;

            case StudentToMeetProfessionalExperience::class:

                $excludedAssociations = [
                    'experienceFiles',
                    'originalRequest',
                    'shares'
                ];

                break;

            case SchoolExperience::class:

                $excludedAssociations = [
                    'experienceFiles',
                    'userRegisterForSchoolExperienceRequests',
                ];

                break;

            case TeachLessonExperience::class:

                $excludedAssociations = [
                    'shares',
                    'experienceFiles',
                    'originalRequest',
                ];

                break;

            default:
                $excludedAssociations = [];
                break;

        }

        if (in_array($fieldName, $excludedAssociations, true)) {
            return true;
        }

        return false;
    }

    private function contextOverride($fieldName, $entityName, $context)
    {
        switch ($entityName) {

            case User::class:

                if ($fieldName === 'roles') {

                    $context['type']      = 'string';
                    $context['input']     = 'select';
                    $context['values']    = Report::getUserRoles();
                    $context['operators'] = [
                        'contains',
                        'not_contains',
                    ];
                }

                break;

            default:
                $excludedFields = [];
                break;

        }

        return $context;
    }

    private function associationLabelOverride($label, $entityName)
    {
        switch ($entityName) {

            case TeachLessonExperience::class:

                if ($label === '1:N Educator Review Teach Lesson Experience Feedback') {
                    $label = '1:N Educator Feedback';
                }

                if ($label === '1:N Student Review Teach Lesson Experience Feedback') {
                    $label = '1:N Student Feedback';
                }

                break;

            case StudentToMeetProfessionalExperience::class:

                if ($label === '1:N Professional Review Meet Student Experience Feedback') {
                    $label = '1:N Professional Feedback';
                }

                break;

            case School::class:

                if ($label === '1:N Teach Lesson Experiences') {
                    $label = '1:N Teach Lesson Experiences (Topic Instructor)';
                }

                break;

            case Lesson::class:

                if ($label === '1:N Educator Review Teach Lesson Experience Feedback') {
                    $label = '1:N Educator Feedback';
                }

                if ($label === '1:N Student Review Teach Lesson Experience Feedback') {
                    $label = '1:N Student Feedback';
                }

                break;

            case EducatorUser::class:

                if ($label === '1:N Educator Review Company Experience Feedback') {
                    $label = '1:N Educator Company Experience Feedback';
                }

                if ($label === '1:N Educator Review Teach Lesson Experience Feedback') {
                    $label = '1:N Educator Teach Lesson Experience Feedback';
                }

                if ($label === '1:N Lesson Teachables') {
                    $label = '1:N Lessons I Want Taught';
                }

                break;

            case ProfessionalUser::class:

                if ($label === '1:N Professional Review Meet Student Experience Feedback') {
                    $label = '1:N Professional Feedback';
                }

                if ($label === '1:N Teach Lesson Experiences') {
                    $label = '1:N Teach Lesson Experiences (Topic Instructor)';
                }

                if ($label === '1:N Lesson Teachables') {
                    $label = '1:N Lessons I Can Teach';
                }

                if ($label === '1:N Report Volunteer Schools') {
                    $label = '1:N Volunteer Schools';
                }

                if ($label === '1:N Report Volunteer Regions') {
                    $label = '1:N Volunteer Regions';
                }

                if ($label === '1:N Report Volunteer Roles') {
                    $label = '1:N Volunteer Roles';
                }

                break;
        }

        return $label;
    }

    private function fieldLabelOverride($label, $entityName)
    {
        switch ($entityName) {

            case LessonTeachable::class:

                if ($label === 'Report Lesson Name') {
                    $label = 'Lesson Name';
                }

                break;
        }

        switch ($entityName) {

            case ProfessionalUser::class:

                if ($label === 'Report Schools Volunteer At') {
                    $label = 'Volunteer Schools';
                }

                break;
        }

        return $label;
    }

    /**
     * @return Builder[]
     */
    public function getBuilders()
    {
        return $this->builders;
    }

    /**
     * @param string $builderId
     *
     * @return Builder|null
     */
    public function getBuilderById(string $builderId)
    {
        if (array_key_exists($builderId, $this->builders)) {
            return $this->builders[$builderId];
        }

        return null;
    }
}