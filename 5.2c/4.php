<?php
include 'functions.php';

//**DATA**
initVar('intensity', 8);
initVar('counter', 1);

//**CODE**
define('WAIT', 1000);

function main()
{
    global $intensity, $location, $counter,$lights;
    installCountdown('loop');
    $intensity = 0;//R0
    $counter = 0;//R1
    $location = 0;//R2

    _storeData($counter, 'counter', 0);

    init();
}

function init()
{
    global $location, $intensity;
    $location++;
    _storeData($intensity, 'intensity', $location);
    if ($location == 7) {
        setTimer(WAIT);
        startCountdown();
        emptyLoop();
    }
    init();
}

function emptyLoop()
{
    emptyLoop();
}

function loop()
{
    global $counter, $location, $lights, $temp;
    //set the timer
    setTimer(WAIT);
    startCountdown();

    //get the variables from the GB
    $counter = _getData('counter', 0);//R1
    $counter++;

    if ($counter == 10) {
        $counter = 1;
    }

    _storeData($counter, 'counter', 0);


    $location = -1;//R2
    $lights = 0;//R3
    $temp = 0;//R4
    getValues();
}

function getValues()
{
    global $counter, $location, $lights, $temp;
    //get all values to see which lights should be on or off

    $location++;
    if ($location == 0) {
        getInput($temp, 'analog');
        $temp /= 25;//divide by 25 to make it between 0 and 10
        //$temp++;
    }
    if ($location != 0) {
        $temp = _getData('intensity', $location);
    }

    if ($counter < $temp) {
        //$temp = 2;
        stackPush($lights);
        pow(2, $location);
        stackPull($lights);
        $lights += R5;
    }

    if ($location == 7) {
        display($lights, 'leds');
        $location = 0;
        checkButtons();
    }
    getValues();
}

function checkButtons()
{
    global $counter, $location, $temp, $lights;
    if ($counter != 5) {
        //otherwise he checks the buttons too often, resulting in it going straight to full ON
        returnt;
    }
    $location++;
    //check button 1
    buttonPressed($location);//returns its value into R4
    if (R5 == 1) {
        $temp = _getData('intensity', $location);
        $temp++;

        buttonPressed(0);//returns its value into R4
        if (R5 == 1) {
            $temp -= 2;
        }
        if ($temp != 11) {
            if ($temp != -1) {
                _storeData($temp, 'intensity', $location);
            }
        }
    }
    if ($location != 7) {
        checkButtons();
    }
    returnt;
}