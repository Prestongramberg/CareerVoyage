<?php

namespace App\Request\ParamConverter;

use App\Entity\Chat;
use App\Repository\ChatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;


class ChatConverter implements ParamConverterInterface
{

    /**
     * @var ChatRepository
     */
    private $chatRepository;

    /**
     * PropertyConverter constructor.
     * @param ChatRepository $chatRepository
     */
    public function __construct(ChatRepository $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }


    /**
     * Stores the object in the request.
     *
     * @param Request $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
	    $chatId = $request->attributes->get('id', false);
	    if(!$chatId) {
		    $chatId = $request->attributes->get('chatId');
	    }
        $chat = $this->chatRepository->find($chatId);
        if(!$chat) {
            return false;
        }
        $request->attributes->set($configuration->getName(), $chat);
        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {

        if($configuration->getClass() !== Chat::class) {
            return false;
        }

        return true;
    }
}
