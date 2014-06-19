<?php

class valarmTest extends iCalCreator_TestCase
{	
	public function testUid()
	{											
		$comp = new valarm();
		$uid = $comp->getProperty( 'Uid' );
		$this->assertTrue(empty($uid), 'a valarm must not have a uid');
	}
}