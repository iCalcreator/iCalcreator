<?php

namespace Kigkonsult\Icalcreator;

use ArrayIterator;
use ArrayObject;
use IteratorIterator;
use PHPUnit\Framework\TestCase;

class PcTest extends TestCase
{
    /**
     * Test instance
     *
     * @test
     */
    public function pcTest0() : void
    {
        $pc = new Pc(
            [ Vcalendar::LANGUAGE => 'en' ],
            ArrayObject::STD_PROP_LIST | ArrayObject::ARRAY_AS_PROPS,
            IteratorIterator::class
        );

        $this->assertInstanceOf( ArrayObject::class, $pc, 'case 1a' );
        $this->assertInstanceOf( Pc::class, $pc, 'case 1b' );
        $this->assertSame( 2, $pc->count(), 'case 2' );
        $this->assertCount( 2, $pc->getAsArray(), 'case 3' );
        $this->assertSame( ArrayObject::ARRAY_AS_PROPS, $pc->getFlags(), 'case 4' );
        $this->assertSame( ArrayIterator::class, $pc->getIteratorClass(), 'case 5' );

        $old1 = $pc->getAsArray();
        $old2 = $pc->exchangeArray( [ Vcalendar::VALUE => Vcalendar::DATE_TIME ] );
        $new  = $pc->getAsArray();
        $this->assertSame( $old1, $old2, 'case 6a' );
        $this->assertSame( $old1, $new, 'case 6b' );

        $old = $pc->getFlags();
        $pc->setFlags( ArrayObject::STD_PROP_LIST | ArrayObject::ARRAY_AS_PROPS );
        $new = $pc->getFlags();
        $this->assertSame( $old, $new, 'case 7' );

        $old = $pc->getIteratorClass();
        $pc->setIteratorClass( IteratorIterator::class );
        $new = $pc->getIteratorClass();
        $this->assertSame( $old, $new, 'case 8' );

//      var_dump( $pc ); // test ###
    }

    /**
     * Test static methods
     *
     * @test
     */
    public function pcTest10() : void
    {
        $key  = 'key';

        $this->assertFalse( Pc::isXprefixed( $key ), 'case 11' );

        $key2 = Pc::setXPrefix( $key );
        $this->assertTrue( Pc::isXprefixed( $key2 ), 'case 12');

        $key3 = Pc::unsetXPrefix( $key2 );
        $this->assertFalse( Pc::isXprefixed( $key3 ), 'case 13');
    }

    /**
     * Test 'empty' with Pc as array
     *
     * @test
     */
    public function pcTest20() : void
    {
        $pc = Pc::factory();

        $this->assertFalse( isset( $pc[Pc::$LCvalue] ), 'case 21' );

        $this->assertNull( $pc[Pc::$LCvalue], 'case 22' );

        $this->assertSame( [], $pc[Pc::$LCparams], 'case 23' );

        $this->assertSame( [], array_keys( $pc[Pc::$LCparams] ), 'case 24' );

        $this->assertFalse( isset( $pc[Pc::$LCparams][Vcalendar::LANGUAGE] ), 'case 25' );
    }

    /**
     * Test 'empty' with Pc properties and methods
     *
     * @test
     */
    public function pcTest30() : void
    {
        $pc = Pc::factory();

        $this->assertFalse( isset( $pc->value ), 'case 31' );

        $this->assertNull( $pc->value, 'case 32' );

        $this->assertSame( [], $pc->params, 'case 33' );

        $this->assertSame( [], $pc->getParamKeys(), 'case 34' );

        $this->assertFalse( isset( $pc->params[Vcalendar::LANGUAGE] ), 'case 34' );
    }

    /**
     * Test as Pc as array
     *
     * @test
     */
    public function pcTest40() : void
    {
        $propValue   = Vcalendar::VALUE;
        $propValue2  = Vcalendar::UNKNOWN;
        $xKey        = 'x-key';
        $xValue      = 'xValue';
        $propParams  = [ Vcalendar::LANGUAGE => 'en', Vcalendar::ISLOCALTIME => true, $xKey => $xValue ];
        $propParams2 = [ Vcalendar::VALUE => Vcalendar::DATE_TIME ];
        $altLang     = 'sv';
        $xKey2       = 'x-key2';
        $xValue2     = 'xValue2';

        $pc = Pc::factory( $propValue, $propParams );

        $this->assertTrue( isset( $pc[Pc::$LCvalue] ), 'case 41' );

        $this->assertSame( $propValue, $pc[Pc::$LCvalue], 'case 42' );
        $pc[Pc::$LCvalue] = $propValue2;
        $this->assertSame( $propValue2, $pc[Pc::$LCvalue], 'case 42d' );

        $this->assertNotSame( $propParams, $pc[Pc::$LCparams], 'case 43a' );
        $ucKeyArr = [];
        foreach( $propParams as $key => $value ) {
           $ucKeyArr[strtoupper( $key )] = $value;
        }
        $this->assertSame( $ucKeyArr, $pc[Pc::$LCparams], 'case 43b' );

        $pc[Pc::$LCparams] = $propParams2; // i.e. replace
        $this->assertNotSame( $ucKeyArr, $pc[Pc::$LCparams], 'case 43c' );
        $this->assertSame( $propParams2, $pc[Pc::$LCparams], 'case 43f' );
        $this->assertSame( Vcalendar::DATE_TIME, $pc[Pc::$LCparams][Vcalendar::VALUE], 'case 43g' );
        $pc[Pc::$LCparams] = $ucKeyArr; // i.e. replace back

        $currParamKeys = array_keys( $pc[Pc::$LCparams] );
        $this->assertNotSame( array_keys( $propParams ), $currParamKeys, 'case 44a' );
        $this->assertSame( array_keys( $ucKeyArr ), $currParamKeys, 'case 44b' );

        $this->assertTrue( isset( $pc[Pc::$LCparams][Vcalendar::LANGUAGE] ), 'case 45a' );
        $this->assertFalse( isset( $pc[Pc::$LCparams][$xKey] ), 'case 45b' );
        $this->assertTrue( isset( $pc[Pc::$LCparams][strtoupper( $xKey )] ), 'case 45c' );

        $this->assertSame( $propParams[Vcalendar::LANGUAGE], $pc[Pc::$LCparams][Vcalendar::LANGUAGE], 'case 46a' );
        $this->assertFalse( isset( $pc[Pc::$LCparams][$xKey] ), 'case 46b' );
        $this->assertSame( $xValue, $pc[Pc::$LCparams][strtoupper( $xKey )], 'case 46c' );

        $pc[Pc::$LCparams][Vcalendar::LANGUAGE] = $altLang;
        $this->assertSame( $altLang, $pc[Pc::$LCparams][Vcalendar::LANGUAGE], 'case 47a' );
        $this->assertFalse( isset( $pc[Pc::$LCparams][$xKey2] ), 'case 47b' );
        $pc[Pc::$LCparams][$xKey2] = $xValue2;
        $this->assertSame( $xValue2, $pc[Pc::$LCparams][$xKey2], 'case 47c' );
        $this->assertNull( $pc[Pc::$LCparams][strtoupper( $xKey2 )] ?? null, 'case 47d' );

        $pc->setEmpty();
        $pc[Pc::$LCparams][Vcalendar::VALUE] = Vcalendar::DATE_TIME;
        $pc[Pc::$LCparams][Vcalendar::ISLOCALTIME] = true;
        $exp = [ Vcalendar::VALUE => Vcalendar::DATE_TIME, Vcalendar::ISLOCALTIME => true ];
        $this->assertSame( $pc[Pc::$LCparams], $exp,'case 49b' );
    }

    /**
     * Test with Pc properties and methods
     *
     * Just to assure access to public Pc properties value/params, NOT used anywhere
     *
     * @test
     */
    public function pcTest70() : void
    {
        $propValue   = Vcalendar::VALUE;
        $propValue2  = Vcalendar::UNKNOWN;
        $xKey        = 'x-key';
        $xValue      = 'xValue';
        $language    = 'en';
        $propParams  = [ Vcalendar::LANGUAGE => $language, Vcalendar::ISLOCALTIME => true, $xKey => $xValue ];
        $propParams2 = [ Vcalendar::VALUE => Vcalendar::DATE_TIME ];
        $altLang     = 'sv';
        $xKey2       = 'x-key2';
        $xValue2     = 'xValue2';

        $pc = Pc::factory( $propValue, $propParams );

        $this->assertTrue( $pc->isset(), 'case 71' );

        $this->assertSame( $propValue, $pc->getValue(), 'case 72b' );
        $this->assertSame( $propValue, $pc->value, 'case 72b' );
        $pc->value = $propValue2;
        $this->assertSame( $propValue2, $pc->getValue(), 'case 72c' );
        $this->assertSame( $propValue2, $pc->value, 'case 72d' );

        $this->assertNotSame( $propParams, $pc->getParams(), 'case 73a' );
        $ucKeyArr = [];
        foreach( $propParams as $key => $value ) {
            $ucKeyArr[strtoupper( $key )] = $value;
        }
        $this->assertSame( $ucKeyArr, $pc->getParams(), 'case 73b' );
        $this->assertSame( $ucKeyArr, $pc->params, 'case 73c' );

        $oldParams = $pc->getParams();
        $pc->setParams( $propParams2 ); // i.e. add
        $newParams = $pc->getParams();
        $this->assertNotSame( $propParams, $newParams, 'case 73d' );
        $this->assertNotSame( $oldParams, $newParams, 'case 73e' );
        $this->assertSame( Vcalendar::DATE_TIME, $pc->getValueParam(), 'case 73g' );
        $cmpParams = array_merge( $ucKeyArr, $propParams2 );
        $this->assertSame( $cmpParams, $newParams, 'case 73f' );

        $currParamKeys = $pc->getParamKeys();
        $this->assertNotSame( array_keys( $propParams ), $currParamKeys, 'case 74a' );
        $this->assertSame( array_keys( $cmpParams ), $currParamKeys, 'case 74b' );

        $this->assertTrue( $pc->hasParamKey( Vcalendar::LANGUAGE ), 'case 75a' );
        $this->assertTrue( $pc->hasParamKey( Vcalendar::LANGUAGE, $language ), 'case 75b' );
        $this->assertFalse( $pc->hasParamKey( Vcalendar::LANGUAGE, Vcalendar::LANGUAGE ), 'case 75c' );

        $this->assertTrue( $pc->hasParamKey( $xKey ), 'case 75c' );
        $this->assertTrue( $pc->hasParamKey( $xKey, $xValue ), 'case 75d' );
        $this->assertFalse( $pc->hasParamKey( $xKey, $xKey ), 'case 75e' );

        $this->assertSame( $propParams[Vcalendar::LANGUAGE], $pc->getParams( Vcalendar::LANGUAGE ), 'case 76a' );
        $this->assertSame( $xValue, $pc->getParams( $xKey ), 'case 76c' );

        $pc->addParam( Vcalendar::LANGUAGE, $altLang );
        $this->assertSame( $altLang, $pc->getParams( Vcalendar::LANGUAGE ), 'case 77a' );
        $this->assertFalse( $pc->hasParamKey( $xKey2 ), 'case 77b' );
        $this->assertNull( $pc->getParams( $xKey2 ), 'case 77c' );
        $pc->addParam( $xKey2, $xValue2 );
        $this->assertSame( $xValue2, $pc->getParams( $xKey2 ), 'case 77d' );

        $pc->removeParam( Vcalendar::LANGUAGE );
        $this->assertFalse( $pc->hasParamKey( Vcalendar::LANGUAGE ), 'case 78a' );
        $pc->addParam( Vcalendar::LANGUAGE, $altLang );

        $pc->addXparam( Vcalendar::LANGUAGE, $altLang );
        $this->assertFalse( $pc->hasXparamKey( Vcalendar::LANGUAGE, Vcalendar::LANGUAGE ), 'case 78b' );
        $this->assertTrue( $pc->hasXparamKey( Vcalendar::LANGUAGE ), 'case 78c' );
        $pc->removeParam( Vcalendar::LANGUAGE, Vcalendar::LANGUAGE );
        $this->assertTrue( $pc->hasXparamKey( Vcalendar::LANGUAGE ), 'case 78d' );
        $this->assertTrue( $pc->hasXparamKey( Vcalendar::LANGUAGE, $altLang ), 'case 78e' );
        $pc->removeXparam( Vcalendar::LANGUAGE );
        $this->assertfalse( $pc->hasXparamKey( Vcalendar::LANGUAGE ), 'case 78f' );

        foreach( $pc->getParamKeys() as $key ) {
            $pc->removeParam( $key );
        }
        $this->assertSame( [], $pc->getParams(), 'case 78f' );

        $pc->addParam( Vcalendar::VALUE, Vcalendar::DATE_TIME );
        $this->assertTrue( $pc->hasParamValue( Vcalendar::DATE_TIME ), 'case 79a' );

        $pc->addParam( Vcalendar::ISLOCALTIME, true );
        $this->assertTrue( $pc->hasParamIsLocalTime(), 'case 79b' );
    }
}
