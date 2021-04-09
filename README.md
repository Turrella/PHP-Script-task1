# PHP-Script-task1


####Before running user_upload.php script, please 
####1. Configure Config/databaseConfig.php file
####2. Make sure database has been created.
####3. Update users.csv file if you want to do more test.

###valid command:
```
--file [csv file name] – this is the name of the CSV to be parsed,
example: php user_upload.php --file users.csv

--create_table – this will cause the MySQL users table to be built (and no further action will be taken), note: it will only report error if the table has already existed before,
example: php user_upload.php --create_table

--dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered, please add this command at the end,
example: php user_upload.php --file users.csv --dry_run

-u – MySQL username,
example: php user_upload.php -u

-p – MySQL password,
example: php user_upload.php -u

-h – MySQL host,
example: php user_upload.php -u

```

###table structure:
```
column      type        length
id          int         unsigned
name        VARCHAR     30
surname     VARCHAR     30
email       VARCHAR     50
```