<?php

namespace App\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Filters
 * @package App\Model
 */
class Filters
{

    const ITEMS_PER_PAGE = 20;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var int
     */
    private $page = 1;

    /**
     * @var string
     */
    private $search = '';

    /**
     * @var array
     */
    private $companies = [];

    /**
     * @var array
     */
    private $primaryIndustries = [];

    /**
     * @var array
     */
    private $secondaryIndustries = [];

    /**
     * @var array
     */
    private $roles = [];

    /**
     * @var string
     */
    private $zipcode;

    /**
     * @var string
     */
    private $radius;

    public function __construct(Request $request = null)
    {
        if (!$request) {
            return;
        }

        $this->request = $request;
        $search        = $request->query->get('search')['search'];
    }

}