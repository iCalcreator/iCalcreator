<?php
class attachTest extends calendarComponent_TestCase
{
	
	public function testSingleAttachment()
	{											
		$comp = new vevent();
		$comp->setProperty( 'attach', 'http://doclib.domain.net/lib1234567890/docfile1.txt' );
		$expected = 'ATTACH:http://doclib.domain.net/lib1234567890/docfile1.txt';
		$actual = $this->normalizeSpace($comp->createAttach());
		$this->assertEquals($expected, $actual);
	}
	
	public function testMultiple()
	{
		$comp = new vevent();
		$comp->setProperty( 'attach', 'MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcNAQEEBQAwdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bmljYXRpb25zIE.....', array('FMTTYPE' => 'image/basic', 'ENCODING' => 'BASE64', 'VALUE' => 'BINARY', 'hejsan' ));
		$comp->setProperty( 'attach', 'http://doclib.domain.net/lib1234567890/docfile2.txt', array( 'filetype' => 'text' ));
		$comp->setProperty( 'attach'
						, 'http://doclib.domain.net/lib1234567890/docfile11.txt'
						, array( 'filetype' => 'text' )
						, 11 );
		
		$expected = 'ATTACH;FMTTYPE=image/basic;hejsan;ENCODING=BASE64;VALUE=BINARY:MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcNAQEEBQAwdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bmljYXRpb25zIE.....';
		$actual = $this->normalizeSpace($comp->createAttach());
		$this->assertEquals($expected, $actual);
	}		
}