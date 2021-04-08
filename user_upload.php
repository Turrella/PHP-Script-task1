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


$test = new CatalystDatabase();
try
{
    $connect = $test->connect();

    if ($connect->connect_error) {

    }
    if ($test->checkTableExisted($connect,$GLOBALS['users'])){
        fwrite(STDOUT, 'existed');
    } else {
        fwrite(STDOUT, 'not existed');
    }
} catch (Exception $ex){
    return '=========================test============================';
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

    protected $username, $password, $host, $table;

    public function __construct()
    {
        $this->username = $GLOBALS['databaseUsername'];
        $this->password = $GLOBALS['databasePassword'];
        $this->host = $GLOBALS['databaseHost'];
        $this->table = $GLOBALS['databaseTable'];
    }


    public function connect(): mysqli
    {
        return new mysqli($this->host, $this->username, $this->password);
    }


    public function checkTableExisted(mysqli $connection, string $table): bool
    {
        return $connection->select_db($table);
    }
}
