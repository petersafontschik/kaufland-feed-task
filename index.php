<?php
declare(strict_types=1);

require("vendor/autoload.php");

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\ErrorHandler;

use Safontschik\Kaufland\DataImporter;
use Safontschik\Kaufland\DataConversionConfig;
use Safontschik\Kaufland\Client;
use Safontschik\Kaufland\SQLiteExportStrategy;

// Unlink all files which were generated by previous runs
if (file_exists("error.log")) {
    unlink("error.log");
}
if (file_exists("catalog.csv")) {
    unlink("catalog.csv");
}
if (file_exists("catalog.sql")) {
    unlink("catalog.sql");
}
if (file_exists("catalog.db")) {
    unlink("catalog.db");
}

// Create a new Monolog logger and assign it as a "global" PHP logger which handles errors
$logger = new Logger('Error Logger');
$logger->pushHandler(new StreamHandler("error.log"),Level::Error);
ErrorHandler::register($logger);

// Data conversion starts here
$dataConversionConfig = new DataConversionConfig("pipeline_config.json");
$dataConversionConfig->Load();
$client = new Client($dataConversionConfig);
$client->InitClient();

// Adapter handles the conversion from XML to CSV
$adapter = $client->GetAdapter();
$adapter->LoadFile();
$entityName = $adapter->GetEntityName();
$fieldNames = $adapter->GetFieldNames();
$fieldTypes = $adapter->GetFieldTypes();
$adapter->WriteIntermediateDataFile($entityName,$fieldTypes);

// Data export is handeled by the corresponding strategy
switch($dataConversionConfig->GetExportDataType()){
    case "sqlite":
    {
        $sqliteExportStrategy = new SQLiteExportStrategy($fieldNames,$fieldTypes);
        $sqlSchema = $sqliteExportStrategy->GenerateSchema($entityName);
        $sqliteExportStrategy->WriteSchemaFile($entityName,$sqlSchema);
        $sqliteExportStrategy->ExportToSink($dataConversionConfig->GetSinkPath(),"$entityName.sql");
    }
}
?>