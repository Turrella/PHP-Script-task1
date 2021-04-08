<?php

require 'Config/databaseConfig.php';        # import database configuration


/**
 * process command
 */
if ($argc < 2) {
    processInvalidCommand();
}


switch ($argv[1]){
    case '-u':
        fwrite(STDOUT, $GLOBALS['databaseUsername'] . "\n");
        break;

    case '-p':
        fwrite(STDOUT, $GLOBALS['databasePassword'] . "\n");
        break;

    case '-h':
        fwrite(STDOUT, $GLOBALS['databaseHost'] . "\n");
        break;

    case '--create_table':
        $database = new CatalystDatabase();
        $connection = $database->connect();

        if ($connection->connect_error) {
            die("MySQL Connection failed: " . $connection->connect_error . "\n");
        }
        if ($database->checkTableExists($connection, $GLOBALS['databaseTable'])){
            fwrite(STDERR, $GLOBALS['databaseTable'] . " table has already existed\n");
        } else {
            $database->createTable($connection, $GLOBALS['databaseTable']);
        }
        break;

    case '--help':
        $helpContent = commandDescription();
        foreach ($helpContent as $row)
        {
            fwrite(STDOUT, $row);
        }
        break;

    default:
        fwrite(STDERR, "Invalid operation, please try --help for help \n");
}


function processInvalidCommand()
{
    fwrite(STDERR, "Invalid operation, please try --help for help \n");
    exit;
}


function commandDescription(): array
{
    return array(
        "--file [csv file name] – this is the name of the CSV to be parsed\n",
        "--create_table – this will cause the MySQL users table to be built (and no further action will be taken)\n",
        "--dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered\n",
        "-u – MySQL username\n",
        "-p – MySQL password\n",
        "-h – MySQL host\n"
    );
}



class CatalystDatabase
{

    protected $username, $password, $host, $database, $table;

    public function __construct()
    {
        $this->username = $GLOBALS['databaseUsername'];
        $this->password = $GLOBALS['databasePassword'];
        $this->host = $GLOBALS['databaseHost'];
        $this->database = $GLOBALS['database'];
        $this->table = $GLOBALS['databaseTable'];
    }


    public function connect(): mysqli
    {
        return new mysqli($this->host, $this->username, $this->password, $this->database);
    }


    public function checkDatabaseExisted(mysqli $connection, string $database): bool
    {
        return $connection->select_db($database);
    }


    public function checkTableExists(mysqli $connection, string $table):bool
    {
        return $connection->query("SHOW TABLES LIKE '$table'")->num_rows > 0;
    }

    public function createTable(mysqli $connection, string $table)
    {
        $sql = "CREATE TABLE $table (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(30) NOT NULL,surname VARCHAR(30) NOT NULL,email VARCHAR(50) NOT NULL)";
        if ($connection->query($sql)) {
            fwrite(STDOUT, "table " . $table . " has been created successfully\n");
        } else {
            fwrite (STDOUT, "table creation error: " . $connection->error . "\n");
        }
    }
}
