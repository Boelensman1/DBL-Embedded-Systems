<?php
include 'functions.php';
//everything above //**DATA** is left out
//**DATA**
initVar('intensity', 8);
initVar('counter', 1);

//**CODE**
define('WAIT', 1000);//define a value for wait

function main()
{
    global $intensity, $location, $counter,$lights;//just for php purposes  doesn't reoccur in the assembly program
    installCountdown('loop');//initializes the timer interrupt
    $intensity = 0;//is stored in R0
    $counter = 0;//is stored in R1
    $location = 0;//is stored in R2

    _storeData($counter, 'counter', 0);//stores counter in the global base

    init();//calls the init function
}

function init()
{
    global $location, $intensity;//just for php purposes  doesn't reoccur in the assembly program
    $location++;//increments $location
    _storeData($intensity, 'intensity', $location);//stores $intensity at $location
    if ($location == 7) {//checks if it went through all the locations
        setTimer(WAIT);//set the timer at value wait
        startCountdown();//starts the timer
        emptyLoop();//calls the function emptyloop
    }
    init();//calls itself
}

function emptyLoop()
{
    emptyLoop();//keeps calling itself
}

function loop()
{
    global $counter, $location, $lights, $temp;//just for php purposes  doesn't reoccur in the assembly program
    
    setTimer(WAIT);//set the timer at value wait
    startCountdown();//starts the countdown


    $counter = _getData('counter', 0);//stores $counter in the GB
    $counter++;//increments $counter

    if ($counter == 10) {//checks if timer has run 10 times since when it was set to 1
        $counter = 1;//make the $counter ready for the next call
    }

    _storeData($counter, 'counter', 0);//stores $counter in the GB


    $location = -1;//set the $location to the AD
    $lights = 0;//initialize the value of $lights
    $temp = 0;//initialize the value of $temp
    getValues();//call the function getValues
}

function getValues()
{
    global $counter, $location, $lights, $temp;//just for php purposes  doesn't reoccur in the assembly program
    //get all values to see which lights should be on or off

    $location++;//increment $location
    if ($location == 0) {//check if the $location is the location of the AD
        getInput($temp, 'analog');//set $temp to the value of the AD
        $temp /= 25;//divide by 25 to make it between 0 and 10
     
    }
    if ($location != 0) {//get the value of the light
        $temp = _getData('intensity', $location);//set $temp to intensity of that light
    }

    if ($counter < $temp) {//check if the light should be on
  
        stackPush($lights);//put $lights on the stack
        pow(2, $location);//get the right $location of the LED result gets stored in R5
        stackPull($lights);//pull $lights of the stack
        $lights += R5;//creates the value of which LEDs need to be on
    }

    if ($location == 7) {//check if each value of all lights have been added to $lights
        display($lights, 'leds');//set the LEDs on according to the value $lights
        $location = 0;//set $location back to 0
        checkButtons();//call checkbuttons
    }
    getValues();//call itself
}

function checkButtons()
{
    global $counter, $location, $temp, $lights;//just for php purposes  doesn't reoccur in the assembly program
    if ($counter != 5) {
        //otherwise he checks the buttons too often, resulting in it going straight to full ON
        returnt;//returns from the timer-interrupt
    }
    $location++;//increment $location
    //check button 1
    buttonPressed($location);//set a 1 or a 0 depending on the state of the button at the $location in R5
    if (R5 == 1) {
        $temp = _getData('intensity', $location);
        $temp++;//increment $temp

        buttonPressed(0);//check if button 0 is pressed value gets put in R5
        if (R5 == 1) {//button 0 is pressed
            $temp -= 2;//decrement $temp by 2
        }
		//make sure that the intensity of the light is between 0 and 10
        if ($temp != 11) {
            if ($temp != -1) {
                _storeData($temp, 'intensity', $location);
            }
        }
    }
	//
    if ($location != 7) {//check if all buttons have been checked
        checkButtons();//if not then call itself
    }
    returnt;//returns from the timer the timer-interrupt
}