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

use DateInterval;
use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Vcalendar as Formatter;
use Kigkonsult\Icalcreator\Traits\MvalTrait;
use Kigkonsult\Icalcreator\Parser\VcalendarParser;
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
use function implode;
use function in_array;
use function is_array;
use function is_null;
use function ksort;
use function method_exists;
use function property_exists;
use function strlen;
use function strpos;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;
use function usort;

/**
 * Vcalendar class
 *
 * @since  2.41.54 - 2022-08-09
 */
final class Vcalendar extends IcalBase
{
    // The following are REQUIRED, but MUST NOT occur more than once.
    use Traits\PRODIDtrait;
    use Traits\VERSIONtrait;
    // The following are OPTIONAL, but MUST NOT occur more than once.
    use Traits\CALSCALEtrait;
    use Traits\METHODtrait;
    // The following are OPTIONAL, but MUST NOT occur more than once (rfc7986).
    use Traits\UIDrfc7986trait;
    use Traits\LAST_MODIFIEDtrait;
    use Traits\URLtrait;
    use Traits\REFRESH_INTERVALrfc7986trait;
    use Traits\SOURCErfc7986trait;
    use Traits\COLORrfc7986trait;
    // The following are OPTIONAL, and MAY occur more than once (rfc7986).
    use Traits\NAMErfc7986trait;
    use Traits\DESCRIPTIONtrait;
    use Traits\CATEGORIEStrait;
    use Traits\IMAGErfc7986trait;

    /**
     * @const
     */
    public const VCALENDAR = 'Vcalendar';

    /**
     * Constructor for calendar object
     *
     * @param null|array $config
     * @throws Exception
     * @since 2.41.55 - 2022-08-13
     */
    public function __construct( ? array $config = [] )
    {
        $this->compType = self::VCALENDAR;
        $this->setConfig( $config ?? [] );
        $this->setUid();
        $this->prodid   = $this->makeProdid();
    }

    /**
     * Destructor
     *
     * @since  2.40.11 - 2011-01-25
     */
    public function __destruct()
    {
        if( ! empty( $this->components )) {
            foreach( array_keys( $this->components ) as $cix ) {
                $this->components[$cix]->__destruct();
            }
        }
        unset(
            $this->compType,
            $this->xprop,
            $this->components,
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
     * @param null|array $config
     * @return self
     * @throws Exception
     * @since  2.18.5 - 2013-08-29
     */
    public static function factory( ? array $config = [] ) : self
    {
        return new self( $config );
    }

    /**
     * Return iCalcreator version
     *
     * @return string
     * @since  2.18.5 - 2013-08-29
     */
    public static function iCalcreatorVersion() : string
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
     * ATTENDEE*, CONTACT, ORGANIZER*   *:prefixed by "protocol" like "mailto:....
     * RECURRENCE-ID *4 (alt. "R-UID")
     * RELATED-TO, URL, UID
     * @param string $propName
     * @return array|bool   false on not found propName
     * @since  2.40.11 - 2021-01-25
     */
    public function getProperty( string $propName ) : bool | array
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
        if( ! in_array( $propName, $PROPS, true )) {
            return false;
        }
        $output  = [];
        $content = null;
        foreach( array_keys( $this->components ) as $cix ) {
            switch( true ) {
                case ( ! in_array( $this->components[$cix]->getCompType(), self::$VCOMPS, true )) :
                    continue 2;
                case ( ! property_exists( $this->components[$cix], StringFactory::getInternalPropName( $propName ))) :
                    continue 2;
                case ( in_array( $propName, self::$MPROPS1, true )) :
                    $this->components[$cix]->getProperties( $propName, $output );
                    continue 2;
                case (( 3 < strlen( $propName )) &&
                    ( self::UID === substr( $propName, -3 ))) :
                    if( false !== ( $content = $this->components[$cix]->getRecurrenceid())) {
                        $content = $this->components[$cix]->getUid();
                    }
                    break;
                case (( self::GEOLOCATION === $propName ) &&
                    ( ! property_exists( $this->components[$cix], strtolower( self::GEO )) ||
                        ( false === ( $content = $this->components[$cix]->getGeoLocation())))) :
                    continue 2;
                default :
                    $method = StringFactory::getGetMethodName( $propName );
                    if( ! method_exists( $this->components[$cix], $method ) ||
                        ( false === ( $content = $this->components[$cix]->{$method}()))) {
                        continue 2;
                    }
            } // end switch
            if( empty( $content )) {
                continue;
            }
            switch( true ) {
                case ( $content instanceof DateTime ) :
                    $key = $content->format( DateTimeFactory::$Ymd );
                    if( ! isset( $output[$key] )) {
                        $output[$key] = 1;
                    }
                    else {
                        ++$output[$key];
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
                    ++$output[$content];
                    break;
            } // end switch
        } // end foreach( $this->components as $cix => $component)
        if( ! empty( $output )) {
            ksort( $output );
        }
        return $output;
    }

    /**
     * Return Vevent object instance
     *
     * @param null|string|DateTimeInterface $dtstart
     * @param null|string|DateTimeInterface $dtend   one of dtend or duration
     * @param null|string|DateInterval $duration
     * @param null|string $summary
     * @return Vevent
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.41.53 - 2022-08-08
     */
    public function newVevent(
        null|string|DateTimeInterface $dtstart = null,
        null|string|DateTimeInterface $dtend = null,
        null|string|DateInterval $duration = null,
        ? string $summary = null
    ) : Vevent
    {
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = Vevent::factory(
            $this->getConfig(),
            $dtstart,
            $dtend,
            $duration,
            $summary
        );
        return $this->components[$ix];
    }

    /**
     * Return Vtodo object instance
     *
     * @param null|string|DateTimeInterface $dtstart
     * @param null|string|DateTimeInterface $due   one of due or duration
     * @param null|string|DateInterval $duration
     * @param null|string $summary
     * @return Vtodo
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.41.53 - 2022-08-08
     */
    public function newVtodo(
        null|string|DateTimeInterface $dtstart = null,
        null|string|DateTimeInterface $due = null,
        null|string|DateInterval $duration = null,
        ? string $summary = null
    ) : Vtodo
    {
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = Vtodo::factory(
            $this->getConfig(),
            $dtstart,
            $due,
            $duration,
            $summary
        );
        return $this->components[$ix];
    }

    /**
     * Return Vjournal object instance
     *
     * @param null|string|DateTimeInterface $dtstart
     * @param null|string $summary
     * @return Vjournal
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.41.53 - 2022-08-08
     */
    public function newVjournal(
        null|string|DateTimeInterface $dtstart = null,
        ? string $summary = null
    ) : Vjournal
    {
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = Vjournal::factory( $this->getConfig(), $dtstart, $summary );
        return $this->components[$ix];
    }

    /**
     * Return Vfreebusy object instance
     *
     * @param null|string $attendee
     * @param null|string|DateTimeInterface $dtstart
     * @param null|string|DateTimeInterface $dtend
     * @return Vfreebusy
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.41.28 - 2022-08-08
     */
    public function newVfreebusy(
        ? string $attendee = null,
        null|string|DateTimeInterface $dtstart = null,
        null|string|DateTimeInterface $dtend = null,
    ) : Vfreebusy
    {
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = Vfreebusy::factory( $this->getConfig(), $attendee, $dtstart, $dtend );
        return $this->components[$ix];
    }

    /**
     * Return Vavailability object instance
     *
     * @param null|string $busyType
     * @param null|string|DateTimeInterface $dtstart
     * @param null|string|DateTimeInterface $dtend
     * @param null|string|DateInterval $duration
     * @return Vavailability
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.41.53 - 2022-08-08
     */
    public function newVavailability(
        ? string $busyType = null,
        null|string|DateTimeInterface $dtstart = null,
        null|string|DateTimeInterface $dtend = null,
        null|string|DateInterval $duration = null
    ) : Vavailability
    {
        $ix   = $this->getNextComponentIndex();
        $this->components[$ix] = Vavailability::factory(
            $this->getConfig(),
            $busyType,
            $dtstart,
            $dtend,
            $duration
        );
        return $this->components[$ix];
    }

    /**
     * Return Vtimezone object instance
     *
     * @param null|string $tzid
     * @return Vtimezone
     * @since  2.41.53 - 2022-08-08
     */
    public function newVtimezone( ? string $tzid = null ) : Vtimezone
    {
        $vTimezones = $others = [];
        foreach( array_keys( $this->components ) as $cix ) {
            if( self::VTIMEZONE === $this->components[$cix]->getCompType()) {
                $vTimezones[] = clone $this->components[$cix];
                continue;
            }
            $others[] = clone $this->components[$cix];
        } // end foreach
        $vtix              = count( $vTimezones );
        $vTimezones[$vtix] = Vtimezone::factory( $this->getConfig(), $tzid );
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
     * @return self
     * @throws InvalidArgumentException
     * @since  2.27.3 - 2018-12-28
     */
    public function replaceComponent( CalendarComponent $component ) : self
    {
        static $ERRMSG1 = 'Invalid component type \'%s\' or Vtimezone with no TZID';
        static $ERRMSG2 = 'Vtimezone with tzid \'%s\' not found, found \'%s\'';
        if( in_array( $component->getCompType(), self::$VCOMPS, true )) {
            return $this->setComponent( $component, $component->getUid());
        }
        if(( self::VTIMEZONE !== $component->getCompType()) ||
            ( false === ( $tzId = $component->getTzid()))) {
            throw new InvalidArgumentException(
                sprintf( $ERRMSG1, $component->getCompType())
            );
        }
        $found = [];
        foreach( array_keys( $this->components  ) as $cix ) {
            if( self::VTIMEZONE !== $this->components[$cix]->getCompType()) {
                continue;
            }
            $foundTxid = $this->components[$cix]->getTzid();
            if( $tzId === $foundTxid ) {
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
     * @param null|int|array|DateTimeInterface $startY (int) start Year,  default current Year
     *                                      ALT. DateTime start date
     *                                      ALT. array selectOptions ( *[ <propName> => <uniqueValue> ] )
     * @param null|int|DateTimeInterface $startM (int) start Month, default current Month
     *                                      ALT. DateTime end date
     * @param null|int $startD start Day,   default current Day
     * @param null|int $endY end   Year,  default $startY
     * @param null|int $endM end   Month, default $startM
     * @param null|int $endD end   Day,   default $startD
     * @param null|string|string[] $cType calendar component type(-s), default false=all else string/array type(-s)
     * @param bool $flat false (default) => output : array[Year][Month][Day][]
     *                                       true            => output : array[] (ignores split)
     * @param bool $any true (default) - select component(-s) that occurs within period
     *                                       false          - only component(-s) that starts within period
     * @param bool $split true (default) - one component copy every DAY it occurs during the
     *                                       period (implies flat=false)
     *                                       false          - one occurance of component only in output array
     * @return bool|array  array on success, bool false on error
     * @throws Exception
     * @since  2.29.16 - 2020-01-24
     */
    public function selectComponents(
        null|int|array|DateTimeInterface $startY = null,
        null|int|DateTimeInterface $startM = null,
        ? int $startD = null,
        ? int $endY   = null,
        ? int $endM   = null,
        ? int $endD   = null,
        null|string|array $cType  = null,
        ? bool $flat   = null,
        ? bool $any    = null,
        ? bool $split  = null
    ) : bool | array
    {
        return SelectFactory::selectComponents(
            $this,
            $startY, $startM, $startD, $endY, $endM, $endD,
            $cType, $flat, $any, $split
        );
    }

    /**
     * Sort iCal compoments
     *
     * Ascending sort on properties (if exist) x-current-dtstart, dtstart,
     * x-current-dtend, dtend, x-current-due, due, duration, created, dtstamp, uid if called without arguments,
     * otherwise sorting on specific (argument) property values
     *
     * @param string|null $sortArg
     * @return self
     * @since  2.40.11 - 2022-01-25
     */
    public function sort( ? string $sortArg = null ) : self
    {
        static $SORTER = [ SortFactory::class, 'cmpfcn' ];
        if( 2 > $this->countComponents()) {
            return $this;
        }
        if( ! is_null( $sortArg )) {
            $sortArg = strtoupper( $sortArg );
            if(( self::DTSTAMP !== $sortArg ) &&
                ! in_array( $sortArg, self::$SELSORTPROPS, true )) {
                $sortArg = null;
            }
        }
        foreach( array_keys( $this->components ) as $cix ) {
            SortFactory::setSortArgs( $this->components[$cix], $sortArg );
        }
        usort( $this->components, $SORTER );
        return $this;
    }

    /**
     * Parse iCal text/file into Vcalendar, components, properties and parameters
     *
     * @param string|string[] $unParsedText strict rfc2445 formatted, single property string or array of strings
     * @return self
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @since  2.41.54 - 2022-08-09
     */
    public function parse( string|array $unParsedText ) : self
    {
        VcalendarParser::factory( $this )->parse( $unParsedText );
        return $this;
    }

    /**
     * return self with (replaced) populated Vtimezone component
     *
     * @param string|null   $timezone valid timezone acceptable by PHP5 DateTimeZone
     * @param null|array $xProp *[x-propName => x-propValue]
     * @param null|int|DateTimeInterface $start .. or unix timestamp
     * @param null|int|DateTimeInterface $end .. or unix timestamp
     * @return Vcalendar
     * @throws Exception
     * @since  2.29.16 - 2020-01-25
     */
    public function vtimezonePopulate(
        ? string $timezone = null,
        ? array $xProp = [],
        null|int|DateTimeInterface $start = null,
        null|int|DateTimeInterface $end = null
    ) : self
    {
        return VtimezonePopulateFactory::process(
            $this,
            $timezone,
            $xProp,
            $start,
            $end
        );
    }

    /**
     * Components may have PARTICPANTs
     *
     * used below and in Participants2AttendeesTrait
     *
     * @var string[]
     */
    public static array $VCOMBS = [
        self::VEVENT,
        self::VTODO,
        self::VJOURNAL,
        self::VFREEBUSY
    ];

    /**
     * Set subComponent Participants (calendaraddress) as Attendees, skip if set
     *
     * @return self
     * @since 2.41.4 - 2022-02-16
     */
    public function participants2Attendees() : self
    {
        foreach( array_keys( $this->components ) as $cix ) {
            if( in_array( $this->components[$cix]->getCompType(), self::$VCOMBS, true )) {
                $this->components[$cix]->participants2Attendees();
            }
        }
        return $this;
    }

    /**
     * Set Vevent/Vtodo subComponent Vlocation names as Locations, skip if set
     *
     * Vlocation UID set as Location X-param x-vlocationid
     * All Vlocation name parameters are set if not exist.
     * Vlocation LOCATION_TYPE set as Location X-param x-location-type
     *
     * @return self
     * @since 2.41.19 - 2022-02-18
     */
    public function vlocationNames2Location() : self
    {
        foreach( array_keys( $this->components ) as $cix ) {
            if( in_array( $this->components[$cix]->getCompType(), [ self::VEVENT, self::VTODO ] , true )) {
                $this->components[$cix]->vlocationNames2Location();
            }
        }
        return $this;
    }

    /**
     * Set Vevent/Vtodo subComponent Vresource names as Resource, skip if set
     *
     * Vresource UID set as Resurce X-param x-participantid
     * Other Vresource name parameters are set if ot exist.
     * Vresource RESOURCE_TYPE set as Location X-param x-resource-type
     *
     * @return static
     * @since 2.41.21 - 2022-02-18
     */
    public function vresourceNames2Resources() : self
    {
        foreach( array_keys( $this->components ) as $cix ) {
            if( in_array( $this->components[$cix]->getCompType(), [ self::VEVENT, self::VTODO ] , true )) {
                $this->components[$cix]->vresourceNames2Resources();
            }
        }
        return $this;
    }

    /**
     * Return formatted output for calendar object instance
     *
     * @return string
     * @throws Exception
     * @since 2.41.55 2022-08-13
     */
    public function createCalendar() : string
    {
        return Formatter::format( $this );
    }

    /**
     * Return created, updated and/or parsed calendar,
     * sending a HTTP redirect header.
     *
     * @param bool $utf8Encode
     * @param bool $gzip
     * @param bool $cdType true : Content-Disposition: attachment... (default), false : ...inline...
     * @param string|null $fileName
     * @return bool true on success, false on error
     * @throws Exception
     * @since  2.29.15 - 2020-01-19
     */
    public function returnCalendar(
        ? bool $utf8Encode = false,
        ? bool $gzip = false,
        ? bool $cdType = true,
        ? string $fileName = null
    ) : bool
    {
        return HttpFactory::returnCalendar( $this, $utf8Encode, $gzip, $cdType, $fileName );
    }

    /**
     * Component multi-property help methods
     */
    use MvalTrait;
}
