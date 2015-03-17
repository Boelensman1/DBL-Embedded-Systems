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
        $timerStart=$timemotordown;
        
        $state = 1;
        display($state,"leds");
        calibrateSorter();
       
    }
    initial();
  }
  void calibrateSorter(){
      $state=1;
      

  }
  
  public static void main( String args[] ) {
    new SoftwareDesign().initialise();
  }
}