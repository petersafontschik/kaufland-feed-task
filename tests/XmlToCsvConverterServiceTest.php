<?php
    declare(strict_types=1);

    namespace Safontschik\Kaufland;

    use PHPUnit\Framework\TestCase;
    use Safontschik\Kaufland\XmlToCsvConverterService;
    use Safontschik\Kaufland\FieldDataType;

    class XmlToCsvConverterServiceTest extends TestCase
    {
        protected static XmlToCsvConverterService $xmlToCsvConverterService;
        protected static array $fieldNames;
        protected static array $fieldTypes;

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
            self::$xmlToCsvConverterService = new XmlToCsvConverterService("data/feed.xml");
            self::$xmlToCsvConverterService->LoadXmlFile();
        }

        public function testSuccesfullXmlFileLoad(): void
        {
            $currElement = self::$xmlToCsvConverterService->GetCurrentElement();
            $this->assertSame(get_class($currElement),"SimpleXMLElement");
        }

        public function testFailXmlFileLoad(): void
        {
            $badXmlToCsvConverterService = new XmlToCsvConverterService("data/fee.xml");
            try {
                $badXmlToCsvConverterService->LoadXmlFile();
                $this->fail("ERROR: Since \"data/fee.xml\" doesn't exist, this test shouldn't get this far altogether.");
            } catch (\TypeError $typeError) {
                $this->assertTrue(true);
            }
            $this->assertTrue(true);
        }

        public function testEmptyXmlFileReturnsEmptyArray(): void
        {
            $badXmlToCsvConverterService = new XmlToCsvConverterService("data/empty_feed.xml");
            $badXmlToCsvConverterService->LoadXmlFile();
            $columnNames = $badXmlToCsvConverterService->GetFieldNames();
            $this->assertTrue(empty($columnNames));
        }

        public function testXmlFileReturnsNonEmptyArray(): void
        {
            self::$fieldNames = self::$xmlToCsvConverterService->GetFieldNames();
            self::$fieldTypes = self::$xmlToCsvConverterService->GetFieldTypes();
            $this->assertSame(!empty(self::$fieldNames),!empty(self::$fieldTypes));
            $this->assertSame(count(self::$fieldNames),18);
            $this->assertSame(count(self::$fieldTypes),18);
        }

        public function testWriteIntermediateDataFile(): void
        {
            $entityName = self::$xmlToCsvConverterService->GetEntityName();
            self::$xmlToCsvConverterService->WriteIntermediateDataFile($entityName,self::$fieldTypes);
            $lineCount = count(file($entityName . ".csv"));
            $this->assertSame($lineCount,3449);
        }

        protected function tearDown(): void {
            $csv = glob("*.csv");
            array_map('unlink',$csv);
        }
    }