<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
*/

namespace Kigkonsult\Icalcreator;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\HttpFactory;
use Kigkonsult\Icalcreator\Util\SelectFactory;
use Kigkonsult\Icalcreator\Util\SortFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\VtimezonePopulateFactory;
use UnexpectedValueException;

use function array_keys;
use function count;
use function ctype_digit;
use function end;
use function explode;
use function gethostbyname;
use function implode;
use function in_array;
use function is_array;
use function is_null;
use function is_string;
use function ksort;
use function method_exists;
use function property_exists;
use function rtrim;
use function strcasecmp;
use function strlen;
use function stripos;
use function strpos;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;
use function usort;

/**
 * Vcalendar class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.29.16 - 2020-01-25
 */
final class Vcalendar extends IcalBase
{
    use Traits\CALSCALEtrait,
        Traits\METHODtrait,
        Traits\PRODIDtrait,
        Traits\VERSIONtrait;
    // The following are OPTIONAL, but MUST NOT occur more than once.
    use Traits\UIDrfc7986trait,
        Traits\LAST_MODIFIEDtrait,
        Traits\URLtrait,
        Traits\REFRESH_INTERVALrfc7986trait,
        Traits\SOURCErfc7986trait,
        Traits\COLORrfc7986trait;
    // The following are OPTIONAL, and MAY occur more than once.
    use Traits\NAMErfc7986trait,
        Traits\DESCRIPTIONtrait,
        Traits\CATEGORIEStrait,
        Traits\IMAGErfc7986trait;

    /**
     * @const
     */
    const VCALENDAR = 'Vcalendar';

    /**
     * @var string property output formats, used by CALSCALE, METHOD, PRODID and VERSION
     */
    private static $FMTICAL = "%s:%s\r\n";

    /**
     * @var array  iCal component date-property collection
     */
    private static $DATEPROPS  = [
        self::DTSTART, self::DTEND, self::DUE, self::CREATED, self::COMPLETED,
        self::DTSTAMP, self::LAST_MODIFIED, self::RECURRENCE_ID,
    ];

    /**
     * Constructor for calendar object
     *
     * @param array $config
     * @since  2.29.5 - 2019-06-20
     */
    public function __construct( $config = [] )
    {
        static $SERVER_NAME = 'SERVER_NAME';
        static $LOCALHOST   = 'localhost';
        $this->compType     = self::VCALENDAR;
        $this->setConfig(
            self::UNIQUE_ID,
            ( isset( $_SERVER[$SERVER_NAME] ))
                ? gethostbyname( $_SERVER[$SERVER_NAME] )
                : $LOCALHOST
        );
        $this->setConfig( $config );
        $this->setUid();
    }

    /**
     * Destructor
     *
     * @since  2.29.5 - 2019-06-20
     */
    public function __destruct()
    {
        if( ! empty( $this->components )) {
            foreach( $this->components as $cix => $comp ) {
                $this->components[$cix]->__destruct();
            }
        }
        unset(
            $this->compType,
            $this->xprop,
            $this->components,
            $this->unparsed,
            $this->config,
            $this->compix,
            $this->propIx,
            $this->propDelIx
        );
        unset(
            $this->calscale,
            $this->method,
            $this->prodid,
            $this->version,
            $this->uid,
            $this->lastmodified,
            $this->url,
            $this->refreshinterval,
            $this->source,
            $this->color,
            $this->name,
            $this->description,
            $this->categories,
            $this->image
        );
    }

    /**
     * Return iCalcreator instance, factory method
     *
     * @param array $config
     * @return static
     * @since  2.18.5 - 2013-08-29
     */
    public static function factory( $config = [] )
    {
        return new self( $config );
    }

    /**
     * Return iCalcreator version
     *
     * @return string
     * @since  2.18.5 - 2013-08-29
     */
    public static function iCalcreatorVersion()
    {
        return trim(
            substr(
                ICALCREATOR_VERSION,
                strpos( ICALCREATOR_VERSION, Util::$SP1 )
            )
        );
    }

    /**
     * Return calendar component properties value(s)
     *
     * CATEGORIES, LOCATION, GEOLOCATION, PRIORITY, RESOURCES, STATUS, SUMMARY
     * DTSTART (Ymd only)
     * ATTENDEE*, CONTACT, ORGANIZER*   *:prefixed by "protocol" like "MAILTO:....
     * RECURRENCE-ID *4 (alt. "R-UID")
     * RELATED-TO, URL, UID
     * @param string $propName
     * @return mixed
     * @since  2.29.17 - 2020-01-25
     */
    public function getProperty( $propName )
    {
        static $PROPS = [
            self::ATTENDEE,
            self::CATEGORIES,
            self::CONTACT,
            self::DTSTART,
            self::GEOLOCATION,
            self::LOCATION,
            self::ORGANIZER,
            self::PRIORITY,
            self::RESOURCES,
            self::STATUS,
            self::SUMMARY,
            'RECURRENCE-ID-UID',
            self::RELATED_TO,
            'R-UID',
            self::UID,
            self::URL
        ];
        $propName = strtoupper( $propName );
        if( ! Util::isPropInList( $propName, $PROPS )) {
            return false;
        }
        $output = [];
        foreach( $this->components as $cix => $component ) {
            switch( true ) {
                case ( ! Util::isCompInList( $component->getCompType(), self::$VCOMPS )) :
                    continue 2;
                case ( ! property_exists(
                    $component,
                    self::getInternalPropName( $propName ))
                ) :
                    continue 2;
                case ( Util::isPropInList( $propName, self::$MPROPS1 )) :
                    $component->getProperties( $propName, $output );
                    continue 2;
                case (( 3 < strlen( $propName )) &&
                    ( self::UID == substr( $propName, -3 ))) :
                    if( false !== ( $content = $component->getRecurrenceid())) {
                        $content = $component->getUid();
                    }
                    break;
                case (( self::GEOLOCATION == $propName ) &&
                    ( ! property_exists( $component, strtolower( self::GEO )) ||
                        ( false === ( $content = $component->getGeoLocation())))) :
                    continue 2;
                default :
                    $method = parent::getGetMethodName( $propName );
                    if( ! method_exists( $component, $method ) ||
                        ( false === ( $content = $component->{$method}()))) {
                        continue 2;
                    }
            } // end switch
            if(( false === $content ) || empty( $content )) {
                continue;
            }
            switch( true ) {
                case ( $content instanceof DateTime ) :
                    $key = $content->format( DateTimeFactory::$Ymd );
                    if( ! isset( $output[$key] )) {
                        $output[$key] = 1;
                    }
                    else {
                        $output[$key] += 1;
                    }
                    break;
                case ( is_array( $content )) :
                    foreach( $content as $partKey => $partValue ) {
                        if( ! isset( $output[$partKey] )) {
                            $output[$partKey] = $partValue;
                        }
                        else {
                            $output[$partKey] += $partValue;
                        }
                    } // end foreach
                    break;
                case ( ! isset( $output[$content] )) :
                    $output[$content] = 1;
                    break;
                default :
                    $output[$content] += 1;
                    break;
            } // end switch
        } // end foreach( $this->components as $cix => $component)
        if( ! empty( $output )) {
            ksort( $output );
        }
        return $output;
    }

    /**
     * Return clone of calendar component
     *
     * @param mixed $arg1 ordno/component type/component uid
     * @param mixed $arg2 ordno if arg1 = component type
     * @return mixed CalendarComponent|bool (false on error)
     * @since  2.27.14 - 2019-02-20
     * @todo throw InvalidArgumentException on unknown component
     */
    public function getComponent( $arg1 = null, $arg2 = null )
    {
        $index = $argType = null;
        switch( true ) {
            case is_null( $arg1 ) : // first or next in component chain
                $argType = self::$INDEX;
                if( isset( $this->compix[self::$INDEX] )) {
                    $this->compix[self::$INDEX] = $this->compix[self::$INDEX] + 1;
                }
                else {
                    $this->compix[self::$INDEX] = 1;
                }
                $index = $this->compix[self::$INDEX];
                break;
            case is_array( $arg1 ) : // [ *[propertyName => propertyValue] ]
                $arg2 = implode( Util::$MINUS, array_keys( $arg1 ));
                if( isset( $this->compix[$arg2] )) {
                    $this->compix[$arg2] = $this->compix[$arg2] + 1;
                }
                else {
                    $this->compix[$arg2] = 1;
                }
                $index = $this->compix[$arg2];
                break;
            case ctype_digit((string) $arg1 ) : // specific component in chain
                $argType      = self::$INDEX;
                $index        = (int) $arg1;
                $this->compix = [];
                break;
            case Util::isCompInList( $arg1, self::$CALCOMPS ) : // component type
                unset( $this->compix[self::$INDEX] );
                $argType = $arg1;
                if( is_null( $arg2 )) {
                    if( isset( $this->compix[$argType] )) {
                        $this->compix[$argType] = $this->compix[$argType] + 1;
                    }
                    else {
                        $this->compix[$argType] = 1;
                    }
                    $index = $this->compix[$argType];
                }
                elseif( isset( $arg2 ) && ctype_digit((string) $arg2 )) {
                    $index = (int) $arg2;
                }
                break;
            case is_string( $arg1 ) : // assume UID as 1st argument
                if( is_null( $arg2 )) {
                    if( isset( $this->compix[$arg1] )) {
                        $this->compix[$arg1] = $this->compix[$arg1] + 1;
                    }
                    else {
                        $this->compix[$arg1] = 1;
                    }
                    $index = $this->compix[$arg1];
                }
                elseif( isset( $arg2 ) && ctype_digit((string) $arg2 )) {
                    $index = (int) $arg2;
                }
                break;
        } // end switch( true )
        if( isset( $index )) {
            $index -= 1;
        }
        $cKeys = array_keys( $this->components );
        if( ! empty( $index ) && ( $index > end( $cKeys ))) {
            return false;
        }
        $cix1gC = 0;
        foreach( $cKeys as $cix ) {
            switch( true ) {
                case  empty( $this->components[$cix] ) :
                    break;
                case (( self::$INDEX == $argType ) && ( $index == $cix )) :
                    return clone $this->components[$cix];
                    break;
                case ( 0 == strcasecmp(
                    $argType,
                    $this->components[$cix]->getCompType()
                    )) :
                    if( $index == $cix1gC ) {
                        return clone $this->components[$cix];
                    }
                    $cix1gC++;
                    break;
                case is_array( $arg1 ) : // [ *[propertyName => propertyValue] ]
                    if( self::isFoundInCompsProps( $this->components[$cix], $arg1 )) {
                        if( $index == $cix1gC ) {
                            return clone $this->components[$cix];
                        }
                        $cix1gC++;
                    }
                    break;
                case ( ! $argType && ( $arg1 == $this->components[$cix]->getUid())) :
                    if( $index == $cix1gC ) {
                        return clone $this->components[$cix];
                    }
                    $cix1gC++;
                    break;
            } // end switch
        } // end foreach( $cKeys as $cix )
        /* not found.. . */
        $this->compix = [];
        return false;
    }

    /**
     * Return bool true on argList values found in any component property
     *
     * @param CalendarComponent $component
     * @param array             $argList
     * @return bool
     * @since  2.29.17 - 2020-01-25
     */
    private static function isFoundInCompsProps(
        CalendarComponent $component,
        array $argList
    ) {
        foreach( $argList as $propName => $propValue ) {
            switch( true ) {
                case ( ! Util::isPropInList( $propName, self::$DATEPROPS ) &&
                    ! Util::isPropInList( $propName, Vcalendar::$OTHERPROPS )) :
                    continue 2;
                    break;
                case ( ! property_exists( $component, parent::getInternalPropName( $propName ))) :
                    continue 2;
                    break;
                case ( Util::isPropInList( $propName, self::$MPROPS1 )) : // multiple occurrence
                    $propValues = [];
                    $component->getProperties( $propName, $propValues );
                    if( in_array( $propValue, array_keys( $propValues ))) {
                        return true;
                    }
                    continue 2;
                    break;
            } // end switch
            $method = parent::getGetMethodName( $propName );
            if( ! method_exists( $component, $method )) {
                continue;
            }
            if( false === ( $value = $component->{$method}())) { // single occurrence
                continue; // missing/empty property
            }
            switch( true ) {
                case ( self::SUMMARY == $propName ) : // exists in (any case)
                    if( false !== stripos( $value, $propValue )) {
                        return true;
                    }
                    continue 2;
                    break;
                case ( Util::isPropInList( $propName, self::$DATEPROPS )) :
                    $fmt       = ( 9 > strlen( $propValue ))
                        ? DateTimeFactory::$Ymd
                        : DateTimeFactory::$YmdHis;
                    $valueDate = $value->format( $fmt );
                    if( $propValue == $valueDate ) {
                        return true;
                    }
                    continue 2;
                    break;
                case ! is_array( $value ) :
                    $value = [ $value ];
                    break;
            } // end switch
            foreach( $value as $part ) {
                $part = ( false !== strpos( $part, Util::$COMMA ))
                    ? explode( Util::$COMMA, $part )
                    : [ $part ];
                foreach( $part as $subPart ) {
                    if( $propValue == $subPart ) {
                        return true;
                    }
                }
            } // end foreach( $value as $part )
        } // end  foreach( $arg1 as $propName => $propValue )
        return false;
    }

    /**
     * Return Vevent object instance
     *
     * @return Vevent
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.27.14 - 2018-02-19
     */
    public function newVevent()
    {
        $comp = new Vevent( $this->getConfig());
        $comp->getDtstamp();
        $comp->getUid();
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = $comp;
        return $comp;
    }

    /**
     * Return Vtodo object instance
     *
     * @return Vtodo
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.27.14 - 2018-02-19
     */
    public function newVtodo()
    {
        $comp = new Vtodo( $this->getConfig());
        $comp->getDtstamp();
        $comp->getUid();
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = $comp;
        return $comp;
    }

    /**
     * Return Vjournal object instance
     *
     * @return Vjournal
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.27.14 - 2018-02-19
     */
    public function newVjournal()
    {
        $comp = new Vjournal( $this->getConfig());
        $comp->getDtstamp();
        $comp->getUid();
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = $comp;
        return $comp;
    }

    /**
     * Return Vfreebusy object instance
     *
     * @return Vfreebusy
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.27.14 - 2018-02-19
     */
    public function newVfreebusy()
    {
        $comp = new Vfreebusy( $this->getConfig());
        $comp->getDtstamp();
        $comp->getUid();
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = $comp;
        return $comp;
    }

    /**
     * Return Vtimezone object instance
     *
     * @return Vtimezone
     * @since  2.29.8 - 2019-07-03
     */
    public function newVtimezone()
    {
        $vTimezones = $others = [];
        foreach( array_keys( $this->components ) as $cix ) {
            if( self::VTIMEZONE == $this->components[$cix]->getCompType()) {
                $vTimezones[] = clone $this->components[$cix];
                continue;
            }
            $others[] = clone $this->components[$cix];
        } // end foreach
        $vtix              = count( $vTimezones );
        $vTimezones[$vtix] = new Vtimezone( $this->getConfig());
        $this->components  = [];
        foreach( array_keys( $vTimezones ) as $cix ) {
            $this->components[] = $vTimezones[$cix];
        }
        foreach( array_keys( $others ) as $cix ) {
            $this->components[] = $others[$cix];
        }
        return $this->components[$vtix];
    }

    /**
     * Replace calendar component in Vcalendar
     *
     * @param CalendarComponent $component
     * @return static
     * @throws InvalidArgumentException
     * @since  2.27.3 - 2018-12-28
     */
    public function replaceComponent( CalendarComponent $component )
    {
        static $ERRMSG1 = 'Invalid component type \'%s\'';
        static $ERRMSG2 = 'Vtimezone with tzid \'%s\' not found, found \'%s\'';
        if( Util::isCompInList( $component->getCompType(), self::$VCOMPS )) {
            return $this->setComponent( $component, $component->getUid());
        }
        if(( self::VTIMEZONE != $component->getCompType()) ||
            ( false === ( $tzId = $component->getTzid()))) {
            throw new InvalidArgumentException(
                sprintf( $ERRMSG1, $component->getCompType())
            );
        }
        $found = [];
        foreach( $this->components as $cix => $comp ) {
            if( self::VTIMEZONE != $component->getCompType()) {
                continue;
            }
            $foundTxid = $comp->getTzid();
            if( $tzId == $foundTxid ) {
                $component->compix      = [];
                $this->components[$cix] = $component;
                return $this;
            }
            $found[] = $foundTxid;
        } // end foreach
        throw new InvalidArgumentException(
            sprintf(
                $ERRMSG2,
                $component->getCompType(),
                implode( Util::$COMMA, $found )
            )
        );
    }

    /**
     * Return selected components from calendar on date or selectOption basis
     *
     * DTSTART MUST be set for every component.
     * No date check.
     *
     * @param int|array|DateTimeInterface $startY    (int) start Year,  default current Year
     *                                      ALT. DateTime start date
     *                                      ALT. array selectOptions ( *[ <propName> => <uniqueValue> ] )
     * @param int|DateTimeInterface $startM    (int) start Month, default current Month
     *                                      ALT. DateTime end date
     * @param int   $startD                  start Day,   default current Day
     * @param int   $endY                    end   Year,  default $startY
     * @param int   $endM                    end   Month, default $startM
     * @param int   $endD                    end   Day,   default $startD
     * @param mixed $cType                   calendar component type(-s), default false=all else string/array type(-s)
     * @param bool  $flat                    false (default) => output : array[Year][Month][Day][]
     *                                       true            => output : array[] (ignores split)
     * @param bool  $any                     true (default) - select component(-s) that occurs within period
     *                                       false          - only component(-s) that starts within period
     * @param bool  $split                   true (default) - one component copy every DAY it occurs during the
     *                                       period (implies flat=false)
     *                                       false          - one occurance of component only in output array
     * @return mixed   array on success, bool false on error
     * @throws Exception
     * @since  2.29.16 - 2020-01-24
     */
    public function selectComponents(
        $startY = null,
        $startM = null,
        $startD = null,
        $endY   = null,
        $endM   = null,
        $endD   = null,
        $cType  = null,
        $flat   = null,
        $any    = null,
        $split  = null
    ) {
        try {
            return SelectFactory::selectComponents(
                $this,
                $startY, $startM, $startD, $endY, $endM, $endD,
                $cType, $flat, $any, $split
            );
        }
        catch( Exception $e ) {
            throw $e;
        }
    }

    /**
     * Sort iCal compoments
     *
     * Ascending sort on properties (if exist) x-current-dtstart, dtstart,
     * x-current-dtend, dtend, x-current-due, due, duration, created, dtstamp, uid if called without arguments,
     * otherwise sorting on specific (argument) property values
     *
     * @param string $sortArg
     * @return static
     * @since  2.27.3 - 2018-12-28
     */
    public function sort( $sortArg = null )
    {
        static $SORTER = [ 'Kigkonsult\Icalcreator\Util\SortFactory', 'cmpfcn' ];
        if( 2 > $this->countComponents()) {
            return $this;
        }
        if( ! is_null( $sortArg )) {
            $sortArg = strtoupper( $sortArg );
            if( ! Util::isPropInList( $sortArg, Vcalendar::$OTHERPROPS ) &&
                ( self::DTSTAMP != $sortArg )) {
                $sortArg = null;
            }
        }
        foreach( $this->components as $cix => $component ) {
            SortFactory::setSortArgs( $this->components[$cix], $sortArg );
        }
        usort( $this->components, $SORTER );
        return $this;
    }

    /**
     * Parse iCal text/file into Vcalendar, components, properties and parameters
     *
     * @param string|array $unParsedText strict rfc2445 formatted, single property string or array of strings
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @since  2.29.3  2019-08-29
     */
    public function parse( $unParsedText )
    {
        $rows = StringFactory::conformParseInput( $unParsedText );
        $this->parse2intoComps( $rows );
        $this->parse3thisProperties();
        /* parse Components */
        if( ! empty( $this->countComponents())) {
            $this->parse4subComps();
        }
        return $this;
    }

    /**
     * Parse into calendar and comps data
     *
     * @param array $rows
     * @throws Exception
     * @throws UnexpectedValueException
     * @since  2.29.3 - 2019-08-26
     */
    private function parse2intoComps( array $rows )
    {
        static $ERR20           = 'Ical content not in sync (row %d) %s';
        static $BEGIN_VCALENDAR = 'BEGIN:VCALENDAR';
        static $END_VCALENDAR   = 'END:VCALENDAR';
        static $ENDSARR         = [ 'END:VE', 'END:VF', 'END:VJ', 'END:VT' ];
        static $BEGIN_VEVENT    = 'BEGIN:VEVENT';
        static $BEGIN_VFREEBUSY = 'BEGIN:VFREEBUSY';
        static $BEGIN_VJOURNAL  = 'BEGIN:VJOURNAL';
        static $BEGIN_VTODO     = 'BEGIN:VTODO';
        static $BEGIN_VTIMEZONE = 'BEGIN:VTIMEZONE';
        $comp    = $this;
        $calSync = $compSync = 0;
        /* identify components and update unparsed data for components */
        foreach( $rows as $lix => $row ) {
            switch( true ) {
                case StringFactory::startsWith( $row, $BEGIN_VCALENDAR ) :
                    $calSync++;
                    break;
                case StringFactory::startsWith( $row, $END_VCALENDAR ) :
                    $calSync  -= 1;
                    if( 0 != $calSync ) {  /* err 20 */
                        throw new UnexpectedValueException(
                            sprintf( $ERR20, $lix, PHP_EOL . implode( PHP_EOL, $rows ))
                        );
                    }
                    break 2;
                case ( in_array( strtoupper( substr( $row, 0, 6 )), $ENDSARR )) :
                    $compSync  -= 1;
                    break;
                case StringFactory::startsWith( $row, $BEGIN_VEVENT ) :
                    $comp      = $this->newVevent();
                    $compSync += 1;
                    break;
                case StringFactory::startsWith( $row, $BEGIN_VFREEBUSY ) :
                    $comp      = $this->newVfreebusy();
                    $compSync += 1;
                    break;
                case StringFactory::startsWith( $row, $BEGIN_VJOURNAL ) :
                    $comp      = $this->newVjournal();
                    $compSync += 1;
                    break;
                case StringFactory::startsWith( $row, $BEGIN_VTODO ) :
                    $comp      = $this->newVtodo();
                    $compSync += 1;
                    break;
                case StringFactory::startsWith( $row, $BEGIN_VTIMEZONE ) :
                    $comp      = $this->newVtimezone();
                    $compSync += 1;
                    break;
                default : /* update component with unparsed data */
                    $comp->unparsed[] = $row;
                    break;
            } // switch( true )
        } // end foreach( $rows as $lix => $row )
    }

    /**
     * Parse calendar data
     *
     * @throws UnexpectedValueException
     * @since  2.29.22 - 2020-08-26
     */
    private function parse3thisProperties()
    {
        static $NLCHARS   = '\n';
        static $BEGIN     = 'BEGIN:';
        static $ERR       = 'Unknown ical component (row %d) %s';
        static $PVPROPS   = [ self::PRODID, self::VERSION ];
        static $CALPROPS  = [
            self::CALSCALE,
            self::METHOD,
            self::PRODID,
            self::VERSION,
        ];
        static $RFC7986PROPS = [
            self::COLOR,
            self::CATEGORIES,
            self::DESCRIPTION,
            self::IMAGE,
            self::NAME,
            self::LAST_MODIFIED,
            self::REFRESH_INTERVAL,
            self::SOURCE,
            self::UID,
            self::URL,
        ];
        if( ! isset( $this->unparsed ) ||
            ! is_array( $this->unparsed ) ||
            ( 1 > count( $this->unparsed ))) {
            return;
        }
            /* concatenate property values spread over several rows */
        static $TRIMCHARS = "\x00..\x1F";
        $rows = StringFactory::concatRows( $this->unparsed );
        foreach( $rows as $lix => $row ) {
            if( StringFactory::startsWith( $row, $BEGIN )) {
                throw new UnexpectedValueException(
                    sprintf( $ERR, $lix, PHP_EOL . implode( PHP_EOL, $rows ))
                );
            }
            /* split property name  and  opt.params and value */
            list( $propName, $row ) = StringFactory::getPropName( $row );
            switch( true ) {
                case ( StringFactory::isXprefixed( $propName ) ||
                       Util::isPropInList( $propName, $RFC7986PROPS )) :
                    break;
                case Util::isPropInList( $propName, $PVPROPS ) :
                    continue 2;  // ignore version/prodid properties
                    break;
                case ( ! Util::isPropInList( $propName, $CALPROPS )) :
                    continue 2;  // skip non standard property names
                    break;
            } // end switch
            /* separate attributes from value */
            list( $value, $propAttr ) = StringFactory::splitContent( $row );
            /* update Property */
            if( StringFactory::isXprefixed( $propName )) {
                $this->setXprop(
                    $propName,
                    StringFactory::strunrep( $value ),
                    $propAttr
                );
                continue;
            }
            if(( $NLCHARS == strtolower( substr( $value, -2 ))) &&
                ! Util::isPropInList( $propName, self::$TEXTPROPS )) {
                $value = StringFactory::trimTrailNL( $value );
            }
            $method = parent::getSetMethodName( $propName );
            switch( $propName ) {
                case self::LAST_MODIFIED :    // fall through
                case self::REFRESH_INTERVAL : // fall through
                case self::URL :
                    $this->{$method}( $value, $propAttr );
                    break;
                default :
                    $value = StringFactory::strunrep( rtrim( $value, $TRIMCHARS ));
                    $this->{$method}( $value, $propAttr );
            } // end switch
        } // end foreach
        unset( $this->unparsed );
    }

    /**
     * Parse sub-components
     *
     * @since  2.29.3 - 2019-07-02
     */
    private function parse4subComps()
    {
        foreach( array_keys( $this->components ) as $ckey ) {
            if( ! empty( $this->components[$ckey] ) &&
                ! empty( $this->components[$ckey]->unparsed )) {
                $this->components[$ckey]->parse();
            }
        } // end foreach
    }

    /**
     * Return static with (replaced) populated Vtimezone component
     *
     * @param string        $timezone valid timezone acceptable by PHP5 DateTimeZone
     * @param array         $xProp    *[x-propName => x-propValue]
     * @param DateTimeInterface|int  $start    .. or unix timestamp
     * @param DateTimeInterface|int  $end      .. or unix timestamp
     * @return Vcalendar
     * @throws Exception
     * @throws InvalidArgumentException;
     * @since  2.29.16 - 2020-01-25
     */
    public function vtimezonePopulate(
        $timezone = null,
        $xProp = [],
        $start = null,
        $end = null
    ) {
        return VtimezonePopulateFactory::process(
            $this,
            $timezone,
            $xProp,
            $start,
            $end
        );
    }

    /**
     * Return formatted output for calendar object instance
     *
     * @return string
     * @throws Exception
     * @since  2.29.05 - 2019-07-02
     */
    public function createCalendar()
    {
        static $BEGIN_VCALENDAR = "BEGIN:VCALENDAR";
        static $END_VCALENDAR   = "END:VCALENDAR";
        $calendar  = $BEGIN_VCALENDAR . Util::$CRLF;
        $calendar .= $this->createVersion();
        $calendar .= $this->createProdid();
        $calendar .= $this->createCalscale();
        $calendar .= $this->createMethod();
        $calendar .= $this->createLastmodified();
        $calendar .= $this->createUid();
        $calendar .= $this->createUrl();
        $calendar .= $this->createRefreshinterval();
        $calendar .= $this->createSource();
        $calendar .= $this->createColor();
        $calendar .= $this->createName();
        $calendar .= $this->createDescription();
        $calendar .= $this->createCategories();
        $calendar .= $this->createImage();
        $calendar .= $this->createXprop();
        $config    = $this->getConfig();
        $this->reset();
        foreach( array_keys( $this->components ) as $cix ) {
            if( ! empty( $this->components[$cix] )) {
                $this->components[$cix]->setConfig( $config, false, true );
                $calendar .= $this->components[$cix]->createComponent();
            }
        }
        return $calendar . $END_VCALENDAR . Util::$CRLF;
    }

    /**
     * Return created, updated and/or parsed calendar,
     * sending a HTTP redirect header.
     *
     * @param bool    $utf8Encode
     * @param bool    $gzip
     * @param bool    $cdType true : Content-Disposition: attachment... (default), false : ...inline...
     * @param string  $fileName
     * @return bool true on success, false on error
     * @throws Exception
     * @since  2.29.15 - 2020-01-19
     */
    public function returnCalendar(
        $utf8Encode = false,
        $gzip = false,
        $cdType = true,
        $fileName = null
    ) {
        return HttpFactory::returnCalendar(
            $this,
            $utf8Encode,
            $gzip,
            $cdType,
            $fileName
        );
    }

}
