<?php
/*
 * Defines a load of useful helper functions, which make testing datebased 
 * properties easier.
 */
class calendarComponent_TestCase extends iCalCreator_TestCase
{		
	public function setUp()
	{
		parent::setUp();
		$this->component = new calendarComponent();
	}
	
	/**
	 * Provides parameters for the tests.
	 * 
	 * Shoud return at least one entry for each format accepted as date. Don't
	 * forget to include edge cases.
	 * 
	 * @return array
	 */
	public function timeProvider()
	{
		return array(
			//time, timezoneid
			array( time(),							'Europe/Rome' ),
			array( date('Y-m-d H:i:s', time()),		'Europe/Stockholm'),
			array( date('Y-m-d', time()),			'Europe/Rome'),
			array( date('YmdHis', time()),			'Europe/Rome'),
			array( date('Ymd\THis', time()),		'US/Eastern'),
			array( date('Ymd\THis\Z', time()),		'Europe/Rome'),
			array( date('Ymd', time()),				'Europe/Rome'),
			array( date('d M Y', time()),			'Europe/Rome'),
			array( date('m\\d\\Y', time()),			'Europe/Rome'),
			//array( array('timestamp' => time()),	'Europe/Rome'),
			array( array( date('Y'), date('m'), date('d') ), 'Europe/Rome'),
			array( array( date('Y'), date('m'), date('d'), date('H'), date('i'), date('s') ), 'Europe/Rome'),
			array( array( 'day'=>date('d'), 'month'=>date('m'), 'year'=>date('Y') ), 'Europe/Rome'),
			array( array( 'sec'=>date('s'), 'min'=>date('i'), 'hour'=>date('H'), 'day'=>date('d'), 'month'=>date('m'), 'year'=>date('Y') ), 'Europe/Rome'),				
		);
	}	
	

	/**
	 * Calculates most used derived fomrats, given a valid iCalcreator Dateformat.  
	 * 
	 * This function also executes the necessary steps to have native php 
	 * calculate the correct gmt times.
	 * 
	 * @param mixed $time a valid iCalCreator DateFormat
	 * @param string $tzid a valid timezone identifier
	 * @return array
	 */
	protected function preparetime($time, $tzid)
	{
		// We're dealing with times. If possible let PHP do the heavy lifting
		// regarding timezones, daylight saving, etc.
		$mytz = date_default_timezone_get();
		date_default_timezone_set($tzid);
		ini_set('date.timezone', $tzid);
		
		// $time can be passed in many different forms 
		// calculate date representation and timestamp
		if( is_array($time) )
		{		
			$date = $time;
			$timestamp = $this->datearray2time($time);
		}
		else
		{
			if( !is_int($time) )
			{
				$timestamp = strtotime($time);
			}
			else
			{
				$timestamp = $time;
			}
			$date = date('Y-m-d H:i:s', $timestamp);
		}
		
		$timezone = new DateTimeZone($tzid);
		// can't get seconds from DateTimeZone, can I?
		$tzsec = date('Z');		
		$tzoff = date('O');
		$dateiso = date('Ymd\THis', $timestamp);		
		$datetime = new DateTime(date('Y-m-d H:i:s',$timestamp), new DateTimeZone($tzid));
		$datetime->setTimezone(new DateTimeZone('UTC'));
		$utctime = $datetime->format('YmdHis');
		$utciso = $datetime->format('Ymd\THis\Z');
		
		// reset timezone settings
		date_default_timezone_set($mytz);
		ini_set('date.timezone', $mytz);
		return array(
			'date' => $date, 
			'timestamp' => $timestamp, 			
			'utctimestamp' => $utctime,
			'utciso' => $utciso,
			'tzoff' => $tzoff,
			'tzsec' => $tzsec,
			'dateiso' => $dateiso);
	}
	
	
	/**
	 * convert the given (assoc.) array into a timestamp
	 * 
	 * @param int[] $arr
	 * @return int a timestamp
	 */
	public function datearray2time( $arr )
	{
		if( isset($arr['timestamp']) )
		{
			return $arr['timestamp'];
		}
		if( $this->is_assoc($arr) )
		{
			$Y = isset($arr['year'])? $arr['year'] : 0;
			$m = isset($arr['month'])? $arr['month'] : 0;
			$d = isset($arr['day'])? $arr['day'] : 0;
			$H = isset($arr['hour'])? $arr['hour'] : 0;
			$i = isset($arr['min'])? $arr['min'] : 0;
			$s = isset($arr['sec'])? $arr['sec'] : 0;
		}
		else
		{
			$Y = isset($arr[0])? $arr[0] : 0;
			$m = isset($arr[1])? $arr[1] : 0;
			$d = isset($arr[2])? $arr[2] : 0;
			$H = isset($arr[3])? $arr[3] : 0;
			$i = isset($arr[4])? $arr[4] : 0;
			$s = isset($arr[5])? $arr[5] : 0;
		}
		return mktime($H, $i, $s, $m, $d, $Y);
	}
	
	/**
	 * Appends the timezone to a date respecting the format given.
	 * 
	 * Will expand the given date to include a time (midnight) if neccessary.
	 * 
	 * @param type $date
	 * @param type $tz
	 * @return type
	 */
	public function appendTimezone($date, $tz)
	{
		// depending on the format of the date, the timezone has to be passed 
		// differently.
		if( !is_array($date) )
		{
			$date.= ' '.$tz;
		}
		// is_assoc seems to have problems when only one key is specified. :(
		elseif($this->is_assoc($date) || isset($date['timestamp']))
		{			
			$date['tz'] = $tz;
			if( isset($date['year']) )
			{
				foreach( array('year', 'day', 'month', 'min', 'sec', 'hour') as $i )
				{
					$date[$i] = isset($date[$i])?$date[$i]:0;
				}
			}
		}
		elseif(count($date) == 6)
		{			
			$date[] = $tz;			
		}
		else
		{
			// Non assoc. array with less than 6 entries ususally constitue
			// a three-tuple of year, month and date
			foreach( range(0,5) as $i )
			{
				$date[$i] = isset($date[$i])?$date[$i]:0;
			}
			$date[6] = $tz;
		}
		return $date;
	}
	
	/**
	 * Wether or not the given array has non int keys, i.e is associatie.
	 * 
	 * @param array $a
	 * @return bool
	 */
	public function is_assoc($a){
		$a = array_keys($a);
		return ($a != array_keys($a));
	 }
	 
	 /**
	  * Removes linebreaks an spaces.
	  * 
	  * Useful to compare two strings regardless of whitespace.
	  * 
	  * @param mixed $input sting or string[]
	  * @return mixed same as input
	  */
	 public function normalizeSpace( $input )
	 {
		 return str_replace(array(' ', "\n", "\r"), '', $input);
	 }
	 
	 public function assertStringEquals( $expected, $actual, $message = null )
	 {
		 $this->assertEquals( $this->normalizeSpace($expected), $this->normalizeSpace($actual), $message);
	 }
}
