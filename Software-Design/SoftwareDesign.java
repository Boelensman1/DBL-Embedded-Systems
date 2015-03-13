/**
 * Sort of a simulation of the PP2 program controlling the Fischer Technik in order to sort black and white discs. 
 * @team Group 16
 * @author Maarten Keet
 * @since 13/3/2015
 */

class SoftwareDesign {
  String state; 
  Boolean lensLampPosition;
  
  void initialise() {
    state = "Initial State";
  }
  
  void pressStartStop() {
    if(state.equals("Resting State")) {
      state = "Running State";
      lensLampPosition = true;
    }
  }
  
  public static void main( String args[] ) {
    new SoftwareDesign().initialise();
  }
}