<?php

namespace Ams\DistributionBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CompteRenduControllerTest extends WebTestCase
{
    public function testReceptiondepot()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'compte-rendu');
    }

}
