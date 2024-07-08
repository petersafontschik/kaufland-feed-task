<?php
    declare(strict_types=1);

    namespace Safontschik\Kaufland;
   
    /**
     * Interface for all data export operations
     */
    interface DataExportStrategy
    {
        public function GenerateSchema(string $entityName) : string;
        public function ExportToSink(string $sinkPath,string $scriptFile) : void;
    }