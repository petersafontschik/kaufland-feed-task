<?php
    declare(strict_types=1);

    namespace Safontschik\Kaufland\Tests;

    use Safontschik\Kaufland\DataConversionConfig;
    use Safontschik\Kaufland\Client;
    use Safontschik\Kaufland\XmlToCsvAdapter;
    use PHPUnit\Framework\TestCase;

    final class ClientTest extends TestCase
    {
        protected static DataConversionConfig $dataConversionConfig;
        protected static Client $client;
        protected static XmlToCsvAdapter $adapter;

        public static function setUpBeforeClass(): void
        {
            $globDb = glob("*.db");
            $globCsv = glob("*.csv");
            $globSql = glob("*.sql");
            array_map('unlink',$globDb);
            array_map('unlink',$globCsv);
            array_map('unlink',$globSql);
        }

        protected function setUp(): void
        {
            self::$dataConversionConfig = new DataConversionConfig("pipeline_config.json");
            self::$client = new Client(self::$dataConversionConfig);
            self::$client->InitClient();
            self::$adapter = self::$client->GetAdapter();
            self::$adapter->LoadFile();
        }

        public function testWhetherConfigurationIsCorrect(): void
        {
            $sourcePath = self::$dataConversionConfig->GetSourcePath();
            $this->assertSame($sourcePath,"data/feed.xml");
        }

        public function testIfAdaptersIsProperlyInitialized(): void
        {
            $this->assertSame(self::$adapter->GetSourcePath(),"data/feed.xml");
        }

        public function testEntityNameIsCorrect(): void
        {
            $entityName = self::$adapter->GetEntityName();
            $this->assertSame($entityName,"catalog");
        }

        public function testAllFieldNamesCaptured(): void
        {
            $fieldNames = self::$adapter->GetFieldNames();
            $this->assertSame(count($fieldNames),18);
        }

        public function testAllFieldTypesCaptured(): void
        {
            $fieldTypes = self::$adapter->GetFieldTypes();
            $this->assertSame(count($fieldTypes),18);
        }
    }