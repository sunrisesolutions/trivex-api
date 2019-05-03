<?php

namespace App\Controller;

use App\Entity\IndividualMember;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SendEmailToIndividualMember
{
    private $mailer;
    private $registry;

    public function __construct(RegistryInterface $registry, \Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
        $this->registry = $registry;
    }

    public function __invoke(IndividualMember $data): IndividualMember
    {

        /** @var IndividualMember $member */
        $member = $this->registry->getRepository(IndividualMember::class)->find($data->emailTo);
        if (!empty($member)) {
            if (!empty($toEmail = $member->getPerson()->getEmail())) {
                $message = (new \Swift_Message($data->emailSubject))
                    ->setFrom('no-reply@magentapulse.com')
                    ->setTo($toEmail)
                    ->setBody(
                        $data->emailBody,
                        'text/html'
                    )/*
                 * If you also want to include a plaintext version of the message
                ->addPart(
                    $this->renderView(
                        'emails/registration.txt.twig',
                        array('name' => $name)
                    ),
                    'text/plain'
                )
                */
                ;

                $this->mailer->send($message);
            }
        }
        return $data;
    }
}