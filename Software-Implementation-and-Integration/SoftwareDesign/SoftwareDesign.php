<?php
/**
 * Sort of a simulation of the PP2 program controlling the Fischer Technik in order to sort black and white discs.
 * @team Group 16
 * @author Maarten Keet
 * @author stefan van den Berg
 * @author Rolf Verschuuren
 * @since 13/3/2015
 */
include 'functions.php';


//**DATA**
//outputs
initVar('$outputs', 10);

//**CODE**
//inputs
//Boolean $startStop, $abort, $push, $position, $colour;
//int $timer;
//variables
//int $state = 0;
//int $sleep = 0;
//int $location;
//int $counter;
//int $engines;

//constants
define('TIMEMOTORDOWN', 300);
define('BELT', 1200);
define('SORT', 850);
define('LENSLAMPPOSITION', 5);
define('LENSLAMPSORTER', 6);
define('HBRIDGE0', 0);
define('HBRIDGE1', 1);
define('CONVEYORBELT', 3);
define('FEEDERENGINE', 7);
define('DISPLAY', 8);
define('LEDSTATEINDICATOR', 9);


function main()
{
    initial();
}

function initial()
{
    global $outputs;
    timerManage($outputs);
    $push = _getButtonPressed(5);
    if ($push == 1) {
        $temp=9;
        _storeData($temp,'$outputs',HBRIDGE1);
        $temp=0;
        _storeData($temp,'$outputs',HBRIDGE0);
        $state = 1;
        display($state, "leds2", "");
        unset( $state,$push);
        calibrateSorter();

    }
    initial();
}

function calibrateSorter()
{
    global $outputs, $sleep;
    timerManage($outputs);
    if ($sleep == TIMEMOTORDOWN) {
        $temp=9;
        _storeData($temp,'$outputs',HBRIDGE1);
        $state = 2;
        display($state, "leds", "");
        $sleep = 0;
        unset($state);
        resting();
    }
    $sleep++;
    calibrateSorter();
}

function resting()
{
    global $outputs;
    timerManage($outputs);
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp=12;
        _storeData($temp,'$outputs',LENSLAMPPOSITION);
        _storeData($temp,'$outputs',LENSLAMPSORTER);
        $temp=9;
        _storeData($temp,'$outputs',CONVEYORBELT);
        $temp=5;
        _storeData($temp,'$outputs',FEEDERENGINE);
        setTimer(2 + BELT);
        debug();
        $state = 3;
        display($state, "leds2", "");
        unset($startStop,$state);
        running();
    }
    resting();
}

function running()
{
    global $outputs, $position,$startStop;
    timerManage($outputs);
    $position = _getButtonPressed(7);
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp=0;
        _storeData($temp,'$outputs',FEEDERENGINE);
        setTimer(BELT);
        $state=9;
        display($state,"leds2","");
        unset($state);
        runningTimer();

    }
    if ($position == 1) {
        setTimer(2 + BELT);

        $state = 4;
        display($state, "leds2", "");
        unset($state,$temp);
        runningWait();
    }
    running();
}

function runningWait()
{
    global $outputs;
    timerManage($outputs);
    $position = _getButtonPressed(7);
    $colour = _getButtonPressed(6);
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp=0;
        _storeData($temp,'$outputs',FEEDERENGINE);
        setTimer(BELT);
        unset($colour);
        $state=9;
        display($state,"leds2","");
        unset($position,$startStop,$state);
        runningTimer();

    }
    if ($position == 1) {
        setTimer(2 + BELT);
        $state = 5;
        display($state, "leds2", "");
        runningTimerReset();

    }
    if ($colour == 1) {
        $temp=9;
        _storeData($temp,'$outputs',HBRIDGE0);
        setTimer(SORT);
        $state = 6;
        display($state, "leds2", "");
        unset($position,$state);
        motorUp();
    }
    runningWait();
}

function runningTimerReset()
{
    global $outputs;
    timerManage($outputs);
    runningWait();
}

function motorUp()
{
    global $outputs,$push,$startStop;
    timerManage($outputs);
    $push = _getButtonPressed(7);
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp=0;
        _storeData($temp,'$outputs',FEEDERENGINE);
        setTimer(BELT);
        unset($temp);
        $state=10;
        display($state,"leds2","");
        unset($startStop,$push,$state);
        motorUpTimer();

    }
    if ($push == 1) {
        $temp=0;
        _storeData($temp,'$outputs',HBRIDGE0);
        $state = 7;
        display($state, "leds2", "");
        unset($push,$state);
        whiteWait();

    }
}

function whiteWait()
{
    global $outputs, $sleep;
    timerManage($outputs);
    $startStop = _getButtonPressed(0);
    if ($sleep == SORT) {;
        $temp=9;
        _storeData($temp,'$outputs',HBRIDGE1);
        $state = 8;
        display($state, "leds2", "");
        $sleep = 0;
        unset($state,$startStop);
        motorDown();

    }

    if ($startStop == 1) {
        $temp=0;
        _storeData($temp,'$outputs',FEEDERENGINE);
        setTimer(BELT);
        $state=11;
        display($state,"leds2","");
        unset($startStop);
        whiteWaitTimer();
    }
    $sleep++;
    whiteWait();
}

function motorDown()
{
    global $outputs, $sleep;
    timerManage($outputs);
    $startStop = _getButtonPressed(0);
    if ($sleep == TIMEMOTORDOWN ) {
        $temp=0;
        _storeData($temp,'$outputs',HBRIDGE1);
        $state = 9;
        $sleep = 0;
        display($state, "leds2", "");
        unset($state,$startStop,$temp);
        runningWait();
    }

    if ($startStop == 1) {
        $temp=0;
        _storeData($temp,'$outputs',FEEDERENGINE);
        setTimer(BELT);
        $state=12;
        display($state,"leds2","");
        motorDownTimer();
        unset($state,$startStop);
    }
    $sleep++;
    motorDown();

}

function runningTimer()
{
    global $outputs;
    timerManage($outputs);
    runningStop();
}

function motorUpTimer()
{
    global $outputs;
    timerManage($outputs);
    motorUpStop();
}

function whiteWaitTimer()
{
    global $outputs;
    timerManage($outputs);
    whiteWaitStop();
}

function motorDownTimer()
{
    global $outputs;
    timerManage($outputs);
    motorDownStop();
}


function runningStop()
{
    global $outputs;
    timerManage($outputs);
    $colour = _getButtonPressed(6);
    if ($colour == 1) {
        $temp=9;
        _storeData($temp,'$outputs',HBRIDGE0);
        $state = 10;
        display($state, "leds2", "");
        unset($colour,$state);
        motorUpStop();
    }
    runningStop();
}

function motorUpStop()
{
    global $outputs;
    timerManage($outputs);
    $push = _getButtonPressed(5);
    if ($push == 1) {
        $temp=0;
        _storeData($temp,'$outputs',HBRIDGE0);
        $state = 11;
        display($state, "leds2", "");
        whiteWaitStop();
        unset($push,$state);
    }
    motorUpStop();
}

function whiteWaitStop()
{
    global $outputs, $sleep;
    timerManage($outputs);
    if ($sleep == SORT * 1000) {
        $temp=9;
        _storeData($temp,'$outputs',HBRIDGE1);
        $state = 12;
        display($state, "leds2", "");
        $sleep = 0;
        motorDownStop();
        unset($state);
    }

    $sleep++;
    whiteWait();
}

function motorDownStop()
{
    global $outputs, $sleep;
    timerManage($outputs);
    if ($sleep == TIMEMOTORDOWN ) {
        $temp=0;
        _storeData($temp,'$outputs',HBRIDGE1);
        $state = 9;
        $sleep = 0;
        display($state, "leds2", "");
        unset($state);
        runningStop();
    }
    $sleep++;
    motorDown();
}

function timerInterrupt()
{
    global $outputs;
    timerManage($outputs);
    $temp=9;
    _storeData($temp,'$outputs',HBRIDGE1);
    $temp=0;
    _storeData($temp,'$outputs',HBRIDGE0);
    _storeData($temp,'$outputs',LENSLAMPPOSITION);
    _storeData($temp,'$outputs',LENSLAMPSORTER);
    _storeData($temp,'$outputs',LEDSTATEINDICATOR);
    _storeData($temp,'$outputs',DISPLAY);
    _storeData($temp,'$outputs',CONVEYORBELT);
    _storeData($temp,'$outputs',FEEDERENGINE);

    initial();

}

function abort()
{
    global $outputs;
    timerManage($outputs);
    $temp=0;
    _storeData($temp,'$outputs',HBRIDGE1);
    _storeData($temp,'$outputs',HBRIDGE0);
    _storeData($temp,'$outputs',LENSLAMPPOSITION);
    _storeData($temp,'$outputs',LENSLAMPSORTER);
    _storeData($temp,'$outputs',LEDSTATEINDICATOR);
    _storeData($temp,'$outputs',DISPLAY);
    _storeData($temp,'$outputs',CONVEYORBELT);
    _storeData($temp,'$outputs',FEEDERENGINE);
    aborted();

}

function aborted()
{
    global $outputs;
    timerManage($outputs);
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp=9;
        _storeData($temp,'$outputs',HBRIDGE0);
        $state = 0;
        display($state, "leds2", "");
        initial();
        unset($state,$startStop);
    }
    aborted();

}

function timerManage()
{
    global $outputs, $location, $counter, $engines;
    mod(7,$location);
    mod(12,$counter);
    $temp = _getData('$outputs', $location);
    if ($counter < $temp) {
        $engines = $engines + pow(2, $location);
    }

    if ($location >= 7) {
        display($engines, "leds", "");
        $engines = 0;
        return;
    }

    $location++;
    $counter++;
    timerManage($outputs);
    return;
}




