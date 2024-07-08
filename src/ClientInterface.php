<?php
    declare(strict_types=1);

    namespace Safontschik\Kaufland;

    /**
     * Interface which all adapters have to comply with. 
     */
    interface ClientInterface
    {
        public function LoadFile(): void;
        public function GetEntityName(): string;
        public function GetFieldNames() : array;
        public function GetFieldTypes() : array;
        public function WriteIntermediateDataFile(string $entityName,array $fieldTypes) : int;
    };