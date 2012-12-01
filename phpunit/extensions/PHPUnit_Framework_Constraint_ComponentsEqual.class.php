<?php

class PHPUnit_Framework_Constraint_ComponentsEqual extends PHPUnit_Framework_Constraint
{
	public function __construct( $component )
	{
		$this->component = $component;
	}
	
	public function evaluate( $other )
	{
		return $this->compareObjects($this->component, $other);
	}
	
	public function compareObjects( $obj1, $obj2 )
	{		
		$obj_keys = array_keys(get_object_vars( $obj1 ));
				
		foreach( $obj_keys as $key )
		{
			if( $key == 'uid' || empty($key) )
			{
				continue;
			}
					
			if(is_object($obj1->$key) && !$this->compareObjects($obj1->$key, $obj2->$key))
			{
				return FALSE;
			}
			elseif( ! $obj1->$key == $obj2->$key )
			{
				return FALSE;
			}			
		}
		return TRUE;
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
			PHPUnit_Util_Type::toString($this->component);
	}
}

# vim:encoding=utf8:syntax=php
