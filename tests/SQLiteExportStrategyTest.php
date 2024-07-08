<?php
declare(strict_types=1);

namespace Safontschik\Kaufland\Tests;

use PHPUnit\Framework\TestCase;
use Safontschik\Kaufland\DataConversionConfig;
use Safontschik\Kaufland\Client;
use Safontschik\Kaufland\SQLiteExportStrategy;
use Safontschik\Kaufland\XmlToCsvAdapter;

final class SQLiteExportStrategyTest extends TestCase
{
    protected static DataConversionConfig $dataConversionConfig;
    protected static Client $client;
    protected static SQLiteExportStrategy $sqliteExportStrategy;
    protected static XmlToCsvAdapter $adapter;
    protected static string $entityName;
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
        self::$dataConversionConfig = new DataConversionConfig("pipeline_config.json");
        self::$dataConversionConfig->Load();
        self::$client = new Client(self::$dataConversionConfig);
        self::$client->InitClient();
        self::$adapter = self::$client->GetAdapter();
        self::$adapter->LoadFile();
        self::$adapter->entityName = "";
        self::$adapter->fieldNames = array();
        self::$adapter->fieldTypes = array();
    }

    public function testGeneratedDataSchema(): void
    {
        $expectedSqlScript = "catalog.sql";
        $entityName = self::$adapter->GetEntityName();
        $fieldNames = self::$adapter->GetFieldNames();
        $fieldTypes = self::$adapter->GetFieldTypes();

        self::$sqliteExportStrategy = new SQLiteExportStrategy($fieldNames,$fieldTypes);
        $sqlSchema = self::$sqliteExportStrategy->GenerateSchema($entityName);
        self::$sqliteExportStrategy->WriteSchemaFile($entityName,$sqlSchema);

        $this->assertSame(hash('sha256',$sqlSchema),hash('sha256',file_get_contents("data/$expectedSqlScript")));
    }

    public function testOnSuccesfullDataExport(): void
    {
        $entityName = self::$adapter->GetEntityName();
        $fieldNames = self::$adapter->GetFieldNames();
        $fieldTypes = self::$adapter->GetFieldTypes();
        $sinkPath = self::$dataConversionConfig->GetSinkPath();
        self::$adapter->WriteIntermediateDataFile($entityName,$fieldTypes);
        $sqliteExportStrategy = new SQLiteExportStrategy($fieldNames,$fieldTypes);
        $sqlSchema = $sqliteExportStrategy->GenerateSchema($entityName);
        $sqliteExportStrategy->WriteSchemaFile($entityName,$sqlSchema);
        $sqliteExportStrategy->ExportToSink($sinkPath,"$entityName.sql");
        $db = new \SQLite3("$entityName.db");
        $query = $db->querySingle("SELECT COUNT(*) FROM $entityName;");
        $itemCount = intval($query);
        $this->assertSame($itemCount,3449);
    }

    protected function tearDown(): void {
        $csv = glob("*.csv");
        $db = glob("*db");
        $sql = glob("*sql");
        array_map('unlink',$csv);
        array_map('unlink',$db);
        array_map('unlink',$sql);
    }
}