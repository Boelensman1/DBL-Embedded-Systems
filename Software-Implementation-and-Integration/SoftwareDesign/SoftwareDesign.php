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
define('$timeMotorDown', -1);
define('$belt', -1);
define('$sort', -1);
define('timerSort', -1);
define('$lensLampPosition', 5);
define('$lensLampSorter', 6);
define('hbridge0', 0);
define('hbridge1', 1);
define('conveyorBelt', 3);
define('feederEngine', 7);
define('display', 8);
define('ledStateIndicator', 9);


function main()
{
    initial();
}

function initial()
{
    global $outputs, $hbridge0, $hbridge1;
    timerManage($outputs);
    $push = _getButtonPressed(5);
    if ($push == 1) {
        $outputs[$hbridge0] = 0;
        $outputs[$hbridge1] = 9;
        $state = 1;
        display($state, "leds2", "");
        calibrateSorter();

    }
    initial();
}

function calibrateSorter()
{
    global $outputs, $timeMotorDown, $sleep, $hbridge1;
    timerManage($outputs);
    if ($sleep == $timeMotorDown * 1000) {
        $outputs[$hbridge1] = 9;
        $state = 2;
        display($state, "leds", "");
        resting();
        $sleep = 0;
    }
    $sleep++;
    calibrateSorter();
}

function resting()
{
    global $outputs, $lensLampPosition, $lensLampSorter, $conveyorBelt, $feederEngine, $belt;
    timerManage($outputs);
    $startStop = _getButtonPressed(0);
    if ($startStop == 1) {
        $outputs[$lensLampPosition] = 12;
        $outputs[$lensLampSorter] = 12;
        $outputs[$conveyorBelt] = 9;
        $outputs[$feederEngine] = 5;
        setTimer(2 + $belt);


        $state = 3;
        display($state, "leds2", "");
        running();
    }
    resting();
}

function running()
{
    global $outputs, $feederEngine, $belt;
    timerManage($outputs);
    $position = _getButtonPressed(7);
    $startStop = _getButtonPressed(0);
    if ($startStop = 1) {
        $outputs[$feederEngine] = 0;
        setTimer($belt);
        runningTimer();
    }
    if ($position = 1) {
        setTimer(2 + $belt);

        $state = 4;
        display($state, "leds2", "");
        runningWait();
    }
    running();
}

function runningWait()
{
    global $outputs, $feederEngine, $belt, $hbridge0, $sort;
    timerManage($outputs);
    $position = _getButtonPressed(7);
    $colour = _getButtonPressed(6);
    $startStop = _getButtonPressed(0);
    if ($startStop = 1) {
        $outputs[$feederEngine] = 0;
        setTimer($belt);
        runningTimer();
    }
    if ($position) {
        setTimer(2 + $belt);

        $state = 5;
        display($state, "leds2", "");
        runningTimerReset();
    }
    if ($colour) {
        $outputs[$hbridge0] = 9;

        setTimer($sort);

        $state = 6;
        display($state, "leds2", "");
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
    global $outputs, $feederEngine, $belt, $hbridge0;
    timerManage($outputs);
    $push = _getButtonPressed(7);
    $startStop = _getButtonPressed(0);
    if ($startStop = 1) {
        $outputs[$feederEngine] = 0;
        setTimer($belt);
        motorUpTimer();
    }
    if ($push = 1) {
        $outputs[$hbridge0] = 0;
        $state = 7;
        display($state, "leds2", "");
        whiteWait();
    }
}

function whiteWait()
{
    global $outputs, $sleep, $timerSort, $hbridge1, $feederEngine, $belt;
    timerManage($outputs);
    if ($sleep == $timerSort * 1000) {
        $outputs[$hbridge1] = 9;
        $state = 8;
        display($state, "leds2", "");
        motorDown();
        $sleep = 0;
    }
    $startStop = _getButtonPressed(0);
    if ($startStop = 1) {
        $outputs[$feederEngine] = 0;
        setTimer($belt);
        whiteWaitTimer();
    }
    $sleep++;
    whiteWait();
}

function motorDown()
{
    global $outputs, $sleep, $timeMotorDown, $hbridge1, $feederEngine, $belt;
    timerManage($outputs);
    if ($sleep == $timeMotorDown * 1000) {
        $outputs[$hbridge1] = 0;
        $state = 9;
        $sleep = 0;
        display($state, "leds2", "");
        runningWait();
    }
    $startStop = _getButtonPressed(0);
    if ($startStop = 1) {
        $outputs[$feederEngine] = 0;
        setTimer($belt);
        motorDownTimer();
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
    global $outputs, $hbridge0;
    timerManage($outputs);
    $colour = _getButtonPressed(6);
    if ($colour == 1) {
        $outputs[$hbridge0] = 9;
        $state = 10;
        display($state, "leds2", "");
        motorUpStop();
    }
    runningStop();
}

function motorUpStop()
{
    global $outputs, $hbridge0;
    timerManage($outputs);
    $push = _getButtonPressed(5);
    if ($push == 1) {
        $outputs[$hbridge0] = 0;
        $state = 11;
        display($state, "leds2", "");
    }
    motorUpStop();
}

function whiteWaitStop()
{
    global $outputs, $sleep, $hbridge1, $timerSort;
    timerManage($outputs);
    if ($sleep == $timerSort * 1000) {
        $outputs[$hbridge1] = 9;
        $state = 12;
        display($state, "leds2", "");
        motorDown();
        $sleep = 0;
    }

    $sleep++;
    whiteWait();
}

function motorDownStop()
{
    global $outputs, $sleep, $timeMotorDown, $hbridge1;
    timerManage($outputs);
    if ($sleep == $timeMotorDown * 1000) {
        $outputs[$hbridge1] = 0;
        $state = 9;
        $sleep = 0;
        display($state, "leds2", "");
        runningWait();
    }
    $sleep++;
    motorDown();
}

function timerInterrupt()
{
    global $outputs, $hbridge0, $hbridge1, $lensLampPosition, $lensLampSorter, $ledStateIndicator, $display, $conveyorBelt, $feederEngine;
    timerManage($outputs);
    $outputs[$hbridge0] = 1;
    $outputs[$hbridge1] = 0;
    $outputs[$lensLampPosition] = 0;
    $outputs[$lensLampSorter] = 0;
    $outputs[$ledStateIndicator] = 0;
    $outputs[$display] = 0;
    $outputs[$conveyorBelt] = 0;
    $outputs[$feederEngine] = 0;
    initial();

}

function abort()
{
    global $outputs, $hbridge0, $hbridge1, $lensLampPosition, $lensLampSorter, $ledStateIndicator, $display, $conveyorBelt, $feederEngine;
    timerManage($outputs);
    $outputs[$hbridge0] = 0;
    $outputs[$hbridge1] = 0;
    $outputs[$lensLampPosition] = 0;
    $outputs[$lensLampSorter] = 0;
    $outputs[$ledStateIndicator] = 0;
    $outputs[$display] = 0;
    $outputs[$conveyorBelt] = 0;
    $outputs[$feederEngine] = 0;
    aborted();

}

function aborted()
{
    global $outputs, $hbridge0;
    timerManage($outputs);
    $startStop = _getButtonPressed(0);
    if ($startStop = 1) {
        $outputs[$hbridge0] = 1;
        $state = 0;
        display($state, "leds2", "");
        initial();
    }
    aborted();

}

function timerManage()
{
    global $outputs, $location, $counter, $engines;
    $location = $location % 7;
    $counter = $counter % 12;
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




