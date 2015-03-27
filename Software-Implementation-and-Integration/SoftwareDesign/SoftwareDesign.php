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
moveFunction('timerManage', 50);

//**DATA**
initVar('offset', 1);
initVar('stackPointer', 1);
initVar('outputs', 12);
initVar('state', 1);

//**CODE**
define('TIMEMOTORDOWN', 200); //how long the sorter takes to move down
define('BELT', 2000);//TODO: ????
define('BELTROUND', 2000);//Time for the belt to make a rotation
define('SORT', 1);//Clockticks to make a rotation
define('COUNTDOWN',40000);
//outputs
define('LENSLAMPPOSITION', 2);
define('LENSLAMPSORTER', 6);
define('HBRIDGE0', 0);
define('HBRIDGE1', 1);
define('CONVEYORBELT', 7);
define('FEEDERENGINE', 3);
define('DISPLAY', 8);
define('LEDSTATEINDICATOR', 9);

//not a state
function main()
{
    global $counter, $location;

    //store the offset of the program, this is used in the interrupt
    storeData(R5, 'offset', 0);
    //install the countdown
    installCountdown('timerInterrupt');

    //save the location of the stackPointer, so we can clear the stack
    storeData(SP, 'stackPointer', 0);

    //the variables that are the same throughout the program:
    $counter = 0;
    $location = 0;
    $sleep = 0;


    //stop everything
    $temp = 0;
    storeData($temp, 'outputs', HBRIDGE1);
    storeData($temp, 'outputs', LENSLAMPPOSITION);
    storeData($temp, 'outputs', LENSLAMPSORTER);
    storeData($temp, 'outputs', LEDSTATEINDICATOR);
    storeData($temp, 'outputs', DISPLAY);
    storeData($temp, 'outputs', CONVEYORBELT);
    storeData($temp, 'outputs', FEEDERENGINE);

    //sh0w the state
    $state = 0;
    storeData($state, 'state', 0);

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

    $temp = getData('stackPointer', 0);
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
        $temp = 1;
        storeData($temp, 'state', 0);
        unset($temp);

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
        storeData($state, 'state', 0);
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
    timerManage();

    //the program is now waiting for the user to press start/stop
    $startStop = getButtonPressed(0);
    if ($startStop == 1) {
        //sleep so we don't go to pause immediately


        //power up the lamps
        $temp = 12;
        storeData($temp, 'outputs', LENSLAMPPOSITION);
        unset($temp);
        timerManage();
        sleep(1000);
        $temp=12;
        storeData($temp, 'outputs', LENSLAMPSORTER);
        unset($temp);
        timerManage();
        sleep(2000);


        //start up the belt and feeder
        $temp = 9;
        storeData($temp, 'outputs', CONVEYORBELT);
        $temp = 5;
        storeData($temp, 'outputs', FEEDERENGINE);
        unset($temp);

        //set and start the countdown for the moment there are no more disks
        //this countdown will reset every time a disk is found
        //when it triggers, timerInterrupt will be ran.
        setCountdown(COUNTDOWN);
        startCountdown();

        //update the state
        $state = 3;
        storeData($state, 'state', 0);
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
        setCountdown(BELT*10);

        //update the state
        $state = 9;//TODO: echte state
        storeData($state, 'state', 0);
        unset($state);

        runningTimer();

    }
    unset($startStop);

    //check if a disk is at the position detector
    $position = getButtonPressed(7);
    if ($position == 1) {
        //reset the countdown, because a disk was just detected
        setCountdown(COUNTDOWN);

        //update the state
        $state = 4;
        storeData($state, 'state', 0);
        unset($state);
        runningWait();
    }
    unset($position);

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
        setCountdown(BELT*10);

        //update the state
        $state = 9;
        storeData($state, 'state', 0);
        unset($state);

        runningTimer();

    }
    unset($startStop);

    //check if a disk is at the position detector
    $position = getButtonPressed(7);
    if ($position == 0) {
        //reset the countdown, because a disk was just detected
        setCountdown(COUNTDOWN);

        //update state
        $state = 5;
        storeData($state, 'state', 0);
        unset($state);

        runningTimerReset();

    }
    unset($position);

    //check if a white disk is at the colour detector
    $colour = getButtonPressed(6);
    if ($colour == 1) {
        //move the sorter up so the disk goes to the correct box
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE0);

        //stop the feeder engine
        $temp = 0;
        storeData($temp, 'outputs', FEEDERENGINE);
        unset($temp);

        //update state
        $state = 6;
        storeData($state, 'state', 0);
        unset($state);

        motorUp();
    }
    unset($colour);

    //loop
    runningWait();
}

//state 5
function runningTimerReset()
{
    timerManage();

    //update state
    $state = 4;
    storeData($state, 'state', 0);
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
        setCountdown(BELT*10);

        //update the state
        $state = 10;
        storeData($state, 'state', 0);
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
        unset($temp);

        //update state
        $state = 7;
        storeData($state, 'state', 0);
        unset($state);

        //set sleep for the next function
        $sleep = 0;

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
        storeData($state, 'state', 0);
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
        setCountdown(BELT*10);

        //update the state
        $state = 11;
        storeData($state, 'state', 0);
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
        $temp=5;
        storeData($temp,'outputs',FEEDERENGINE);
        unset($temp);

        //update state
        $state = 4;
        storeData($state, 'state', 0);
        //reset sleep for the next function
        $sleep = 0;
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
        setCountdown(BELT*10);

        //update the state
        $state = 12;
        storeData($state, 'state', 0);
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
    storeData($state, 'state', 0);
    unset($state);

    runningStop();
}

//state 10
function motorUpTimer()
{
    timerManage();

    //update state
    $state = 14;
    storeData($state, 'state', 0);
    unset($state);

    motorUpStop();
}

//state 11
function whiteWaitTimer()
{
    timerManage();

    //update state
    $state = 15;
    storeData($state, 'state', 0);
    unset($state);

    whiteWaitStop();
}

//state 12
function motorDownTimer()
{
    timerManage();

    //update state
    $state = 16;
    storeData($state, 'state', 0);
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

        //stop the feeder engine
        $temp = 0;
        storeData($temp, 'outputs', FEEDERENGINE);
        unset($temp);

        //update state
        $state = 10;
        storeData($state, 'state', 0);
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
        //stop the engine of the sorter
        $temp = 0;
        storeData($temp, 'outputs', HBRIDGE0);
        unset($temp);

        //update state
        $state = 11;
        storeData($state, 'state', 0);
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
        $temp=0;
        storeData($temp,'outputs',FEEDERENGINE);
        unset($temp);

        //update state
        $state = 12;
        storeData($state, 'state', 0);
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
        storeData($state, 'state', 0);
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
    display($temp, 'display');

    //start moving the sorter up, to start the calibration
    $temp = 9;
    storeData($temp, 'outputs', HBRIDGE0);

    //stop the rest
    $temp = 0;
    storeData($temp, 'outputs', LENSLAMPPOSITION);
    storeData($temp, 'outputs', LENSLAMPSORTER);
    storeData($temp, 'outputs', LEDSTATEINDICATOR);
    storeData($temp, 'outputs', DISPLAY);
    storeData($temp, 'outputs', CONVEYORBELT);
    storeData($temp, 'outputs', FEEDERENGINE);


    //reset, because we will no longer be in timerInterrupt
    display($temp, 'display');
    unset($temp);

    //go back to initial
    $temp = getData('offset', 0);
    $temp2 = getFuncLocation('initial');
    $temp += $temp2;


    addStackPointer(2);
    pushStack($temp);
    addStackPointer(-1);
}

//not a state
function abort()
{
    //free some memory
    unset($engines);

    $temp = getData('stackPointer', 0);
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
    storeData($state, 'state', 0);


    //show we aborted
    $state = 7;
    display($state, 'leds2', 0);
    unset($state);

    aborted();
}

//state 17
function aborted()
{
    timerManage();
    //check if we can start again
    $startStop = getButtonPressed(0);
    if ($startStop == 1) {
        //start moving the sorter up, to start the calibration
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE0);
        unset($temp);

        //update the state
        $state = 0;
        storeData($state, 'state', 0);
        unset($state);

        initial();
    }
    unset($startStop);
    aborted();

}

//not a state
function timerManage()
{
    global $location, $counter, $engine, $sleep;

    if ($location == 0) {
        $engines = 0;
    }

    //makes sure that when $counter >12 it will reset to 0
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
    if ($location == 7) {
        //actually output the result
        sleep(1);
        display($engines, 'leds');


        unset($voltage);
        //check if abort is pressed
        $abort = getButtonPressed(1);
        if ($abort == 1) {
            abort();//STOP THE MACHINE!
        }
        unset($abort);

        //check if we are in a new iteration
        if ($counter == 6) {
            //set the first part of the display
            $temp = getData('state', 0);
            mod(10,$temp);
            display($temp, 'display',1);
            unset($temp);
        }
        //check if we are at the end of the iteration
        if ($counter == 11) {
            //set the second part of the display;
            pushStack($sleep);

            $temp = getData('state', 0);
            //get the last digit of the state
            //we have no variables left, so we use $sleep

            $sleep = $temp;
            mod(10, $sleep);
            $temp -= $sleep;
            $temp /=10;
            //display the last digit
            display($temp, 'display', 2);

            pullStack($sleep);
            unset($temp);
        }


        //set the variables for the next run
        $engines = 0;
        $location = 0;
        $counter++;

        //and return to where we came from
        return;
    }

    //loop
    $location++;
    branch('timerManage');
}