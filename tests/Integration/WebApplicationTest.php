<?php

namespace Majordome\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

class WebApplicationTest extends WebTestCase
{
    private static HttpKernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        static::$client = static::createClient();
    }

    public function testGetListRuns(): void
    {
        $crawler = static::$client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $runRows = $crawler->filter('table')
            ->filter('tbody')
            ->filter('tr');

        $violationsCountElementIndex = 4;
        $this->assertEquals(2, $runRows->count());
        $this->assertEquals(1, $runRows->first()->filter('td')->eq($violationsCountElementIndex)->text());
        $this->assertEquals(4, $runRows->last()->filter('td')->eq($violationsCountElementIndex)->text());
    }

    public function testGetRunDetails(): void
    {
        static::$client->request('GET', '/run/1');
        $this->assertResponseIsSuccessful();
        $pageContent = static::$client->getResponse()->getContent();
        $this->assertStringContainsString("Run 1", $pageContent);
    }

    public function testGetRunNotFound(): void
    {
        static::$client->request('GET', '/run/16152');
        $this->assertTrue(static::$client->getResponse()->isNotFound());
    }
}
