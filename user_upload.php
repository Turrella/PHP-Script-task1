<?php

require 'Config/databaseConfig.php';        # import database configuration

/**
 * process command
 */
if ($argc < 2) {
    processInvalidCommand();
}

if ($argc == 2) {
    switch ($argv[1]) {
        case '-u':
            fwrite(STDOUT, "MySQL username: " . $GLOBALS['databaseUsername'] . "\n");
            break;

        case '-p':
            fwrite(STDOUT, "MySQL password: " . $GLOBALS['databasePassword'] . "\n");
            break;

        case '-h':
            fwrite(STDOUT, "MySQL host: " . $GLOBALS['databaseHost'] . "\n");
            break;

        case '--create_table':
            $database = new CatalystDatabase();
            $connection = $database->connect();

            if ($connection->connect_error) {
                exit("MySQL Connection failed: " . $connection->connect_error . "\n");
            }
            if ($database->checkTableExists($connection, $GLOBALS['databaseTable'])) {
                fwrite(STDERR, $GLOBALS['databaseTable'] . " table has already existed\n");
            } else {
                $database->createTable($connection, $GLOBALS['databaseTable']);
            }
            break;

        case '--help':
            $helpContent = commandDescription();
            foreach ($helpContent as $row) {
                fwrite(STDOUT, $row);
            }
            break;

        default:
            fwrite(STDERR, "Invalid operation, please try --help for help \n");
    }
} elseif ($argv[1] == '--file') {
    if ($argc < 3 || $argc > 4) {
        processInvalidCommand();
    }
    $dryRun = false;
    if ($argc == 4) {
        if ($argv[3] != '--dry_run') {
            processInvalidCommand();
        } else {
            $dryRun = true;
        }
    }
    $filename = $argv[2];

    if (!CatalystCSV::checkFile($filename)) {
        exit("csv file not exists, please check\n");
    }
    $CSV = fopen($filename, 'r');
    if (!$CSV) {
        exit('file open failed');
    }
    $data = CatalystCSV::processCSV($CSV);

    if (!$dryRun) {
        $database = new CatalystDatabase();
        $connection = $database->connect();
        if ($connection->connect_error) {
            exit("MySQL Connection failed: " . $connection->connect_error . "\n");
        }
        if (!$database->checkTableExists($connection, $GLOBALS['databaseTable'])) {
            exit("please create users table first!");
        }
    }

    foreach ($data as $row) {
        $processedRow = CatalystCSV::processData($row);
        if (!$processedRow['email_validate']) {
            fwrite(STDERR, 'invalid email: ' . $row['email'] . "\n");
            continue;
        } else {
            if (!$dryRun) {
                $database->insertRow($connection, $GLOBALS['databaseTable'], $processedRow['name'], $processedRow['surname'], $processedRow['email']);
            }
        }
    }
} else {
    processInvalidCommand();
}


/**
 * output invalid command error msg
 */
function processInvalidCommand()
{
    exit("Invalid operation, please try --help for help \n");
}


/**
 * help description
 * @return string[]
 */
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


    public function checkTableExists(mysqli $connection, string $table): bool
    {
        return $connection->query("SHOW TABLES LIKE '$table'")->num_rows > 0;
    }

    public function createTable(mysqli $connection, string $table)
    {
        $sql = "CREATE TABLE $table (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(30) NOT NULL,surname VARCHAR(30) NOT NULL,email VARCHAR(50) NOT NULL)";
        if ($connection->query($sql)) {
            fwrite(STDOUT, "table " . $table . " has been created successfully\n");
        } else {
            fwrite(STDOUT, "table creation error: " . $connection->error . "\n");
        }
    }

    public function insertRow(mysqli $connection, string $table, string $name, string $surname, string $email)
    {
        $sql = "INSERT INTO $table (name, surname, email) VALUES ('$name', '$surname', '$email')";
        if (!$result = $connection->query($sql)) {
            fwrite(STDERR, "data insert failed! Error: $connection->error ... name: $name, surname: $surname, email: $email \n");
        }
    }
}


class CatalystCSV
{
    public static function checkFile(string $filename): bool
    {
        return file_exists($filename);
    }

    public static function processCSV($file): array
    {
        $array = array();
        while (($data = fgetcsv($file)) !== false) {
            if ($data[0] == 'name') {
                continue;
            }
            $array[] = [
                'name' => $data[0],
                'surname' => $data[1],
                'email' => $data[2]
            ];
        }
        return $array;
    }

    public static function processData(array $data): array
    {
        $pattern = "/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
        if (!preg_match($pattern, $data['email'])) {
            return ['email_validate' => false];
        }
        return [
            'email_validate' => true,
            'name' => ucfirst(strtolower($data['name'])),
            'surname' => ucfirst(strtolower($data['surname'])),
            'email' => strtolower($data['email'])
        ];
    }
}
