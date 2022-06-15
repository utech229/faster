<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializerBuilder;

class APIResponse extends AbstractController
{
    public function new($data = [], $code = 404)
    {
        $response = new Response(
            $this->serialize(["error"=>"not found"]),
            Response::HTTP_NOT_FOUND,
            ['content-type' => 'application/json']
        );

        if($data != []) $response->setContent($this->serialize($data));
        if((int)$code != 404) $response->setStatusCode((int)$code);
        return $response;
    }

    public function serialize($data = [])
    {
        $serializer = SerializerBuilder::create()->build();

        return $serializer->serialize($data, 'json');
    }

    public function deserialize($jsonData = "{}", $objectEntity = null)
    {
        $serializer = SerializerBuilder::create()->build();

        return $serializer->deserialize($jsonData, $objectEntity, 'json');
    }
}
