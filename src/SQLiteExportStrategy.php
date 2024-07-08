<?php
    declare(strict_types=1);

    namespace Safontschik\Kaufland;

    use Safontschik\Kaufland\DataExportStrategy;
    use Safontschik\Kaufland\FieldDataType;

    /**
     * Handles the export from Xml to Sqlite
     */
    class SQLiteExportStrategy implements DataExportStrategy
    {
        private array $fieldNames;
        private array $fieldDataTypes;

        public function __construct(array $fieldNames,array $fieldDataTypes) {
            $this->fieldNames = $fieldNames;
            $this->fieldDataTypes = $fieldDataTypes;
        }

        /**
         * @param $entityName represents the name of the table
         * @return sqlSchema represented as a string
         */
        public function GenerateSchema(string $entityName): string
        {
            $result = "CREATE TABLE $entityName(\n";
            $currField = 0;
            $currFieldName = "";
            $currFieldTypeDescriptor = "";

            foreach($this->fieldDataTypes as $fieldDataType)
            {
                $currFieldName = $this->fieldNames[$currField];
                $currFieldTypeDescriptor = $this->GetFieldTypeDescription($fieldDataType);
                if ($fieldDataType !== FieldDataType::EMPTY) {
                    $result = "$result \t$currFieldName $currFieldTypeDescriptor,\n";
                }
                $currField++;
            }
            $result = substr_replace($result,"",-2);
            $result = "$result \n);\n";
            $result = "$result.mode csv\n.import $entityName.csv $entityName\n";
            return $result;
        }

        /**
         * @param $entityName represents the name of the table
         * @param $payload schema which was passed by the previous function
         */
        public function WriteSchemaFile(string $entityName,string $payload): void
        {
            file_put_contents($entityName . ".sql",$payload);
        }

        public function ExportToSink(string $sinkPath,string $scriptFile): void
        {
            $db = new \SQLite3($sinkPath);
            shell_exec("cat $scriptFile | sqlite3 $sinkPath");
        }

        /**
         * @param $type a FieldDataType which is not FieldDatatType::EMPTY
         * @return the description of the SQL data type
         */
        private function GetFieldTypeDescription(FieldDataType $type)
        {
            $result = "";
            switch ($type)
            {
                case FieldDataType::ID:
                {
                    $result = "$result INTEGER NOT NULL PRIMARY KEY";
                    break;
                }
                case FieldDataType::INTEGER:
                {
                    $result = "$result INTEGER NOT NULL";
                    break;
                }
                case FieldDataType::REAL:
                {
                    $result = "$result REAL NOT NULL";
                    break;
                }

                case FieldDataType::BOOL:
                {
                    $result = "$result TEXT NOT NULL";
                    break;
                }

                case FieldDataType::TEXT:
                {
                    $result = "$result TEXT NOT NULL";
                    break;
                }
            }
            return $result;
        }
    }