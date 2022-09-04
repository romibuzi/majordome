<?php

namespace Majordome\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

class WebApplicationTest extends TestCase
{
    /** @var \Silex\Application|null */
    private static $app = null;

    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass(): void
    {
        // some part of the application will be bootstraped differently with env set at 'test'
        // see app/app.php for details
        putenv("ENV=test");
        $app = require dirname(__DIR__) . '/app/app.php';
        self::$app = $app;
    }

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass(): void
    {
        if (null !== self::$app) {
            // Destroy the test database
            unlink(self::$app['db_test.path']);

            self::$app = null;
        }
    }

    public function testIndexPage()
    {
        $runId = $this->createFakeRun();
        $client = $this->createClient();

        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());

        $pageContent = $client->getResponse()->getContent();

        $this->assertStringContainsString('List of last runs', $pageContent);
        $this->assertStringContainsString('<table', $pageContent);

        // The table shoud contains the run inserted
        $list = $crawler->filterXPath('//table/tbody/tr');
        $this->assertSame(1, $list->count());

        $runLink = $list->first()->selectLink('View Run')->link()->getUri();
        $this->assertStringContainsString("/run/$runId", $runLink);
    }

    public function testRunDetailsPage()
    {
        $runId = $this->createFakeRun();
        $client = $this->createClient();

        $client->request('GET', "/run/$runId");
        $this->assertTrue($client->getResponse()->isOk());

        $pageContent = $client->getResponse()->getContent();
        $this->assertStringContainsString("Run $runId", $pageContent);
    }

    public function testNotFoundRun()
    {
        $client = $this->createClient();

        // Go to a non existing run details page, which should result to a 404
        $client->request('GET', '/run/16152');
        $this->assertTrue($client->getResponse()->isNotFound());
    }

    private function createClient()
    {
        static $client = null;
        if (null === $client) {
            $client = new HttpKernelBrowser(self::$app);
        }

        return $client;
    }

    /**
     * @return int the id of the created run
     */
    private function createFakeRun()
    {
        /** @var \Doctrine\DBAL\Connection $db */
        $db = self::$app['db'];

        $db->insert('runs', [
            'createdAt' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);

        return $db->lastInsertId();
    }
}
