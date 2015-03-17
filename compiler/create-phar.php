<?php

//check php.ini
if (ini_get('phar.readonly') != 0) {
    echo('Please make sure phar.readonly in php.ini is set to "Off". Loaded php.ini: '.php_ini_loaded_file().PHP_EOL);
    throw new Exception('Incorrect ini settings');
}
$rootPath=dirname($_SERVER['SCRIPT_NAME']);
$srcRoot = $rootPath.'/src';
$buildRoot = $rootPath.'/build';
$tmpRoot = './docs';
$docRoot = '/tmp/docs';
$functionRoot= './'.basename($buildRoot).'/functions.php';

echo "creating phar...\n";
$phar = new Phar($buildRoot."/compiler.phar",
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, "compiler.phar");
$phar["index.php"] = file_get_contents($srcRoot."/index.php");
$phar["consoleFunctions.php"] = file_get_contents($srcRoot."/consoleFunctions.php");
$phar["compiler.php"] = file_get_contents($srcRoot."/compiler.php");
$phar["defaultFunctions.php"] = file_get_contents($srcRoot."/defaultFunctions.php");
$phar->setStub($phar->createDefaultStub("index.php"));

echo "copy assembler9.jar...\n";
//also copy the assembler himself
copy($srcRoot."/Assembler9.jar", $buildRoot."/Assembler9.jar");

echo "copying the standard functions...\n";
//and the standard functions
copy($srcRoot."/functions.php", $buildRoot."/functions.php");

if (file_exists($tmpRoot))
{
    echo "deleting old tempfolder...\n";
    deleteDirectory($tmpRoot);
}
echo "generating docs...\n";
//generate docs for the functions
$command='phpdoc -t '.$docRoot .' -f '.$functionRoot.' --template=responsive';
echo $command;
shell_exec($command);

if (file_exists($docRoot))
{
    echo "deleting old docs...\n";
    deleteDirectory($docRoot);
}

echo "copying temp to docs...";
recurse_copy($tmpRoot,$docRoot);

echo '';



function deleteDirectory($dir) {
    system('rm -rf ' . escapeshellarg($dir), $retval);
    return $retval == 0; // UNIX commands return zero on success
}

function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}