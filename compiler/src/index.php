<?php namespace AssemblyCompiler;

include 'compile.php';

//get the arguments
$arguments = $argv;

unset($arguments[0]);//first one is location of script

//set the default values
$verboseLevel=0;
$outPath=null;
$filePath=null;

foreach ($arguments as $argument) {
    if (!preg_match('/^--(\w+)=?(.*)?$/', $argument, $argumentParsed)) {
        echo_console('unknown argument "' . $argument . '"');
        show_help();
    }
    switch ($argumentParsed[1]) {
        case 'file': {
            $filePath=$argumentParsed[2];
            break;
        }
        case 'out': {
            $outPath=$argumentParsed[2];
            break;
        }
        case 'verbose': {
            $verboseLevel=(int) $argumentParsed[2];
            break;
        }
        case 'help': {
            show_help();
            break;
        }
        default: {
            echo_console('unknown argument "' . $argument . '"');
            show_help();
            break;
        }
    }
}

//some checks
if ($filePath===null)
{
    show_help();
    die;
}

if (!file_exists($filePath))
{
    echo_console('file "'.$filePath.'" not found');
    die;
}

if ($outPath===null)
{
    $info = pathinfo($filePath);
    $outPath =  $info['dirname'].'/'.$info['filename'].'.asm';
}

//get the file
$file = file_get_contents($filePath);

$compiler = new Compiler();
$compiler->debug=($verboseLevel>0);
$compiler->maxVariables=5;
$compiler->loadCode($file);
$compiled= $compiler->compile();
echo_console($compiled,2);
file_put_contents($outPath,$compiled);

//compile to code the processor understands
echo_console(shell_exec ('Java -jar "./Assembler9.jar" "'.$outPath.'"'),1);

echo echo_console('Succesfully compiled.');

function show_help()
{
    echo_console("usage:compiler.phar --file=source_file [--out=output_file] [--verbose=verbose_level]\n");
}

function echo_console($input, $verbose = 0)
{
    global $verboseLevel;
    if ($verbose <= $verboseLevel) {
        echo $input . "\n";
    }
}