<?php

namespace LaminasTest\Cache\Psr\SimpleCache;

use Cache\IntegrationTests\SimpleCacheTest;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Cache\Storage\Adapter\ExtMongoDb;
use Laminas\Cache\Storage\PluginAwareInterface;
use Laminas\Cache\StorageFactory;
use Psr\SimpleCache\CacheInterface;

use function date_default_timezone_get;
use function date_default_timezone_set;
use function getenv;

class ExtMongoDbIntegrationTest extends SimpleCacheTest
{
    /**
     * Backup default timezone
     *
     * @var string
     */
    private $tz;

    protected function setUp(): void
    {
        // set non-UTC timezone
        $this->tz = date_default_timezone_get();
        date_default_timezone_set('America/Vancouver');

        /** @psalm-suppress MixedArrayAssignment */
        $this->skippedTests['testBasicUsageWithLongKey'] = 'SimpleCacheDecorator requires keys to be <= 64 chars';
        /** @psalm-suppress MixedArrayAssignment */
        $this->skippedTests['testBinaryData'] = 'Binary data not supported';

        parent::setUp();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->tz);

        parent::tearDown();
    }

    public function createSimpleCache(): CacheInterface
    {
        $storage = new ExtMongoDb([
            'server'     => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_CONNECTSTRING'),
            'database'   => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_DATABASE'),
            'collection' => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_COLLECTION'),
        ]);

        $serializer = StorageFactory::pluginFactory('serializer');
        self::assertInstanceOf(PluginAwareInterface::class, $storage);
        $storage->addPlugin($serializer);

        return new SimpleCacheDecorator($storage);
    }
}
