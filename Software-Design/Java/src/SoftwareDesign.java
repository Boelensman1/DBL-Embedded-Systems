/**
 * Sort of a simulation of the PP2 program controlling the Fischer Technik in order to sort black and white discs.
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
    int $push,$startStop,$abort,$position,$colour;
    
    //variables
    int $state = 0;
    int $sleep = 0;
    int $temp = 0;
    int $location;
    int $counter = 0;
    int $engines;
 
    
    //constants
    final int TIMEMOTORDOWN = 30;
    final int BELTROUND=2000;
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
        SoftwareDesign SoftwareDesign = new SoftwareDesign();
        
       
        //values for the data segment
        SoftwareDesign.initVar("outputs",12);
        SoftwareDesign.initVar("stackpointer", 1);
        SoftwareDesign.initVar("offset", 1);
        
         //store the offset of the programm,this is used in the interrupt
        SoftwareDesign.storeData(startofthecode,"offset",0);
        
        //store the vlue of the stackpointer,so we can clear the stack easily
        SoftwareDesign.storeData(SP,"stackpointer",0);
        
        $counter=0;
        
        
        //reset outputs
        SoftwareDesign.storeData(0, "outputs", SoftwareDesign.HBRIDGE1);
        SoftwareDesign.storeData(0, "outputs", SoftwareDesign.LENSLAMPPOSITION);
        SoftwareDesign.storeData(0, "outputs", SoftwareDesign.LENSLAMPSORTER);
        SoftwareDesign.storeData(0, "outputs", SoftwareDesign.LEDSTATEINDICATOR);
        SoftwareDesign.storeData(0, "outputs", SoftwareDesign.DISPLAY);
        SoftwareDesign.storeData(0, "outputs", SoftwareDesign.CONVEYORBELT);
        SoftwareDesign.storeData(0, "outputs", SoftwareDesign.FEEDERENGINE);

        //start moving the sorter up
        SoftwareDesign.storeData(9, "outputs", SoftwareDesign.HBRIDGE0);
        
        //go to the first state and set the value for the display
        $state=0;
        SoftwareDesign.initial();
    }
    
    //state0
    void initial() {
        setStackPointer(getData('stackpointer',0));
        timerManage();
        //check if the sorter push button is pressed
        $push = getButtonPressed(5);
        if ($push == 1) {
            //move the sorter down
            storeData(0,"outputs",HBRIDGE0);
            storeData(9,"outputs",HBRIDGE1);
            //update the state
            $state = 1;
            //reset sleep for the next function
            $sleep=0;
            calibrateSorter();

        }
        //loop
        initial();
    }
    
    //state 1
    void calibrateSorter() {
        timerManage();
        //the sorter is now moving down, 
        //and we're waitng for it to reach the bottom
        if ($sleep == TIMEMOTORDOWN * 1000) {
            //stop the sorter
            storeData(0,"outputs",HBRIDGE1);
            //update the state
            $state = 2;
            //reset sleep
            $sleep=0;
            resting();
        }
        //loop
        $sleep++;
        calibrateSorter();
    }
    //state 2
    void resting() {
        timerManage();
        //the program waits for the user to press the start/stop
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            //sleep so we don't go to the pause immediatly
            sleep(2000);
            //power up the lights
            storeData(12,"outputs",LENSLAMPPOSITION);
            storeData(12,"outputs",LENSLAMPSORTER);
            //start up the belt and the feeder
            storeData(9,"outputs",CONVEYORBELT);
            storeData(5,"outputs",FEEDERENGINE);
            //set and start the countdown 
            setCountdown(BELTROUND+BELT);
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
            storeData(0,"outputs",FEEDERENGINE);
            //set the timer
            setCountdown(BELT);
            //update the state
            $state=9;
            runningTimer();
        }
        //check if a disk is at the position detector
        $position = getButtonPressed(7);
        if ($position == 1) {
             //reset the countdown,because a disk was detected
            setCountdown(BELTROUND+BELT);
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
            storeData(0,"outputs",FEEDERENGINE);
            //set the timer
            setCountdown(BELT);
            //update the state
            $state=9;
            runningTimer();
        }
        //check if a disk is at the positiond detector
         $position = getButtonPressed(7);
        if ($position==1) {
            //reset the countdown,because a disk was detected
            setCountdown(BELTROUND+BELT);
            //update the state
            $state = 5;
            runningTimerReset();
        }
        //check if a white disk is at the color detector
        $colour = getButtonPressed(6);
        if ($colour==1) {
            //move the sorter up
            storeData(9,"outputs",HBRIDGE0);
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
        $state=5;
        runningWait();
    }

    //state 6
    void motorUp() {
        timerManage();
        //check if we need to pause
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            //stop the feeder engine
            storeData(0,"outputs",FEEDERENGINE);
            //set the timer 
            setCountdown(BELT);
            motorUpTimer();
        }
        //check if the sorter push button is pressed
        $push = getButtonPressed(5);
        if ($push == 1) {
            //stop the engine,because it is in the right position
            storeData(0,"outputs",HBRIDGE0);
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
        //we are waiting for the white disk to be sorted
        if ($sleep == SORT * 1000) {
            //start moving the sorter down
            storeData(9,"outputs",HBRIDGE1);
            //update the state
            $state = 8;
            //reset sleep for the next function
            $sleep=0;
            motorDown();
      
        }
        //check if we need to pause
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            //stop the feeder engine
            storeData(0,"outputs",FEEDERENGINE);
            //set the timer
            setCountdown(BELT);
            //update the state
            $state=11;
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
            storeData(0,"outputs",HBRIDGE1);
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
            storeData(0,"outputs",FEEDERENGINE);
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
        $state=13;
        runningStop();
    }
    
    //state 10
    void motorUpTimer() {
        timerManage();
        //update state
        $state=14;
        motorUpStop();
    }

    //state 11
    void whiteWaitTimer() {
        timerManage();
        //update state
        $state=15;
        whiteWaitStop();
    }

    //state 12
    void motorDownTimer() {
        timerManage();
        //update state
        $state=16;
        motorDownStop();
    }

    //state 13
    void runningStop() {
        timerManage();
        //check if a white disk is at the colour detector
        $colour = getButtonPressed(6);
        if ($colour == 1) {
            //move the sorter engine up
            storeData(9,"outputs",HBRIDGE0);
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
        //check if the sorter push button is pressed
        $push = getButtonPressed(5);
        if ($push == 1) {
            //stop the engien for the sorter
            storeData(0,"outputs",HBRIDGE0);
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
            storeData(9,"outputs",HBRIDGE1);
            //update the state
            $state = 12;
            //reset the sleep for the next function
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
        if ($sleep == TIMEMOTORDOWN ) {
            //stop the engine of the sorter
            storeData(0,"outputs",HBRIDGE1);
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
        $state=18;
        //make the sorter move up
        storeData(9,"outputs",HBRIDGE0);
        //stop all other outputs
        storeData(0,"outputs",HBRIDGE1);
        storeData(0,"outputs",LENSLAMPPOSITION);
        storeData(0,"outputs",LENSLAMPSORTER);
        storeData(0,"outputs",LEDSTATEINDICATOR);
        storeData(0,"outputs",DISPLAY);
        storeData(0,"outputs",CONVEYORBELT);
        storeData(0,"outputs",FEEDERENGINE);
        //make sure that the outputs get set immediatly
        timerManage();
        //set the display to the state of initial
        $state=0;
         
        initial();

    }

    void abort() {
        //stop all outputs
        storeData(0,"outputs",HBRIDGE0);
        storeData(0,"outputs",HBRIDGE1);
        storeData(0,"outputs",LENSLAMPPOSITION);
        storeData(0,"outputs",LENSLAMPSORTER);
        storeData(0,"outputs",LEDSTATEINDICATOR);
        storeData(0,"outputs",DISPLAY);
        storeData(0,"outputs",CONVEYORBELT);
        storeData(0,"outputs",FEEDERENGINE);
        //make sure the outputs stop immediatly
        timerManage();
        //update the state to be correct in aborted
        $state=17;
        aborted();

    }
    //state 17
    void aborted() {
        timerManage();
        //check if we can start again
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            //start moving the sorter up for calibration
            storeData(1,"outputs",HBRIDGE0);
            //update the state
            $state = 0;
            initial();
        }
        //loop
        aborted();

    }

    void timerManage() {
        
      
        //make sure that when counter can not be higher than 12
        mod(13,$counter);
        //get the voltage of output $location
        int $voltage=getData("outputs", $location);
        //power up the output when it needs to
        if ($voltage > $counter) {
            $engines += pow(2, $voltage);
        }
        //check if we are in a new itteration
        if($counter==0){
            //set the first part of the display
            $temp=getData("state",0);
            mod(10,$temp);        
            display($temp,"display","1");

        
        }
        //check if we are at the end of the itteration
        if($counter==12){
         //set the second part of the display;
        $temp=getData("state",0);
        $temp=$temp/10;
        mod(10,$temp);
        display($temp,"display","01");            
            
        }
        //check if we did all outputs
        if ($location > 7) {
            display($engines, "leds", "");
            //set the variables for the next run
            $engines = 0;
            $location = 0;
            $counter++;
            
              //check if abort is pressed
            $abort=getButtonPressed(1);
            if($abort==1){
               abort();//stop the machine
             }
            return;
        }


        $location++;
        timerManage();
    }

    /**
     * Store a value in the ram.
     * <p/>
     * Example: storeRam($location,$value)
     *
     * @param $location The location (a variable) to store the value in the ram
     * @param $value    The value to store, needs to be a variable
     */
    public void storeRam(int $location, int $value) {
    }

    /**
     * Get a value from the ram.
     * <p/>
     * Example: $value=getRam($location)
     *
     * @param $location The location (a variable) where the value is stored
     */
    public void getRam(int $location) {
    }

    /**
     * Display something on either the display or the leds
     * <p/>
     * Possible values for $onwhat:
     * leds: the leds at the top
     * leds2: the leds to the right
     * display: the display
     * Example:
     * display($value, 'display',000100)
     * This will display $value in the middle of the display
     *
     * @param $what     what to display
     * @param $onWhat   on what to display
     * @param $location Where to show the value when using the display,
     *                  defaults to the right position
     */
    public void display(int $what, String $onWhat, String $location) {
    }

    /**
     * Get the power of a number
     * <p/>
     * Example: $temp=pow(2,$power)
     * This will make $temp equal to 2^$power
     *
     * @param $number the number to power
     * @param $power  the power value
     */
    public int pow(int $number, int $power) {
        return 0;
    }

    /**
     * Take the mod of a number
     * <p/>
     * Example: mod($variable,2)
     * This will return the mod 2 of $variable
     *
     * @param $what     modulo what
     * @param $variable variable to modulo over
     */
    public void mod(int $what, int $variable) {
    }

    /**
     * Get button or analog input
     * <p/>
     * When you just want hte input of 1 button, use getButtonPressed instead
     * Example: getInput($variable,'analog')
     * This will put the value of the analog into $variable
     *
     * @param $writeTo Variable to write the input to
     * @param $type    Type of input, possible values are: buttons, analog
     */
    public void getInput(int $writeTo, String $type) {
    }

    /**
     * Check if a button is pressed
     * <p/>
     * Puts the result into R5
     * Example: $pressed=getButtonPressed($location);
     *
     * @param $button Which button to check (input a variable)
     */
    public int getButtonPressed(int $button) {
        return 1;
    }

    /**
     * Install the countdown
     * <p/>
     * Do not forget to add returnt at the end of the interrupt function
     * Example: installCountdown('timerInterrupt')
     * This will install the countdown.
     * In this example when the timer interrupt triggers,
     * the public void timerInterrupt is ran.
     *
     * @param $functionName The name of the public void where the timer should go to
     */
    public void installCountdown(String $functionName) {
    }

    /**
     * Start the countdown.
     */
    public void startCountdown() {
    }

    /**
     * Push a variable to the stack
     *
     * @param $variable the variable to push to the stack
     */
    public void pushStack(String $variable) {
    }

    /**
     * Pull a from the stack
     *
     * @param $variable the variable where the pulled variable is put into
     */
    public void pullStack(String $variable) {
    }

    /**
     * Set the timer interrupt to a value.
     * <p/>
     * It will first reset the timer to 0.
     * Example: setCountdown(10)
     * This will interrupt the program after 10 timer ticks
     *
     * @param $timer how long the timer should wait, in timer ticks
     */
    public void setCountdown(int $timer) {
    }


    /**
     * Get data
     * <p/>
     * Use offset 0 when it is just a single value.
     * Example: $data=getData('data',1)
     * This will put the value of the data segment "data" at position 1, into $data.
     *
     * @param $location The location where the variable is stored
     * @param $offset   The offset of the location
     */
    public int getData(String $location, int $offset) {
        return 1;
    }

    /**
     * Store data
     * <p/>
     * Use offset 0 when it is just a single value.
     * Example: storeData($data,'data',1)
     * This will put the value of $data into the data segment "data" at position 1
     *
     * @param $variable The variable to store
     * @param $location The name of the location where the variable is stored
     * @param $offset   The offset of the location
     */
    public void storeData(int $variable, String $location, int $offset) {
    }


    /**
     * Pause the program
     * <p/>
     * Example:
     * sleep(10)
     * This will sleep for 10 clockticks
     *
     * @param $howLong How long to sleep
     */
    public void sleep(int $howLong) {
    }


    /**
     * Init a variable that is used in that data segment
     * <p/>
     * Example:
     * initVar("outputs", 10);
     * This will init the data segement outputs and reserve 10 spots
     * If you just want to save a single variable, set $places to 1
     *
     * @param $variable The name of the variable
     * @param $places   How long the array is
     */
    public void initVar(String $variable, int $places) {
    }


    /**
     * branch to a function
     * <p/>
     * Example:
     * branch('test');
     * This will branch to the public void test
     *
     * @param $branchTO where to branch to
     */
    public void branch(String $branchTO) {
    }
}
