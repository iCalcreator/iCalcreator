<?php
/**
 * This constraint compares two calendars in ical-Format.
 * 
 * Compares the two calendars, where every valid permutation of 
 * lines is considered equal.
 */
class PHPUnit_Framework_Constraint_icalsEqual extends PHPUnit_Framework_Constraint
{
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
		
		$constraint = new PHPUnit_Framework_Constraint_IsEqual( $c1 );
		return $constraint->evaluate($c2);
	}
	
	/**
	 * Creates an array representation of the calendar.
	 * 
	 * @param string $cal
	 * 
	 * @return Array
	 */
	public function createArray( $cal )
	{		
		$line_set = explode( "\n", $cal );		
		
		$cur = null;
		foreach( $line_set as $idx => $line )
		{
			$l = explode(':', $line, 2);			
			$key = trim($l[0]);
			$value = trim($l[1]);
			
			switch( $key ) 
			{
				case 'BEGIN':
					if( is_null( $cur) )
					{
						$cur = array();
						$cur['type'] = $value;
						$cur['subcomponents'] = array();
					}
					else
					{
						$_line_set = array_slice( $line_set, $idx );
						$c = implode( "\n", $_line_set );
						$cur['subcomponents'][] = $this->createArray($c);
					}
					break;
				case 'END':
					return $cur;
					break;
				case 'DTSTAMP':
					// Datestamp changes on each creation and is irrelavant
					$cur[$key] = '12345678T123456Z';
					break;
				default:
					$cur[$key] = $value;
					break;
			}
		}
		return $cur;
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
			PHPUnit_Util_Type::toString($this->calendar);
	}
}

# vim:encoding=utf8:syntax=php
