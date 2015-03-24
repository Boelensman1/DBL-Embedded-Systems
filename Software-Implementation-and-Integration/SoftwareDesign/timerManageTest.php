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
    setVars();
}

function setVars()
{
    timerManage();
    //reset Hbridge
    $temp = 0;
    _storeData($temp, 'outputs', HBRIDGE0);

    $temp = 12;
    _storeData($temp, 'outputs', LENSLAMPPOSITION);
    _storeData($temp, 'outputs', LENSLAMPSORTER);
    $temp = 9;
    _storeData($temp, 'outputs', CONVEYORBELT);
    $temp = 5;
    _storeData($temp, 'outputs', FEEDERENGINE);



    test();
}

function test()
{
    timerManage();
    test();
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




