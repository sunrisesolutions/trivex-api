<?php

declare(strict_types=1);

namespace App\Message;

use Doctrine\ORM\EntityManagerInterface;

abstract class Message
{
    public $version;

    public $operation;

    public $data;

    public abstract function updateEntity(EntityManagerInterface $manager);

    public $url;

    public $id;

    public $body;

    public $receiptHandle;
}
