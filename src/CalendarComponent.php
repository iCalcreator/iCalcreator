<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @license   Subject matter of licence is the software iCalcreator.
 *            The above copyright, link, package and version notices,
 *            this licence notice and the invariant [rfc5545] PRODID result use
 *            as implemented and invoked in iCalcreator shall be included in
 *            all copies or substantial portions of the iCalcreator.
 *
 *            iCalcreator is free software: you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *            iCalcreator is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public License
 *            along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 */
declare( strict_types = 1 );
namespace Kigkonsult\Icalcreator;

use Exception;
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\RecurFactory;
use Kigkonsult\Icalcreator\Util\RexdateFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use UnexpectedValueException;

use function array_keys;
use function count;
use function explode;
use function get_class;
use function implode;
use function in_array;
use function is_array;
use function ksort;
use function method_exists;
use function property_exists;
use function stripos;
use function str_contains;
use function str_starts_with;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;
use function ucfirst;

/**
 *  Parent class for calendar components
 *
 * @since 2.41.1 2022-01-15
 */
abstract class CalendarComponent extends IcalBase
{
    /**
     * @var mixed[]  component sort params
     */
    public array $srtk = [];

    /**
     * @var string component number
     */
    public string $cno = '';

    /**
     * @var string
     */
    protected static string $FMTBEGIN      = "BEGIN:%s\r\n";

    /**
     * @var string
     */
    protected static string $FMTEND        = "END:%s\r\n";

    /**
     * @var string
     */
    protected static string $compSgn = 'xx';

    /**
     * Constructor for calendar component
     *
     * @param null|mixed[] $config
     * @since  2.27.14 - 2019-07-03
     */
    public function __construct( ? array $config = [] )
    {
        static $objectNo = 0;
        $class           = static::class;
        $this->compType  = ucfirst(
            strtolower( StringFactory::afterLast( StringFactory::$BS2, $class  ))
        );
        $this->cno       = $class::$compSgn . ++$objectNo;
        $this->setConfig( $config ?? [] );
    }

    /**
     * Returns calendar property unique values
     *
     * For ATTENDEE, CATEGORIES, CONTACT, RELATED_TO or RESOURCES (keys)
     * and for each, number of occurrence (values)
     *
     * @param string $propName
     * @param mixed[] $output incremented result array
     * @return void
     * @since  2.29.17 - 2020-01-25
     */
    public function getProperties( string $propName, array & $output ) : void
    {
        if( empty( $output )) {
            $output = [];
        }
        if( ! Util::isPropInList( $propName, self::$MPROPS1 )) {
            return;
        }
        $method = StringFactory::getGetMethodName( $propName );
        if( ! method_exists( $this, $method )) {
            return;
        }
        while( false !== ( $content = $this->{$method}())) {
            if( empty( $content )) {
                continue;
            }
            if( is_array( $content )) {
                foreach( $content as $part ) {
                    if( str_contains( $part, Util::$COMMA ) ) {
                        $part = explode( Util::$COMMA, $part );
                        foreach( $part as $contentPart ) {
                            $contentPart = trim( $contentPart );
                            if( ! empty( $contentPart )) {
                                if( ! isset( $output[$contentPart] )) {
                                    $output[$contentPart] = 1;
                                }
                                else {
                                    ++$output[$contentPart];
                                }
                            }
                        } // end foreach
                    }
                    else {
                        $part = trim( $part );
                        if( ! isset( $output[$part] )) {
                            $output[$part] = 1;
                        }
                        else {
                            ++$output[$part];
                        }
                    }
                } // end foreach
            } // end if( is_array( $content ))
            elseif( str_contains( $content, Util::$COMMA ) ) {
                $content = explode( Util::$COMMA, $content );
                foreach( $content as $contentPart ) {
                    $contentPart = trim( $contentPart );
                    if( ! empty( $contentPart )) {
                        if( ! isset( $output[$contentPart] )) {
                            $output[$contentPart] = 1;
                        }
                        else {
                            ++$output[$contentPart];
                        }
                    }
                } // end foreach
            } // end elseif( false !== strpos( $content, Util::$COMMA ))
            else {
                $content = trim( $content );
                if( ! empty( $content )) {
                    if( ! isset( $output[$content] )) {
                        $output[$content] = 1;
                    }
                    else {
                        ++$output[$content];
                    }
                }
            }
        } // end while
        ksort( $output );
    }

    /*
     * @var string
     */
    protected static string $NLCHARS = '\n';

    /*
     * @var string
     */
    protected static string $BEGIN   = 'BEGIN:';

    /**
     * Parse data into component properties
     *
     * @param null|string|string[] $unParsedText strict rfc2445 formatted, single property string or array of strings
     * @return static
     * @throws Exception
     * @throws UnexpectedValueException;
     * @since  2.29.3 - 2019-06-20
     * @// todo report invalid properties, Exception.. ??
     */
    public function parse( null|string|array $unParsedText = null ) : static
    {
        $rows = $this->parse1prepInput( $unParsedText );
        $this->parse2intoComps( $rows );
        $this->parse3thisProperties();
        $this->parse4subComps();
        return $this;
    }

    /**
     * Return rows to parse
     *
     * @param null|string|string[] $unParsedText strict rfc2445 formatted, single property string or array of strings
     * @return string[]
     * @since  2.29.3 - 2019-06-20
     */
    private function parse1prepInput( null|string|array $unParsedText = null ) : array
    {
        switch( true ) {
            case ( ! empty( $unParsedText )) :
                $arrParse = false;
                if( is_array( $unParsedText )) {
                    $unParsedText = implode(
                        self::$NLCHARS . Util::$CRLF,
                        $unParsedText
                    );
                    $arrParse     = true;
                }
                $rows = StringFactory::convEolChar( $unParsedText );
                if( $arrParse ) {
                    foreach( $rows as $lix => $row ) {
                        $rows[$lix] = StringFactory::trimTrailNL( $row );
                    }
                }
                break;
            case empty( $this->unparsed ) :
                $rows = [];
                break;
            default :
                $rows = $this->unparsed;
                break;
        } // end switch
        /* skip leading (empty/invalid) lines */
        foreach( $rows as $lix => $row ) {
            if( false !== ( $pos = stripos( $row, self::$BEGIN ))) {
                $rows[$lix] = substr( $row, $pos );
                break;
            }
            $tst = trim( $row );
            if(( self::$NLCHARS === $tst ) || empty( $tst )) {
                unset( $rows[$lix] );
            }
        } // end foreach
        return $rows;
    }

    /**
     * Parse into this and sub-components data
     *
     * @param string[] $rows
     * @return void
     * @since  2.41.11 - 2022-01-27
     */
    private function parse2intoComps( array $rows ) : void
    {
        static $END_ALARM         = 'END:VALARM';
        static $END_PARTICIPANT   = 'END:PARTICIPANT';
        static $ENDSARR           = [ 'END:AV', 'END:DA', 'END:ST', 'END:VL', 'END:VR' ];
        static $END               = 'END:';
        static $BEGIN             = 'BEGIN:';
        static $BEGIN_AVAILABLE   = 'BEGIN:AVAILABLE';
        static $BEGIN_DAYLIGHT    = 'BEGIN:DAYLIGHT';
        static $BEGIN_PARTICIPANT = 'BEGIN:PARTICIPANT';
        static $BEGIN_STANDARD    = 'BEGIN:STANDARD';
        static $BEGIN_VALARM      = 'BEGIN:VALARM';
        static $BEGIN_VLOCATION   = 'BEGIN:VLOCATION';
        static $BEGIN_VRESOURCE   = 'BEGIN:VRESOURCE';
        $this->unparsed = [];
        $comp     = $this;
        $compType = strtoupper( $this->getCompType());
        $beginTag = $BEGIN . $compType;
        $endTag   = $END . $compType;
        $isParticipantCurrent = $isValarmCurrent = false;
        foreach( $rows as $lix => $row ) {
            switch( true ) {
                case str_starts_with( $row, $beginTag ) :  // begin:<thisComponent>
                    break;
                case str_starts_with( $row, $endTag ) : // end:<thisComponent>
                    break 2;  // skip opt trailing empty lines..
                case $isParticipantCurrent && str_starts_with( $row, $END_PARTICIPANT ) :
                    $isParticipantCurrent = false;
                    break;
                case $isValarmCurrent && str_starts_with( $row, $END_ALARM ) :
                    $isValarmCurrent = false;
                    break;
                case ( $isParticipantCurrent || $isValarmCurrent ) :
                    $comp->unparsed[] = $row;
                    break;
                case ( in_array( strtoupper( substr( $row, 0, 6 )), $ENDSARR, true )) :
                    break;
                case str_starts_with( $row, $BEGIN_AVAILABLE ) :
                    $comp     = $this->newAvailable();
                    break;
                case str_starts_with( $row, $BEGIN_VALARM ) :
                    $comp     = $this->newValarm();
                    $isValarmCurrent = true;
                    break;
                case str_starts_with( $row, $BEGIN_DAYLIGHT ) :
                    $comp     = $this->newDaylight();
                    break;
                case str_starts_with( $row, $BEGIN_PARTICIPANT ) :
                    $comp     = $this->newParticipant();
                    $isParticipantCurrent = true;
                    break;
                case str_starts_with( $row, $BEGIN_STANDARD ) :
                    $comp     = $this->newStandard();
                    break;
                case str_starts_with( $row, $BEGIN_VLOCATION ) :
                    $comp     = $this->newVlocation();
                    break;
                case str_starts_with( $row, $BEGIN_VRESOURCE ) :
                    $comp     = $this->newVresource();
                    break;
                default :
                    $comp->unparsed[] = $row;
                    break;
            } // end switch( true )
        } // end foreach( $rows as $lix => $row )
    }

    /**
     * Parse this properties
     *
     * @return void
     * @since 2.41.9 2022-01-22
     * @todo report invalid properties ??
     */
    private function parse3thisProperties() : void
    {
        /* concatenate property values spread over several lines */
        $this->unparsed = StringFactory::concatRows( $this->unparsed );
        /* parse each property 'line' */
        foreach( $this->unparsed as $row ) {
            /* get propname  +  split property name  and  opt.params and value */
            [ $propName, $row ] = StringFactory::getPropName( $row );
            if( StringFactory::isXprefixed( $propName )) {
                [ $value, $propAttr ] = StringFactory::splitContent( $row );
                $this->setXprop( $propName, StringFactory::strunrep( $value ), $propAttr );
                continue;
            }
            if( ! property_exists( $this, StringFactory::getInternalPropName( $propName ))) {
                continue; // todo report invalid properties ??
            } // skip property names not in comp
            /* separate attributes from value */
            [ $value, $propAttr ] = StringFactory::splitContent( $row, $propName );
            if( ! Util::isPropInList( $propName, self::$TEXTPROPS ) &&
                ( ! StringFactory::isXprefixed( $propName ))) {
                $value = StringFactory::trimTrailNL( $value );
            }
            /* call set<Propname>(.. . */
            $method = StringFactory::getSetMethodName( $propName );
            switch( strtoupper( $propName )) {
                case self::ATTENDEE :
                    $this->{$method}( $value, CalAddressFactory::splitMultiParams( $propAttr ));
                    break;
                case self::CATEGORIES :   // fall through // i.e. self::$TEXTPROPS above
                case self::COMMENT :      // fall through
                case self::CONTACT :      // fall through
                case self::DESCRIPTION :  // fall through
                case self::LOCATION :     // fall through
                case self::PROXIMITY :    // fall through
                case self::RESOURCES :    // fall through
                case self::STRUCTURED_DATA :    // dito
                case self::STYLED_DESCRIPTION : // dito
                case self::SUMMARY :
                    if( empty( $value )) {
                        $propAttr = [];
                    }
                    $this->{$method}( StringFactory::strunrep( $value ), $propAttr );
                    break;
                case self::REQUEST_STATUS :
                    $values    = explode( Util::$SEMIC, $value, 3 );
                    $values[1] = ( isset( $values[1] ))
                        ? StringFactory::strunrep( $values[1] )
                        : null;
                    $values[2] = ( isset( $values[2] ))
                        ? StringFactory::strunrep( $values[2] )
                        : null;
                    $this->{$method}( $values[0], $values[1], $values[2], $propAttr );
                    break;
                case self::FREEBUSY :
                    $class = get_class( $this );
                    [ $fbtype, $values, $propAttr ] =
                        $class::parseFreebusy( $value, $propAttr );
                    $this->{$method}( $fbtype, $values, $propAttr );
                    break;
                case self::GEO :
                    $values = explode( Util::$SEMIC, $value, 2 );
                    if( 2 > count( $values )) {
                        $values[1] = null;
                    }
                    $this->{$method}( $values[0], $values[1], $propAttr );
                    break;
                case self::EXDATE :
                    $values = ( empty( $value ))
                        ? null
                        : explode( Util::$COMMA, $value );
                    $this->{$method}( $values, $propAttr );
                    break;
                case self::RDATE :
                    [ $values, $propAttr ] =
                        RexdateFactory::parseRexdate( $value, $propAttr );
                    $this->{$method}( $values, $propAttr );
                    break;
                case self::EXRULE :     // fall through
                case self::RRULE :
                    $recur  = RecurFactory::parseRexrule( $value );
                    $this->{$method}( $recur, $propAttr );
                    break;
                case self::ACTION :         // fall through
                case self::BUSYTYPE :       // dito
                case self::KLASS :          // fall through
                case self::RELATED_TO :     // fall through
                case self::STATUS :         // fall through
                case self::TRANSP :         // fall through
                case self::TZID :           // fall through
                case self::TZID_ALIAS_OF :  // fall through
                case self::TZNAME :         // fall through
                case self::UID :
                    $value = StringFactory::strunrep( $value );
                // fall through
                default:
                    $this->{$method}( $value, $propAttr );
                    break;
            } // end  switch( $propName.. .
        } // end foreach( $this->unparsed as $lix => $row )
        unset( $this->unparsed );
    }

    /**
     * Parse sub-components
     *
     * @return void
     * @since  2.29.3 - 2019-06-20
     */
    private function parse4subComps() : void
    {
        if( empty( $this->countComponents())) {
            return;
        }
        foreach( array_keys( $this->components ) as $ckey ) {
            if( ! empty( $this->components[$ckey] ) &&
                ! empty( $this->components[$ckey]->unparsed )) {
                $this->components[$ckey]->parse();
            }
        } // end foreach
    }

    /**
     * Add calendar component as subcomponent to container for subcomponents
     *
     * @param CalendarComponent $component
     * @return static
     * @since  1.x.x - 2007-04-24
     */
    public function addSubComponent( CalendarComponent $component ) : static
    {
        $this->setComponent( $component );
        return $this;
    }

    /**
     * Return formatted output for subcomponents
     *
     * @return string
     * @since  2.27.2 - 2018-12-21
     * @throws Exception  (on Valarm/Standard/Daylight) err)
     */
    public function createSubComponent() : string
    {
        $config = $this->getConfig();
        $output = Util::$SP0;
        foreach( array_keys( $this->components ) as $cix ) {
            if( ! empty( $this->components[$cix] )) {
                $this->components[$cix]->setConfig( $config, false, true );
                $output .= $this->components[$cix]->createComponent();
            }
        }
        return $output;
    }

    /**
     * Component multi-prop methods
     */

    /**
     * Recount property propIx, used at consecutive getProperty calls
     *
     * @param string[] $propArr   component (multi-)property
     * @param int   $propIx getter counter
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public static function recountMvalPropix( array $propArr, int & $propIx ) : bool
    {
        if( empty( $propArr )) {
            return false;
        }
        $last = key( array_slice( $propArr, -1, 1, true ));
        while( ! isset( $propArr[$propIx] ) && ( $last > $propIx )) {
            $propIx++;
        }
        return true;
    }

    /**
     * Return propName index
     *
     * @param mixed[] $indexArr
     * @param string $propName
     * @param int|null $index
     * @return int
     * @since  2.27.1 - 2018-12-15
     */
    protected static function getIndex(
        array & $indexArr,
        string $propName,
        ? int $index = null
    ) : int
    {
        if( null === $index ) {
            $index = ( isset( $indexArr[$propName] )) ? $indexArr[$propName] + 2 : 1;
        }
        --$index;
        $indexArr[$propName] = $index;
        return $index;
    }

    /**
     * Get calendar component multpProp property
     *
     * @param mixed[]  $multiProp component (multi-)property
     * @param string   $propName
     * @param IcalBase $instance
     * @param null|int $propIx    specific property in case of multiply occurrence
     * @param bool     $inclParam
     * @return bool|string|mixed[]
     * @since  2.27.1 - 2018-12-15
     */
    public static function getPropertyM(
        array $multiProp,
        string $propName,
        IcalBase $instance,
        ? int $propIx = null,
        ? bool $inclParam = false
    ) : bool | string | array
    {
        if( empty( $multiProp )) {
            unset( $instance->propIx[$propName] );
            return false;
        }
        $propIx = self::getIndex( $instance->propIx, $propName, $propIx );
        if( ! self::recountMvalPropix( $multiProp, $propIx )) {
            unset( $instance->propIx[$propName] );
            return false;
        }
        $instance->propIx[$propName] = $propIx;
        if( ! isset( $multiProp[$propIx] )) {
            unset( $instance->propIx[$propName] );
            return false;
        }
        return ( $inclParam )
            ? $multiProp[$propIx]
            : $multiProp[$propIx][Util::$LCvalue];
    }

    /**
     * Delete calendar component multiProp property[ix]
     *
     * @param mixed[]   $multiProp component (multi-)property
     * @param string    $propName
     * @param IcalBase  $instance
     * @param null|int  $propDelIx specific property in case of multiply occurrence
     * @return bool   true on success
     * @since  2.27.1 - 2018-12-15
     */
    public static function deletePropertyM(
        array & $multiProp,
        string $propName,
        IcalBase $instance,
        ? int $propDelIx = null
    ) : bool
    {
        if( empty( $multiProp ) ) {
            unset( $instance->propDelIx[$propName] );
            return false;
        }
        if( $propDelIx === null ) {
            $propDelIx = null; // tidy up, altered default value
        }
        $propDelIx = self::getIndex( $instance->propDelIx, $propName, $propDelIx );
        if( isset( $multiProp[$propDelIx] ) ) {
            unset( $multiProp[$propDelIx] );
        }
        if( empty( $multiProp ) ) {
            $multiProp = [];
            unset( $instance->propDelIx[$propName] );
            return false;
        }
        return true;
    }

    /**
     * Check index and set (an indexed) content in a multiple value array
     *
     * @param null|mixed[]  $valueArr
     * @param null|mixed    $value  whatever...
     * @param null|mixed[]  $params
     * @param null|string[] $defaults
     * @param null|int      $index
     * @return void
     * @since  2.22.23 - 2017-04-08
     */
    public static function setMval(
        ? array & $valueArr = [],
        mixed $value = null,
        ? array $params = [],
        ? array $defaults = [],
        ? int $index = null
    ) : void
    {
        if( empty( $valueArr )) {
            $valueArr = [];
        }
        $params2 = ParameterFactory::setParams( $params, $defaults );
        if( null === $index ) { // i.e. next
            $valueArr[] = [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params2,
            ];
            return;
        }
        --$index;
        if( isset( $valueArr[$index] )) { // replace
            $valueArr[$index] = [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params2,
            ];
            return;
        }
        $valueArr[$index] = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params2,
        ];
        ksort( $valueArr ); // order
    }
}
