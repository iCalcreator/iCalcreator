<?php
/**
 * This constraint compares two calendars in ical-Format.
 * 
 * Compares the two calendars, where every valid permutation of 
 * lines is considered equal.
 */
class PHPUnit_Framework_Constraint_icalsEqual extends PHPUnit_Framework_Constraint
{
	/* @var array[] The errors of the array comparison array(array('reason' => value)) */
	protected $error = array();

	protected $doLog = FALSE;
	/**
	 * @param string $ical the calendar to compare to
	 */
	public function __construct( $ical )
	{
		$this->calendar = $ical;
	}
	
	/**
	 * compares other to our calendar
	 * 
	 * @param  string $other the calendar to compare with
	 * 
	 * @return bool
	 */
	public function evaluate( $other )
	{		
		// get simplest case out of the way
		if( $this->calendar === $other )
		{
			return TRUE;
		}
		
		$c1 = $this->createArray($this->calendar);
		$c2 = $this->createArray($other);
		
		return $this->compareArrays($c1, $c2);
	}
	
	/**
	 * Creates an array representation of the calendar.
	 * 
	 * @param string $cal
	 * @param array $line-set a recursion parameter
	 * @return Array
	 */
	public function createArray( $cal, & $line_set = null )
	{		
		// The line_set is the exploded calendar.
		// It will be passed as reference and already processed lines will
		// be removed, so that they are not processed twice. Which means, that a 
		// non-recursive loop (a component without subcomponents) will remove 
		// its begin and end tags and every line inbetween. 
		
		if( is_null( $line_set ) )
		{
			$line_set = explode( "\n", trim($cal) );		
		}
		
		// The current component-array
		$cur = null;
		while( current($line_set) )
		{
			$l = current($line_set);
						
			// see if this line breaks and continues on next
			$nKey = key($line_set) + 1;
			if( isset($line_set[$nKey]) && substr($line_set[$nKey], 0, 1) === ' ' )
			{
				$l.= $line_set[$nKey];
				unset($line_set[$nKey]);
			}
			
			extract($this->extractKeyValueAndParams($l));
			
			switch( $key ) 
			{
				case 'BEGIN':			
					if( is_null( $cur) )
					{
						array_shift($line_set);
						$cur = array();
						$cur['type'] = $value;
						$cur['subcomponents'] = array();
					}
					else
					{						
						// don't shift. This line (begin) belongs to the component
						// being worked on in the recursion
						$cal = implode( "\n", $line_set );
						$cur['subcomponents'][] = $this->createArray($cal, $line_set);
					}
					break;
				case 'END':
					array_shift($line_set);
					return $cur;
					break;				
//				case 'DTSTAMP':
//					array_shift($line_set);
//					// These change on each creation and are irrelavant
//					$cur[$key] = array($key.'_params' => $params);
//					break;
				default:
					array_shift($line_set);
					$cur[$key] = array($key.'_value' => $value, $key.'_params' => $params);
					break;
			}			
		}
		return $cur;
	}
	
	public function extractKeyValueAndParams( $line )
	{		
		$l = explode(':', $line, 2);			
		$keyparams = trim($l[0]);
		$value =  isset($l[1]) ? trim($l[1]): '';
		$kp = explode(';', $keyparams);
		$key = $kp[0];
		$params = array();
		for( $i = 1; $i < count($kp); $i++ )
		{			
			$v = explode('=', $kp[$i]);
			$params[$v[0]] = isset($v[1]) ? trim($v[1]): '';			
		}
		return array( 'key' => $key, 'value' => $value, 'params' => $params );
	}
	
	/**
	 * Recursively compare the two arrays given.
	 * 
	 * Errors of the array comparison are stored in $this->error
	 * 
	 * @param array $c1
	 * @param array $c2
	 * @return boolean false if the two arrays do not match, true else.
	 */
	public function compareArrays( $c1, $c2 )
	{			
		foreach( $c1 as $key => $value )
		{			
			if( !isset($c2[$key]) )
			{
				$this->error[] = array('missing key' => $key, 'expected' => $value);
			}
			elseif( is_array($value) )
			{
				$this->compareArrays($c1[$key], $c2[$key]);				
			}			
			else
			{
				$this->log( sprintf( "%s => %s | %s", $key, $c1[$key], $c2[$key]));
				if( is_string( $c1[$key] ) )
				{
					$l = str_replace(array("\n", "\r", '\n'),'', $c1[$key]);
					$l = str_replace('  ',' ', $l);
					$c1[$key] = $l;
				}
				if( is_string( $c2[$key] ) )
				{
					$l = str_replace(array("\n", "\r", '\n'),'', $c2[$key]);
					$l = str_replace('  ',' ', $l);
					$c2[$key] = $l;
				}
				if( $c1[$key] === $c2[$key] )
				{
					$this->log( sprintf('%s === %s = %s', $c1[$key], $c2[$key], $c1[$key] === $c2[$key]));
				}
				else
				{
					$this->error[] = array("values don't match on $key" => $c1[$key]." vs. ".$c2[$key]);					
				}
			}
		}
		
		if( empty( $this->error ) )
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}


	/**
	 * String representation of this constraint.
	 * 
	 * This will be printed on executioon
	 * 
	 * @return string.
	 */
	public function toString()
	{
		return 'is equal to '.
			PHPUnit_Util_Type::toString($this->calendar). ' errors on '.print_r($this->error, true);
	}
	
	protected function log( $message )
	{
		if( $this->doLog )
		{
			echo $message."\n";
		}
	}
}

# vim:encoding=utf8:syntax=php
