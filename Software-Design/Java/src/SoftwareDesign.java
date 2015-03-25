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

        //outputs
        SoftwareDesign.initVar("outputs",12);

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
        
        SoftwareDesign.installCountdown("timerInterrupt");

        SoftwareDesign.initial();
    }

    void initial() {
        timerManage();
        $push = getButtonPressed(5);
        if ($push == 1) {
            storeData(0,"outputs",HBRIDGE0);
            storeData(9,"outputs",HBRIDGE1);
            $state = 1;
            display($state, "leds2", "");
            calibrateSorter();

        }
        
        initial();
    }

    void calibrateSorter() {
        timerManage();
        if ($sleep == TIMEMOTORDOWN * 1000) {
            storeData(0,"outputs",HBRIDGE1);
            $state = 2;
            display($state, "leds2", "");
            resting();
            $sleep = 0;
        }
        $sleep++;
        calibrateSorter();
    }

    void resting() {
        timerManage();
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            sleep(2000);
            storeData(12,"outputs",LENSLAMPPOSITION);
            storeData(12,"outputs",LENSLAMPSORTER);
            storeData(9,"outputs",CONVEYORBELT);
            storeData(5,"outputs",FEEDERENGINE);
            setCountdown(BELTROUND+BELT);
            startCountdown();
            $state = 3;
            display($state, "leds2", "");
            running();
        }
        resting();
    }

    void running() {
        timerManage();
        $position = getButtonPressed(7);
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            storeData(0,"outputs",FEEDERENGINE);
            setCountdown(BELT);
            runningTimer();
        }
        if ($position == 1) {
            setCountdown(BELTROUND+BELT);

            $state = 4;
            display($state, "leds2", "");
            runningWait();
        }
        running();
    }

    void runningWait() {
        timerManage();
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            storeData(0,"outputs",FEEDERENGINE);
            setCountdown(BELT);
            runningTimer();
        }
         $position = getButtonPressed(7);
        if ($position==1) {
            setCountdown(BELTROUND+BELT);

            $state = 5;
            display($state, "leds2", "");
            runningTimerReset();
        }
        $colour = getButtonPressed(6);
        if ($colour==1) {
            storeData(9,"outputs",HBRIDGE0);

            setCountdown(SORT);

            $state = 6;
            display($state, "leds2", "");
            motorUp();
        }
        runningWait();
    }

    void runningTimerReset() {
        timerManage();
        $state=5;
        display($state, "leds2", "");
        runningWait();
    }

    void motorUp() {
        timerManage();
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            storeData(0,"outputs",FEEDERENGINE);
            setCountdown(BELT);
            motorUpTimer();
        }
        $push = getButtonPressed(5);
        if ($push == 1) {
            storeData(0,"outputs",HBRIDGE0);
            $state = 7;
            display($state, "leds2", "");
            whiteWait();
        }
        motorUp();
    }

    void whiteWait() {
        timerManage();
        if ($sleep == SORT * 1000) {
            storeData(9,"outputs",HBRIDGE1);
            $state = 8;
            display($state, "leds2", "");
            motorDown();
            $sleep = 0;
        }
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            storeData(0,"outputs",FEEDERENGINE);
            setCountdown(BELT);
            whiteWaitTimer();
        }
        $sleep++;
        whiteWait();
    }

    void motorDown() {
        timerManage();
        if ($sleep == TIMEMOTORDOWN * 1000) {
            storeData(0,"outputs",HBRIDGE1);
            $state = 9;
            $sleep = 0;
            display($state, "leds2", "");
            runningWait();
        }
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            storeData(0,"outputs",FEEDERENGINE);
            setCountdown(BELT);
            motorDownTimer();
        }
        $sleep++;
        motorDown();

    }

    void runningTimer() {
        timerManage();
        runningStop();
    }

    void motorUpTimer() {
        timerManage();
        motorUpStop();
    }

    void whiteWaitTimer() {
        timerManage();
        whiteWaitStop();
    }

    void motorDownTimer() {
        timerManage();
        motorDownStop();
    }


    void runningStop() {
        timerManage();
        $colour = getButtonPressed(6);
        if ($colour == 1) {
            storeData(9,"outputs",HBRIDGE0);
            $state = 10;
            display($state, "leds2", "");
            motorUpStop();
        }
        runningStop();
    }

    void motorUpStop() {
        timerManage();
        $push = getButtonPressed(5);
        if ($push == 1) {
            storeData(0,"outputs",HBRIDGE0);
            $state = 11;
            display($state, "leds2", "");
        }
        motorUpStop();
    }

    void whiteWaitStop() {
        timerManage();
        if ($sleep == SORT * 1000) {
            storeData(9,"outputs",HBRIDGE1);
            $state = 12;
            display($state, "leds2", "");
            motorDown();
            $sleep = 0;
        }

        $sleep++;
        whiteWaitStop();
    }

    void motorDownStop() {
        timerManage();
        if ($sleep == TIMEMOTORDOWN * 1000) {
            storeData(0,"outputs",HBRIDGE1);
            $state = 9;
            $sleep = 0;
            display($state, "leds2", "");
            runningWait();
        }
        $sleep++;
        motorDownStop();
    }

    void timerInterrupt() {
        timerManage();
        storeData(1,"outputs",HBRIDGE0);
        storeData(0,"outputs",HBRIDGE1);
        storeData(0,"outputs",LENSLAMPPOSITION);
        storeData(0,"outputs",LENSLAMPSORTER);
        storeData(0,"outputs",LEDSTATEINDICATOR);
        storeData(0,"outputs",DISPLAY);
        storeData(0,"outputs",CONVEYORBELT);
        storeData(0,"outputs",FEEDERENGINE);
        initial();

    }

    void abort() {
        timerManage();
        storeData(0,"outputs",HBRIDGE0);
        storeData(0,"outputs",HBRIDGE1);
        storeData(0,"outputs",LENSLAMPPOSITION);
        storeData(0,"outputs",LENSLAMPSORTER);
        storeData(0,"outputs",LEDSTATEINDICATOR);
        storeData(0,"outputs",DISPLAY);
        storeData(0,"outputs",CONVEYORBELT);
        storeData(0,"outputs",FEEDERENGINE);
        aborted();

    }

    void aborted() {
        timerManage();
        $startStop = getButtonPressed(0);
        if ($startStop == 1) {
            storeData(1,"outputs",HBRIDGE0);
            $state = 0;
            display($state, "leds2", "");
            initial();
        }
        aborted();

    }

    void timerManage() {
        
      
        
        $counter = $counter % 12;

        int $intensity=getData("outputs", $location);

        if ($intensity > $counter) {
            $engines += pow(2, $intensity);
        }

        if ($location > 7) {
            display($engines, "leds", "");
            $engines = 0;
            $location = 0;
            $counter++;
            
              //check if abort is set
            $abort=getButtonPressed(1);
            if($abort==1){
               abort();
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
