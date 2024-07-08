<?php
    declare(strict_types=1);

    namespace Safontschik\Kaufland;

    /**
     * Configuration object which consists of the datatype for the import and export as well as
     * the source and sink
     */
    class DataConversionConfig
    {
        private string $importDataType;
        private string $exportDataType;
        private string $sourcePath;
        private string $sinkPath;

        public function __construct(string $configPath) {
            $this->configPath = $configPath;
            $this->importDataType = "";
            $this->exportDataType = "";
            $this->sourcePath = "";
            $this->sinkPath = "";
        }

        /**
         * Convert the JSON configuration into a associative array
         */
        public function Load() {
            $configArray = json_decode(file_get_contents($this->configPath),true);
            $this->importDataType = $configArray["importDataType"];
            $this->exportDataType = $configArray["exportDataType"];
            $this->sourcePath = $configArray["sourcePath"];
            $this->sinkPath = $configArray["sinkPath"];
        }

        /**
         * Get the datatype of the data source
         * @return importDataType
         */
        public function GetImportDataType(): string
        {
            return $this->importDataType;
        }

        /**
         * Get the datatype of the data sink
         * @return exportDataType
         */
        public function GetExportDataType(): string
        {
            return $this->exportDataType;
        }

        /**
         * Get the path of the source path
         * @return sourcePath which is the path to our data source
         */
        public function GetSourcePath(): string
        {
            return $this->sourcePath;
        }

        /**
         * Get the path of the data sink
         * @return sinkPath which is the path to our data sink
         */
        public function GetSinkPath(): string 
        {
            return $this->sinkPath;
        }
    };