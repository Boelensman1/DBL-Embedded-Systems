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
<<<<<<< HEAD

//**DATA**
initVar('outputs', 12);

//**CODE**
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
=======
moveFunction('timerManage', 50);

//**DATA**
initVar('offset', 1);
initVar('stackPointer', 1);
initVar('outputs', 12);
initVar('state', 1);

//**CODE**
define('TIMEMOTORDOWN', 170); //how long the sorter takes to move down
define('BELT', 2000);//TODO: ????
define('BELTROUND', 2000);//Time for the belt to make a rotation
define('SORT', 200);//Clockticks to make a rotation
define('COUNTDOWN', 30000);
//outputs
define('LENSLAMPPOSITION', 2);
define('LENSLAMPSORTER', 6);
define('HBRIDGE0', 0);
define('HBRIDGE1', 1);
define('CONVEYORBELT', 7);
define('FEEDERENGINE', 3);
define('FEEDERENGINEVOLTAGE',6);
define('HBRIDGEVOLTAGE',9);
>>>>>>> dev
define('DISPLAY', 8);
define('LEDSTATEINDICATOR', 9);

//not a state
function main()
{
<<<<<<< HEAD
    global $counter;

    //install the countdown
    installCountdown('timerInterrupt');

    //the variables that are the same throughout the program:
    $counter = 0;

=======
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
>>>>>>> dev
    $temp = 0;
    storeData($temp, 'outputs', HBRIDGE1);
    storeData($temp, 'outputs', LENSLAMPPOSITION);
    storeData($temp, 'outputs', LENSLAMPSORTER);
    storeData($temp, 'outputs', LEDSTATEINDICATOR);
    storeData($temp, 'outputs', DISPLAY);
    storeData($temp, 'outputs', CONVEYORBELT);
    storeData($temp, 'outputs', FEEDERENGINE);
<<<<<<< HEAD
    $state = 0;
    display($state, "leds2", "");

    //set HBridge so the sorter starts moving up
    $temp = 9;
=======

    //sh0w the state
    $state = 0;
    storeData($state, 'state', 0);

    //set HBridge so the sorter starts moving up
    $temp = HBRIDGEVOLTAGE;
>>>>>>> dev
    storeData($temp, 'outputs', HBRIDGE0);
    unset($temp, $state);

    //go to the first state
    initial();
}

//state 0
function initial()
{
    global $sleep;
<<<<<<< HEAD
=======
    //disable the lights on the right hand side
    $temp = 0;
    display($temp, 'leds2');

    $temp = getData('stackPointer', 0);
    setStackPointer($temp);

>>>>>>> dev
    timerManage();

    //check if the sorter push button is pressed
    $push = getButtonPressed(5);
    if ($push == 1) {
        //move sorter down
        $temp = 0;
        storeData($temp, 'outputs', HBRIDGE0);
<<<<<<< HEAD
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE1);

        //update state
        $state = 1;
        display($state, "leds2", "");
        unset($state);
=======
        $temp = HBRIDGEVOLTAGE;
        storeData($temp, 'outputs', HBRIDGE1);

        //update state
        $temp = 1;
        storeData($temp, 'state', 0);
        unset($temp);
>>>>>>> dev

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
<<<<<<< HEAD
        display($state, "leds2", "");
=======
        storeData($state, 'state', 0);
>>>>>>> dev
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
<<<<<<< HEAD
    unset ($sleep);
=======
>>>>>>> dev
    timerManage();

    //the program is now waiting for the user to press start/stop
    $startStop = getButtonPressed(0);
    if ($startStop == 1) {
        //sleep so we don't go to pause immediately
<<<<<<< HEAD
        sleep(2000);
=======

>>>>>>> dev

        //power up the lamps
        $temp = 12;
        storeData($temp, 'outputs', LENSLAMPPOSITION);
<<<<<<< HEAD
        storeData($temp, 'outputs', LENSLAMPSORTER);
=======
        unset($temp);
        timerManage();
        sleep(1000);
        $temp = 12;
        storeData($temp, 'outputs', LENSLAMPSORTER);
        unset($temp);
        timerManage();
        sleep(2000);

>>>>>>> dev

        //start up the belt and feeder
        $temp = 9;
        storeData($temp, 'outputs', CONVEYORBELT);
<<<<<<< HEAD
        $temp = 5;
        storeData($temp, 'outputs', FEEDERENGINE);
=======
        $temp = FEEDERENGINEVOLTAGE;
        storeData($temp, 'outputs', FEEDERENGINE);
        unset($temp);
>>>>>>> dev

        //set and start the countdown for the moment there are no more disks
        //this countdown will reset every time a disk is found
        //when it triggers, timerInterrupt will be ran.
<<<<<<< HEAD
        setCountdown(BELTROUND + BELT);
=======
        setCountdown(COUNTDOWN);
>>>>>>> dev
        startCountdown();

        //update the state
        $state = 3;
<<<<<<< HEAD
        display($state, "leds2", "");
=======
        storeData($state, 'state', 0);
>>>>>>> dev
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
<<<<<<< HEAD
        setCountdown(BELT);

        //update the state
        $state = 9;//TODO: echte state
        display($state, "leds2", "");
=======
        setCountdown(BELT * 10);

        //update the state
        $state = 9;//TODO: echte state
        storeData($state, 'state', 0);
>>>>>>> dev
        unset($state);

        runningTimer();

    }
    unset($startStop);

    //check if a disk is at the position detector
    $position = getButtonPressed(7);
<<<<<<< HEAD
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
=======
    if ($position == 0) {
        //reset the countdown, because a disk was just detected
        setCountdown(COUNTDOWN);

        //update the state
        $state = 4;
        storeData($state, 'state', 0);
        unset($state);
        runningWait();
    }
    unset($position);
>>>>>>> dev

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
<<<<<<< HEAD
        setCountdown(BELT);

        //update the state
        $state = 9;//TODO: echte state
        display($state, "leds2", "");
=======
        setCountdown(BELT * 10);

        //update the state
        $state = 9;
        storeData($state, 'state', 0);
>>>>>>> dev
        unset($state);

        runningTimer();

    }
<<<<<<< HEAD
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
=======
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
>>>>>>> dev

        runningTimerReset();

    }
<<<<<<< HEAD
    unset ($position);
=======
    unset($position);
>>>>>>> dev

    //check if a white disk is at the colour detector
    $colour = getButtonPressed(6);
    if ($colour == 1) {
        //move the sorter up so the disk goes to the correct box
<<<<<<< HEAD
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE0);
=======
        $temp = HBRIDGEVOLTAGE;
        storeData($temp, 'outputs', HBRIDGE0);

        //stop the feeder engine
        $temp = 0;
        storeData($temp, 'outputs', FEEDERENGINE);
>>>>>>> dev
        unset($temp);

        //update state
        $state = 6;
<<<<<<< HEAD
        display($state, "leds2", "");
=======
        storeData($state, 'state', 0);
>>>>>>> dev
        unset($state);

        motorUp();
    }
<<<<<<< HEAD
    unset ($colour);
=======
    unset($colour);
>>>>>>> dev

    //loop
    runningWait();
}

//state 5
function runningTimerReset()
{
    timerManage();

    //update state
    $state = 4;
<<<<<<< HEAD
    display($state, "leds2", "");
=======
    storeData($state, 'state', 0);
>>>>>>> dev
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
<<<<<<< HEAD
        setCountdown(BELT);

        //update the state
        $state = 10;
        display($state, "leds2", "");
=======
        setCountdown(BELT * 10);

        //update the state
        $state = 10;
        storeData($state, 'state', 0);
>>>>>>> dev
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
<<<<<<< HEAD

        //update state
        $state = 7;
        display($state, "leds2", "");
        unset($state);

        //set sleep for the next function
        $sleep=0;
=======
        unset($temp);

        //update state
        $state = 7;
        storeData($state, 'state', 0);
        unset($state);

        //set sleep for the next function
        $sleep = 0;
>>>>>>> dev

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
<<<<<<< HEAD
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE1);
        unset($temp);

        //update state
        $state = 8;
        display($state, "leds2", "");
=======
        $temp = HBRIDGEVOLTAGE;
        storeData($temp, 'outputs', HBRIDGE1);
        unset($temp);

        //make sure the timerinterrupt is correct
        setCountdown(COUNTDOWN);

        //update state
        $state = 8;
        storeData($state, 'state', 0);
>>>>>>> dev
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
<<<<<<< HEAD
        setCountdown(BELT);

        //update the state
        $state = 11;
        display($state, "leds2", "");
=======
        setCountdown(BELT * 10);

        //update the state
        $state = 11;
        storeData($state, 'state', 0);
>>>>>>> dev
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

<<<<<<< HEAD
=======

>>>>>>> dev
    //the sorter is moving down, we are waiting for that to complete
    if ($sleep == TIMEMOTORDOWN) {
        //stop the sorter, its where it should be
        $temp = 0;
        storeData($temp, 'outputs', HBRIDGE1);
<<<<<<< HEAD
=======
        $temp = FEEDERENGINEVOLTAGE;
        storeData($temp, 'outputs', FEEDERENGINE);
>>>>>>> dev
        unset($temp);

        //update state
        $state = 4;
<<<<<<< HEAD
        display($state, "leds2", "");
        unset($state);

        unset($sleep);//TODO: nakijken of het klopt dat sleep niet meer nodig is
=======
        storeData($state, 'state', 0);
        //reset sleep for the next function
        $sleep = 0;
        unset($state);

>>>>>>> dev
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
<<<<<<< HEAD
        setCountdown(BELT);

        //update the state
        $state = 12;
        display($state, "leds2", "");
=======
        setCountdown(BELT * 10);

        //update the state
        $state = 12;
        storeData($state, 'state', 0);
>>>>>>> dev
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
<<<<<<< HEAD
    display($state, "leds2", "");
=======
    storeData($state, 'state', 0);
>>>>>>> dev
    unset($state);

    runningStop();
}

//state 10
function motorUpTimer()
{
    timerManage();

    //update state
    $state = 14;
<<<<<<< HEAD
    display($state, "leds2", "");
=======
    storeData($state, 'state', 0);
>>>>>>> dev
    unset($state);

    motorUpStop();
}

//state 11
function whiteWaitTimer()
{
    timerManage();

    //update state
    $state = 15;
<<<<<<< HEAD
    display($state, "leds2", "");
=======
    storeData($state, 'state', 0);
>>>>>>> dev
    unset($state);

    whiteWaitStop();
}

//state 12
function motorDownTimer()
{
    timerManage();

    //update state
    $state = 16;
<<<<<<< HEAD
    display($state, "leds2", "");
=======
    storeData($state, 'state', 0);
>>>>>>> dev
    unset($state);

    motorDownStop();
}

//state 13
function runningStop()
{
    timerManage();

<<<<<<< HEAD
    //
    $colour = getButtonPressed(6);
    if ($colour == 1) {
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE0);
        $state = 10;
        display($state, "leds2", "");
        unset($colour, $state);
        motorUpStop();
    }
=======
    //check if a white disk is at the colour detector
    $colour = getButtonPressed(6);
    if ($colour == 1) {
        //stop the sorter engine, because its at its highest position
        $temp = HBRIDGEVOLTAGE;
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
>>>>>>> dev
    runningStop();
}

//state 14
function motorUpStop()
{
    timerManage();

    //check if the sorter push button is pressed
    $push = getButtonPressed(5);
    if ($push == 1) {
<<<<<<< HEAD
        $temp = 0;
        storeData($temp, 'outputs', HBRIDGE0);
        $state = 11;
        display($state, "leds2", "");
        whiteWaitStop();
        unset($push, $state);
    }
=======
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
>>>>>>> dev
    motorUpStop();
}

//state 15
function whiteWaitStop()
{
    global $sleep;
    timerManage();
<<<<<<< HEAD
    if ($sleep == SORT * 1000) {
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE1);
        $state = 12;
        display($state, "leds2", "");
        $sleep = 0;
        motorDownStop();
        unset($state);
    }

=======

    //check if the white disk has been sorted
    if ($sleep == SORT) {
        //it has, so lets start moving the sorter down
        $temp = HBRIDGEVOLTAGE;
        storeData($temp, 'outputs', HBRIDGE1);
        $temp = 0;
        storeData($temp, 'outputs', FEEDERENGINE);
        unset($temp);

        //update state
        $state = 12;
        storeData($state, 'state', 0);
        unset($state);

        $sleep = 0;
        motorDownStop();
    }

    //loop
>>>>>>> dev
    $sleep++;
    whiteWaitStop();
}

//state 16
function motorDownStop()
{
    global $sleep;
    timerManage();
<<<<<<< HEAD
    if ($sleep == TIMEMOTORDOWN) {
        $temp = 0;
        storeData($temp, 'outputs', HBRIDGE1);
        $state = 9;
        $sleep = 0;
        display($state, "leds2", "");
        unset($state);
        runningStop();
    }
=======

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
>>>>>>> dev
    $sleep++;
    motorDownStop();
}

//not a state
function timerInterrupt()
{
<<<<<<< HEAD
    $temp = 5;
    display($temp, "leds2", "");

    timerManage();
    $temp = 9;
    storeData($temp, 'outputs', HBRIDGE1);
    $temp = 0;
    storeData($temp, 'outputs', HBRIDGE0);
=======
    timerManage();
    //show that we are in the timer interrupt
    $temp = 5;
    display($temp, 'display');

    //start moving the sorter up, to start the calibration
    $temp = HBRIDGEVOLTAGE;
    storeData($temp, 'outputs', HBRIDGE0);

    //stop the rest
    $temp = 0;
>>>>>>> dev
    storeData($temp, 'outputs', LENSLAMPPOSITION);
    storeData($temp, 'outputs', LENSLAMPSORTER);
    storeData($temp, 'outputs', LEDSTATEINDICATOR);
    storeData($temp, 'outputs', DISPLAY);
    storeData($temp, 'outputs', CONVEYORBELT);
    storeData($temp, 'outputs', FEEDERENGINE);

<<<<<<< HEAD
    initial();

}


function abort()
{

    timerManage();
=======

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

    //prevent timerinterrupt
    setCountdown(1000);
    $temp = getData('stackPointer', 0);
    setStackPointer($temp);

    //stop everything
>>>>>>> dev
    $temp = 0;
    storeData($temp, 'outputs', HBRIDGE1);
    storeData($temp, 'outputs', HBRIDGE0);
    storeData($temp, 'outputs', LENSLAMPPOSITION);
    storeData($temp, 'outputs', LENSLAMPSORTER);
    storeData($temp, 'outputs', LEDSTATEINDICATOR);
    storeData($temp, 'outputs', DISPLAY);
    storeData($temp, 'outputs', CONVEYORBELT);
    storeData($temp, 'outputs', FEEDERENGINE);
<<<<<<< HEAD
    aborted();

}

function aborted()
{

    timerManage();
    $startStop = getButtonPressed(0);
    if ($startStop == 1) {
        $temp = 9;
        storeData($temp, 'outputs', HBRIDGE0);
        $state = 0;
        display($state, "leds2", "");
        initial();
        unset($state, $startStop);
    }
=======
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
    //prevent timer interrupt
    setCountdown(1000);
    timerManage();
    //check if we can start again
    $startStop = getButtonPressed(0);
    if ($startStop == 1) {
        //start moving the sorter up, to start the calibration
        $temp = HBRIDGEVOLTAGE;
        storeData($temp, 'outputs', HBRIDGE0);
        unset($temp);

        //update the state
        $state = 0;
        storeData($state, 'state', 0);
        unset($state);

        initial();
    }
    unset($startStop);
>>>>>>> dev
    aborted();

}

<<<<<<< HEAD
function timerManage()
{
    global $location, $counter, $engines;
    mod(12, $counter); //makes sure that when $counter >13 it will reset to 0
    $temp = getData('outputs', $location);
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

=======
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
            mod(10, $temp);
            display($temp, 'display', 1);
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
            $temp /= 10;
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
>>>>>>> dev
    $location++;
    branch('timerManage');
}