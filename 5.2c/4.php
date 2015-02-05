<?php
/**
 * Created by PhpStorm.
 * User: Wigger
 * Date: 16/12/14
 * Time: 23:08
 */
include 'functions.php';

//**START**
define('WAIT', 100);

function main()
{
    global $intensity, $location, $counter;
    $intensity = 0;//R0
    $counter = 0;//R1
    $location = 0;//R2
    init();
}

function init()
{
    global $location, $intensity;
    $location++;
    _storeRam($intensity, $location);
    if ($location == 7) {
        loop();
    }
    init();
}

function loop()
{
    global $counter, $location, $lights, $temp;
    sleep(WAIT);
    $counter++;
    if ($counter == 10) {
        $counter = 1;
    }
    $location = -1;
    $temp = 0;
    $lights = 0;
    getValues();//at the end of this function, loop is called.
}

function getValues()
{
    global $counter, $location, $lights, $temp;
    //get all values to see which lights should be on or off

    $location++;
    if ($location == 0) {
        getInput($temp, 'analog');
        $temp /= 28;//divide by 25 to make it between 0 and 10
        $temp++;
    }
    if ($location != 0) {
        $temp = _getRam($location);
    }

    if ($counter < $temp) {
        $temp = 2;
        stackPush($lights);
        pow($temp, $location);
        stackPull($lights);
        $lights += R5;
    }
    $temp = _getRam($location);
    if ($counter == $temp) {
        $temp = 2;
        stackPush($lights);
        pow($temp, $location);
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
    global $counter, $location, $temp;
    if ($counter != 5) {
        //otherwise he checks the buttons too often, resulting in it going straight to full ON
        loop();
    }
    $location++;
    //check button 1
    buttonPressed($location);//returns its value into R4
    if (R4 == 1) {
        $temp = _getRam($location);
        $temp++;
        buttonPressed(0);//returns its value into R4
        if (R4 == 1) {
            $temp -= 2;
        }
        if ($temp != 10) {
            if ($temp != -1) {
                _storeRam($temp, $location);
            }
        }
    }
    if ($location == 7) {
        loop();
    }
    checkButtons();
}