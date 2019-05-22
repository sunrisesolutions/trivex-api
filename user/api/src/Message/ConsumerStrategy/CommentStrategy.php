<?php

declare(strict_types=1);

namespace App\Message\ConsumerStrategy;

use App\Message\Message;
use App\Util\AwsSqsUtilInterface;
use Psr\Log\LoggerInterface;

class CommentStrategy implements StrategyInterface
{
    public const QUEUE_NAME = 'comment';

    private $awsSqsUtil;
    private $logger;

    public function __construct(
        AwsSqsUtilInterface $awsSqsUtil,
        LoggerInterface $logger
    ) {
        $this->awsSqsUtil = $awsSqsUtil;
        $this->logger = $logger;
    }

    public function canProcess(Message $message = null, string $queue = null): bool
    {
        return self::QUEUE_NAME === $queue;
    }

    public function process(Message $message): void
    {
        $body = json_decode($message->body, true);

        if (array_key_exists('is_good_message', $body) && $body['is_good_message']) {
            $this->logger->alert(sprintf('The message "%s" has been consumed.', $message->id));
        } else {
            $this->logger->alert(sprintf('The message "%s" has been deleted.', $message->id));
        }

        $this->awsSqsUtil->deleteMessage($message);
    }
}
