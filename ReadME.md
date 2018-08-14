#1 Php class Autoloader conceptual reference has been obtained from the following template 
https://github.com/joequery/ComposerPSR4Example

#2 To install composer, download composer from the following location, install and then restart your system
https://getcomposer.org/Composer-Setup.exe

    - composer to php is like npm to node.

#3 Install Carbon through the following command. Its sort of a date time utility for php
>composer require nesbot/carbon

#4 To Enable Php Autoloader, please do the following
    - create folder structure with basic files as illustrated in the following template reference
        https://github.com/ozinisle/PhpRefernce/tree/master/ComposerPSR4Example-master/ComposerPSR4Example-master
    - make necessary modifications to composer.json with autoload settings
    <code>
        {
            "require": {
                "phpunit/phpunit": "4.8.*"
            },
            "autoload": {
                "psr-4": {
                    "MatrixAgentsAPI\\": "matrixAgentsAPI/"
                }
            }
        }
    </code>

    - Only in windows -> add php path to environment variables and restart system
    
    - Only for Xampp users -> php path is c:/xampp/php -> the installation drive 'c' may vary as per user 
    
    - Run the following commands to create the autoLoad.php file
        > php composer.phar self-update
        > php composer.phar install

    - When ever new namespaces and files are added do the following
        > php composer.phar update

    - This creates Vendor folder with the autoLoader file. You may have to include the file in your php file. In my case, one such example is as follows
        include('../vendor/autoload.php'); 