<?php

namespace App\DataFixtures;

use App\Entity\Organisation;
use App\Entity\OrganisationUser;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class OrganisationFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $org = new Organisation();
        $org->setCode('magenta');
        
        $user = new User();
        $user->setIdNumber('024290123');
        $user->setEmail('user1-with-organisation@gmail.com');
        $user->setUsername('user1-with-organisation');
        $user->setPhone('01234567247');
        $user->setBirthDate(new \DateTime('04-10-1987'));
        $user->setRoles(['ROLE_USER',
        ]);
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'p@ssword'
        ));
        
        $ou = new OrganisationUser();
        $org->addOrganisationUser($ou);
        $user->addOrganisationUser($ou);
    
    
        $manager->persist($org);
        $manager->persist($user);
        $manager->persist($ou);
        
        $manager->flush();
    }
}
