<?php
    declare(strict_types=1);

    namespace Safontschik\Kaufland;

    use Safontschik\Kaufland\ClientInterface;
    use Safontschik\Kaufland\XmlToCsvConverterService;

    /**
     * Adapter which converts XML input into a CSV file. Keep in mind that it isn't
     * suitable for data collections like MongoDB time series collections since these
     * have a special CSV format.
     */
    class XmlToCsvAdapter implements ClientInterface
    {
        private XmlToCsvConverterService $xmlToCsvConverterService;
        private string $sourcePath;

        /**
         * Constructor for the XmlToCsvAdapter
         * @param $sourcePath path to the XML file which will be converted
         * @return XmlToCsvAdapter
         */
        public function __construct(string $sourcePath)
        {
            $this->sourcePath = $sourcePath;
            $this->xmlToCsvConverterService = new XmlToCsvConverterService("");
        }

        /**
         * Loads the XML content into the XmlToCsvConverterService object 
         */
        public function LoadFile(): void
        {
            $this->xmlToCsvConverterService = new XmlToCsvConverterService($this->sourcePath);
            $this->xmlToCsvConverterService->LoadXmlFile();
        }

        /**
         * Get the name of the root element of the XML file
         * @return string name of the root element
         */
        public function GetEntityName(): string
        {
            return $this->xmlToCsvConverterService->GetEntityName();
        }

        /**
         * Gets the names of the individual data fields
         * @return array of field names
         */
        public function GetFieldNames() : array
        {
            return $this->xmlToCsvConverterService->GetFieldNames();
        }

        /**
         * Get the datatype of all data fields
         * @return array of datatypes which are associated to the data fields
         */
        public function GetFieldTypes() : array
        {
            return $this->xmlToCsvConverterService->GetFieldTypes();
        }

        /**
         * Writes the Xml file to an 'intermediate' data format (in this case: .csv)
         * @return number of lines which we're written to the .csv file
         */
        public function WriteIntermediateDataFile(string $entityName,array $fieldTypes) : int
        {
            return $this->xmlToCsvConverterService->WriteIntermediateDataFile($entityName,$fieldTypes);
        }

        /**
         * Get the sourcePath which was given to the adapter
         * @return sourcePath path where the .xml file resides
         */
        public function GetSourcePath(): string {
            return $this->sourcePath;
        }
    }