<?php

declare(strict_types=1);

namespace App\Message;

use Doctrine\ORM\EntityManagerInterface;

abstract class Message
{
    public $version;

    public function getUpdatedEntity(EntityManagerInterface $manager){
        $body = json_decode($this->body);
        $type = $body['TYPE'];

    }

    public $url;

    public $id;

    public $body;

    public $receiptHandle;
}
