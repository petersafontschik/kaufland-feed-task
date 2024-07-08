<?php
    declare(strict_types=1);

    namespace Safontschik\Kaufland;

    /**
     * Enum which represents either data types with actual content (here: ID,INTEGER,REAL,etc.)
     * or no content (here: EMPTY_INTEGER,EMPTY_REAL,etc.)
     */
    enum FieldDataType
    {
        case ID;
        case INTEGER;
        case REAL;
        case BOOL;
        case TEXT;
        case EMPTY; 
        case EMPTY_INTEGER;
        case EMPTY_REAL;
        case EMPTY_BOOL;
        case EMPTY_TEXT;
    }