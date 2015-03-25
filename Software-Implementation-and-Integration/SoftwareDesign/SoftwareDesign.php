<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
moveFunction('timerInterrupt', 1);

//**DATA**
initVar('offset', 1);
initVar('stackPointer', 1);
initVar('outputs', 12);

//**CODE**
define('TIMEMOTORDOWN', 3000); //how long the sorter takes to move down
define('BELT', 12000);//TODO: ????
define('BELTROUND', 20000);//Time for the belt to make a rotation
define('SORT', 8500);//Clockticks to make a rotation

//outputs
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

    //store the offset of the program, this is used in the interrupt
    storeData(R5,'offset',0);
    //install the countdown
    installCountdown('timerInterrupt');

    //save the location of the stackPointer, so we can clear the stack
    storeData(SP,'stackPointer',0);

    //the variables that are the same throughout the program:
    $counter = 0;

    //show that we are in main
    $temp = 2;
    display($temp, "leds2", "");

    //stop everything
    $temp = 0;
    storeData($temp, 'outputs', HBRIDGE1);
    storeData($temp, 'outputs', LENSLAMPPOSITION);
    storeData($temp, 'outputs', LENSLAMPSORTER);
    storeData($temp, 'outputs', LEDSTATEINDICATOR);
    storeData($temp, 'outputs', DISPLAY);
    storeData($temp, 'outputs', CONVEYORBELT);
    storeData($temp, 'outputs', FEEDERENGINE);
    $state = 0;
    display($state, "leds2", "");

    //set HBridge so the sorter starts moving up
    $temp = 9;
    storeData($temp, 'outputs', HBRIDGE0);
    unset($temp, $state);

    //go to the first state
    initial();
}

//state 0
function initial()
{
    global $sleep;

    $temp=getData('stackPointer',0);
    setStackPointer($temp);

    timerManage();

    //check if the sorter push button is pressed
    $push = getButtonPressed(5);
    if ($push == 1) {
        //move sorter down
        $temp = 0;
        storeData($temp, 'outputs', HBRIDGE0);
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE1);

        //update state
        $state = 1;
        display($state, "leds2", "");
        unset($state);

        //reset sleep for the next function
        $sleep = 0;
        calibrateSorter();

    }
    unset($push);

    //loop
    initial();
}

//state 1
function calibrateSorter()
{
    global $sleep;
    timerManage();

    //the sorter is now moving down,
    //we're waiting for it to reach its bottom position
    if ($sleep == TIMEMOTORDOWN) {
        //stop the sorter
        $temp = 0;
        storeData($temp, 'outputs', HBRIDGE1);

        //update the state
        $state = 2;
        display($state, "leds2", "");
        unset($state);

        //reset sleep for the next state
        $sleep = 0;
        resting();
    }

    //loop
    $sleep++;
    calibrateSorter();
}

//state 2
function resting()
{
    unset ($sleep);
    timerManage();

    //the program is now waiting for the user to press start/stop
    $startStop = getButtonPressed(0);
    if ($startStop == 1) {
        //sleep so we don't go to pause immediately
        sleep(2000);

        //power up the lamps
        $temp = 12;
        storeData($temp, 'outputs', LENSLAMPPOSITION);
        storeData($temp, 'outputs', LENSLAMPSORTER);

        //start up the belt and feeder
        $temp = 9;
        storeData($temp, 'outputs', CONVEYORBELT);
        $temp = 5;
        storeData($temp, 'outputs', FEEDERENGINE);

        //set and start the countdown for the moment there are no more disks
        //this countdown will reset every time a disk is found
        //when it triggers, timerInterrupt will be ran.
        setCountdown(BELTROUND + BELT);
        startCountdown();

        //update the state
        $state = 3;
        display($state, "leds2", "");
        unset($state);

        running();
    }
    unset($startStop);

    //loop
    resting();
}

//state 3
function running()
{
    timerManage();

    //check if we need to pause
    $startStop = getButtonPressed(0);
    if ($startStop == 1) {
        //stop the feeder engine
        $temp = 0;
        storeData($temp, 'outputs', FEEDERENGINE);
        unset($temp);

        //exit after 1 rotation of the belt
        setCountdown(BELT);

        //update the state
        $state = 9;//TODO: echte state
        display($state, "leds2", "");
        unset($state);

        runningTimer();

    }
    unset($startStop);

    //check if a disk is at the position detector
    $position = getButtonPressed(7);
    if ($position == 1) {
        //reset the countdown, because a disk was just detected
        setCountdown(BELTROUND + BELT);

        //update the state
        $state = 4;
        display($state, "leds2", "");
        unset($state);
        runningWait();
    }
    unset ($position);

    //loop
    running();
}

//state 4
function runningWait()
{
    timerManage();

    //check if we need to pause
    $startStop = getButtonPressed(0);
    if ($startStop == 1) {
        //stop the feeder engine
        $temp = 0;
        storeData($temp, 'outputs', FEEDERENGINE);
        unset($temp);

        //exit after 1 rotation of the belt
        setCountdown(BELT);

        //update the state
        $state = 9;//TODO: echte state
        display($state, "leds2", "");
        unset($state);

        runningTimer();

    }
    unset ($startStop);

    //check if a disk is at the position detector
    $position = getButtonPressed(7);
    if ($position == 1) {
        //reset the countdown, because a disk was just detected
        setCountdown(BELTROUND + BELT);

        //update state
        $state = 5;
        display($state, "leds2", "");
        unset ($state);

        runningTimerReset();

    }
    unset ($position);

    //check if a white disk is at the colour detector
    $colour = getButtonPressed(6);
    if ($colour == 1) {
        //move the sorter up so the disk goes to the correct box
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE0);
        unset($temp);

        //update state
        $state = 6;
        display($state, "leds2", "");
        unset($state);

        motorUp();
    }
    unset ($colour);

    //loop
    runningWait();
}

//state 5
function runningTimerReset()
{
    timerManage();

    //update state
    $state = 4;
    display($state, "leds2", "");
    unset($state);

    runningWait();
}

//state 6
function motorUp()
{
    global $sleep;
    timerManage();

    //check if we need to pause
    $startStop = getButtonPressed(0);
    if ($startStop == 1) {
        //stop the feeder engine
        $temp = 0;
        storeData($temp, 'outputs', FEEDERENGINE);
        unset($temp);

        //exit after 1 rotation of the belt
        setCountdown(BELT);

        //update the state
        $state = 10;
        display($state, "leds2", "");
        unset($state);

        motorUpTimer();

    }
    unset($startStop);

    //check if the sorter push button is pressed
    $push = getButtonPressed(5);
    if ($push == 1) {
        //stop the sorter engine, because its at its highest position
        $temp = 0;
        storeData($temp, 'outputs', HBRIDGE0);

        //update state
        $state = 7;
        display($state, "leds2", "");
        unset($state);

        //set sleep for the next function
        $sleep=0;

        whiteWait();
    }
    unset($push);

    //loop
    motorUp();
}

//state 7
function whiteWait()
{
    global $sleep;
    timerManage();

    //we are waiting for the white disk to be sorted
    if ($sleep == SORT) {
        //start moving the sorter down
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE1);
        unset($temp);

        //update state
        $state = 8;
        display($state, "leds2", "");
        unset($state);

        //reset sleep for the next function
        $sleep = 0;
        motorDown();

    }

    //check if we need to pause
    $startStop = getButtonPressed(0);
    if ($startStop == 1) {
        //stop the feeder engine
        $temp = 0;
        storeData($temp, 'outputs', FEEDERENGINE);
        unset($temp);

        //exit after 1 rotation of the belt
        setCountdown(BELT);

        //update the state
        $state = 11;
        display($state, "leds2", "");
        unset($state);

        whiteWaitTimer();
    }
    unset($startStop);

    //loop
    $sleep++;
    whiteWait();
}

//state 8
function motorDown()
{
    global $sleep;
    timerManage();

    //the sorter is moving down, we are waiting for that to complete
    if ($sleep == TIMEMOTORDOWN) {
        //stop the sorter, its where it should be
        $temp = 0;
        storeData($temp, 'outputs', HBRIDGE1);
        unset($temp);

        //update state
        $state = 4;
        display($state, "leds2", "");
        unset($state);

        runningWait();
    }

    //check if we need to pause
    $startStop = getButtonPressed(0);
    if ($startStop == 1) {
        //stop the feeder engine
        $temp = 0;
        storeData($temp, 'outputs', FEEDERENGINE);
        unset($temp);

        //exit after 1 rotation of the belt
        setCountdown(BELT);

        //update the state
        $state = 12;
        display($state, "leds2", "");
        unset($state);

        motorDownTimer();
    }
    unset($startStop);

    //loop
    $sleep++;
    motorDown();

}

//state 9
function runningTimer()
{
    timerManage();

    //update state
    $state = 13;
    display($state, "leds2", "");
    unset($state);

    runningStop();
}

//state 10
function motorUpTimer()
{
    timerManage();

    //update state
    $state = 14;
    display($state, "leds2", "");
    unset($state);

    motorUpStop();
}

//state 11
function whiteWaitTimer()
{
    timerManage();

    //update state
    $state = 15;
    display($state, "leds2", "");
    unset($state);

    whiteWaitStop();
}

//state 12
function motorDownTimer()
{
    timerManage();

    //update state
    $state = 16;
    display($state, "leds2", "");
    unset($state);

    motorDownStop();
}

//state 13
function runningStop()
{
    timerManage();

    //check if a white disk is at the colour detector
    $colour = getButtonPressed(6);
    if ($colour == 1) {
        //stop the sorter engine, because its at its highest position
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE0);
        unset($temp);

        //update state
        $state = 10;
        display($state, "leds2", "");
        unset($state);

        motorUpStop();
    }
    unset($colour);

    //loop
    runningStop();
}

//state 14
function motorUpStop()
{
    timerManage();

    //check if the sorter push button is pressed
    $push = getButtonPressed(5);
    if ($push == 1) {
        //move the sorter up so the disk goes to the correct box
        $temp = 0;
        storeData($temp, 'outputs', HBRIDGE0);
        unset ($temp);

        //update state
        $state = 11;
        display($state, "leds2", "");
        unset($state);

        whiteWaitStop();
    }
    unset($push);

    //loop
    motorUpStop();
}

//state 15
function whiteWaitStop()
{
    global $sleep;
    timerManage();

    //check if the white disk has been sorted
    if ($sleep == SORT) {
        //it has, so lets start moving the sorter down
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE1);
        unset($temp);

        //update state
        $state = 12;
        display($state, "leds2", "");
        unset($state);

        $sleep = 0;
        motorDownStop();
    }

    //loop
    $sleep++;
    whiteWaitStop();
}

//state 16
function motorDownStop()
{
    global $sleep;
    timerManage();

    //check if the sorter has moved down
    if ($sleep == TIMEMOTORDOWN) {
        //it has, so lets stop it
        $temp = 0;
        storeData($temp, 'outputs', HBRIDGE1);
        unset($temp);

        //update the state
        $state = 9;
        display($state, "leds2", "");
        unset($state);

        $sleep = 0;
        runningStop();
    }

    //loop
    $sleep++;
    motorDownStop();
}

//not a state
function timerInterrupt()
{
    timerManage();
    //show that we are in the timer interrupt
    $temp = 5;
    display($temp, "leds2", "");

    //start moving the sorter up, to start the calibration
    $temp = 9;
    storeData($temp, 'outputs', HBRIDGE1);

    //stop the rest
    $temp=0;
    storeData($temp, 'outputs', LENSLAMPPOSITION);
    storeData($temp, 'outputs', LENSLAMPSORTER);
    storeData($temp, 'outputs', LEDSTATEINDICATOR);
    storeData($temp, 'outputs', DISPLAY);
    storeData($temp, 'outputs', CONVEYORBELT);
    storeData($temp, 'outputs', FEEDERENGINE);


    //reset, because we will no longer be in timerInterrupt
    display($temp, "leds2", "");
    unset($temp);

    //go back to initial
    $temp=getData('offset',0);
    $temp2=getFuncLocation('initial');
    $temp+=$temp2;


    addStackPointer(2);
    pushStack($temp);
    addStackPointer(-1);
}

//not a state
function abort()
{
    //free some memory
    unset($engines);
    unset($location);
    unset($voltage);
    $temp=getData('stackPointer',0);
    setStackPointer($temp);

    //stop everything
    $temp = 0;
    storeData($temp, 'outputs', HBRIDGE1);
    storeData($temp, 'outputs', HBRIDGE0);
    storeData($temp, 'outputs', LENSLAMPPOSITION);
    storeData($temp, 'outputs', LENSLAMPSORTER);
    storeData($temp, 'outputs', LEDSTATEINDICATOR);
    storeData($temp, 'outputs', DISPLAY);
    storeData($temp, 'outputs', CONVEYORBELT);
    storeData($temp, 'outputs', FEEDERENGINE);
    unset($temp);

    //apply the changes to actually stop it
    timerManage();

    //update the state
    $state = 17;
    display($state, "leds2", "");
    unset($state);

    //show we aborted
    $state = 7;
    display($state, "leds2", "");
    unset($state);

    aborted();
}

//state 17
function aborted()
{
    //check if we can start again
    $startStop = getButtonPressed(0);
    if ($startStop == 1) {
        //start moving the sorter up, to start the calibration
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE0);

        //update the state
        $state = 0;
        display($state, "leds2", "");
        unset($state);

        initial();
    }
    unset($startStop);
    aborted();

}

function timerManage()
{
    global $location, $counter, $engines;

    //makes sure that when $counter >13 it will reset to 0
    mod(12, $counter);

    //get the voltage of output $location
    $voltage = getData('outputs', $location);

    //power up the output when it needs to
    if ($voltage > $counter) {
        $voltage = $location;
        $voltage = pow(2, $voltage);
        $engines += $voltage;
    }

    //check if we did all outputs
    if ($location > 7) {
        //actually output the result
        display($engines, "leds", "");

        //set the variables for the next run
        $engines = 0;
        $location = 0;
        $counter++;
        unset($location);

        //check if abort is pressed
        $abort = getButtonPressed(1);
        if ($abort == 1) {
            abort();//STOP THE MACHINE!
        }
        unset($abort);


        //and return to where we came from
        return;
    }

    //loop
    $location++;
    branch('timerManage');
}