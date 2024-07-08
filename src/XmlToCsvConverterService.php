<?php
    declare(strict_types=1);

    namespace Safontschik\Kaufland;

    use SimpleXMLElement;
    use Safontschik\Kaufland\FieldDataType;

    /*
    * Service which is in charge of the conversion from the XML payload into a CSV file as well
    * as well as an intermediate data format which will later be used to generate the schema of
    * the corresponding database
    */
    class XmlToCsvConverterService
    {
        private SimpleXMLElement $currElement;
        private string $sourcePath;

        public function __construct(string $sourcePath){
            $this->sourcePath = $sourcePath;
            $this->currElement = new SimpleXMLElement("<foo></foo>");
        }

        /**
         * Load the .xml file
         */
        public function LoadXmlFile(): void {
            $this->currElement = simplexml_load_file($this->sourcePath,null,LIBXML_NOCDATA);
        }

        /**
         * Retrieve the field types from the first child of the root element. 
         * @return associative array where the key is a runtime number and the value the datatype
         */
        public function GetFieldTypes() : array
        {
            $result = array();
            $childCount = count($this->currElement->children());
            if ($childCount <= 0) {
                return $result;
            }
            $firstChild = $this->currElement->children()[0];
            $currChild = 0;
            $row_name = "";
            $regexBase = $this->currElement->asXml();
            
            // Iterate through the first child to retrieve the name of the table columns
            foreach ($firstChild->children() as $data_row) {
                // Get the column name and cache it inside the columnNames variable
                $row_name = $data_row->getName();
                $fieldDataType = $this->GetFieldType($row_name,$regexBase);
                $result[$currChild] = $fieldDataType;
                $currChild++;
            }
            return $result;
        }

        /**
         * Write the CsvFile to the project root. In case of the feed.xml file there should
         * be exactly 4339 lines.
         * @return number of lines written during the function execution
         */
        public function WriteIntermediateDataFile(string $entityName,array $fieldTypes) : int {
            $currItem = 0;
            $csvFile = fopen($entityName . ".csv",'a');
            $writeBuffer = "";
            foreach ($this->currElement->children() as $item) {
                $writeBuffer = $this->ItemToCsv($item,$fieldTypes);
                file_put_contents($entityName . ".csv",$writeBuffer,FILE_APPEND);
                $currItem++;
            }
            return $currItem;
        }

        /**
         * Converts one item from the catalog to a CSV line
         */
        public function ItemToCsv(SimpleXMLElement $item, array $fieldTypes) : string {
            $result = "";
            $currChild = 0;
            $isValid = false;
            $currentFieldType = FieldDataType::EMPTY;

            // Iterate through every Item
            foreach($item->children() as $child) {
                // Check if the data field has no value
                $isValueEmpty = strlen($child->__toString()) <= 0;
                // Check for the datatype
                $isId = $fieldTypes[$currChild] === FieldDataType::ID;
                $isInteger = $fieldTypes[$currChild] === FieldDataType::INTEGER;
                $isReal = $fieldTypes[$currChild] === FieldDataType::REAL;
                $isBool = $fieldTypes[$currChild] === FieldDataType::BOOL;
                $isText = $fieldTypes[$currChild] === FieldDataType::TEXT;
                $isEmpty = $fieldTypes[$currChild] === FieldDataType::EMPTY;
                // Check whether or not the data field is empty or not
                $emptyInteger = $isValueEmpty && $isInteger;
                $emptyReal = $isValueEmpty && $isReal;
                $emptyBool = $isValueEmpty && $isBool;
                $emptyText = empty($item->__toString()) && $isText;

                // Apply default values if the data value is absent
                if ($emptyInteger) {
                    $result = $result . "0";
                } else if ($emptyReal) {
                    $result = $result . "0.0";
                } else if ($emptyBool) {
                    $result = $result . "\"No\"";
                }


                // If we have a data value, we have to differentiate between IDs, numeric and
                // text values
                $elementValue = $child->__toString();
                if ($isId) {
                    $result = $result . $elementValue;
                } else if(($isInteger || $isReal) && (!$emptyInteger || !$emptyReal)) {
                    $result = $result . $elementValue;
                } else if ($isBool && !$emptyBool) {
                    $result = $result . "\"" . $elementValue . "\"";
                } else if ($isText && !$emptyText) {
                    $elementValue = str_replace("\n","",$elementValue);
                    $elementValue = str_replace("\"","\"\"",$elementValue);
                    $result = $result . "\"" . $elementValue . "\"";
                }

                // If the datatype for the field is FieldDataType::EMPTY, ignore the current
                // data field and carry on with the next one
                if (!$isEmpty) {
                    $result = $result . ",";
                }
                $currChild++;
            }
            $result = substr_replace($result,"\n",-1);
            return $result;
        }

        /**
         * Get the currently selected element where SimpleXml is pointing at
         * @return element where SimpleXml points to
         */
        public function GetCurrentElement(): SimpleXMLElement {
            return $this->currElement;
        }


        /**
         * Get name of the root element
         * @return name of the root element
         */
        public function GetEntityName() : string{
            return $this->currElement->GetName();
        }

        /**
         * Parse the names of each field by processing the first item
         * @return names of all fields
         */
        public function GetFieldNames(): array
        {
            $result = array();
            $childrenCount = count($this->currElement->children());
            $row_name = "";
            $regexBase = $this->currElement->asXml();

            if($childrenCount <= 0) {
                return $result;
            }

            $firstChild = $this->currElement->children()[0];
            $currChild = 0;
            
            foreach($firstChild->children() as $data_row) {
                $row_name = $data_row->GetName();
                $result[$currChild] = $row_name;
                $currChild++;
            }
            
            return $result;
        }

        /**
         * Retrieve of a field
         * @param $field_name name of the current field
         * @param $regexBase Xml document as a string
         * @return FieldDataType which is associated with the data field
         */
        private function GetFieldType(string $field_name,string $regexBase): FieldDataType {
            $childCount = count($this->currElement->children());
            $result = FieldDataType::EMPTY;

            $isId = str_contains($field_name,"id");
            $isText = preg_match("/<$field_name>\D+<\/$field_name>/",$regexBase) && !$isId;
            $isInteger = preg_match("/<$field_name>\d+<\/$field_name>/",$regexBase) && !$isText;
            $isReal = preg_match("/<$field_name>\d+\.\d+<\/$field_name>/",$regexBase);
            $isEmpty = preg_match_all("/<$field_name\/>/",$field_name) === $childCount;
            $isBool = preg_match("/<$field_name>Yes|No<\/$field_name>/",$regexBase);

            if ($isInteger && !$isId) {
                $result = FieldDataType::INTEGER;
            } else if ($isInteger && $isId) {
                $result = FieldDataType::ID;
            }else if ($isReal) {
                $result = FieldDataType::REAL;
            } else if ($isBool) {
                $result = FieldDataType::BOOL;
            } else if ($isText) {
                $result = FieldDataType::TEXT;
            }

            return $result;
        }
    }
?>