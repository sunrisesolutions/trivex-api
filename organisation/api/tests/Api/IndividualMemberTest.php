<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use App\Entity\IndividualMember;

class IndividualMemberTest extends WebTestCase {

    //use RefreshDatabaseTrait;

    function setUp()
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testIsAdmin() {
        $imRepo = static::$container->get('doctrine')->getRepository(IndividualMember::class);
        /** @Var IndividualMember */
        $ims = $imRepo->findAll();
        foreach ($ims as $im) {
            echo $im->getId() . ' - ';
            var_dump($im->isAdmin());
            if (!$im->isAdmin()) {
                $im->makeAdmin();
                break;
            }
        }
    }
}