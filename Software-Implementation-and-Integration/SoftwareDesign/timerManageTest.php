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

//**COMPILER**
moveFunction('interrupt', 1);

//**DATA**
//outputs
initVar('offset', 1);
initVar('stackPointer', 1);

//**CODE**
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
    //install the countdown
    storeData(R5,'offset',0);
    installCountdown('interrupt');

    storeData(SP,'stackPointer',0);

    //reset lights
    $temp = 0;
    display($temp, 'leds2', '');
    $counter = 0;

    //start the countdown
    setCountdown(2000);
    //startCountdown();

    $temp=93492304;
    pushStack($temp);
    pushStack($temp);
    pushStack($temp);
    pushStack($temp);
    pushStack($temp);
    pushStack($temp);
    pushStack($temp);
    pushStack($temp);
    init();
    //loop();
}


function interrupt()
{
    $temp = 5;
    display($temp, 'leds2', '');
    sleep(1000);

    //reset the lights
    $temp = 0;
    display($temp, 'leds2', '');

    //start the countdown
    setCountdown(2000);
    startCountdown();

    $temp=getData('offset',0);
    $temp2=getFuncLocation('init');
    $temp+=$temp2;


    addStackPointer(2);
    pushStack($temp);
    addStackPointer(-1);

    returnt;
}




function init()
{
    $temp=getData('stackPointer',0);
    setStackPointer($temp);
    $temp=0;
    $temp++;
    pushStack($temp);
    $temp++;
    pushStack($temp);
    $temp++;
    pushStack($temp);
    $temp++;
    pushStack($temp);
    $temp++;
    pushStack($temp);
    init();
}

function loop()
{
    global $counter;
    mod(255, $counter);
    $counter++;
    display($counter, 'leds', '');
    sleep(1);
    loop();
}

