<?php
class uidTest extends calendarComponent_TestCase
{
	
	public function testautomaticUid()
	{											
		$comp = $this->component;
		$uid = $comp->getProperty( 'Uid' );
		preg_match('/(?<uid>[\d]{8}T[\d]{6}[\w]{3}\-[\d]{4}[\d\w]{6}@[\d\w]*)/', $uid, $matches);
		$this->assertEquals($uid, $matches['uid'], 'If no uid is set a valid uid should be created');
	}
	
	public function testUid()
	{											
		$comp = $this->component;
		$comp->setProperty( 'Uid', 'testuid' );
		$uid = $comp->getProperty('Uid');
		$this->assertEquals('testuid', $uid, 'If a uid is set it should be returned');
	}
		
}