/**
 * Sort of a simulation of the PP2 program controlling the Fischer Technik in order to sort black and white discs. 
 * @team Group 16
 * @author Maarten Keet
 * @since 13/3/2015
 */

class SoftwareDesign {
  int $state; 
  
  //outputs 
  Boolean $lensLampPosition,$lensLampSorter,$hbridge1,$hbridge0,$ledStateIndicator;
  int $conveyorBelt,$feederEngine,$display,$timerStart; 
  
  //inputs
  Boolean $startStop,$abort,$push,$position,$colour;
  int $timer;
  
  //constants
  int $timemotordown;
  
  void initial() {
   
    $push=buttonPressed(5);
    if($push==true){
        $hbridge0=false;
        $hbridge1=true;
        $state = 1;
        display($state,"leds");
        calibrateSorter();
       
    }
    initial();
  }
  void calibrateSorter(){
      sleep($timemotordown*1000)
      $hbridge1=false;
      
      $state=2;        
      display($state,"leds");
      resting();
  }
  
  void resting(){
      $startStop=buttonPressed(6);
      if($startStop==true){
          $lensLampPosition=true;
          $lensLampSorter=true;
          $conveyorBelt=9;
          $feederEngine=5;
          $timerStart=2+belt;
          
          $state=3;
          display($state,"leds");
          
      }
      resting();
  }
  
  public static void main( String args[] ) {
    new SoftwareDesign().initial();
  }
}