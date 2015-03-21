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
initVar('outputs', 12);

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
define('TIMEMOTORDOWN', 30);
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
    global $counter;
    //the variables that are the same through the program:
    $counter=0;

    $temp = 0;
    _storeData($temp, 'outputs', HBRIDGE1);
    _storeData($temp, 'outputs', HBRIDGE0);
    _storeData($temp, 'outputs', LENSLAMPPOSITION);
    _storeData($temp, 'outputs', LENSLAMPSORTER);
    _storeData($temp, 'outputs', LEDSTATEINDICATOR);
    _storeData($temp, 'outputs', DISPLAY);
    _storeData($temp, 'outputs', CONVEYORBELT);
    _storeData($temp, 'outputs', FEEDERENGINE);
    $state = 0;
    display($state, "leds2", "");

    //set HBridge so the sorter starts moving up
    $temp = 9;
    _storeData($temp, 'outputs', HBRIDGE0);
    unset($temp, $state);
    initial();
}

/*
function initial()
{
    $temp=6;
    _storeData($temp,'outputs',4);
    $temp=12;
    _storeData($temp,'outputs',5);
    timerManage();
    sleep(100);
    initial();
}
*/
function initial()
{
    global $sleep;
    timerManage();
    $push = _getButtonPressed(5);
    if ($push == 1) {
        $temp = 9;
        _storeData($temp, 'outputs', HBRIDGE0);
        $temp = 0;
        _storeData($temp, 'outputs', HBRIDGE1);
        $state = 1;
        display($state, "leds2", "");
        unset($state, $push);
        $sleep = 0;
        calibrateSorter();

    }
    initial();
}

function calibrateSorter()
{
    global $sleep;
    timerManage();
    if ($sleep == TIMEMOTORDOWN) {
        $temp = 9;
        _storeData($temp, 'outputs', HBRIDGE1);
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
    unset ($sleep);
    timerManage();
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp = 12;
        _storeData($temp, 'outputs', LENSLAMPPOSITION);
        _storeData($temp, 'outputs', LENSLAMPSORTER);
        $temp = 9;
        _storeData($temp, 'outputs', CONVEYORBELT);
        $temp = 5;
        _storeData($temp, 'outputs', FEEDERENGINE);
        setTimer(2 + BELT);
        $state = 3;
        display($state, "leds2", "");
        unset($startStop, $state);
        running();
    }
    resting();
}

function running()
{
    global $position, $startStop;
    timerManage();
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp = 0;
        _storeData($temp, 'outputs', FEEDERENGINE);
        setTimer(BELT);
        $state = 9;
        display($state, "leds2", "");
        unset($state, $temp);
        runningTimer();

    }
    unset($startStop);
    $position = _getButtonPressed(7);
    if ($position == 1) {
        setTimer(2 + BELT);

        $state = 4;
        display($state, "leds2", "");
        unset($state);
        runningWait();
    }
    running();
}

function runningWait()
{
    global $position;
    timerManage();
    $position = _getButtonPressed(7);
    $colour = _getButtonPressed(6);
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp = 0;
        _storeData($temp, 'outputs', FEEDERENGINE);
        setTimer(BELT);
        unset($colour);
        $state = 9;
        display($state, "leds2", "");
        unset($position, $startStop, $state);
        runningTimer();

    }
    if ($position == 1) {
        setTimer(2 + BELT);
        $state = 5;
        display($state, "leds2", "");
        runningTimerReset();

    }
    if ($colour == 1) {
        $temp = 9;
        _storeData($temp, 'outputs', HBRIDGE0);
        setTimer(SORT);
        $state = 6;
        display($state, "leds2", "");
        unset($position, $state);
        motorUp();
    }
    runningWait();
}

function runningTimerReset()
{

    timerManage();
    runningWait();
}

function motorUp()
{
    global $push, $startStop;
    timerManage();
    $push = _getButtonPressed(7);
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp = 0;
        _storeData($temp, 'outputs', FEEDERENGINE);
        setTimer(BELT);
        unset($temp);
        $state = 10;
        display($state, "leds2", "");
        unset($startStop, $push, $state);
        motorUpTimer();

    }
    if ($push == 1) {
        $temp = 0;
        _storeData($temp, 'outputs', HBRIDGE0);
        $state = 7;
        display($state, "leds2", "");
        unset($push, $state);
        whiteWait();

    }
}

function whiteWait()
{
    global $sleep;
    timerManage();
    if ($sleep == SORT) {
        $temp = 9;
        _storeData($temp, 'outputs', HBRIDGE1);
        $state = 8;
        display($state, "leds2", "");
        $sleep = 0;
        unset($state, $temp);
        motorDown();

    }

    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp = 0;
        _storeData($temp, 'outputs', FEEDERENGINE);
        unset($temp);
        setTimer(BELT);
        $state = 11;
        display($state, "leds2", "");
        whiteWaitTimer();
    }
    unset($startStop);
    $sleep++;
    whiteWait();
}

function motorDown()
{
    global $sleep, $startStop;
    timerManage();
    $startStop = _getButtonPressed(0);
    if ($sleep == TIMEMOTORDOWN) {
        $temp = 0;
        _storeData($temp, 'outputs', HBRIDGE1);
        $state = 9;
        $sleep = 0;
        display($state, "leds2", "");
        unset($state, $startStop, $temp);
        runningWait();
    }

    if ($startStop == 1) {
        $temp = 0;
        _storeData($temp, 'outputs', FEEDERENGINE);
        setTimer(BELT);
        $state = 12;
        display($state, "leds2", "");
        motorDownTimer();
        unset($state, $startStop);
    }
    $sleep++;
    motorDown();

}

function runningTimer()
{

    timerManage();
    runningStop();
}

function motorUpTimer()
{

    timerManage();
    motorUpStop();
}

function whiteWaitTimer()
{

    timerManage();
    whiteWaitStop();
}

function motorDownTimer()
{

    timerManage();
    motorDownStop();
}


function runningStop()
{

    timerManage();
    $colour = _getButtonPressed(6);
    if ($colour == 1) {
        $temp = 9;
        _storeData($temp, 'outputs', HBRIDGE0);
        $state = 10;
        display($state, "leds2", "");
        unset($colour, $state);
        motorUpStop();
    }
    runningStop();
}

function motorUpStop()
{

    timerManage();
    $push = _getButtonPressed(5);
    if ($push == 1) {
        $temp = 0;
        _storeData($temp, 'outputs', HBRIDGE0);
        $state = 11;
        display($state, "leds2", "");
        whiteWaitStop();
        unset($push, $state);
    }
    motorUpStop();
}

function whiteWaitStop()
{
    global $sleep;
    timerManage();
    if ($sleep == SORT * 1000) {
        $temp = 9;
        _storeData($temp, 'outputs', HBRIDGE1);
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
    global $sleep;
    timerManage();
    if ($sleep == TIMEMOTORDOWN) {
        $temp = 0;
        _storeData($temp, 'outputs', HBRIDGE1);
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

    timerManage();
    $temp = 9;
    _storeData($temp, 'outputs', HBRIDGE1);
    $temp = 0;
    _storeData($temp, 'outputs', HBRIDGE0);
    _storeData($temp, 'outputs', LENSLAMPPOSITION);
    _storeData($temp, 'outputs', LENSLAMPSORTER);
    _storeData($temp, 'outputs', LEDSTATEINDICATOR);
    _storeData($temp, 'outputs', DISPLAY);
    _storeData($temp, 'outputs', CONVEYORBELT);
    _storeData($temp, 'outputs', FEEDERENGINE);

    initial();

}

function abort()
{

    timerManage();
    $temp = 0;
    _storeData($temp, 'outputs', HBRIDGE1);
    _storeData($temp, 'outputs', HBRIDGE0);
    _storeData($temp, 'outputs', LENSLAMPPOSITION);
    _storeData($temp, 'outputs', LENSLAMPSORTER);
    _storeData($temp, 'outputs', LEDSTATEINDICATOR);
    _storeData($temp, 'outputs', DISPLAY);
    _storeData($temp, 'outputs', CONVEYORBELT);
    _storeData($temp, 'outputs', FEEDERENGINE);
    aborted();

}

function aborted()
{

    timerManage();
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp = 9;
        _storeData($temp, 'outputs', HBRIDGE0);
        $state = 0;
        display($state, "leds2", "");
        initial();
        unset($state, $startStop);
    }
    aborted();

}

function timerManage()
{
    global $location, $counter, $engines;
    mod(12, $counter); //makes sure that when $counter >13 it will reset to 0
    $temp = _getData('outputs', $location);
    if ($temp > $counter) {
        $temp = $location;
        $temp = pow(2, $temp);
        $engines += $temp;
    }

    if ($location > 7) {
        display($engines, "leds", "");
        $engines = 0;
        $location = 0;
        $counter++;
        return;
    }

    $location++;
    branch('timerManage');
}




