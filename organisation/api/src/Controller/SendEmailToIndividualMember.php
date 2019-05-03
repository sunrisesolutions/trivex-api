<?php


namespace App\Controller;


use App\Entity\IndividualMember;

class SendEmailToIndividualMember
{
    private $mailer;
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(IndividualMember $data): IndividualMember
    {

        $message = (new \Swift_Message($data->emailSubject))
            ->setFrom('no-reply@magentapulse.com')
            ->setTo($data->emailTo)
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
        return $data;
    }
}