<?php

namespace App\Tests\Api;

use App\Entity\ACRole;
use App\Entity\Organisation;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ACRoleTest extends WebTestCase
{
    function setUp()
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testACRole() {
        $role = new ACRole();
        $role->initiateUuid();
        $role->setName('ROLE_USER');
        $em = static::$container->get('doctrine')->getManager();
        $em->persist($role);
        $em->flush();
    }
}