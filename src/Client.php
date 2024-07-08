<?php
    declare(strict_types=1);

    namespace Safontschik\Kaufland;

    use Safontschik\Kaufland\DataConversionConfig;
    use Safontschik\Kaufland\DataExportStrategy;
    use Safontschik\Kaufland\XmlToCsvAdapter;
    use Safontschik\Kaufland\ClientInterface;
    use Safontschik\Kaufland\SQLiteExportStrategy;

    /**
     * Client class which has access to all adapters which implement the ClientInterface
     * and every strategy which implements the DataExportStrategy interface. It's the
     * starting point of the program
     */
    class Client
    {
        private DataConversionConfig $dataConversionConfig;
        private DataExportStrategy $dataExportStrategy;
        
        private ClientInterface $adapter;
        private string $entityName;
        private array $fieldNames;
        private array $fieldDataTypes;

        /**
         * Creates a client object and loads the DataConversionConfig automatically
         * @param $config config object which contains all informations which are needed at runtime
         * @return Client Object
         */
        public function __construct(DataConversionConfig $config) {
            $this->dataConversionConfig = $config;
            $this->dataConversionConfig->Load();
            $this->adapter = new XmlToCsvAdapter("");
            $this->entityName = "";
            $this->fieldNames = array();
            $this->fieldDataTypes = array();
            $this->dataExportStrategy = new SQLiteExportStrategy($this->fieldNames,$this->fieldDataTypes);
        }

        /**
         * Initializes the client object by converting the data from the DataConversionConfig
         * into the appropriate adapter object.
         */
        public function InitClient() {
            $selectedImportType = $this->dataConversionConfig->GetImportDataType();
            $sourcePath = $this->dataConversionConfig->GetSourcePath();
            switch($selectedImportType)
            {
                case "xml":
                {
                    $this->adapter = new XmlToCsvAdapter($sourcePath);
                    $this->adapter->LoadFile();
                    break;
                }
                // You can add new data source types by adding the corresponding cases here
            }
            $this->entityName = $this->adapter->GetEntityName();
            $this->fieldNames = $this->adapter->GetFieldNames();
            $this->fieldTypes = $this->adapter->GetFieldTypes();
        }

        /**
         * Returns the DataConversionConfig which is associated with the client
         * @return $dataConversionConfig config for the conversion process
         */
        public function GetDataConversionConfig(): DataConversionConfig {
            return $this->dataConversionConfig;
        }

        /**
         * Returns the adapter which was dynamically selected by the client
         * @return $adapter adapter which is compatible with ClientInterface
         */
        public function GetAdapter() {
            return $this->adapter;
        }

        /**
         * Assigns the names of the data fields to a private collection
         */
        public function GetFieldNames() {
            $this->fieldNames = $this->adpater->GetFieldNames();
        }

        /**
         * Assigns the datatypes of the data fields to the corresponding private variable
         */
        public function GetFieldSchemaDescription() {
            $this->fieldSchemaDescription = $this->adapter->GetFieldSchemaDescription();
        }
    }