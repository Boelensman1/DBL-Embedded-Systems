<?php

//check php.ini
if (ini_get('phar.readonly') != 0) {
    echo('Please make sure phar.readonly in php.ini is set to "Off". Loaded php.ini: '.php_ini_loaded_file().PHP_EOL);
    throw new Exception('Incorrect ini settings');
}
$rootPath=dirname($_SERVER['SCRIPT_NAME']);
$srcRoot = $rootPath.'/src';
$buildRoot = $rootPath.'/build';
$docRoot = './docs/';
$tmpRoot = '/tmp/docs';
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
$command='phpdoc -t '.$tmpRoot .' -f '.$functionRoot.' --template=responsive';
echo $command;
shell_exec($command);

if (file_exists($docRoot))
{
    echo "deleting old docs...\n";
    deleteDirectory($docRoot);
}

echo "copying temp to docs...";
xcopy($tmpRoot, $docRoot);



function deleteDirectory($dir) {
    system('rm -rf ' . escapeshellarg($dir), $retval);
    return $retval == 0; // UNIX commands return zero on success
}
/**
 * Copy a file, or recursively copy a folder and its contents
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @param       string   $permissions New folder creation permissions
 * @return      bool     Returns true on success, false on failure
 */
function xcopy($source, $dest, $permissions = 0755)
{
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest, $permissions);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        xcopy("$source/$entry", "$dest/$entry", $permissions);
    }

    // Clean up
    $dir->close();
    return true;
}
