<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class UsernameInvalidValidator
 *
 * @package App\Validator\Constraints
 */
class YoutubeVideoIdValidator extends ConstraintValidator
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $youtubeApiKey;

    /**
     * FreshAddress constructor.
     *
     * @param HttpClientInterface $client
     * @param string              $youtubeApiKey
     */
    public function __construct(HttpClientInterface $client, string $youtubeApiKey)
    {
        $this->client        = $client;
        $this->youtubeApiKey = $youtubeApiKey;
    }

    /**
     * @param            $value
     * @param Constraint $constraint
     *
     * @return \Exception|void
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        try {
            $url = "https://www.googleapis.com/youtube/v3/videos";

            $response = $this->client->request(
                'GET', $url, [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'query' => [
                        'part' => 'id',
                        'id' => $value,
                        'key' => $this->youtubeApiKey,
                    ],
                ]
            );

            $responseArray = $response->toArray();

            if ($responseArray && !empty($responseArray['items'])) {
                return;
            }

        } catch (\Exception $exception) {
        }

        $this->context->buildViolation($constraint->message)
                      ->addViolation();
    }
}