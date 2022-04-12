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
namespace Kigkonsult\Icalcreator\Util;

use InvalidArgumentException;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\CalendarComponent;

use function count;
use function explode;
use function in_array;
use function is_array;
use function method_exists;
use function sprintf;
use function strcasecmp;
use function strtoupper;
use function substr;
use function trim;

/**
 * iCalcreator attendee support class
 *
 * @since 2022-01-31 2.41.15
 */
class CalAddressFactory
{
    /**
     * @var string[]
     */
    private static array $CALADDRESSPROPERTIES = [
        IcalInterface::ATTENDEE,
        IcalInterface::CONTACT,
        IcalInterface::ORGANIZER,
        IcalInterface::PARTICIPANT
    ];

    /**
     * Attendee parameters with cal-address
     *
     * @var string[]
     */
    private static array $ParamArrayKeys = [
        IcalInterface::MEMBER,
        IcalInterface::DELEGATED_TO,
        IcalInterface::DELEGATED_FROM,
    ];

    /**
     * @var string Prefix for Ical cal-address etc
     */
    public  static string $MAILTOCOLON = 'MAILTO:';
    private static string $AT          = '@';

    /**
     * Assert cal-address (i.e. MAILTO.prefixed)
     *
     * @param string $calAddress
     * @return void
     * @throws InvalidArgumentException
     * @since  2.27.8 - 2019-03-18
     */
    public static function assertCalAddress( string $calAddress ) : void
    {
        static $DOT    = '.';
        static $XDOT   = 'x.';
        static $ERRMSG = 'Invalid email %s';
        if( ! str_contains( $calAddress, self::$AT )) {
            throw new InvalidArgumentException( sprintf( $ERRMSG, $calAddress ));
        }
        $domain = StringFactory::after( self::$AT, $calAddress );
        if( !str_contains( StringFactory::before( self::$AT, $calAddress ), $DOT )) {
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
    public static function conformCalAddress( string $calAddress, ? bool $forceMailto = false ) : string
    {
        switch( true ) {
            case empty( $calAddress ) :
                break;
            case ( 0 === strcasecmp( self::$MAILTOCOLON, substr( $calAddress, 0, 7 ))) :
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
        return ( 0 === strcasecmp( self::$MAILTOCOLON, substr( $email, 0, 7 )));
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
     * @param Pc   $contents
     * @return void
     * @since  2.41.36 - 2022-03-31
     */
    public static function sameValueAndEMAILparam( Pc $contents ) : void
    {
        if( $contents->hasParamKey( IcalInterface::EMAIL ) &&
            ( 0 === strcasecmp(
                self::removeMailtoPrefix( $contents->value ),
                self::removeMailtoPrefix( $contents->getParams( IcalInterface::EMAIL ))
            ))) {
            $contents->removeParam( IcalInterface::EMAIL );
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
     * @param Vcalendar     $calendar    iCalcreator Vcalendar instance
     * @param null|string[] $properties
     * @return string[]
     * @since  2.39 - 2021-06-14
     */
    public static function getCalAddresses(
        Vcalendar $calendar,
        ? array $properties = []
    ) : array
    {
        $searchProperties = [];
        if( empty( $properties )) {
            $searchProperties = self::$CALADDRESSPROPERTIES;
        }
        else {
            foreach( $properties as $property ) {
                if( in_array( $property, self::$CALADDRESSPROPERTIES, true )) {
                    $searchProperties[] = $property;
                }
            } // end foreach
        }
        $output = [];
        foreach( $searchProperties as $propName ) {
            foreach( self::getCalAdressValuesFromProperty( $calendar, $propName ) as $calAddress ) {
                $output[] = $calAddress;
            }
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
     * @return string[]
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
        foreach((array) $propValues as $propValue => $counts ) {
            $propValue = self::removeMailtoPrefix( $propValue );
            if( str_contains( $propValue, Util::$COMMA )) {
                $propValue = StringFactory::before( Util::$COMMA, $propValue );
            }
            try {
                self::assertCalAddress( $propValue );
            }
            catch( InvalidArgumentException $e ) {
                continue;
            }
            if( ! in_array( $propValue, $output, true )) {
                $output[] = $propValue;
            }
        } // end foreach
        sort( $output );
        return array_unique( $output );
    }

    /**
     * Return value and parameters cal-addresses from Vcalendar properties
     *
     * From one of ATTENDEEs and ORGANIZERs, name part from CONTACTs
     *
     * @param Vcalendar    $calendar  iCalcreator Vcalendar instance
     * @param null|string|string[]  $propName
     * @return string[]
     * @since  2.39 - 2021-06-19
     */
    public static function getCalAdressesAllFromProperty(
        Vcalendar $calendar,
        null | string | array $propName = null
    ) : array
    {
        if( empty( $propName )) {
            $propName = self::$CALADDRESSPROPERTIES;
        }
        $calendar->resetCompCounter();
        $output = [];
        while( $comp = $calendar->getComponent()) {
            foreach((array) $propName as $pName ) {
                $method = StringFactory::getGetMethodName( $pName );
                if( ! method_exists( $comp, $method )) {
                    continue;
                }
                switch( $pName ) {
                    case IcalInterface::ATTENDEE :
                        foreach( self::getCalAdressesAllFromAttendee( $comp ) as $email ) {
                            if( ! in_array( $email, $output, true )) {
                                $output[] = $email;
                            }
                        }
                        break;
                    case IcalInterface::ORGANIZER :
                        foreach( self::getCalAdressesAllFromOrganizer( $comp ) as $email ) {
                            if( ! in_array( $email, $output, true )) {
                                $output[] = $email;
                            }
                        }
                        break;
                    default :
                        foreach( self::getCalAdressesAllFromContact( $comp ) as $email ) {
                            if( ! in_array( $email, $output, true )) {
                                $output[] = $email;
                            }
                        }
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
     * @return string[]
     * @since  2.41.36 - 2022-04-03
     */
    public static function getCalAdressesAllFromAttendee( CalendarComponent $component ) : array
    {
        $output = [];
        while(( false !== ( $propValue = $component->getAttendee( null, true ))) &&
            ! empty( $propValue )) {
            $value = self::removeMailtoPrefix( $propValue->value );
            if( !in_array( $value, $output, true )) {
                $output[] = $value;
            }
            foreach( $propValue->params as $pLabel => $pValue ) {
                switch( $pLabel ) {
                    case IcalInterface::MEMBER:       // fall through
                    case IcalInterface::DELEGATED_TO: // fall through
                    case IcalInterface::DELEGATED_FROM:
                        $params2[$pLabel] = [];
                        foreach( $pValue as $pValue2 ) {
                            $pValue2 = self::removeMailtoPrefix( trim( $pValue2, StringFactory::$QQ ));
                            if( !in_array( $pValue2, $output, true )) {
                                $output[] = $pValue2;
                            }
                        } // end foreach
                        break;
                    case IcalInterface::EMAIL :       // fall through
                    case IcalInterface::SENT_BY :
                        $pValue2 = self::removeMailtoPrefix(
                            trim( $pValue, StringFactory::$QQ )
                        );
                        if( ! in_array( $pValue2, $output, true )) {
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
     * @return string[]
     * @since  2.41.36 - 2022-04-03
     */
    public static function getCalAdressesAllFromOrganizer( CalendarComponent $component ) : array
    {
        if(( false === ( $propValue = $component->getOrganizer( true ))) ||
            empty( $propValue )) {
            return [];
        }
        $output = [];
        $value  = self::removeMailtoPrefix( $propValue->value );
        if( ! in_array( $value, $output, true )) {
            $output[] = $value;
        }
        foreach( [ IcalInterface::EMAIL, IcalInterface::SENT_BY ] as $key ) {
            if( $propValue->hasParamkey( $key )) {
                $value = self::removeMailtoPrefix( $propValue->getParams( $key ));
                if( ! in_array( $value, $output, true )) {
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
     * @return string[]
     * @since  2.29 - 2021-06-19
     */
    public static function getCalAdressesAllFromContact( CalendarComponent $component ) : array
    {
        $output = [];
        while(( false !== ( $propValue = $component->getContact( null, true ))) &&
            ! empty( $propValue )) {
            $value =
                (str_contains( $propValue->value, Util::$COMMA ))
                    ? StringFactory::before( Util::$COMMA, $propValue->value )
                    : $propValue->value;
            try {
                self::assertCalAddress( $value );
            }
            catch( InvalidArgumentException $e ) {
                continue;
            }
            if( ! in_array( $value, $output, true )) {
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
     * @param string[] $list
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
     * @param string[]  $params
     * @param string $compType
     * @param bool|string $lang  bool false if not config lang found
     * @return string[]
     * @throws InvalidArgumentException
     * @since  2.39 - 2021-06-17
     */
    public static function inputPrepAttendeeParams(
        array $params,
        string $compType,
        bool | string $lang
    ) : array
    {
        static $NoParamComps = [ IcalInterface::VFREEBUSY, IcalInterface::VALARM ];
        $params2 = [];
        foreach( $params as $pLabel => $pValue ) {
            if( ! StringFactory::isXprefixed( $pLabel ) &&
                Util::isCompInList( $compType, $NoParamComps )) { // skip
                continue;
            }
            $params2[$pLabel] = match( $pLabel ) {
                IcalInterface::MEMBER, IcalInterface::DELEGATED_TO, IcalInterface::DELEGATED_FROM
                                       => self::prepInputMDtDf((array) $pValue ),
                IcalInterface::EMAIL   => self::prepEmail( $pValue ),
                IcalInterface::SENT_BY => self::prepSentBy( $pValue ),
                default                => trim( $pValue, StringFactory::$QQ ),
            }; // end match( $pLabel.. .
        } // end foreach( $params as $pLabel => $optParamValue )
        // remove defaults
        if( isset( $params2[IcalInterface::CUTYPE] ) &&
            ( IcalInterface::INDIVIDUAL === $params2[IcalInterface::CUTYPE] )) {
            unset( $params2[IcalInterface::CUTYPE] );
        }
        if( isset( $params2[IcalInterface::PARTSTAT] ) &&
            ( IcalInterface::NEEDS_ACTION === $params2[IcalInterface::PARTSTAT] )) {
            unset( $params2[IcalInterface::PARTSTAT] );
        }
        if( isset( $params2[IcalInterface::ROLE] ) &&
            ( IcalInterface::REQ_PARTICIPANT === $params2[IcalInterface::ROLE] )) {
            unset( $params2[IcalInterface::ROLE] );
        }
        if( isset( $params2[IcalInterface::RSVP] ) &&
            ( IcalInterface::FALSE === $params2[IcalInterface::RSVP] )) {
            unset( $params2[IcalInterface::RSVP] );
        }
        // check language setting
        if( isset( $params2[IcalInterface::CN] ) &&
            ! isset( $params2[IcalInterface::LANGUAGE] ) &&
            ! empty( $lang )) {
            $params2[IcalInterface::LANGUAGE] = $lang;
        }
        return $params2;
    }

    /**
     * Prepare input Member, DELEGATED_TO, DELEGATED_FROM parameters
     *
     * @param string[] $calAddress
     * @return string[]
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
        if( 0 === strcasecmp( self::$MAILTOCOLON, substr( $calAddress, 0, 7 ))) {
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
     * @param Pc[] $attendeeData
     * @param bool    $allowEmpty
     * @return string
     * @since  2022-04-04 2.41.37
     */
    public static function outputFormatAttendee(
        array $attendeeData,
        bool $allowEmpty
    ) : string
    {
        static $AllKeys = [
            IcalInterface::CUTYPE,
            IcalInterface::MEMBER,
            IcalInterface::ROLE,
            IcalInterface::PARTSTAT,
            IcalInterface::RSVP,
            IcalInterface::DELEGATED_TO,
            IcalInterface::DELEGATED_FROM,
            IcalInterface::SENT_BY,
            IcalInterface::EMAIL,
            IcalInterface::DIR,
            IcalInterface::CN,
            IcalInterface::LANGUAGE
        ];
        static $KEYGRP1 = [ IcalInterface::ROLE, IcalInterface::PARTSTAT, IcalInterface::RSVP ];
        static $KEYGRP2 = [ IcalInterface::DELEGATED_TO, IcalInterface::DELEGATED_FROM ];
        static $KEYGRP3 = [ IcalInterface::SENT_BY, IcalInterface::EMAIL ];
        static $KEYGRP4 = [ IcalInterface::CN, IcalInterface::LANGUAGE ];
        static $FMTKEYVALUE = ';%s=%s';
        static $FMTDIREQ    = ';%s=%s%s%s';
        $output = Util::$SP0;
        foreach( array_keys( $attendeeData ) as $aPix ) { // Pc
            $attendeePart = clone $attendeeData[$aPix];
            if( empty( $attendeePart->value )) {
                if( $allowEmpty ) {
                    $output .= StringFactory::createElement( IcalInterface::ATTENDEE );
                }
                continue;
            }
            $content     = $attendeePart->value;
            if( empty( $attendeePart->params )) {
                $output .= StringFactory::createElement( IcalInterface::ATTENDEE, null, $content );
                continue;
            }
            $attributes = Util::$SP0;
            foreach( $attendeePart->params as $pLabel2 => $pValue2 ) { // fix (opt) quotes
                if( is_array( $pValue2 ) || in_array( $pLabel2, self::$ParamArrayKeys, true )) {
                    continue;
                } // all but DELEGATED-FROM, DELEGATED-TO, MEMBER
                $attendeePart->params[$pLabel2] = ParameterFactory::circumflexQuoteInvoke( $pValue2 );
                if( StringFactory::hasColonOrSemicOrComma( $pValue2 )) {
                    $attendeePart->params[$pLabel2] = self::getQuotedItem( $pValue2 );
                }
            } // end foreach
            /* set attendee parameters in (almost) rfc2445 order */
            if( isset( $attendeePart->params[IcalInterface::CUTYPE] )) {
                $attributes .= sprintf(
                    $FMTKEYVALUE,
                    IcalInterface::CUTYPE,
                    $attendeePart->params[IcalInterface::CUTYPE]
                );
            }
            if( isset( $attendeePart->params[IcalInterface::MEMBER] )) {
                $attributes .= sprintf(
                    $FMTKEYVALUE,
                    IcalInterface::MEMBER,
                    self::getQuotedListItems( $attendeePart->params[IcalInterface::MEMBER] )
                );
            }
            foreach( $KEYGRP1 as $key ) { // ROLE, PARTSTAT, RSVP
                if( isset( $attendeePart->params[$key] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, $key, $attendeePart->params[$key] );
                }
            } // end foreach
            foreach( $KEYGRP2 as $key ) { // DELEGATED_TO, DELEGATED_FROM
                if( isset( $attendeePart->params[$key] )) {
                    $attributes .= sprintf(
                        $FMTKEYVALUE,
                        $key,
                        self::getQuotedListItems( $attendeePart->params[$key] )
                    );
                }
            } // end foreach
            foreach( $KEYGRP3 as $key ) { // SENT_BY, EMAIL
                if( isset( $attendeePart->params[$key] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, $key, $attendeePart->params[$key] );
                }
            } // end foreach
            if( isset( $attendeePart->params[IcalInterface::DIR] )) {
                $delim = ( ! str_contains( $attendeePart->params[IcalInterface::DIR], StringFactory::$QQ ))
                    ? StringFactory::$QQ
                    : null;
                $attributes .= sprintf(
                    $FMTDIREQ,
                    IcalInterface::DIR,
                    $delim,
                    $attendeePart->params[IcalInterface::DIR],
                    $delim
                );
            }
            foreach( $KEYGRP4 as $key ) { // CN, LANGUAGE
                if( isset( $attendeePart->params[$key] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, $key, $attendeePart->params[$key] );
                }
            } // end foreach
            $xParams = [];
            foreach( $attendeePart->params as $pLabel2 => $pValue2 ) {
                if( ! in_array( $pLabel2, $AllKeys, true )) {
                    $xParams[$pLabel2] = $pValue2;
                }
            }
            if( ! empty( $xParams )) {
                ksort( $xParams, SORT_STRING );
                foreach( $xParams as $pLabel2 => $pValue2 ) {
                    $attributes .= sprintf( $FMTKEYVALUE, $pLabel2, $pValue2 );
                }
            }
            $output .= StringFactory::createElement( IcalInterface::ATTENDEE, $attributes, $content );
        } // end foreach( $attendeeData as $ax => $attendeePart )
        return $output;
    }

    /**
     * Split multiple Attendees MEMBER/DELEGATED-TO/DELEGATED-FROM into array, if found
     *
     * @param mixed[] $propAttr
     * @return mixed[]
     * @since  2.27.11 - 2019-01-04
     */
    public static function splitMultiParams( array $propAttr ) : array
    {
        foreach( $propAttr as $pix => $attr ) {
            if( ! in_array( strtoupper( $pix ), self::$ParamArrayKeys, true )) {
                continue;
            }  // 'MEMBER', 'DELEGATED-TO', 'DELEGATED-FROM'
            $attr2 = explode( Util::$COMMA, $attr );
            if( 1 < count( $attr2 )) {
                $propAttr[$pix] = $attr2;
            }
        }
        return $propAttr;
    }
}
