<?php

declare(strict_types=1);

namespace App\Message\ConsumerStrategy;

use App\Message\Message;

interface StrategyInterface
{
    public function canProcess(string $queue): bool;
    
    public function process(Message $message): void;
}
