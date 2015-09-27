<?php
/**
  * Solution for assignment 2
  * @author Daniel Toll
  */
namespace view;
class DateTimeView {
	public function show() {
		date_default_timezone_set("Europe/Stockholm");
		$dayOfWeek = date("l");
		$dayOfMonth = date("jS");
		$month = date("F");
		$year = date("Y");
		$time = date("H:i:s");
		$timeString =  "$dayOfWeek, the $dayOfMonth of $month $year, The time is $time";
		echo "<p>$timeString</p>";
	}
}