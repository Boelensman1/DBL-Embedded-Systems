/**
 * Sort of a simulation of the PP2 program controlling the Fischer Technik in order to sort black and white discs. 
 * @team Group 16
 * @author Maarten Keet
 * @since 13/3/2015
 */


class SoftwareDesign {
  
  
  //**@DATA**
  //outputs 
 int[] $outputs=new int[8];
 
  //**@CODE**
  //inputs
  Boolean $startStop,$abort,$push,$position,$colour;
  int $timer;
  //variables
  int $state=0;
  int $sleep=0;
          
  //constants
  final int $timeMotorDown=-1;
  final int $belt=-1;
  final int $sort=-1;
  final int $timerSort=-1;
  final int $lensLampPosition=0,$lensLampSorter=1,$hbridge1=2,$hbridge0=3, $conveyorBelt=4,$feederEngine=5,$display=6,$ledStateIndicator=7; 
          
  

  
  void initial() {
    timerManage($outputs);
    $push=buttonPressed(5);
    if($push==true){
        $outputs[$hbridge0]=0;
        $outputs[$hbridge1]=9;
        $state = 1;
        display($state,"leds2","");
        calibrateSorter();
       
    }
    initial();
  }
  void calibrateSorter(){
      timerManage($outputs);
      if($sleep==$timeMotorDown*1000){
         $outputs[$hbridge1]=9;     
         $state=2;        
         display($state,"leds","");
         resting();
         $sleep=0;
      }
      $sleep++;
      calibrateSorter();
  }
  
  void resting(){
      timerManage($outputs);
      $startStop=buttonPressed(0);
      if($startStop==true){
          $outputs[$lensLampPosition]=12;
          $outputs[$lensLampSorter]=12;
          $outputs[$conveyorBelt]=9;
          $outputs[$feederEngine]=5;
          setTimer(2+$belt);
          
          
          $state=3;
          display($state,"leds2","");
          running();
      }
      resting();
  }
  
  void running(){
      timerManage($outputs);
      $position=buttonPressed(7);
      $startStop=buttonPressed(0);
      if($startStop=true){
          $outputs[$feederEngine]=0;
          setTimer($belt);
          runningTimer();
      }
      if($position=true){
          setTimer(2+$belt);
          
          $state=4;
          display($state,"leds2","");
          runningWait();
      }
      running();
  }
  
  void runningWait(){
      timerManage($outputs);
      $position=buttonPressed(7);
      $colour=buttonPressed(6);
      $startStop=buttonPressed(0);
      if($startStop=true){
          $outputs[$feederEngine]=0;
          setTimer($belt);
          runningTimer();
      }
      if($position){
           setTimer(2+$belt);        
          
          $state=5;
          display($state,"leds2","");
          runningTimerReset();
      }
      if($colour){
          $outputs[$hbridge0]=9;
          
          setTimer($sort);
          
          $state=6;
          display($state,"leds2","");
          motorUp();          
      }
      runningWait();
  }
  
  void runningTimerReset(){
      timerManage($outputs);
      runningWait();
  }
  
  void motorUp(){
      timerManage($outputs);
      $push=buttonPressed(7);
      $startStop=buttonPressed(0);
      if($startStop=true){
          $outputs[$feederEngine]=0;
          setTimer($belt);
          motorUpTimer();          
      }
      if($push=true){
          $outputs[$hbridge0]=0;
          $state=7;
          display($state,"leds2","");
          whiteWait();
      }
  }
  
 void whiteWait(){
      timerManage($outputs);
      if($sleep==$timerSort*1000){
     $outputs[$hbridge1]=9;
      $state=8;
      display($state,"leds2","");
      motorDown();
      $sleep=0;
      }
      $startStop=buttonPressed(0);
      if($startStop=true){
          $outputs[$feederEngine]=0;
          setTimer($belt);
          whiteWaitTimer();
      }
      $sleep++;
      whiteWait();
  }
 
 void motorDown(){
     timerManage($outputs);
     if($sleep==$timeMotorDown*1000){
         $outputs[$hbridge1]=0;
         $state=9;
         $sleep=0;
         display($state,"leds2","");
         runningWait();
     }
     $startStop=buttonPressed(0);
     if($startStop=true){
          $outputs[$feederEngine]=0;
          setTimer($belt);
          motorDownTimer();
      }
     $sleep++;
     motorDown();
          
 }
 
 void runningTimer(){
     timerManage($outputs);
     runningStop();
 }
 
 void motorUpTimer(){
     timerManage($outputs);
     motorUpStop();
 }
 
 void whiteWaitTimer(){
     timerManage($outputs);
     whiteWaitStop();
 }
 
 void motorDownTimer(){
     timerManage($outputs);
     motorDownStop();
 }
 
 
 void runningStop(){
     timerManage($outputs);
     $colour=buttonPressed(6);
     if($colour==true){
         $outputs[$hbridge0]=9;
         $state=10;
         display($state,"leds2","");
         motorUpStop();
     }
     runningStop();
 }
 
 void motorUpStop(){
     timerManage($outputs);
     $push=buttonPressed(5);
     if($push==true){
         $outputs[$hbridge0]=0;
          $state=11;
          display($state,"leds2","");
     }
     motorUpStop();
 }
 
 void whiteWaitStop(){
      timerManage($outputs);
      if($sleep==$timerSort*1000){
      $outputs[$hbridge1]=9;
      $state=12;
      display($state,"leds2","");
      motorDown();
      $sleep=0;
      }
      
      $sleep++;
      whiteWait();
 }
 
 void motorDownStop(){
     timerManage($outputs);
     if($sleep==$timeMotorDown*1000){
         $outputs[$hbridge1]=0;
         $state=9;
         $sleep=0;
         display($state,"leds2","");
         runningWait();
     }
     $sleep++;
     motorDown();
 }
 
 void timerInterrupt(){
     timerManage($outputs);
     $outputs[$hbridge0]=1;
     $outputs[$hbridge1]=0;
     $outputs[$lensLampPosition]=0;
     $outputs[$lensLampSorter]=0;
     $outputs[$ledStateIndicator]=0;
     $outputs[$display]=0;
     $outputs[$conveyorBelt]=0;
     $outputs[$feederEngine]=0;
     initial();
 
 }
 
 void abort(){
     timerManage($outputs);
     $outputs[$hbridge0]=0;
     $outputs[$hbridge1]=0;
     $outputs[$lensLampPosition]=0;
     $outputs[$lensLampSorter]=0;
     $outputs[$ledStateIndicator]=0;
     $outputs[$display]=0;
     $outputs[$conveyorBelt]=0;
     $outputs[$feederEngine]=0;
     aborted();
     
 }
 
 void aborted(){
     timerManage($outputs);
     $startStop=buttonPressed(0);
     if($startStop=true){
         $outputs[$hbridge0]=1;
         $state=0;
         display($state,"leds2","");
         initial();
     }
     aborted();
 
 }

 void timerManage(int[] $outputs){
  $location = $location % 7;
  $counter = $counter % 12;
  
  if($counter < $outputs[$location]){
   $engines = $engines + pow(2, $location);
  }
  
  if($location >= 7){
   display($engines, "leds");
   $engines = 0;
   return;
  }
  
  $location++;
  $counter++;
  TimerManage($outputs);
  return;
 }
 
 
  
  public static void main( String args[] ) {
    new SoftwareDesign().initial();
  }



//public voids for autocorrect
public void sleep(int $seconds)
{}

/**
 * Store a value in the ram.
 *
 * Example: _storeRam($location,$value)
 *
 * @param variable $location The location to store the value in the ram
 * @param variable $value    The value to store
 *
 * @return void
 */
public void _storeRam(int $location,int $value)
{
}

/**
 * Get a value from the ram.
 *
 * Example: $value=_getRam($location)
 *
 * @param variable $location The location where the value is stored
 *
 * @return void
 */
public void _getRam( int $location)
{
}

/**
 * Display something on either the display or the leds
 *
 * Possible values for $onwhat:
 * leds: the leds at the top
 * leds2: the leds to the right
 * display: the display
 * Example:
 * display($value, 'display',000100)
 * This will display $value in the middle of the display
 *
 * @param variable $what     what to display
 * @param variable $onWhat   on what to display
 * @param string   $location Where to show the value when using the display,
 *                           defaults to the right position
 *
 * @return void
 */
public void display(int $what, String $onWhat, String $location)
{
}

/**
 * Take the mod of a number
 *
 * Example: modulo($variable,2)
 * This will return the mod 2 of $variable
 *
 * @param variable $variable variable to modulo over
 * @param int      $what     modulo what
 *
 * @return void
 */
public void modulo(int $variable, int $what)
{
}

/**
 * Get button or analog input
 *
 * When you just want hte input of 1 button, use buttonPressed instead
 * Example: getInput($variable,'analog')
 * This will put the value of the analog into $variable
 *
 * @param variable $writeTo Variable to write the input to
 * @param string   $type    Type of input, possible values are: buttons, analog
 *
 * @return void
 */
public void getInput(int $writeTo, int $type)
{
}

/**
 * Check if a button is pressed
 *
 * Puts the result into R5
 * Example:buttonPressed($location);
 * if (R5 == 1) {}
 *
 * @param variable $button Which button to check
 *
 * @return boolean
 */
public boolean buttonPressed(int $button)
{
    return true;
}

/**
 * Install the countdown
 *
 * Do not forget to add returnt at the end of the interrupt public void
 * Example: installCountdown('timerInterrupt')
 * This will install the countdown.
 * In this example when the timer interrupt triggers,
 * the public void timerInterrupt is ran.
 *
 * @param string int $public voidName The name of the public void where the timer should go to
 *
 * @return void
 */
public void installCountdown(String $public)
{
}

/**
 *Start the countdown.
 *
 * @return void
 */
public void startCountdown()
{
}

/**
 *Push a variable to the stack
 *
 * @param string $variable the variable to push to the stack
 *
 * @return void
 */
public void pushStack(int $variable)
{
}

/**
 *Pull a variable from the stack
 *
 * @param string $variable the variable where the pulled variable is put into
 *
 * @return void
 */
public void pullStack(int $variable)
{
}

/**
 * Set the timer interrupt to a value.
 *
 * It will first reset the timer to 0.
 * Example: setTimer(10)
 * This will interrupt the program after 10 timer ticks
 *
 * @param string $timer how long the timer should wait, in timer ticks
 *
 * @return void
 */
public void setTimer(int $timer)
{
}


/**
 * Get data
 *
 * Use offset 0 when it is just a single value.
 * Example: $data=_getData('data',1)
 * This will put the value of the data segment "data" at position 1, into $data.
 *
 * @param string $location The location where the variable is stored
 * @param int    $offset   The offset of the location
 *
 * @return mixed The value of the data segment
 */
public int _getData(int $location, int $offset)
{
    return 0;
}

/**
 * Store data
 *
 * Use offset 0 when it is just a single value.
 * Example: _storeData($data,'data',1)
 * This will put the value of $data into the data segment "data" at position 1
 *
 * @param string $variable The variable to store
 * @param string $location The name of the location where the variable is stored
 * @param int    $offset   The offset of the location
 *
 * @return void
 */
public void _storeData(int $variable, int $location, int $offset)
{
}

}