<?php

namespace App\DataFixtures;

use App\Entity\Person;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PersonFixtures extends Fixture
{
    private $passwordEncoder;

    const FIRST_PERSON = 'PERSON-5dd3c0ba00f40-103827042019';

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $person = new Person();
        $person->setUuid(self::FIRST_PERSON);
        $person->setEmail('user1@gmail.com');
        $person->setPhoneNumber('0369140916');
        $person->setBirthDate(new \DateTime('04-10-1987'));


        $manager->persist($person);

        $manager->flush();
    }
}
