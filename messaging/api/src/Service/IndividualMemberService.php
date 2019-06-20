<?php

namespace App\Service;

use App\Entity\IndividualMember;
use App\Entity\Message;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IndividualMemberService
{
    private $manager;
    private $container;

    public function __construct(EntityManagerInterface $manager, ContainerInterface $container)
    {
        $this->manager = $manager;
        $this->container = $container;
    }

    public function notifyOneOrganisationIndividualMembers(Message $message)
    {
        $row = 0;

        try {
            $memberRepo = $this->manager->getRepository(IndividualMember::class);
            ////////////// PWA Púh ////////////
//            $members = $memberRepo->findHavingOrganisationSubscriptions((int) $dp->getOwnerId());
//
//
            $path = $this->container->getParameter('PWA_PUBLIC_KEY_PATH');
            $pwaPublicKey = trim(file_get_contents($path));
            $path = $this->container->getParameter('PWA_PRIVATE_KEY_PATH');
            $pwaPrivateKey = trim(file_get_contents($path));
            $auth = [
                'VAPID' => [
                    'subject' => 'mailto:peter@magenta-wellness.com',
                    'publicKey' => $pwaPublicKey,
                    'privateKey' => $pwaPrivateKey, // in the real world, this would be in a secret file
                ],
            ];
            $webPush = new WebPush($auth);
//                $multipleRun = false;
            /*
             * @var IndividualMember
             */
            while (!empty($members = $message->getRecipientsByPage())) {
                /** @var IndividualMember $member */
                foreach ($members as $member) {
                    if ($member->isMessageDelivered($message)) {
                        continue;
                    }
                    ++$row;
//                    if ($row > 1000) {
//                        $multipleRun = true;
//                        break;
//                    }

                    $subscriptions = $member->getNotifSubscriptions();

                    $preparedSubscriptions = [];
                    /**
                     * @var Subscription $_sub
                     */
                    foreach ($subscriptions as $_sub) {
                        $preparedSub = Subscription::create(
                            [
                                'endpoint' => $_sub->getEndpoint(),
                                'publicKey' => $_sub->getPublicKey(),
                                'authToken' => $_sub->getAuthToken(),
                                'contentEncoding' => $_sub->getContentEncoding(), // one of PushManager.supportedContentEncodings
                            ]
                        );
                        $preparedSubscriptions[] = $preparedSub;

                        $webPush->sendNotification(
                            $preparedSub,
                            json_encode([
                                'sender-name' => $message->getSender()->getPerson()->getName(),
                                'message-id' => $message->getId(),
                                'message-subject' => $message->getSubject(),
                                'subscription-id' => $_sub->getId(),]),
                            false
                        );
                    }

//                    $recipient = $member;
//                    $delivery = MessageDelivery::createInstance($message, $recipient);
                }
                $res = $webPush->flush();
            }


            while (!empty($deliveries = $message->commitDeliveries())) {
                foreach ($deliveries as $delivery) {
                    $this->manager->persist($delivery);
                }
                $this->manager->flush();
            }

            if (!$this->manager->isOpen()) {
                throw new \Exception('EM is closed before flushed '.$row);
            } else {

            }
            $message->setStatus(Message::STATUS_DELIVERY_SUCCESSFUL);
            $this->manager->persist($message);
            $this->manager->flush();
        } catch (OptimisticLockException $ope) {
            throw $ope;
        } catch (ORMException $orme) {
            throw $orme;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}