/**
 * Sort of a simulation of the PP2 program
 * controlling the Fischer
 * Technik in order to sort black and white discs.
 *
 * @author Maarten Keet
 * @author Stefan van den Berg
 * @author Rolf Verschuuren
 * @author Wigger Boelens
 * @team Group 16
 * @since 13/3/2015
 */


class SoftwareDesign {
    //**@CODE**
    //inputs
    int $push, $startStop, $abort, $position,
            $colour;

    //variables
    int $state = 0;
    int $sleep = 0;
    int $temp = 0;
    int $location;
    int $counter = 0;
    int $engines;


    //constants
    final int TIMEMOTORDOWN = 30;
    final int BELTROUND = 2000;
    final int BELT = 1200;
    final int SORT = 850;
    final int LENSLAMPPOSITION = 5,
            LENSLAMPSORTER = 6,
            HBRIDGE0 = 0,
            HBRIDGE1 = 1,
            CONVEYORBELT = 3,
            FEEDERENGINE = 7,
            DISPLAY = 8,
            LEDSTATEINDICATOR = 9;

    public static void main(String args[]) {
        SoftwareDesign SoftwareDesign = new
                SoftwareDesign();


        //values for the data segment
        SoftwareDesign.initVar("outputs", 12);
        SoftwareDesign.initVar("stackpointer", 1);
        SoftwareDesign.initVar("offset", 1);

        //store the offset of the programm,this
        // is used in the interrupt
        SoftwareDesign.storeData(startofthecode,
                                 "offset", 0);

        //store the vlue of the stackpointer,so
        // we can clear the stack
        // easily
        SoftwareDesign.storeData(SP,
                                 "stackpointer",
                                 0);

        $counter = 0;


        //reset outputs
        SoftwareDesign.storeData(0, "outputs",
                                 SoftwareDesign
                                .HBRIDGE1);
        SoftwareDesign.storeData(0, "outputs",
                                 SoftwareDesign
                                .LENSLAMPPOSITION);
        SoftwareDesign.storeData(0, "outputs",
                                 SoftwareDesign
                                .LENSLAMPSORTER);
        SoftwareDesign.storeData(0, "outputs",
                                 SoftwareDesign
                                .LEDSTATEINDICATOR);
        SoftwareDesign.storeData(0, "outputs",
                                 SoftwareDesign
                                .DISPLAY);
        SoftwareDesign.storeData(0, "outputs",
                                 SoftwareDesign
                                .CONVEYORBELT);
        SoftwareDesign.storeData(0, "outputs",
                                 SoftwareDesign
                                .FEEDERENGINE);

        //start moving the sorter up
        SoftwareDesign.storeData(9, "outputs",
                                 SoftwareDesign
                                         .HBRIDGE0);

        //go to the first state and set the
        // value for the display
        SoftwareDesign.$state = 0;
        SoftwareDesign.initial();
    }

    //state0
    void initial() {
        setStackPointer(
                getData("stackpointer", 0));
        timerManage();
        //check if the sorter push button is
        // pressed
        $push = getButtonPressed(5);
        if ($push == 1) {
            //move the sorter down
            storeData(0, "outputs", HBRIDGE0);
            storeData(9, "outputs", HBRIDGE1);
            //update the state
            $state = 1;
            //reset sleep for the next function
            $sleep = 0;
            calibrateSorter();

        }
        //loop
        initial();
    }

    //state 1
    void calibrateSorter() {
        timerManage();
        //the sorter is now moving down, 
        //and we're waitng for it to reach the
        // bottom
        if ($sleep == TIMEMOTORDOWN * 1000) {
            //stop the sorter
            storeData(0, "outputs", HBRIDGE1);
            //update the state
            $state = 2;
            //reset sleep
            $sleep = 0;
            resting();
        }
        //loop
        $sleep++;
        calibrateSorter();
    }

    //state 2
    void resting() {
        timerManage();
        //the program waits for the user to
        // press the start/stop
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            //sleep so we don't go to the pause
            // immediatly
            sleep(2000);
            //power up the lights
            storeData(12, "outputs",
                      LENSLAMPPOSITION);
            storeData(12, "outputs",
                      LENSLAMPSORTER);
            //start up the belt and the feeder
            storeData(9, "outputs", CONVEYORBELT);
            storeData(5, "outputs", FEEDERENGINE);
            //set and start the countdown 
            setCountdown(BELTROUND + BELT);
            startCountdown();
            //update the state
            $state = 3;
            running();
        }
        //loop
        resting();
    }

    //state 3
    void running() {
        timerManage();
        //check if we need to pause
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            //stop the feeder engine
            storeData(0, "outputs", FEEDERENGINE);
            //set the timer
            setCountdown(BELT);
            //update the state
            $state = 9;
            runningTimer();
        }
        //check if a disk is at the position
        // detector
        $position = getButtonPressed(7);
        if ($position == 1) {
            //reset the countdown,because a
            // disk was detected
            setCountdown(BELTROUND + BELT);
            //update the state
            $state = 4;
            runningWait();
        }
        //loop
        running();
    }

    void runningWait() {
        timerManage();
        //check if we need to pause
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            //stop the feeder engine
            storeData(0, "outputs", FEEDERENGINE);
            //set the timer
            setCountdown(BELT);
            //update the state
            $state = 9;
            runningTimer();
        }
        //check if a disk is at the positiond
        // detector
        $position = getButtonPressed(7);
        if ($position == 1) {
            //reset the countdown,because a
            // disk was detected
            setCountdown(BELTROUND + BELT);
            //update the state
            $state = 5;
            runningTimerReset();
        }
        //check if a white disk is at the color
        // detector
        $colour = getButtonPressed(6);
        if ($colour == 1) {
            //move the sorter up
            storeData(9, "outputs", HBRIDGE0);
            //update the state
            $state = 6;
            motorUp();
        }
        //loop
        runningWait();
    }

    //state 5
    void runningTimerReset() {
        timerManage();
        //update the state
        $state = 5;
        runningWait();
    }

    //state 6
    void motorUp() {
        timerManage();
        //check if we need to pause
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            //stop the feeder engine
            storeData(0, "outputs", FEEDERENGINE);
            //set the timer 
            setCountdown(BELT);
            motorUpTimer();
        }
        //check if the sorter push button is
        // pressed
        $push = getButtonPressed(5);
        if ($push == 1) {
            //stop the engine,because it is in
            // the right position
            storeData(0, "outputs", HBRIDGE0);
            //update the state
            $state = 7;
            whiteWait();
        }
        //loop
        motorUp();
    }

    //state 7
    void whiteWait() {
        timerManage();
        //we are waiting for the white disk to
        // be sorted
        if ($sleep == SORT * 1000) {
            //start moving the sorter down
            storeData(9, "outputs", HBRIDGE1);
            //update the state
            $state = 8;
            //reset sleep for the next function
            $sleep = 0;
            motorDown();

        }
        //check if we need to pause
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            //stop the feeder engine
            storeData(0, "outputs", FEEDERENGINE);
            //set the timer
            setCountdown(BELT);
            //update the state
            $state = 11;
            whiteWaitTimer();
        }
        //loop
        $sleep++;
        whiteWait();
    }

    //state 8
    void motorDown() {
        timerManage();
        //the sorter is moving down
        if ($sleep == TIMEMOTORDOWN * 1000) {
            //stop the sorter
            storeData(0, "outputs", HBRIDGE1);
            //update the state
            $state = 9;
            //reset sleep for the next function
            $sleep = 0;
            runningWait();
        }
        //check if we need to pause
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            //stop the feeder engine
            storeData(0, "outputs", FEEDERENGINE);
            //set the timer
            setCountdown(BELT);
            motorDownTimer();
        }
        //loop
        $sleep++;
        motorDown();

    }

    //state 9
    void runningTimer() {
        timerManage();
        //update state
        $state = 13;
        runningStop();
    }

    //state 10
    void motorUpTimer() {
        timerManage();
        //update state
        $state = 14;
        motorUpStop();
    }

    //state 11
    void whiteWaitTimer() {
        timerManage();
        //update state
        $state = 15;
        whiteWaitStop();
    }

    //state 12
    void motorDownTimer() {
        timerManage();
        //update state
        $state = 16;
        motorDownStop();
    }

    //state 13
    void runningStop() {
        timerManage();
        //check if a white disk is at the
        // colour detector
        $colour = getButtonPressed(6);
        if ($colour == 1) {
            //move the sorter engine up
            storeData(9, "outputs", HBRIDGE0);
            //update the state
            $state = 10;
            motorUpStop();
        }
        //loop
        runningStop();
    }

    //state 14
    void motorUpStop() {
        timerManage();
        //check if the sorter push button is
        // pressed
        $push = getButtonPressed(5);
        if ($push == 1) {
            //stop the engien for the sorter
            storeData(0, "outputs", HBRIDGE0);
            //update the state
            $state = 11;
            whiteWaitStop();
        }
        motorUpStop();
    }

    //state 15
    void whiteWaitStop() {
        timerManage();
        //check if the white disk has been sorted
        if ($sleep == SORT * 1000) {
            //start moving the sorter down
            storeData(9, "outputs", HBRIDGE1);
            //update the state
            $state = 12;
            //reset the sleep for the next
            // function
            $sleep = 0;
            motorDown();
        }
        //loop
        $sleep++;
        whiteWaitStop();
    }

    //state 16
    void motorDownStop() {
        timerManage();
        //check if the sorter has moved down
        if ($sleep == TIMEMOTORDOWN) {
            //stop the engine of the sorter
            storeData(0, "outputs", HBRIDGE1);
            //update the state
            $state = 9;
            //reset sleep for the next function
            $sleep = 0;
            runningWait();
        }
        //loop
        $sleep++;
        motorDownStop();
    }

    //not a state 
    void timerInterrupt() {
        //show that we have timer interrupt
        $state = 18;
        //make the sorter move up
        storeData(9, "outputs", HBRIDGE0);
        //stop all other outputs
        storeData(0, "outputs", HBRIDGE1);
        storeData(0, "outputs", LENSLAMPPOSITION);
        storeData(0, "outputs", LENSLAMPSORTER);
        storeData(0, "outputs",
                  LEDSTATEINDICATOR);
        storeData(0, "outputs", DISPLAY);
        storeData(0, "outputs", CONVEYORBELT);
        storeData(0, "outputs", FEEDERENGINE);
        //make sure that the outputs get set
        // immediatly
        timerManage();
        //set the display to the state of initial
        $state = 0;

        initial();

    }

    void abort() {
        //stop all outputs
        storeData(0, "outputs", HBRIDGE0);
        storeData(0, "outputs", HBRIDGE1);
        storeData(0, "outputs", LENSLAMPPOSITION);
        storeData(0, "outputs", LENSLAMPSORTER);
        storeData(0, "outputs",
                  LEDSTATEINDICATOR);
        storeData(0, "outputs", DISPLAY);
        storeData(0, "outputs", CONVEYORBELT);
        storeData(0, "outputs", FEEDERENGINE);
        //make sure the outputs stop immediatly
        timerManage();
        //update the state to be correct in
        // aborted
        $state = 17;
        aborted();

    }

    //state 17
    void aborted() {
        timerManage();
        //check if we can start again
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            //start moving the sorter up for
            // calibration
            storeData(1, "outputs", HBRIDGE0);
            //update the state
            $state = 0;
            initial();
        }
        //loop
        aborted();

    }

    void timerManage() {


        //make sure that when counter can not
        // be higher than 12
        mod(13, $counter);
        //get the voltage of output $location
        int $voltage = getData("outputs",
                               $location);
        //power up the output when it needs to
        if ($voltage > $counter) {
            $engines += pow(2, $voltage);
        }
        //check if we are in a new itteration
        if ($counter == 0) {
            //set the first part of the display
            $temp = getData("state", 0);
            mod(10, $temp);
            display($temp, "display", "1");


        }
        //check if we are at the end of the
        // itteration
        if ($counter == 12) {
            //set the second part of the display;
            $temp = getData("state", 0);
            $temp = $temp / 10;
            mod(10, $temp);
            display($temp, "display", "01");

        }
        //check if we did all outputs
        if ($location > 7) {
            display($engines, "leds", "");
            //set the variables for the next run
            $engines = 0;
            $location = 0;
            $counter++;

            //check if abort is pressed
            $abort = getButtonPressed(1);
            if ($abort == 1) {
                abort();//stop the machine
            }
            return;
        }


        $location++;
        timerManage();
    }
}
