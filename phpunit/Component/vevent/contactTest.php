<?php
class contactTest extends calendarComponent_TestCase
{
	
	public function testLong()
	{											
		$comp = new vevent();
		$comp->setProperty( 'contact', 'Very long Contact, ABC Industries, 1234 Aveny Road, 567 890 Town City, +1-919-555-1234' );
		$comp->setProperty( 'contact', 'Johnny Dolittle, Acme & Co, +12 34 56 78 90' );
		$comp->setProperty( 'contact', 'John Doe, Acme Ltd, 2468 Some Avenue, AnyWhere' );

		$expected = "CONTACT:Very long Contact\, ABC Industries\, 1234 Aveny Road\, 567 890 Town  City\, +1-919-555-1234CONTACT:Johnny Dolittle\, Acme & Co\, +12 34 56 78 90CONTACT:John Doe\, Acme Ltd\, 2468 Some Avenue\, AnyWhere";
		$actual = $comp->createContact();
		$this->assertStringEquals( $expected, $actual, 'repeatedly setting contact must not overwrite previous information' );
	}
	
	public function testSimple()
	{
		$contact = 'Jim Dolittle, ABC Industries, +1-919-555-1234';
		
		$comp = new vevent();
		$comp->setProperty( 'contact', $contact );
		
		$expected = "CONTACT:".str_replace(',', '\,', $contact);
		$actual = $comp->createContact();
		$this->assertStringEquals( $expected, $actual, 'the output should correspond to the input (escaping excluded)' );
	}
	
	public function testAltrep()
	{		
		$contact = 'Jim Dolittle, ABC Industries, +1-919-555-1234';
		$altrep     = 'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle)';
		$altrep_exp = 'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJ im%20Dolittle)';
		$comp = new vevent();
		$comp->setProperty( 'contact', 
				$contact, 
				array( 'altrep' => $altrep )
			);
		
		$expected = "CONTACT;ALTREP=\"".$altrep_exp."\":".str_replace(',', '\,', $contact);
		$actual = $comp->createContact();
		$this->assertStringEquals( $expected, $actual, 'contact includes altrep' );
	}
	
	public function testArbitraryParam()
	{
		$contact = 'Jim Dolittle, ABC Industries, +1-919-555-1234';
		$comp = new vevent();
		$comp->setConfig('language', 'no');
		$comp->setProperty( 'contact', 
				$contact, 
				array( 'language' => 'da', 'x-KEy' => 'x-Value' )
			);
		$expected = "CONTACT;LANGUAGE=da;X-KEY=x-Value:Jim Dolittle\, ABC Industries\, +1-919-55 5-1234";
		$actual = $comp->createContact();
		$this->assertStringEquals( $expected, $actual, 'contact should include arbitrary params' );
	}
}