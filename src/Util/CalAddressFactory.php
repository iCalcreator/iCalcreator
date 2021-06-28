<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
namespace Kigkonsult\Icalcreator\Util;

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\CalendarComponent;

use function array_change_key_case;
use function count;
use function explode;
use function in_array;
use function is_array;
use function method_exists;
use function sprintf;
use function strcasecmp;
use function strpos;
use function strtoupper;
use function substr;
use function trim;

/**
 * iCalcreator attendee support class
 *
 * @since 2.39 2021-06-19
 */
class CalAddressFactory
{
    /**
     * @var array
     */
    private static $CALADDRESSPROPERTIES = [
        Vcalendar::ATTENDEE,
        Vcalendar::CONTACT,
        Vcalendar::ORGANIZER
    ];

    /**
     * @var array
     */
    private static $ParamArrayKeys = [
        Vcalendar::MEMBER,
        Vcalendar::DELEGATED_TO,
        Vcalendar::DELEGATED_FROM,
    ];

    /**
     * @var string Prefix for Ical cal-address etc
     */
    public  static $MAILTOCOLON = 'MAILTO:';
    private static $AT          = '@';

    /**
     * Assert cal-address (i.e. MAILTO.prefixed)
     *
     * @param string $calAddress
     * @throws InvalidArgumentException
     * @since  2.27.8 - 2019-03-18
     */
    public static function assertCalAddress( $calAddress )
    {
        static $DOT    = '.';
        static $XDOT   = 'x.';
        static $ERRMSG = 'Invalid email %s';
        if( false == strpos( $calAddress, self::$AT )) {
            throw new InvalidArgumentException( sprintf( $ERRMSG, $calAddress ));
        }
        $domain = StringFactory::after( self::$AT, $calAddress );
        if( false === strpos( StringFactory::before( self::$AT, $calAddress ), $DOT )) {
            $namePart    = self::extractNamepartFromEmail( $calAddress );
            $testAddress = $XDOT . $namePart . self::$AT . $domain;
        }
        else {
            $testAddress = self::removeMailtoPrefix( $calAddress );
        }
        if( ! filter_var( $testAddress, FILTER_VALIDATE_EMAIL ) &&
            ! filter_var( $domain, FILTER_VALIDATE_DOMAIN )) {
            throw new InvalidArgumentException( sprintf( $ERRMSG, $calAddress ));
        }
    }

    /**
     * Return conformed cal-address (i.e. MAILTO-prefixed)
     *
     * @param string $calAddress
     * @param bool $forceMailto   force if missing
     * @return string
     * @since  2.27.8 - 2019-03-17
     */
    public static function conformCalAddress( string $calAddress, $forceMailto = false ) : string
    {
        switch( true ) {
            case empty( $calAddress ) :
                break;
            case ( 0 == strcasecmp( self::$MAILTOCOLON, substr( $calAddress, 0, 7 ))) :
                // exists, force uppercase
                $calAddress = self::$MAILTOCOLON . substr( $calAddress, 7 );
                break;
            case $forceMailto :
                // missing and force
                $calAddress = self::$MAILTOCOLON . $calAddress;
                break;
        } // end switch
        return $calAddress;
    }

    /**
     * Return bool true if email has leading MAILTO:
     *
     * @param string $email
     * @return bool
     * @since  2.27.8 - 2019-03-17
     */
    private static function hasMailtoPrefix( string $email ) : bool
    {
        return ( 0 == strcasecmp( self::$MAILTOCOLON, substr( $email, 0, 7 )));
    }

    /**
     * Return email without prefix (anycase) 'MAILTO;
     *
     * @param string $email
     * @return string
     * @since  2.27.8 - 2019-03-17
     */
    public static function removeMailtoPrefix( string $email ) : string
    {
        if( self::hasMailtoPrefix( $email )) {
            return substr( $email, 7 );
        }
        return $email;
    }

    /**
     * Return bool true if parameter EMAIL equals ATTENDEE/ORGANIZER value
     *
     * @param string $value
     * @param array $params
     * @since  2.29.11 - 2019-08-30
     */
    public static function sameValueAndEMAILparam( string $value, array & $params )
    {
        if( isset( $params[Vcalendar::EMAIL] ) &&
            ( 0 == strcasecmp(
                self::removeMailtoPrefix( $value ),
                self::removeMailtoPrefix( $params[Vcalendar::EMAIL] ))
            )) {
            unset( $params[Vcalendar::EMAIL] );
        } // end if
    }

    /**
     * Extract namePart from email
     *
     * @param string $email
     * @return string
     * @since  2.27.8 - 2019-03-20
     */
    public static function extractNamepartFromEmail( string $email ) : string
    {
        if( self::hasMailtoPrefix( $email )) {
            return StringFactory::before( self::$AT, substr( $email, 7 ));
        }
        return StringFactory::before( self::$AT, $email );
    }

    /**
     * Return cal-addresses and (hopefully) name parts
     *
     * From ATTENDEEs and ORGANIZERs, name part from CONTACTs
     * NO parameters cal-addresses
     *
     * @param Vcalendar $calendar    iCalcreator Vcalendar instance
     * @param array     $properties
     * @return array
     * @since  2.39 - 2021-06-14
     */
    public static function getCalAddresses(
        Vcalendar $calendar,
        $properties = null
    ) : array
    {
        $searchProperties = [];
        if( empty( $properties )) {
            $searchProperties = self::$CALADDRESSPROPERTIES;
        }
        else {
            foreach(((array) $properties ) as $property ) {
                if( in_array( $property, self::$CALADDRESSPROPERTIES )) {
                    $searchProperties[] = $property;
                }
            } // end foreach
        }
        $output = [];
        foreach( $searchProperties as $propName ) {
            $output = array_merge(
                $output,
                self::getCalAdressValuesFromProperty( $calendar, $propName )
            );
        } // end foreach
        sort( $output );
        return array_unique( $output );
    }

    /**
     * Return value cal-addresses from Vcalendar property value
     *
     * From one of ATTENDEEs and ORGANIZERs, name part from CONTACTs
     *
     * @param Vcalendar $calendar  iCalcreator Vcalendar instance
     * @param string    $propName
     * @return array
     * @since  2.27.8 - 2019-03-18
     */
    public static function getCalAdressValuesFromProperty(
        Vcalendar $calendar,
        string $propName
    ) : array
    {
        $propValues = $calendar->getProperty( $propName );
        if( empty( $propValues )) {
            return [];
        }
        $output = [];
        foreach( $propValues as $propValue => $counts ) {
            $propValue = self::removeMailtoPrefix( $propValue );
            if( false !== strpos( $propValue, Util::$COMMA )) {
                $propValue = StringFactory::before( Util::$COMMA, $propValue );
            }
            try {
                self::assertCalAddress( $propValue );
            }
            catch( InvalidArgumentException $e ) {
                continue;
            }
            if( ! in_array( $propValue, $output )) {
                $output[] = $propValue;
            }
        } // end foreach
        sort( $output );
        return array_unique( $output );
    }

    /**
     * Return value and parameters cal-addresses from Vcalendar property
     *
     * From one of ATTENDEEs and ORGANIZERs, name part from CONTACTs
     *
     * @param Vcalendar    $calendar  iCalcreator Vcalendar instance
     * @param null|string  $propName
     * @return array
     * @since  2.39 - 2021-06-19
     */
    public static function getCalAdressesAllFromProperty(
        Vcalendar $calendar,
        $propName = null
    ) : array
    {
        if( empty( $propName )) {
            $propName = self::$CALADDRESSPROPERTIES;
        }
        $calendar->reset();
        $output = [];
        while( $comp = $calendar->getComponent()) {
            foreach((array) $propName as $pName ) {
                $method = StringFactory::getGetMethodName( $pName );
                if( ! method_exists( $comp, $method ) ) {
                    continue;
                }
                switch( $pName ) {
                    case Vcalendar::ATTENDEE :
                        $output = array_merge(
                            $output,
                            self:: getCalAdressesAllFromAttendee( $comp )
                        );
                        break;
                    case Vcalendar::ORGANIZER :
                        $output = array_merge(
                            $output,
                            self::getCalAdressesAllFromOrganizer( $comp )
                        );
                        break;
                    case Vcalendar::CONTACT :
                        $output = array_merge(
                            $output,
                            self::getCalAdressesAllFromContact( $comp )
                        );
                        break;
                } // end switch
            } // end foreach
        } // end while
        sort( $output );
        return array_unique( $output );
    }

    /**
     * Return value and parameters cal-addresses from Vcalendar ATTENDEE property
     *
     * @param CalendarComponent $component  iCalcreator Vcalendar component instance
     * @return array
     * @since  2.29 - 2021-06-19
     */
    public static function getCalAdressesAllFromAttendee( CalendarComponent $component ) : array
    {
        $output = [];
        while(( false !== ( $propValue = $component->getAttendee( null, true ))) &&
            ! empty( $propValue )) {
            $value = self::removeMailtoPrefix( $propValue[Util::$LCvalue] );
            if( ! in_array( $value, $output )) {
                $output[] = $value;
            }
            foreach( $propValue[Util::$LCparams] as $pLabel => $pValue ) {
                switch( $pLabel ) {
                    case Vcalendar::MEMBER:       // fall through
                    case Vcalendar::DELEGATED_TO: // fall through
                    case Vcalendar::DELEGATED_FROM:
                        $params2[$pLabel] = [];
                        foreach( $pValue as $pValue2 ) {
                            $pValue2 = self::removeMailtoPrefix(
                                trim( $pValue2, StringFactory::$QQ )
                            );
                            if( ! in_array( $pValue2, $output )) {
                                $output[] = $pValue2;
                            }
                        } // end foreach
                        break;
                    case Vcalendar::EMAIL :       // fall through
                    case Vcalendar::SENT_BY :
                        $pValue2 = self::removeMailtoPrefix(
                            trim( $pValue, StringFactory::$QQ )
                        );
                        if( ! in_array( $pValue2, $output )) {
                            $output[] = $pValue2;
                        }
                        break;
                } // end switch
            } // end foreach
        } // end while
        return $output;
    }

    /**
     * Return value and parameters cal-addresses from Vcalendar ORGANIZER property
     *
     * @param CalendarComponent $component  iCalcreator Vcalendar component instance
     * @return array
     * @since  2.29 - 2021-06-19
     */
    public static function getCalAdressesAllFromOrganizer( CalendarComponent $component ) : array
    {
        if(( false === ( $propValue = $component->getOrganizer( true ))) ||
            empty( $propValue )) {
            return [];
        }
        $output = [];
        $value = self::removeMailtoPrefix( $propValue[Util::$LCvalue] );
        if( ! in_array( $value, $output )) {
            $output[] = $value;
        }
        foreach( [ Vcalendar::EMAIL, Vcalendar::SENT_BY ] as $key ) {
            if( isset( $propValue[Util::$LCparams][$key] ) ) {
                $value = self::removeMailtoPrefix(
                    $propValue[Util::$LCparams][$key]
                );
                if( ! in_array( $value, $output ) ) {
                    $output[] = $value;
                }
            }
        } // end foreach
        return $output;
    }

    /**
     * Return value and parameters cal-addresses from Vcalendar CONTACT property
     *
     * @param CalendarComponent $component  iCalcreator Vcalendar component instance
     * @return array
     * @since  2.29 - 2021-06-19
     */
    public static function getCalAdressesAllFromContact( CalendarComponent $component ) : array
    {
        $output = [];
        while(( false !== ( $propValue = $component->getContact( null, true ))) &&
            ! empty( $propValue )) {
            $value =
                ( false !== strpos( $propValue[Util::$LCvalue], Util::$COMMA ))
                    ? StringFactory::before( Util::$COMMA, $propValue[Util::$LCvalue] )
                    : $propValue[Util::$LCvalue];
            try {
                self::assertCalAddress( $value );
            }
            catch( InvalidArgumentException $e ) {
                continue;
            }
            if( ! in_array( $value, $output )) {
                $output[] = $value;
            }
        } // end while
        return $output;
    }

    /**
     * Return quoted item
     *
     * @param string $item
     * @return string
     * @since  2.27.11 - 2019-01-03
     */
    private static function getQuotedItem( string $item ) : string
    {
        static $FMTQVALUE = '"%s"';
        return sprintf( $FMTQVALUE, $item );
    }

    /**
     * Return string of comma-separated quoted array members
     *
     * @param array $list
     * @return string
     * @since  2.27.11 - 2019-01-03
     */
    private static function getQuotedListItems( array $list ) : string
    {
        foreach( $list as & $v ) {
            $v = self::getQuotedItem( $v );
        }
        return implode( Util::$COMMA, $list );
    }

    /**
     * Return formatted output for calendar component property attendee
     *
     * @param array  $params
     * @param string $compType
     * @param bool|string $lang  bool false if not config lang found
     * @return array
     * @throws InvalidArgumentException
     * @since  2.39 - 2021-06-17
     */
    public static function inputPrepAttendeeParams(
        array $params,
        string $compType,
        $lang
    ) : array
    {
        static $XX  = 'X-';
        static $NoParamComps = [ Vcalendar::VFREEBUSY, Vcalendar::VALARM ];
        $params2    = [];
        if( is_array( $params )) {
            $params = array_change_key_case( $params, CASE_UPPER );
            foreach( $params as $pLabel => $pValue ) {
                if( ! StringFactory::isXprefixed( $pLabel ) &&
                    Util::isCompInList( $compType, $NoParamComps )) { // skip
                    continue;
                }
                switch( $pLabel ) {
                    case Vcalendar::MEMBER:       // fall through
                    case Vcalendar::DELEGATED_TO: // fall through
                    case Vcalendar::DELEGATED_FROM:
                        $params2[$pLabel] = self::prepInputMDtDf((array) $pValue );
                        break;
                    case Vcalendar::EMAIL :
                        $params2[$pLabel] = self::prepEmail( $pValue );
                        break;
                    case Vcalendar::SENT_BY :
                        $params2[$pLabel] = self::prepSentBy( $pValue );
                        break;
                    default:
                        $params2[$pLabel] = trim( $pValue, StringFactory::$QQ );
                        break;
                } // end switch( $pLabel.. .
            } // end foreach( $params as $pLabel => $optParamValue )
        } // end if( is_array($params ))
        // remove defaults
        ParameterFactory::ifExistRemove(
            $params2,
            Vcalendar::CUTYPE,
            Vcalendar::INDIVIDUAL
        );
        ParameterFactory::ifExistRemove(
            $params2,
            Vcalendar::PARTSTAT,
            Vcalendar::NEEDS_ACTION
        );
        ParameterFactory::ifExistRemove(
            $params2,
            Vcalendar::ROLE,
            Vcalendar::REQ_PARTICIPANT
        );
        ParameterFactory::ifExistRemove(
            $params2,
            Vcalendar::RSVP,
            Vcalendar::FALSE
        );
        // check language setting
        if( isset( $params2[Vcalendar::CN] ) &&
            ! isset( $params2[Vcalendar::LANGUAGE] ) &&
            ! empty( $lang )) {
            $params2[Vcalendar::LANGUAGE] = $lang;
        }
        return $params2;
    }

    /**
     * Prepare input Member, DELEGATED_TO, DELEGATED_FROM parameters
     *
     * @param array $calAddress
     * @return array
     * @throws InvalidArgumentException
     * @since  2.39 - 2021-06-17
     */
    private static function prepInputMDtDf( array $calAddress ) : array
    {
        $output = [];
        foreach( $calAddress as $pValue2 ) {
            if( empty( $pValue2 )) {
                continue;
            }
            $pValue2  = trim( $pValue2, StringFactory::$QQ );
            $pValue2  = self::conformCalAddress( $pValue2, true );
            self::assertCalAddress( $pValue2 );
            $output[] = $pValue2;
        } // end foreach
        return $output;
    }

    /**
     * Prepare input EMAIL parameter (without opt leading MAILTO)
     *
     * @param string $calAddress
     * @return string
     * @throws InvalidArgumentException
     * @since  2.39 - 2021-06-17
     */
    public static function prepEmail( string $calAddress ) : string
    {
        if( 0 == strcasecmp( self::$MAILTOCOLON, substr( $calAddress, 0, 7 ))) {
            $calAddress = substr( $calAddress, 7 );
        }
        self::assertCalAddress( $calAddress );
        return $calAddress;
    }

    /**
     * Prepare input SENT_BY parameter, force leading MAILTO
     *
     * @param string $calAddress
     * @return string
     * @throws InvalidArgumentException
     * @since  2.39 - 2021-06-17
     */
    public static function prepSentBy( string $calAddress ) : string
    {
        $calAddress = self::conformCalAddress(
            trim( $calAddress, StringFactory::$QQ ),
            true
        );
        self::assertCalAddress( $calAddress );
        return $calAddress;
    }

    /**
     * Return formatted output for calendar component property attendee
     *
     * @param array $attendeeData
     * @param bool  $allowEmpty
     * @return string
     * @since  2.29.11 - 2019-08-30
     */
    public static function outputFormatAttendee(
        array $attendeeData,
        bool $allowEmpty
    ) : string
    {
        static $AllKeys = [
            Vcalendar::CUTYPE,
            Vcalendar::MEMBER,
            Vcalendar::ROLE,
            Vcalendar::PARTSTAT,
            Vcalendar::RSVP,
            Vcalendar::DELEGATED_TO,
            Vcalendar::DELEGATED_FROM,
            Vcalendar::SENT_BY,
            Vcalendar::EMAIL,
            Vcalendar::DIR,
            Vcalendar::CN,
            Vcalendar::LANGUAGE
        ];
        static $KEYGRP1 = [ Vcalendar::ROLE, Vcalendar::PARTSTAT, Vcalendar::RSVP ];
        static $KEYGRP2 = [ Vcalendar::DELEGATED_TO, Vcalendar::DELEGATED_FROM ];
        static $KEYGRP3 = [ Vcalendar::SENT_BY, Vcalendar::EMAIL ];
        static $KEYGRP4 = [ Vcalendar::CN, Vcalendar::LANGUAGE ];
        static $FMTKEYVALUE = ';%s=%s';
        static $FMTDIREQ    = ';%s=%s%s%s';
        $output = Util::$SP0;
        foreach( $attendeeData as $ax => $attendeePart ) {
            if( empty( $attendeePart[Util::$LCvalue] )) {
                if( $allowEmpty ) {
                    $output .= StringFactory::createElement( Vcalendar::ATTENDEE );
                }
                continue;
            }
            $attributes = $content = null;
            foreach( $attendeePart as $pLabel => $pValue ) {
                if( Util::$LCvalue == $pLabel ) {
                    $content .= $pValue;
                    continue;
                }
                if(( Util::$LCparams != $pLabel ) ||
                    ( ! is_array( $pValue ))) {
                    continue;
                }
                foreach( $pValue as $pLabel2 => $pValue2 ) { // fix (opt) quotes
                    if( is_array( $pValue2 ) ||
                        in_array( $pLabel2, self::$ParamArrayKeys )) {
                        continue;
                    } // all but DELEGATED-FROM, DELEGATED-TO, MEMBER
                    if(( false !== strpos( $pValue2, Util::$COLON )) ||
                       ( false !== strpos( $pValue2, Util::$SEMIC )) ||
                       ( false !== strpos( $pValue2, Util::$COMMA ))) {
                        $pValue[$pLabel2] = self::getQuotedItem( $pValue2 );
                    }
                } // end foreach
                /* set attendee parameters in (almost) rfc2445 order */
                if( isset( $pValue[Vcalendar::CUTYPE] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, Vcalendar::CUTYPE, $pValue[Vcalendar::CUTYPE] );
                }
                if( isset( $pValue[Vcalendar::MEMBER] )) {
                    $attributes .= sprintf(
                        $FMTKEYVALUE,
                        Vcalendar::MEMBER,
                        self::getQuotedListItems( $pValue[Vcalendar::MEMBER] )
                    );
                }
                foreach( $KEYGRP1 as $key ) { // ROLE, PARTSTAT, RSVP
                    if( isset( $pValue[$key] ) ) {
                        $attributes .= sprintf( $FMTKEYVALUE, $key, $pValue[$key] );
                    }
                } // end foreach
                foreach( $KEYGRP2 as $key ) { // DELEGATED_TO, DELEGATED_FROM
                    if( isset( $pValue[$key] ) ) {
                        $attributes .= sprintf(
                            $FMTKEYVALUE,
                            $key,
                            self::getQuotedListItems( $pValue[$key] )
                        );
                    }
                } // end foreach
                foreach( $KEYGRP3 as $key ) { // SENT_BY, EMAIL
                    if( isset( $pValue[$key] ) ) {
                        $attributes .= sprintf( $FMTKEYVALUE, $key, self::getQuotedListItems( [ $pValue[$key] ] ));
                    }
                } // end foreach
                if( isset( $pValue[Vcalendar::DIR] )) {
                    $delim       = ( false === strpos( $pValue[Vcalendar::DIR], StringFactory::$QQ )) ? StringFactory::$QQ : null;
                    $attributes .= sprintf( $FMTDIREQ, Vcalendar::DIR, $delim, $pValue[Vcalendar::DIR], $delim );
                }
                foreach( $KEYGRP4 as $key ) { // CN, LANGUAGE
                    if( isset( $pValue[$key] )) {
                        $attributes .= sprintf( $FMTKEYVALUE, $key, $pValue[$key] );
                    }
                } // end foreach
                $xParams = [];
                foreach( $pValue as $pLabel2 => $pValue2 ) {
                    if( ! in_array( $pLabel2, $AllKeys )) {
                        $xParams[$pLabel2] = $pValue2;
                    }
                }
                if( ! empty( $xParams )) {
                    ksort( $xParams, SORT_STRING );
                    foreach( $xParams as $pLabel2 => $pValue2 ) {
                        $attributes .= sprintf( $FMTKEYVALUE, $pLabel2, $pValue2 );
                    }
                }
            } // end foreach( $attendeePart )) as $pLabel => $pValue )
            $output .= StringFactory::createElement( Vcalendar::ATTENDEE, $attributes, $content );
        } // end foreach( $attendeeData as $ax => $attendeePart )
        return $output;
    }

    /**
     * Return value and parameters from parsed row and propAttr
     *
     * @param string $row
     * @param array $propAttr
     * @return array
     * @since  2.27.11 - 2019-01-04
     */
    public static function parseAttendee( string $row, array $propAttr ) : array
    {
        foreach( $propAttr as $pix => $attr ) {
            if( ! in_array( strtoupper( $pix ), self::$ParamArrayKeys )) {
                continue;
            }  // 'MEMBER', 'DELEGATED-TO', 'DELEGATED-FROM'
            $attr2 = explode( Util::$COMMA, $attr );
            if( 1 < count( $attr2 )) {
                $propAttr[$pix] = $attr2;
            }
        }
        return [ $row, $propAttr ];
    }
}
