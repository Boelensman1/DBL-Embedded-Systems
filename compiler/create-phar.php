<?php
//check php.ini
if (ini_get('phar.readonly')!=0)
{
    echo ('Please make sure phar.readonly in php.ini is set to "Off". Loaded php.ini: '.php_ini_loaded_file().PHP_EOL);
    throw new Exception('Incorrect ini settings');
}

$srcRoot = "./src";
$buildRoot = "./build";

$phar = new Phar($buildRoot . "/compiler.phar",
    FilesystemIterator::CURRENT_AS_FILEINFO |       FilesystemIterator::KEY_AS_FILENAME, "compiler.phar");
$phar["index.php"] = file_get_contents($srcRoot . "/index.php");
$phar["compile.php"] = file_get_contents($srcRoot . "/compile.php");
$phar["defaultFunctions.php"] = file_get_contents($srcRoot . "/defaultFunctions.php");
$phar->setStub($phar->createDefaultStub("index.php"));

//also copy the assembler himself
copy($srcRoot . "/Assembler9.jar", $buildRoot . "/Assembler9.jar");