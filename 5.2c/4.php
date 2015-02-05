<?php
/**
 * Created by PhpStorm.
 * User: Wigger
 * Date: 16/12/14
 * Time: 23:08
 */
include 'functions.php';

//**START**
define('WAIT', 100);//2==5hz

function main()
{
    global $intensity, $location, $counter;
    installCountdown('loop');
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
        startCountdown();
        loop_empty();
    }
    init();
}

function loop_empty()
{
    loop_empty();
}
function loop()
{
    global $counter, $location, $lights, $temp;
    $counter++;
    if ($counter == 10) {
        $counter = 1;
    }
    $location = -1;
    $temp = 0;
    $lights = 0;
    getValues();
}

function getValues()
{
    global $counter, $location, $lights, $temp;
    //get all values to see which lights should be on or off

    $location++;
    if ($location == 0) {
        getInput($temp, 'analog');
        $temp /= 28;//delen door 25 om er van 0-10 van te maken
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
    global $counter, $location, $lights, $temp;
    if ($counter != 5) {
        setTimer(WAIT);
        startCountdown();
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
        setTimer(WAIT);
        startCountdown();
    }
    checkButtons();
}