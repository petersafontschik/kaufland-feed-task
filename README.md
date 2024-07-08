# Installation

## Requirements
- Linux as an operating system since the data conversion process is tailored for Linux systems
- *sqlite3* should be installed on said operating system
- the mandatory POSIX tool *cat* should be installed on said operating system
- PHP 8.0 or any greater version should be installed on the system
- the internal PHP library *SimpleXML* requires the extension *libxml*
- the internal PHP library *SQLite3* require the extension *libsqlite*

## Composer
Since this project is based on composer, the following commands have to be run before the first run
*composer install*
*composer dump-autoload -o*

# Running the program
Afterwards the program can be run with
*php index.php*

# Testing the program
If you want to run the tests, you can issue the following command
*./vendor/bin/phpunit tests --display-warnings*
Since one of the tests loads a non-existing file, a warning will be issued
## IMPORTANT NOTE
If you first run the program, you see that the files *catalog.db*,*catalog.csv* and *catalog.sql*
appear in the projec root. This is done due to the fact that I wanted to inspect their contents
and properties after each normal run. IF you run a test afterwards, the *setUpBeforeClass()* function
is triggered which immediatly deletes all .csv,.db and .sql files. The reason for this is that some
test runs would write the data from *data/feed.xml* into the database all over again. Please keep that in mind when you run tests