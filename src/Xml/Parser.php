<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2024 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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
namespace Kigkonsult\Icalcreator\Xml;

use Exception;
use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Vcalendar;

use function html_entity_decode;
use function in_array;
use function is_array;
use function sprintf;
use function str_replace;
use function str_starts_with;
use function stripos;
use function strlen;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;
use function ucfirst;

/**
 * iCalcreator XML (rfc6321) parser class
 *
 * @since 2.41.88 2024-01-16
 */
final class Parser extends XmlBase
{
    /**
     * Parse (rfc6321) XML string into iCalcreator instance
     *
     * @param string $xmlStr
     * @param null|string[] $iCalcfg Vcalendar config array (opt)
     * @return Vcalendar|bool   false on error
     * @throws Exception
     * @since 2.41.69 2022-10-04
     */
    public static function XML2iCal( string $xmlStr, ? array $iCalcfg = [] ) : Vcalendar | bool
    {
        static $CRLF = [ "\r\n", "\n\r", "\n", "\r" ];
        $xmlStr      = str_replace( $CRLF, StringFactory::$SP0, $xmlStr );
        $xml         = self::XMLgetTagContent1( $xmlStr, self::$Vcalendar, $endIx );
        $iCal        = new Vcalendar( $iCalcfg ?? [] );
        if( false === self::XMLgetComps( $iCal, $xmlStr )) {
            return false;
        }
        return $iCal;
    }

    /**
     * Parse (rfc6321) XML string into iCalcreator components
     *
     * @ param IcalInterface $iCal
     * @param Vcalendar|CalendarComponent $iCal
     * @param string    $xml
     * @return IcalInterface|bool    false on error
     * @since 2.41.69 2022-10-04
     */
    private static function XMLgetComps( Vcalendar|CalendarComponent $iCal, string $xml ) : IcalInterface | bool
    {
        static $PROPSTAGempty = '<properties/>';
        static $PROPSTAGstart = '<properties>';
        static $COMPSTAGempty = '<components/>';
        static $COMPSTAGstart = '<components>';
        static $NEW      = 'new';
        static $ALLCOMPS = [ // all IcalBase::$CALCOMPS + IcalBase::$TZCOMPS
            IcalInterface::AVAILABLE,
            IcalInterface::DAYLIGHT,
            IcalInterface::PARTICIPANT,
            IcalInterface::STANDARD,
            IcalInterface::VALARM,
            IcalInterface::VAVAILABILITY,
            IcalInterface::VEVENT,
            IcalInterface::VFREEBUSY,
            IcalInterface::VJOURNAL,
            IcalInterface::VLOCATION,
            IcalInterface::VRESOURCE,
            IcalInterface::VTIMEZONE,
            IcalInterface::VTODO,
        ];
        $len = strlen( $xml );
        $sx  = 0;
        while(
            ((( $sx + 12 ) < $len ) &&
                ! str_starts_with( substr( $xml, $sx ), $PROPSTAGstart ) &&
                ! str_starts_with( substr( $xml, $sx ), $COMPSTAGstart )
            ) &&
            ((( $sx + 13 ) < $len ) &&
                ! str_starts_with( substr( $xml, $sx ), $PROPSTAGempty ) &&
                ! str_starts_with( substr( $xml, $sx ), $COMPSTAGempty ))) {
            ++$sx;
        } // end while
        if(( $sx + 11 ) >= $len ) {
            return false;
        }
        if( str_starts_with( $xml, $PROPSTAGempty )) {
            $pos = strlen( $PROPSTAGempty );
            $xml = substr( $xml, $pos );
        }
        elseif( str_starts_with( substr( $xml, $sx ), $PROPSTAGstart )) {
            $xml2 = self::XMLgetTagContent1( $xml, self::$properties, $endIx );
            self::XMLgetProps( $iCal, $xml2 );
            $xml  = substr( $xml, $endIx );
        }
        if( str_starts_with( $xml, $COMPSTAGempty )) {
            $pos = strlen( $COMPSTAGempty );
            $xml = substr( $xml, $pos );
        }
        elseif( str_starts_with( $xml, $COMPSTAGstart )) {
            $xml = self::XMLgetTagContent1( $xml, self::$components, $endIx );
        }
        while( ! empty( $xml )) {
            $xml2     = self::XMLgetTagContent2( $xml, $tagName, $endIx );
            $compType = ucfirst( strtolower( $tagName ));
            if( in_array( $compType, $ALLCOMPS, true )) {
                $iCalComp      = $iCal->{$NEW . $compType}();
                self::XMLgetComps( $iCalComp, $xml2 );
            }
            $xml = substr( $xml, $endIx );
        } // end while( ! empty( $xml ))
        return $iCal;
    }

    /**
     * Parse (rfc6321) XML into iCalcreator properties
     *
     * @param Vcalendar|CalendarComponent $iCalComp
     * @param string $xml
     * @return void
     * @since 2.41.88 2024-01-16
     */
    private static function XMLgetProps( Vcalendar|CalendarComponent $iCalComp, string $xml ) : void
    {
        static $PARAMENDTAG = '<parameters/>';
        static $PARAMTAG    = '<parameters>';
        $endIx3       = strlen( $xml ) + 1; // i.e. init var as int
        while( ! empty( $xml )) {
            $xml2     = self::XMLgetTagContent2( $xml, $propName, $endIx );
            $propName = strtoupper( $propName );
            if( empty( $xml2 ) && ( StringFactory::$ZERO !== $xml2 )) {
                self::XMLgetEmptyProp( $iCalComp, $propName );
                $xml = substr( $xml, $endIx );
                continue;
            } // end if
            $params = [];
            if( str_starts_with( $xml2, $PARAMENDTAG )) { // no params
                $xml2 = substr( $xml2, 13 );
            }
            elseif( str_starts_with( $xml2, $PARAMTAG )) {
                $xml3   = self::XMLgetTagContent1( $xml2, self::$PARAMETERS, $endIx2 );
                self::XMLgetPropParams( $xml3, $params, $endIx3 );
                $xml2 = substr( $xml2, $endIx2 );
            } // end elseif - parameters
            $valueType = StringFactory::$SP0;
            $value     = ( ! empty( $xml2 ) || ( StringFactory::$ZERO === $xml2 ))
                ? self::XMLgetTagContent2( $xml2, $valueType, $endIx3 )
                : StringFactory::$SP0;
            match( $propName ) { // get/prep property value
                IcalInterface::URL, IcalInterface::TZURL =>
                    $value = html_entity_decode( $value ),
                IcalInterface::EXDATE, IcalInterface::RDATE, IcalInterface::FREEBUSY =>
                    $value = self::XMLgetRexFreeBProp( $propName, $xml2, $valueType, $params ),
                IcalInterface::TZOFFSETTO, IcalInterface::TZOFFSETFROM =>
                    $value = str_replace( StringFactory::$COLON, StringFactory::$SP0, $value ),
                IcalInterface::GEO =>
                    $value = self::XMLgetGeoProp( $xml2, $value ),
                IcalInterface::EXRULE, IcalInterface::RRULE =>
                    $value = self::XMLgetRexRuleProp( substr( $xml2, $endIx3 ), [ $valueType => $value ] ),
                IcalInterface::REQUEST_STATUS =>
                    $value = self::XMLgetReqStatProp( $xml2 ),
                IcalInterface::STRUCTURED_DATA =>
                    self::XMLStrucDataProp( $valueType, $params ),
                IcalInterface::STYLED_DESCRIPTION =>
                    self::XMLStyldDescrProp( $valueType, $params ),
                default =>
                    $value = self::XMLgetDefaultProp( $valueType, $value, $propName, $params )
            }; // end match( $propName )
            self::XMLsetIcalProp( $iCalComp, $propName, $value, $params );
            $xml = substr( $xml, $endIx );
        } // end while( ! empty( $xml ))
    }

    /**
     * Manage an empty property
     *
     * @param Vcalendar|CalendarComponent $iCalComp
     * @param string                      $propName
     * @return void
     */
    private static function XMLgetEmptyProp( Vcalendar|CalendarComponent $iCalComp, string $propName ) : void
    {
        if( StringFactory::isXprefixed( $propName )) {
            $iCalComp->setXprop( $propName );
            return;
        }
        $method = StringFactory::getSetMethodName( $propName );
        $iCalComp->{$method}();
    }


    /**
     * Manage prop params
     *
     * @param string $xml3
     * @param string[]|string[][] $params
     * @param int|null $endIx
     * @return void
     * @since 2.41.88 2024-01-16
     */
    private static function XMLgetPropParams( string $xml3, array & $params, ? int & $endIx = null ) : void
    {
        static $ATTENDEEPARKEYS = [
            IcalInterface::DELEGATED_FROM,
            IcalInterface::DELEGATED_TO,
            IcalInterface::MEMBER
        ];
        $endIx3 = 0;
        while( ! empty( $xml3 )) {
            $xml4     = self::XMLgetTagContent2( $xml3, $paramKey, $endIx3 );
            $paramKey = strtoupper( $paramKey );
            if( in_array( $paramKey, $ATTENDEEPARKEYS, true )) {
                while( ! empty( $xml4 )) {
                    $paramValue = self::XMLgetTagContent1( $xml4, self::$cal_address, $endIx4 );
                    if( ! isset( $params[$paramKey] )) {
                        $params[$paramKey] = [ $paramValue ];
                    }
                    else {
                        $params[$paramKey][] = $paramValue;
                    }
                    $xml4 = substr( $xml4, $endIx4 );
                } // end while
            } // end if( in_array( $paramKey, $ATTENDEEPARKEYS ))
            else {
                $pType      = StringFactory::$SP0; // skip parameter valueType
                $paramValue = html_entity_decode(
                    self::XMLgetTagContent2( $xml4, $pType, $endIx4 )
                );
                if( ! isset( $params[$paramKey] )) {
                    $params[$paramKey] = $paramValue;
                }
                else {
                    $params[$paramKey] .= StringFactory::$COMMA . $paramValue;
                }
            }
            $xml3 = substr( $xml3, $endIx3 );
        } // end while
    }

    /**
     * Manage Rdate prop
     *
     * @param string $propName
     * @param string $xml2
     * @param string $valueType
     * @param string[] $params
     * @return string[]
     * @since 2.41.88 2024-01-16
     */
    private static function XMLgetRexFreeBProp(
        string $propName,
        string $xml2,
        string $valueType,
        array & $params
    ) : array
    {
        static $REXDATE = [ IcalInterface::EXDATE, IcalInterface::RDATE ];
        if( in_array( $propName, $REXDATE ) &&
            ( self::$period !== $valueType )) {
            return self::XMLgetRdateProp( $xml2, $valueType, $params );
        }
        if( IcalInterface::FREEBUSY !== $propName ) {
            $params[ IcalInterface::VALUE ] = IcalInterface::PERIOD;
        }
        return self::XMLgetPeriodProp( $xml2 );
    }

    /**
     * Manage Rdate prop
     *
     * @param string $xml2
     * @param string $valueType
     * @param string[] $params
     * @return string[]
     * @since 2.41.88 2024-01-16
     */
    private static function XMLgetRdateProp( string $xml2, string $valueType, array & $params ) : array
    {
        static $DATETAGST = '<date';
        if( self::$date === $valueType ) {
            $params[IcalInterface::VALUE] = IcalInterface::DATE;
        }
        $t = [];
        while( ! empty( $xml2 ) && str_starts_with( $xml2, $DATETAGST )) {
            $t[]  = self::XMLgetTagContent2( $xml2, $pType, $endIx4);
            $xml2 = substr( $xml2, $endIx4 );
        } // end while
        return $t;
    }

    /**
     * Manage a period typed prop
     *
     * @param string $xml2
     * @return string[]
     * @since 2.41.88 2024-01-16
     */
    private static function XMLgetPeriodProp( string $xml2 ) : array
    {
        static $PERIODTAG = '<period>';
        $value            = [];
        while( ! empty( $xml2 ) && str_starts_with( $xml2, $PERIODTAG )) {
            $xml3 = self::XMLgetTagContent1( $xml2, self::$period, $endIx4);
            $t    = [];
            while( ! empty( $xml3 )) { // start - end/duration
                $t[]  = self::XMLgetTagContent2( $xml3, $pType, $endIx5 );
                $xml3 = substr( $xml3, $endIx5 );
            } // end while
            $value[] = $t;
            $xml2    = substr( $xml2, $endIx4 );
        } // end while
        return $value;
    }

    /**
     * Manage Geo prop
     *
     * @param string   $xml2
     * @param string   $value
     * @param null|int $endIx3
     * @return string[]
     * @since 2.41.88 2024-01-20
     */
    private static function XMLgetGeoProp( string $xml2, string $value, ? int & $endIx3 = 0  ) : array
    {
        return [
            IcalInterface::LATITUDE  => $value,
            IcalInterface::LONGITUDE => self::XMLgetTagContent1(
                substr( $xml2, $endIx3 ),
                IcalInterface::LONGITUDE,
                $endIx3
            )
        ];
    }

    /**
     * Manage ExRule/RexRule prop
     *
     * @param string $xml2
     * @param string[] $tValue
     *     $valueType => $value
     * @return string[]
     * @since 2.41.88 2024-01-16
     */
    private static function XMLgetRexRuleProp( string $xml2, array $tValue ) : array
    {
        $valueType = StringFactory::$SP0;
        while( ! empty( $xml2 )) {
            $t     = self::XMLgetTagContent2( $xml2, $valueType, $endIx4 );
            $valueType = strtoupper( $valueType );
            switch( $valueType ) {
                case IcalInterface::FREQ :     // fall through
                case IcalInterface::COUNT :    // fall through
                case IcalInterface::INTERVAL : // fall through
                case IcalInterface::RSCALE :   // fall through
                case IcalInterface::SKIP :     // fall through
                case IcalInterface::UNTIL :    // fall through
                case IcalInterface::WKST :
                    $tValue[$valueType] = $t;
                    break;
                case IcalInterface::BYDAY :
                    self::assureElementIsArray( $tValue, IcalInterface::BYDAY );
                    $tLen = strlen( $t );
                    if( 2 === $tLen ) {
                        self::addElementValue(
                            $tValue,
                            IcalInterface::BYDAY,
                            [ IcalInterface::DAY => $t ]
                        );
                    }
                    else {
                        $day = substr( $t, -2 );
                        $key = substr( $t, 0, ( $tLen - 2 ));
                        self::addElementValue(
                            $tValue,
                            IcalInterface::BYDAY,
                            [ $key, IcalInterface::DAY => $day ]
                        );
                    }
                    break;
                default:
                    self::assureElementIsArray( $tValue, $valueType );
                    self::addElementValue( $tValue, $valueType, $t );
                    break;
            } // end switch
            $xml2 = substr( $xml2, $endIx4 );
        } // end while
        return $tValue;
    }

    /**
     * Manage Req-Stat p
     *
     * @param string $xml2
     * @return string[]
     * @since 2.41.88 2024-01-16
     */
    private static function XMLgetReqStatProp( string $xml2 ) : array
    {
        $value = [
            self::$code        => null,
            self::$description => null,
            self::$data        => null
        ];
        while( ! empty( $xml2 )) {
            $t    = html_entity_decode(
                self::XMLgetTagContent2( $xml2, $valueType, $endIx4 )
            );
            $value[$valueType] = $t;
            $xml2 = substr( $xml2, $endIx4 );
        } // end while
        return $value;
    }

    /**
     * Manage STRUCTURED_DATA prop
     *
     * @param string $valueType
     * @param mixed[] $params
     * @return void
     * @since 2.41.88 2024-01-16
     */
    private static function XMLStrucDataProp( string $valueType, array & $params ) : void
    {
        $params[IcalInterface::VALUE] = match( $valueType ) {
            self::$binary => IcalInterface::BINARY,
            self::$text   => IcalInterface::TEXT,
            self::$uri    => IcalInterface::URI,
        };
    }

    /**
     * Manage STYLED_DESCRIPTION prop
     *
     * @param string $valueType
     * @param mixed[] $params
     * @return void
     * @since 2.41.88 2024-01-16
     */
    private static function XMLStyldDescrProp( string $valueType, array & $params ) : void
    {
        $params[IcalInterface::VALUE] = match( $valueType ) {
            self::$text => IcalInterface::TEXT,
            self::$uri  => IcalInterface::URI
        };
    }

    /**
     * Manage whatever left prop
     *
     * @param string $valueType
     * @param string $value
     * @param string $propName
     * @param string[] $params
     * @return string
     * @since 2.41.88 2024-01-16
     */
    private static function XMLgetDefaultProp(
        string $valueType,
        string $value,
        string $propName,
        array & $params
    ) : string
    {
        static $ATTSRC = [ IcalInterface::ATTACH, IcalInterface::SOURCE ];
        switch( $valueType ) {
            case self::$uri :
                $value = html_entity_decode( $value );
                if( in_array( $propName, $ATTSRC, true )) {
                    break;
                }
                $params[IcalInterface::VALUE] = IcalInterface::URI;
                break;
            case self::$binary :
                $params[IcalInterface::VALUE] = IcalInterface::BINARY;
                break;
            case self::$date :
                $params[IcalInterface::VALUE] = IcalInterface::DATE;
                break;
            case self::$date_time :
                $params[IcalInterface::VALUE] = IcalInterface::DATE_TIME;
                break;
            case self::$text :
                // fall through
            case self::$unknown :
                $value = html_entity_decode( $value );
                break;
            default :
                if( StringFactory::isXprefixed( $propName ) &&
                    ( self::$unknown !== strtolower( $valueType ))) {
                    $params[IcalInterface::VALUE] = strtoupper( $valueType );
                }
                break;
        } // end switch
        return $value;
    }

    /**
     * @param CalendarComponent|Vcalendar $iCalComp
     * @param string                      $propName
     * @param null|string|array           $value
     * @param array                       $params
     * @return void
     */
    private static function XMLsetIcalProp(
        CalendarComponent|Vcalendar $iCalComp,
        string $propName,
        null|string|array $value,
        array $params
    ) : void
    {
        static $VERSIONPRODID = [ IcalInterface::VERSION, IcalInterface::PRODID ];
        static $REXRULE       = [ IcalInterface::EXRULE, IcalInterface::RRULE ];
        $method = StringFactory::getSetMethodName( $propName );
        switch( true ) {
            case ( in_array( $propName, $VERSIONPRODID, true )) :
                break;
            case ( StringFactory::isXprefixed( $propName )) :
                $iCalComp->setXprop( $propName, $value, $params );
                break;
            case ( in_array( $propName, $REXRULE, true ) &&
                isset( $value[self::$recur] ) && empty( $value[self::$recur] )) :
                $iCalComp->{$method}(); // empty rexRule
                break;
            case ( IcalInterface::FREEBUSY === $propName ) :
                $fbtype = $params[IcalInterface::FBTYPE] ?? null;
                unset( $params[IcalInterface::FBTYPE] );
                $iCalComp->{$method}( $fbtype, $value, $params );
                break;
            case ( IcalInterface::GEO === $propName ) :
                $input = $iCalComp::extractGeoLatLong( $value );
                $iCalComp->{$method}( $input[0], $input[1], ( empty( $input[0] ) ? [] : $params ));
                break;
            case ( IcalInterface::REQUEST_STATUS === $propName ) :
                $input = $iCalComp::extractRequeststatus( $value );
                $iCalComp->{$method}( $input[0], $input[1], $input[2], ( empty( $input[1] ) ? [] : $params ));
                break;
            default :
                if( empty( $value ) && ( is_array( $value ) || ( StringFactory::$ZERO > $value ))) {
                    $value = null;
                }
                $iCalComp->{$method}( $value, $params );
                break;
        } // end switch
    }

    /**
     * @param array $array
     * @param string $key
     */
    private static function assureElementIsArray( array & $array, string $key ) : void
    {
        if( ! isset( $array[$key] )) {
            $array[$key] = [];
        }
    }

    /**
     * @param array $array
     * @param string $key
     * @param int|string|array $value
     */
    private static function addElementValue( array & $array, string $key, int|string|array $value ) : void
    {
        $array[$key][] = $value;
    }

    /**
     * Fetch a specific XML tag content
     *
     * @param string   $xml
     * @param string   $tagName
     * @param null|int $endIx
     * @return string
     * @since 2.41.69 2022-10-04
     */
    private static function XMLgetTagContent1( string $xml, string $tagName, ? int & $endIx = 0 ) : string
    {
        static $FMT0 = '<%s>';
        static $FMT1 = '<%s />';
        static $FMT2 = '<%s/>';
        static $FMT3 = '</%s>';
        $tagName = strtolower( $tagName );
        $strLen  = strlen( $tagName );
        $xmlLen  = strlen( $xml );
        $sx1     = 0;
        while( $sx1 < $xmlLen ) {
            if((( $sx1 + $strLen + 1 ) < $xmlLen ) && // start tag
                ( sprintf( $FMT0, $tagName ) === strtolower( substr( $xml, $sx1, ( $strLen + 2 ))))
            ) {
                break;
            }
            if((( $sx1 + $strLen + 3 ) < $xmlLen ) && // empty tag1
                ( sprintf( $FMT1, $tagName ) === strtolower( substr( $xml, $sx1, ( $strLen + 4 ))))
            ) {
                $endIx = $strLen + 5;
                return StringFactory::$SP0; // empty tag
            }
            if((( $sx1 + $strLen + 2 ) < $xmlLen ) && // empty tag2
                ( sprintf( $FMT2, $tagName ) ===  strtolower( substr( $xml, $sx1, ( $strLen + 3 ))))
            ) {
                $endIx = $strLen + 4;
                return StringFactory::$SP0; // empty tag
            }
            ++$sx1;
        } // end while...
        if( ! isset( $xml[$sx1] )) {
            $endIx = ( empty( $sx1 )) ? 0 : $sx1 - 1; // ??
            return StringFactory::$SP0;
        }
        $endTag = sprintf( $FMT3, $tagName );
        if( false === ( $pos = stripos( $xml, $endTag ))) { // missing end tag??
            $endIx = $xmlLen + 1;
            return StringFactory::$SP0;
        }
        $endIx = $pos + $strLen + 3;
        $start = $sx1 + $strLen + 2;
        $len   = $pos - $sx1 - 2 - $strLen;
        return substr( $xml, $start, $len );
    }

    /**
     * Fetch next (unknown) XML tagname AND content
     *
     * @param string $xml
     * @param string|null $tagName
     * @param int|null $endIx
     * @return string
     * @since 2.41.69 2022-10-04
     */
    private static function XMLgetTagContent2( string $xml, ? string & $tagName = null, ? int & $endIx = null ) : string
    {
        static $LT          = '<';
        static $CMTSTART    = '<!--';
        static $EMPTYTAGEND = '/>';
        static $GT          = '>';
        static $DURATION    = 'duration';
        static $DURATIONTAG = '<duration>';
        static $DURENDTAG   = '</duration>';
        static $FMTTAG      = '</%s>';
        $xmlLen = strlen( $xml );
        $endIx  = $xmlLen + 1; // just in case.. .
        $sx1    = 0;
        while( $sx1 < $xmlLen ) {
            if( $LT === $xml[$sx1] ) {
                if((( $sx1 + 3 ) < $xmlLen ) &&
                    str_starts_with( substr( $xml, $sx1 ), $CMTSTART )) { // skip comment
                    ++$sx1;
                }
                else {
                    break;
                } // tagname start here
            }
            else {
                ++$sx1;
            }
        } // end while...
        $sx2 = $sx1;
        while( $sx2 < $xmlLen ) {
            if((( $sx2 + 1 ) < $xmlLen ) &&
                str_starts_with( substr( $xml, $sx2 ), $EMPTYTAGEND )) { // tag with no content
                $tagName = trim( substr( $xml, ( $sx1 + 1 ), ( $sx2 - $sx1 - 1 )));
                $endIx   = $sx2 + 2;
                return StringFactory::$SP0;
            }
            if( $GT === $xml[$sx2] ) { // tagname ends here
                break;
            }
            ++$sx2;
        } // end while...
        $tagName = substr( $xml, ( $sx1 + 1 ), ( $sx2 - $sx1 - 1 ));
        $endIx   = $sx2 + 1;
        if( $sx2 >= $xmlLen ) {
            return StringFactory::$SP0;
        }
        $strLen = strlen( $tagName );
        if(( $DURATION === $tagName ) &&
            ( false !== ( $pos1 = stripos( $xml, $DURATIONTAG, $sx1 + 1 ))) &&
            ( false !== ( $pos2 = stripos( $xml, $DURENDTAG,  $pos1 + 1 ))) &&
            ( false !== ( $pos3 = stripos( $xml, $DURENDTAG,  $pos2 + 1 ))) &&
            ( $pos1 < $pos2 ) && ( $pos2 < $pos3 )) {
            $pos = $pos3;
        }
        elseif( false === ( $pos = stripos( $xml, sprintf( $FMTTAG, $tagName ), $sx2 ))) {
            return StringFactory::$SP0;
        }
        $endIx = $pos + $strLen + 3;
        return substr( $xml, ( $sx1 + $strLen + 2 ), ( $pos - $strLen - 2 ));
    }
}
