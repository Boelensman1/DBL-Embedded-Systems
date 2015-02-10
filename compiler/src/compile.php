<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace AssemblyCompiler;

use stdClass;

// {{{ constants
define('IOAREA', -16);
define('DSPSEG', 8);
define('DSPDIG', 9);
define('TIMER', 13);
define('INPUT', 7);
define('OUTPUT', 11);
define('OUTPUT2',10);
define('ADCONVS', 6);
// }}}

/**
 * Class Compiler
 *
 * @author Wigger Boelens <wigger.boelens@gmail.com>
 */
class Compiler
{
    public $code = array();
    private $data=array();
    public $debug = false;
    public $maxVariables = 4;
    private $_functions = array();
    private $_functionsCompiled = array();
    private $_functionsToCompile = array();
    private $_variables = array();
    private $_inConditional = array();
    //private $conditionals = array();
    private $_conditionalJustClosed = false;
    private $_lineNumber = array();
    private $_functionName;
    private $_line;

    private $_defaultFunctions = array();
    private $_useDisplay = false;
    private $_usePow = false;
    private $_usePressed = false;
    private $_useSleep = false;

    /**
     * Load the code. This function does some first processing
     * and saves the code in the object.
     *
     * @param string $code The input code
     *
     * @return bool Success
     */
    public function loadCode($code)
    {
        //remove all comments
        $isComment = false;
        $codeSegment = false;
        $dataSegment=false;
        //split by _line
        foreach (preg_split("/((\r?\n)|(\r\n?)|;)/", $code) as $line) {
            if (empty($line)) {
                //there are a lot of empty lines because we split on ;
                continue;
            }
            $line = trim($line);//trim the _line.

            //check if the data segment codeSegment
            if ($dataSegment == false && $codeSegment==false) {
                if ($line == '//**DATA**') {
                    $dataSegment = true;
                }
                continue;
            }

            //if we are currently in a block comment,
            //we only need to check if we get out of it
            if ($isComment == true) {
                if ($line == '*/') {
                    $isComment = false;
                }
                continue;
            } else {
                if ($line == '/*' || $line == '/**') {
                    $isComment = true;
                    continue;
                }
                //check for comments, but not the code comment
                if (preg_match(
                    "/(^|[^\\\\])\\/\\//", $line, $matches, PREG_OFFSET_CAPTURE
                ) && $line!='//**CODE**') {
                    $line = substr(
                        $line, 0, $matches[0][1] + 1
                    );//copy all that is not a comment
                    if ($line == '/') {
                        continue;
                    }
                }
                //check for global, which we igore
                if (substr($line, 0, 6) === 'global') {
                    continue;
                }
            }
            if (!empty($line)) {
                if ($dataSegment)
                {
                    //check if the data segement ends
                    if ($codeSegment == false) {
                        if ($line == '//**CODE**') {
                            $dataSegment=false;//datasegment ends when codesegment starts
                            $codeSegment = true;
                            continue;
                        }
                    }

                    //add it to the data
                    $this->data[] = $this->processData($line);
                }
                if ($codeSegment)
                {
                    //check for statements like $abc++
                    if (preg_match("/^\\$(.+)(\\+\\+|--)/", $line, $matches)) {
                        if ($matches[2] == '++') {
                            $line = '$' . $matches[1] . '+=1';
                        } else {
                            $line = '$' . $matches[1] . '-=1';
                        }
                    }
                    //add it to the code
                    $this->code[] = $line;
                }
            }
        }
        return (!empty($this->code));//return true if the code is not empty
    }

    /**
     * Compiles the code loaded in.
     *
     * @return string The compiled code.
     */
    public function compile()
    {
        //lets start with reading all the _functions
        $functions = $this->getFunctions();
        $functionId = 0;//the function we are looking for
        $inFunction = false;
        $codeOutside = array();
        //now we have the _functions lets start with doing all the code outside of the _functions:
        foreach ($this->code as $lineNumber => $line) {
            if ($inFunction) {
                if ($lineNumber === $functions[$functionId]->lineNumberEnd) {
                    $inFunction = false;
                    $functionId++;
                }
                continue;
            }
            if ($lineNumber === $functions[$functionId]->lineNumberStart) {
                $inFunction = true;
                //lets add this function to the main array
                $this->_functions[$functions[$functionId]->name]
                    = $functions[$functionId];
                continue;
            }
            //if we get to here, we are definitely not inside of a function
            $codeOutside[] = $this->processLine(
                $line, $lineNumber, '__outside__'
            )[1];
        }

        //now lets do the _functions
        $this->_functionsToCompile[] = 'main';
        while (!empty($this->_functionsToCompile)) {
            $this->_compileFunction(array_pop($this->_functionsToCompile));
            if (count($this->_inConditional) != 0) {//we are in a conditional
                $this->error(
                    'Still in a conditional at the end of the function!'
                );
            }
        }

        return $this->makeCode($codeOutside);
    }


    /**
     * Subroutine to compile a function, if the function is already compiled it
     * will skip it.
     *
     * @param string $functionName The name of the function to be compiled.
     */
    private function _compileFunction($functionName)
    {
        $this->_functionName = $functionName;
        //if we already have this function compiled. Lets skip it.
        if (isset($this->_functionsCompiled[$functionName])) {
            return;
        }
        if (!isset($this->_functions[$functionName])) {
            //unknown function!
            $this->error('unknown function "' . $functionName . '"', true);
        }
        //create the _variables
        $this->_functionsCompiled[$functionName] = new stdClass();
        $this->_functionsCompiled[$functionName]->code = array();
        $this->_functionsCompiled[$functionName]->returns = array();
        $this->_inConditional = array();

        $this->_lineNumber[$functionName] = 0;
        foreach ($this->_functions[$functionName]->code as $line) {
            $lineTMP = $this->processLine($line);
            //check what happened with our _line
            switch ($lineTMP[0]) {
                case 0: //everything went OK, nothing special
                {
                    $i = count($this->_inConditional);
                    $this->insertCode($functionName, $lineTMP[1], $i);
                    break;
                }
                case
                1://if statement
                {
                    $i = count($this->_inConditional) - 1; //to insert in the parent
                    $this->insertCode($functionName, $lineTMP[1], $i);
                    $this->insertCode($functionName, $lineTMP[2], $i);
                    $this->_functionName = end($this->_inConditional)['name'];
                    if ($this->debug) {
                        echo $lineTMP[1] . "\n";
                        $lineTMP[1] = $lineTMP[2];
                    }
                    break;
                }
                case 2: {
                    $i = count($this->_inConditional);
                    //return of an if/else statement
                    $this->insertCode($functionName, $lineTMP[1], $i);
                    array_pop($this->_inConditional);//remove the last one
                    break;
                }
                case 3: {//TODO: else
                    /*//set to the correct function name

                        if (count($this->_inConditional) > 0) {//we are in a conditional
                            $this->_functionName=end($this->_inConditional)['name'];
                        } else {
                            $this->_functionName = $_functionName;
                        }*/
                    if ($this->debug) {
                        $lineTMP[1] = 'else';
                    }
                    break;
                }
                case 4: {//multi-line response
                    foreach ($lineTMP as $subLine) {
                        $i = count($this->_inConditional);
                        if (is_string($subLine)) {
                            $this->insertCode($functionName, $subLine, $i);
                            if ($this->debug) {
                                echo $subLine . "\n";
                            }
                        }
                    }
                    break;
                }
                case -1: {
                    $this->error('compile error (-1)');
                    break;
                }
                default: {
                    $this->error('unknown error (' . $lineTMP[0] . ')');
                    break;
                }
            }
            if ($this->debug) {
                echo $lineTMP[1] . "\n";
            }

            //check if a conditional just closed
            if ($this->_conditionalJustClosed !== false) {
                if (count($this->_inConditional) > 0) {//we are in a conditional
                    $conName = end($this->_inConditional)['name'];
                    $i = count($this->_functionsCompiled[$conName]->returns);
                    $this->_functionsCompiled[$conName]->returns[] = array();
                    $this->_functionsCompiled[$conName]->returns[$i]['name']
                        = $this->_conditionalJustClosed['name'];
                    $this->_functionsCompiled[$conName]->returns[$i]['_line']
                        = $this->_lineNumber[$conName];
                    $this->_functionName = $conName;
                } else {
                    $i = count(
                        $this->_functionsCompiled[$functionName]->returns
                    );
                    $this->_functionsCompiled[$functionName]->returns[]
                        = array();
                    $this->_functionsCompiled[$functionName]->returns[$i]['name']
                        = $this->_conditionalJustClosed['name'];
                    $this->_functionsCompiled[$functionName]->returns[$i]['_line']
                        = $this->_lineNumber[$functionName];
                    $this->_functionName = $functionName;
                }

                //reset just closed
                $this->_conditionalJustClosed = false;
            }
        }
    }

    /**Subroutine to process a single _line
     *
     * @param string $line The _line to be compiled
     *
     * @return array First element is a status code, the next can be anything,
     *               but most often the compiled lines
     */
    private function processLine($line)
    {
        //$_lineNumber =$this->_lineNumber[$this->_functionName];
        $this->_line = $line;

        //check if we are dealing with a build in function
        if (preg_match(
            "/^\\b[^()]+\\((.*)\\)/", $line, $function, PREG_OFFSET_CAPTURE
        )) {
            $arguments = $function[1][0];//the arguments
            $function = substr(
                $function[0][0], 0, $function[1][1] - 1
            );//the actual function

            //get the arguments
            preg_match_all(
                "/([^,]+\\(.+?\\))|([^,]+)/", $arguments, $arguments
            );
            $arguments = $arguments[0];//the arguments
            //now lets make that _line!
            switch (trim($function)) {
                case 'define': {
                    return [
                        0, trim(trim($arguments[0]), "'\"") . ' EQU '
                        . $this->processArgument(trim($arguments[1]), "'\"")
                    ];
                }
                case '_storeRam': {
                    return [
                        0, 'STOR ' . $this->processArgument($arguments[0]) . ' ['
                        . $this->processArgument($arguments[1]) . ']'
                    ];
                }
                case '_storeData': {
                    //check if we have to add a register
                    if (substr(trim($arguments[2]),0,1)=='$'  && trim($this->processArgument($arguments[2]))!='0')
                    {
                        return [
                            4, 'ADD '. $this->processArgument($arguments[2]).' '.trim($this->processArgument($arguments[1]),'\''),
                            'STOR ' . $this->processArgument($arguments[0]) . ' [ GB + '
                            . $this->processArgument($arguments[2]).']',
                            'SUB '. $this->processArgument($arguments[2]).' '.trim($this->processArgument($arguments[1]),'\''),
                        ];
                    }
                    else
                    {
                        //its just a number
                        return [
                            0, 'STOR '.$this->processArgument($arguments[0]).' [GB +'.
                        trim($this->processArgument($arguments[1]),'\'').' + '. $this->processArgument($arguments[2]).']'];
                    }
                }
                case 'initVar'://only for data segment
                {
                    return [0,trim($this->processArgument($arguments[0]),'\'').' DS '.$this->processArgument($arguments[1])];
                }
                case 'if': {
                    //an if statement, we need to create a function for this.
                    preg_match(
                        "/^if\\s*?\\((.+)(!=|==|>=|<=|<|>)(.+)\\)/", $line, $matches
                    );
                    switch ($matches[2]) {
                        case '!=': {
                            return [
                                1,
                                'CMP ' . $this->processArgument($matches[1]) . ' '
                                . $this->processArgument($matches[3]),
                                'BNE ' . $this->getNextConditional('if')
                            ];
                        }
                        case '==': {
                            return [
                                1,
                                'CMP ' . $this->processArgument($matches[1]) . ' '
                                . $this->processArgument($matches[3]),
                                'BEQ ' . $this->getNextConditional('if')
                            ];
                        }
                        case '<': {
                            return [
                                1,
                                'CMP ' . $this->processArgument($matches[1]) . ' '
                                . $this->processArgument($matches[3]),
                                'BMI ' . $this->getNextConditional('if')
                            ];
                        }
                        case '>': {
                            return [
                                1,
                                'CMP ' . $this->processArgument($matches[1]) . ' '
                                . $this->processArgument($matches[3]),
                                'BPL ' . $this->getNextConditional('if')
                            ];
                        }
                        default: {
                            $this->error('unknown if statement');
                        }
                    }
                    break;
                }
                case 'modulo': {
                    return [
                        0, 'MOD ' . $this->processArgument($arguments[0]) . ' '
                        . $this->processArgument($arguments[1])
                    ];
                }

                case 'getInput': {
                    switch (trim(trim($arguments[1]), "'\"")) {
                        case 'buttons': {
                            return [
                                4, 'LOAD  R5  ' . IOAREA, 'LOAD ' . $this->processArgument($arguments[0]) . ' [R5 + ' . INPUT . ']'
                            ];
                        }
                        case 'analog': {
                            return [4, 'LOAD  R5  ' . IOAREA, 'LOAD ' . $this->processArgument($arguments[0]) . ' [R5 + ' . ADCONVS . ']'];
                        }
                        default: {
                            $this->error('unknown input type.');
                        }
                    }
                    break;
                }

                case 'display': {
                    switch (trim(trim($arguments[1]), '"\'')) {
                        case 'display': {
                            $this->_useDisplay = true;
                            $counter
                                = str_repeat('0', 6 - $arguments[2]) . '1' . str_repeat(
                                    '0', $arguments[2] - 1
                                );//000001
                            return [
                                4, 'LOAD  R5 ' . $this->processArgument($arguments[0]),
                                'BRS _Hex7Seg', 'LOAD  R4  %' . $counter,
                                'STOR  R4  [R5+' . DSPDIG . ']'
                            ];
                            break;
                        }
                        case 'leds': {//the led lights
                            return [
                                4, 'LOAD  R5  ' . IOAREA,
                                'LOAD R4 ' . $this->processArgument($arguments[0]),
                                'STOR R4 [R5+' . OUTPUT . ']'
                            ];
                            break;
                        }
                        case 'leds2':
                        {
                            return [
                                4, 'LOAD  R5  ' . IOAREA,
                                'LOAD R4 ' . $this->processArgument($arguments[0]),
                                'STOR R4 [R5+' . OUTPUT2 . ']'
                            ];
                            break;
                        }
                        default:
                        {
                            $this->error('unknown output type');
                            break;
                        }
                    }
                }

                case 'sleep': {
                    $this->_useSleep = true;
                    return [
                        4, 'LOAD  R5 ' . $this->processArgument($arguments[0]),
                        'BRS _timer'
                    ];
                }

                case 'installCountdown': {
                    $countdown = ";Install timer
                      LOAD  R0  " . trim(trim($arguments[0]), '\'"') . "
                       ADD  R0  R5
                      LOAD  R1  16
                      STOR  R0  [R1]

                      LOAD  R5  " . IOAREA . "

                      ; Set the timer to 0
                      LOAD  R0  0
                       SUB  R0  [R5+" . TIMER . "]
                      STOR  R0  [R5+" . TIMER . "]";
                    $return = [4];
                    $return = array_merge($return, explode("\n", $countdown));
                    $this->_functionsToCompile[] = trim(trim($arguments[0]), '\'"');//add it to the to compile functions
                    $this->_functions[trim(trim($arguments[0]), '\'"')]->isTimer = true;
                    return $return;
                }

                case 'startCountdown': {
                    return [0, 'SETI  8'];
                }
                case 'pushStack': {
                    return [0, 'PUSH ' . $this->processArgument($arguments[0])];
                }
                case 'pullStack': {
                    return [0, 'PULL ' . $this->processArgument($arguments[0])];
                }
                case 'setTimer': {
                    return [4, 'LOAD R5 ' . IOAREA, 'LOAD  R4  0','SUB  R4  [R5+' . TIMER . ']','STOR  R4  [R5+' . TIMER . ']','LOAD R4 ' . $this->processArgument($arguments[0]), 'STOR R4 [R5+' . TIMER . ']'];
                }
                case 'buttonPressed': {
                    $this->_usePressed = true;
                    $this->_usePow = true;
                    //return [4,'LOAD R4 '.$this->processArgument($arguments[1]),'LOAD R5 '.$this->processArgument($arguments[0]),'BRS _pow'];
                    return [
                        4, 'PUSH R3',
                        'LOAD R3 ' . $this->processArgument($arguments[0]),
                        'BRS _pressed', 'PULL R3'
                    ];
                }
                case 'pow': {
                    $this->_usePow = true;
                    return [
                        4, 'LOAD R4 ' . $this->processArgument($arguments[1]),
                        'LOAD R5 ' . $this->processArgument($arguments[0]),
                        'BRS _pow'
                    ];
                }
                case 'stackPush': {
                    return [0, 'PUSH ' . $this->processArgument($arguments[0])];
                }
                case 'stackPull': {
                    return [0, 'PULL ' . $this->processArgument($arguments[0])];
                }
                default: {
                    //error or another function
                    if (isset($this->_functions[$function])) {
                        //okay, function exists, lets add it to the _functions we need to compile
                        if (!isset($this->_functionsCompiled[$function])
                            && !in_array($function, $this->_functionsToCompile)
                        ) {
                            $this->_functionsToCompile[] = $function;
                        }
                        //lets see if it has a return
                        if ($this->_functions[$function]->hasReturn === true) {
                            return [0, 'BRS ' . $function];
                        } else {
                            return [0, 'BRA ' . $function];
                        }
                    }
                    //unknown function
                    $this->error('unknown function "' . $function . '"');
                    break;
                }
            }
        }
        //if not lets see if we are dealing with a = statement
        if (preg_match("/(.*?)=(.*)/", $line, $variable, PREG_OFFSET_CAPTURE)) {
            $rest = trim($variable[2][0]);
            $variable = trim($variable[1][0]);
            $register = '';
            //lets see if we are dealing with a +/-
            switch (substr($variable, -1)) {
                case '+': {
                    //lets see if we are dealing with a variable
                    if (substr($variable, 0, 1) === '$') {
                        $variable = substr($variable, 0, -1);
                        //get the variable
                        $register = $this->getRegister($variable);
                        return [
                            0,
                            'ADD ' . $register . ' ' . $this->processArgument($rest)
                        ];
                    }
                    $this->error('Addition on a non-variable.');
                    break;
                }
                case '-': {
                    if (substr($variable, 0, 1) === '$') {
                        $variable = substr($variable, 0, -1);
                        //get the variable
                        $register = $this->getRegister($variable);
                        return [
                            0,
                            'SUB ' . $register . ' ' . $this->processArgument($rest)
                        ];
                    }
                    $this->error('subtraction on a non-variable.');
                    break;
                }
                case '/': {
                    if (substr($variable, 0, 1) === '$') {
                        $variable = substr($variable, 0, -1);
                        //get the variable
                        $register = $this->getRegister($variable);
                        return [
                            0,
                            'DIV ' . $register . ' ' . $this->processArgument($rest)
                        ];
                    }
                    $this->error('subtraction on a non-variable.');
                    break;
                }
            }
            //lets see if we are dealing with a variable
            if (substr($variable, 0, 1) === '$') {
                //get the variable
                $register = $this->getRegister($variable);
            }

            //okay lets now do something with the rest
            //lets see if there is a function
            if (preg_match(
                "/^\\b[^()]+\\((.*)\\)/", $rest, $function, PREG_OFFSET_CAPTURE
            )) {
                $arguments = $function[1][0];//the arguments
                $function = substr(
                    $function[0][0], 0, $function[1][1] - 1
                );//the actual function
                //get the arguments
                preg_match_all(
                    "/([^,]+\\(.+?\\))|([^,]+)/", $arguments, $arguments
                );
                $arguments = $arguments[0];//the arguments
                switch ($function) {
                    case '_getRam'; {
                        return [
                            0, 'LOAD ' . $register . ' [' . $this->processArgument(
                                $arguments[0]
                            ) . ']'
                        ];
                    }
                    case '_getData'; {
                        //check if we have to add a register
                        if (substr(trim($arguments[1]),0,1)=='$')
                        {
                            return [
                                4, 'ADD '. $this->processArgument($arguments[1]).' '.trim($this->processArgument($arguments[0]),'\''),
                                'LOAD ' . $register . ' [ GB + '
                                . $this->processArgument($arguments[1]).']',
                                'SUB '. $this->processArgument($arguments[1]).' '.trim($this->processArgument($arguments[0]),'\''),
                            ];
                        }
                        else
                        {
                            return [
                                0,'LOAD ' . $register . ' [ GB + '. trim($this->processArgument($arguments[0]),'\'').' + '.$this->processArgument($arguments[1]).' ]'
                            ];
                        }
                    }
                    default: {
                        $this->error('unknown function "' . $function . '"');
                    }
                }
            }
            //if nothing else, its a simple store
            return [
                0, 'LOAD ' . $register . ' ' . $this->processArgument($rest)
            ];
        }
        //maybe its an else
        if (preg_match("/^}\\s*else\\s*{/", $line)) {
            if (count($this->_inConditional) === 0) {
                $this->error('else without an if');
            }
            $this->_inConditional[count($this->_inConditional) - 1]['type']
                = 'else';
            return [3];
        }

        //maybe its something else
        switch ($line) {
            case 'return': {
                return [0, 'RTS'];
            }
            case 'returnt':
            {
                return [0, 'RTE'];
            }
            case '}': {
                $this->_conditionalJustClosed = end($this->_inConditional);
                $id = $this->_conditionalJustClosed['id'];
                //if ($this->_conditionalJustClosed['type'] == 'if') {
                $this->_conditionalJustClosed['name'] = 'return' . $id;
                return [2, 'BRA return' . $id];
                /*}
                if ($this->_conditionalJustClosed['type'] == 'else') {
                    $this->_conditionalJustClosed = str_replace('conditional', 'return', $this->_conditionalJustClosed);
                    return [2, ];
                }*/
                break;
            }
        }
        //its the closing of an if/else
        if ($line === '}') {
        }
        //we do not know how to handle this
        return [-1];
    }

    /**Process an argument, for example $abc + 1 gets translated into R0 +1.
     *
     * @param string $argument The argument to process
     *
     * @return string The processed argument
     */
    private function processArgument(
        $argument
    )
    {
        $argument = trim($argument);
        //lets see if we are dealing with a +, -, * etc.
        if (strpos($argument, '+') !== false) {//+
            $arguments = explode('+', $argument);
            if (empty($arguments[0]) OR empty($arguments[1])) {
                return $argument;
            }
            $argument = '';
            foreach ($arguments as $arg) {
                $argument .= $this->processArgument($arg) . ' + ';
            }
            $argument = substr($argument, 0, -3);
            return $argument;
        }
        if (strpos($argument, '-') !== false) {//+
            $arguments = explode('-', $argument);
            if (empty($arguments[0]) OR empty($arguments[1])) {
                return $argument;
            }
            $argument = '';
            foreach ($arguments as $arg) {
                $argument .= $this->processArgument($arg) . ' - ';
            }
            $argument = substr($argument, 0, -3);
            return $argument;
        }

        if (strpos($argument, '%') !== false) {
            $this->error('Unexpected %');
        }
        //check if variable
        if (substr($argument, 0, 1) === '$') {
            //get the variable
            $argument = $this->getRegister($argument);
            return $argument;
        }

        //nothing special
        return $argument;
    }

    /** Gets the register who belongs to the given variable, creates a new one if none exists.
     *
     * @param string $variable The variable to look for
     *
     * @return string The register belonging to the variable.
     */
    private function getRegister($variable)
    {
        $variable = trim($variable);
        //check if we already have this variable
        if (in_array($variable, $this->_variables)) {
            return 'R' . array_search($variable, $this->_variables);
        } else {
            //if not, make a new one
            //first check if we have space
            if (count($this->_variables) == $this->maxVariables) {
                $this->error(
                    'too many _variables, max is ' . $this->maxVariables
                );
            } else {
                $this->_variables[] = $variable;
                return 'R' . (count($this->_variables) - 1);
            }
        }
        $this->error('Unknown error while getting register');
        return '';
    }

    /** Create a new if/else function
     *
     * @param string $type Whether its a if or an else
     *
     * @return string the name of the new conditional.
     */
    private function getNextConditional($type)
    {

        $i = @end(
            $this->_inConditional
        )['id'];//the last key as starting position
        if (!is_int($i)) {
            $i = -1;
        }
        while (true) {
            $i++;
            if (!isset($this->_functions['conditional' . $i])
                && !isset($this->_functions['return' . $i])
                && !isset($this->_functionsCompiled['conditional' . $i])
                && !isset($this->_functionsCompiled['return' . $i])
            ) {
                $index = count($this->_inConditional);
                $this->_inConditional[$index]['name'] = 'conditional' . $i;
                $this->_inConditional[$index]['id'] = $i;
                $this->_inConditional[$index]['type'] = $type;

                $this->_functionsCompiled['conditional' . $i] = new stdClass();
                $this->_functionsCompiled['conditional' . $i]->code = array();
                $this->_functionsCompiled['conditional' . $i]->returns
                    = array();

                $this->_lineNumber['conditional' . $i] = 0;
                return 'conditional' . $i;
            }
        }
        $this->error('Unknown error while getting conditional');
        return '';
    }

    /** Create the actual compiled code
     *
     * @param $codeOutside array Code not in a function
     *
     * @return string The created code.
     */
    private function makeCode($codeOutside)
    {
        $result = array();

        //insert the data
        $result[] = "@DATA";
        $result=array_merge($result,$this->data);
        $result[] = '';

        $result[] = "@CODE";
        $result[] = '';
        foreach ($codeOutside as $returnCodeLine) {
            $result[] = $returnCodeLine;
        }
        $result[] = "begin:\tBRA main";
        $result[] = '';

        require_once 'defaultFunctions.php';
        if ($this->_useDisplay) {
            $result = array_merge($result, $this->_defaultFunctions['display']);
        }
        if ($this->_useSleep) {
            $result = array_merge($result, $this->_defaultFunctions['sleep']);
        }
        if ($this->_usePow) {
            $result = array_merge($result, $this->_defaultFunctions['pow']);
        }
        if ($this->_usePressed) {
            $result = array_merge($result, $this->_defaultFunctions['pressed']);
        }
        //okay we have the outside code now, lets do the _functions
        foreach ($this->_functionsCompiled as $funcName => $function) {
            $result = array_merge(
                $result, $this->makeFunc($funcName, $function)
            );
        }
        $result[] = '@END';
        return implode("\n", $result);
    }

    /**Creates a function from semi-compiled code.
     *
     * @param string $funcName Name of the function
     * @param object $function Code of the function
     *
     * @return array compiled code
     */
    private function makeFunc($funcName, $function)
    {
        $result = array();
        foreach ($function->code as $i => $line) {
            if ($i == 0)//first
            {
                $result[] = $funcName . ": \t\t" . $line;
            } else {
                $result[] = "\t\t\t" . $line;
            }
        }
        //go back to main
        if (isset($this->_functions[$funcName]) AND ($this->_functions[$funcName]->isTimer === true)) {
            $result[] = "\t\t\t" . 'RTE';
        } else {
            $result[] = "\t\t\t" . 'BRA main';
        }
        foreach ($function->returns as $return) {
            $result[$return['_line']]
                = $return['name'] . ':' . $result[$return['_line']];
        }
        $result[] = "";//white line for readability
        return $result;
    }

    /**Gets all the function defined in the source code
     *
     * @return array The _functions
     */
    private function getFunctions()
    {
        $i = -1;
        $functions = array();
        $brackets = 0;
        foreach ($this->code as $lineNumber => $line) {
            if (substr($line, 0, 8) == 'function') {
                $i++;
                $functions[$i] = new stdClass();
                $functions[$i]->name = trim(substr($line, 9, -2));
                $functions[$i]->lineNumberStart = $lineNumber;
                $functions[$i]->code = array();
                $functions[$i]->hasReturn = false;
                $functions[$i]->isTimer = false;
            } else {
                if ($line == '{'
                    || preg_match(
                        "/(if|else|function).*\\{/", $line
                    )
                )//check for comments
                {
                    $brackets++;
                }
            }
            if (substr($line, 0, 1) == '}') {
                $brackets--;
                if ($brackets == 0) {
                    $functions[$i]->lineNumberEnd = $lineNumber;
                }
            }
            if ($brackets > 0) {
                if (count($functions[$i]->code) == 0 && $line === '{') {
                    continue;
                }
                $functions[$i]->code[] = $line;
                if (trim($line) === 'return') {
                    $functions[$i]->hasReturn = true;
                }
            }
        }
        return $functions;
    }

    /**Inserts a _line of code
     *
     * @param string $functionName Where to insert
     * @param string $toInsert The _line to insert
     * @param int $startLevel How many if/else levels up/down to insert
     */
    private function insertCode($functionName, $toInsert, $startLevel)
    {
        $i = $startLevel - 1;
        while ($i > -1 && $this->_inConditional[$i]['type'] === 'else') {
            $i--;
        }
        if ($i == -1) {
            $this->_functionsCompiled[$functionName]->code[] = $toInsert;
            $this->_lineNumber[$functionName]++;
        } else {
            $this->_functionsCompiled[$this->_inConditional[$i]['name']]->code[]
                = $toInsert;
            $this->_lineNumber[$this->_inConditional[$i]['name']]++;
        }
    }


    private function processData($line)
    {
        //set the line number in case we get an error
        $this->_functionName='@DATA';
        $this->_lineNumber['@DATA']=0;
        $return = $this->processLine($line);
        if ($return[0]!==0)
        {
            //we should always get single lines out of data
            $this->error('Unknown code in data segment');
        }
        return $return[1];
    }

    /**Throws an error
     *
     * @param string $error The error
     * @param bool $less Whether to give less information, default false
     */
    private function error($error, $less = false)
    {
        if ($less == true) {
            die("!!\tERROR while compiling:\n!!\t$error.");
        }
        die("!!\tERROR while compiling around _line #"
            . $this->_lineNumber[$this->_functionName]
            . " in function $this->_functionName:\n!!\t" . $this->_line
            . "\n!!\t$error.");
    }
}