package misc;

public class TimerManage {
	int $location = 0;
	int $counter = 0;
	int $engines = 0;
	int $temp = 0;
	
	//Ignore everything above here, its just to make it work with Java
	public TimerManage(int[] $outputs){
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
}
