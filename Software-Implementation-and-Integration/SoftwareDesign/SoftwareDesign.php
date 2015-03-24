<?php
/**
 * Sort of a simulation of the PP2 program controlling the Fischer Technik in order to sort black and white discs.
 * @team Group 16
 * @author Stefan van den Berg
 * @author Rolf Verschuuren
 * @author Wigger Boelens
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
define('TIMEMOTORDOWN', 300);
define('BELT', 1200);
define('BELTROUND', 2000);
define('SORT', 850);
define('LENSLAMPPOSITION', 5);
define('LENSLAMPSORTER', 6);
define('HBRIDGE0', 0);
define('HBRIDGE1', 1);
define('CONVEYORBELT', 3);
define('FEEDERENGINE', 7);
define('DISPLAY', 8);
define('LEDSTATEINDICATOR', 9);

//not a state

function main()
{
    global $counter;
    installCountdown('timerInterrupt');
    //the variables that are the same through the program:
    $counter = 0;

    $temp = 0;
    _storeData($temp, 'outputs', HBRIDGE1);
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

//state 0
function initial()
{
    global $sleep;
    timerManage();
    $push = _getButtonPressed(5);
    if ($push == 1) {
        $temp = 0;
        _storeData($temp, 'outputs', HBRIDGE0);
        $temp = 9;
        _storeData($temp, 'outputs', HBRIDGE1);
        $state = 1;
        display($state, "leds2", "");
        unset($state, $push);
        $sleep = 0;
        calibrateSorter();

    }
    initial();
}

//state 1
function calibrateSorter()
{
    global $sleep;
    timerManage();
    if ($sleep == TIMEMOTORDOWN) {
        $temp = 0;
        _storeData($temp, 'outputs', HBRIDGE1);
        $state = 2;
        display($state, "leds2", "");
        $sleep = 0;
        unset($state);
        resting();
    }
    $sleep++;
    calibrateSorter();
}

//state 2
function resting()
{
    unset ($sleep);
    timerManage();
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        //sleep so we dont go to pause immediatly
        sleep(2000);

        $temp = 12;
        _storeData($temp, 'outputs', LENSLAMPPOSITION);
        _storeData($temp, 'outputs', LENSLAMPSORTER);
        $temp = 9;
        _storeData($temp, 'outputs', CONVEYORBELT);
        $temp = 5;
        _storeData($temp, 'outputs', FEEDERENGINE);

        setCountdown(BELTROUND + BELT);
        startCountdown();

        $state = 3;
        display($state, "leds2", "");
        unset($startStop, $state);
        running();
    }
    resting();
}

//state 3
function running()
{
    global $position, $startStop;
    timerManage();
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp = 0;
        _storeData($temp, 'outputs', FEEDERENGINE);

        $temp = 5;
        display($temp, "display", "100");

        setCountdown(BELT);
        $state = 9;
        display($state, "leds2", "");
        unset($state, $temp);
        runningTimer();

    }
    unset($startStop);
    $position = _getButtonPressed(7);
    if ($position == 1) {
        setCountdown(BELTROUND + BELT);

        $state = 4;
        display($state, "leds2", "");
        unset($state);
        runningWait();
    }
    running();
}

//state 4
function runningWait()
{
    global $position;
    timerManage();
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp = 0;
        _storeData($temp, 'outputs', FEEDERENGINE);
        setCountdown(BELT);

        $temp = 5;
        display($temp, "display", "100");

        $state = 9;
        display($state, "leds2", "");
        unset($state, $temp);
        runningTimer();

    }
    unset ($startStop);

    $position = _getButtonPressed(7);
    if ($position == 1) {
        setCountdown(BELTROUND + BELT);
        $state = 5;

        display($state, "leds2", "");
        unset ($state);
        runningTimerReset();

    }
    unset ($position);

    $colour = _getButtonPressed(6);
    if ($colour == 1) {
        $temp = 9;
        _storeData($temp, 'outputs', HBRIDGE0);
        setCountdown(SORT);
        $state = 6;
        display($state, "leds2", "");
        unset($state);
        motorUp();
    }
    unset ($colour);

    runningWait();
}

//state 5
function runningTimerReset()
{
    timerManage();
    $state = 4;
    display($state, "leds2", "");
    unset($state);
    runningWait();
}

//state 6
function motorUp()
{
    global $push, $startStop;
    timerManage();

    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp = 0;
        _storeData($temp, 'outputs', FEEDERENGINE);
        setCountdown(BELT);

        $temp = 5;
        display($temp, "display", "100");

        unset($temp);
        $state = 10;
        display($state, "leds2", "");
        unset($state);
        motorUpTimer();

    }
    unset($startStop);

    $push = _getButtonPressed(5);
    if ($push == 1) {
        $temp = 0;
        _storeData($temp, 'outputs', HBRIDGE0);
        $state = 7;
        display($state, "leds2", "");
        unset($state);
        whiteWait();
    }
    unset($push);
    motorUp();
}

//state 7
function whiteWait()
{
    global $sleep;
    timerManage();
    if ($sleep == SORT) {
        $temp = 9;
        _storeData($temp, 'outputs', HBRIDGE1);
        $temp = 1;
        display($temp, "display", "1");
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

        $temp = 5;
        display($temp, "display", "100");

        setCountdown(BELT);
        $state = 11;
        display($state, "leds2", "");
        whiteWaitTimer();

        unset($temp, $state);
    }
    unset($startStop);
    $sleep++;
    whiteWait();
}

//state 8
function motorDown()
{
    global $sleep, $startStop;
    timerManage();
    if ($sleep == TIMEMOTORDOWN) {
        $temp = 0;
        _storeData($temp, 'outputs', HBRIDGE1);
        $state = 4;
        display($state, "leds2", "");
        unset($state, $temp);

        $sleep = 0;
        runningWait();
    }

    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $temp = 0;
        _storeData($temp, 'outputs', FEEDERENGINE);
        setCountdown(BELT);
        $state = 12;
        display($state, "leds2", "");

        $temp = 5;
        display($temp, "display", "100");

        unset($state, $temp);
        motorDownTimer();
    }
    unset($startStop);

    $sleep++;
    motorDown();

}

//state 9
function runningTimer()
{
    timerManage();
    $state = 13;
    display($state, "leds2", "");
    unset($state);
    runningStop();
}

//state 10
function motorUpTimer()
{

    timerManage();
    $state = 14;
    display($state, "leds2", "");
    unset($state);
    motorUpStop();
}

//state 11
function whiteWaitTimer()
{
    timerManage();
    $state = 15;
    display($state, "leds2", "");
    unset($state);
    whiteWaitStop();
}

//state 12
function motorDownTimer()
{
    timerManage();
    $state = 16;
    display($state, "leds2", "");
    unset($state);
    motorDownStop();
}

//state 13
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

//state 14
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

//state 15
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
    whiteWaitStop();
}

//state 16
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
    motorDownStop();
}

//state 17
function timerInterrupt()
{
    $temp = SP + 3;
    display($temp, "display", "100");

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

