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
use function implode;
use function in_array;
use function is_array;
use function is_null;
use function ksort;
use function method_exists;
use function property_exists;
use function rtrim;
use function str_starts_with;
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
 * @since  2.39.1 - 2021-06-26
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
     * @var string property output formats, used by CALSCALE, METHOD, PRODID and VERSION
     */
    private static string $FMTICAL = "%s:%s\r\n";

    /**
     * Constructor for calendar object
     *
     * @param null|mixed[] $config
     * @since  2.39.1 - 2021-06-26
     */
    public function __construct( ? array $config = [] )
    {
        $this->compType     = self::VCALENDAR;
        $this->setConfig( $config ?? [] );
        $this->setUid();
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
     * @param null|mixed[] $config
     * @return self
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
     * ATTENDEE*, CONTACT, ORGANIZER*   *:prefixed by "protocol" like "MAILTO:....
     * RECURRENCE-ID *4 (alt. "R-UID")
     * RELATED-TO, URL, UID
     * @param string $propName
     * @return mixed[]|bool   false on not found propName
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
        if( ! Util::isPropInList( $propName, $PROPS )) {
            return false;
        }
        $output  = [];
        $content = null;
        foreach( array_keys( $this->components ) as $cix ) {
            switch( true ) {
                case ( ! Util::isCompInList( $this->components[$cix]->getCompType(), self::$VCOMPS )) :
                    continue 2;
                case ( ! property_exists( $this->components[$cix], StringFactory::getInternalPropName( $propName ))) :
                    continue 2;
                case ( Util::isPropInList( $propName, self::$MPROPS1 )) :
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
     * @return Vevent
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.27.14 - 2018-02-19
     */
    public function newVevent() : Vevent
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
    public function newVtodo() : Vtodo
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
    public function newVjournal() : Vjournal
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
    public function newVfreebusy() : Vfreebusy
    {
        $comp = new Vfreebusy( $this->getConfig());
        $comp->getDtstamp();
        $comp->getUid();
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = $comp;
        return $comp;
    }

    /**
     * Return Vavailability object instance
     *
     * @return Vavailability
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.41.9 - 2022-01-22
     */
    public function newVavailability() : Vavailability
    {
        $comp = new Vavailability( $this->getConfig());
        $comp->getDtstamp();
        $comp->getUid();
        $ix   = $this->getNextComponentIndex();
        $this->components[$ix] = $comp;
        return $comp;
    }

    /**
     * Return Vtimezone object instance
     *
     * @return Vtimezone
     * @since  2.29.8 - 2019-07-03
     */
    public function newVtimezone() : Vtimezone
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
     * @return self
     * @throws InvalidArgumentException
     * @since  2.27.3 - 2018-12-28
     */
    public function replaceComponent( CalendarComponent $component ) : self
    {
        static $ERRMSG1 = 'Invalid component type \'%s\' or Vtimezone with no TZID';
        static $ERRMSG2 = 'Vtimezone with tzid \'%s\' not found, found \'%s\'';
        if( Util::isCompInList( $component->getCompType(), self::$VCOMPS )) {
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
     * @param null|int|mixed[]|DateTimeInterface $startY (int) start Year,  default current Year
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
     * @return bool|mixed[]  array on success, bool false on error
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
                ! Util::isPropInList( $sortArg, self::$SELSORTPROPS )) {
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
     * @since  2.29.3  2019-08-29
     */
    public function parse( string|array $unParsedText ) : self
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
     * @param string[] $rows
     * @return void
     * @throws Exception
     * @throws UnexpectedValueException
     * @since  2.41.9 - 2022-01-27
     */
    private function parse2intoComps( array $rows ) : void
    {
        static $BEGIN_VCALENDAR     = 'BEGIN:VCALENDAR';
        static $END_VCALENDAR       = 'END:VCALENDAR';
        static $ENDSARR             = [ 'END:VAV', 'END:VEV', 'END:VFR', 'END:VJO', 'END:VTI', 'END:VTO' ];
        static $BEGIN_VAVAILABILITY = 'BEGIN:VAVAILABILITY';
        static $BEGIN_VEVENT        = 'BEGIN:VEVENT';
        static $BEGIN_VFREEBUSY     = 'BEGIN:VFREEBUSY';
        static $BEGIN_VJOURNAL      = 'BEGIN:VJOURNAL';
        static $BEGIN_VTODO         = 'BEGIN:VTODO';
        static $BEGIN_VTIMEZONE     = 'BEGIN:VTIMEZONE';
        $comp    = $this;
        /* identify components and update unparsed data for components */
        foreach( $rows as $row ) {
            switch( true ) {
                case str_starts_with( $row, $BEGIN_VCALENDAR ) :
                    break;
                case str_starts_with( $row, $END_VCALENDAR ) :
                    break 2;
                case ( in_array( strtoupper( substr( $row, 0, 7 )), $ENDSARR, true )) :
                    break;
                case str_starts_with( $row, $BEGIN_VAVAILABILITY ) :
                    $comp      = $this->newVavailability();
                    break;
                case str_starts_with( $row, $BEGIN_VEVENT ) :
                    $comp      = $this->newVevent();
                    break;
                case str_starts_with( $row, $BEGIN_VFREEBUSY ) :
                    $comp      = $this->newVfreebusy();
                    break;
                case str_starts_with( $row, $BEGIN_VJOURNAL ) :
                    $comp      = $this->newVjournal();
                    break;
                case str_starts_with( $row, $BEGIN_VTODO ) :
                    $comp      = $this->newVtodo();
                    break;
                case str_starts_with( $row, $BEGIN_VTIMEZONE ) :
                    $comp      = $this->newVtimezone();
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
     * @return void
     * @throws UnexpectedValueException
     * @since  2.29.22 - 2020-08-26
     */
    private function parse3thisProperties() : void
    {
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
            if( str_starts_with( $row, $BEGIN )) {
                throw new UnexpectedValueException(
                    sprintf( $ERR, $lix, PHP_EOL . implode( PHP_EOL, $rows ))
                );
            }
            /* split property name  and  opt.params and value */
            [ $propName, $row ] = StringFactory::getPropName( $row );
            switch( true ) {
                case ( StringFactory::isXprefixed( $propName ) ||
                       Util::isPropInList( $propName, $RFC7986PROPS )) :
                    break;
                case Util::isPropInList( $propName, $PVPROPS ) :
                    continue 2;  // ignore version/prodid properties
                case ( ! Util::isPropInList( $propName, $CALPROPS )) :
                    continue 2;  // skip non standard property names
            } // end switch
            /* separate attributes from value */
            [ $value, $propAttr ] = StringFactory::splitContent( $row );
            /* update Property */
            if( StringFactory::isXprefixed( $propName )) {
                $this->setXprop( $propName, StringFactory::strunrep( $value ), $propAttr );
                continue;
            }
            if( ! Util::isPropInList( $propName, self::$TEXTPROPS )) {
                $value = StringFactory::trimTrailNL( $value );
            }
            $method = StringFactory::getSetMethodName( $propName );
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
     * @return void
     * @since  2.29.3 - 2019-07-02
     */
    private function parse4subComps() : void
    {
        foreach( array_keys( $this->components ) as $ckey ) {
            if( ! empty( $this->components[$ckey] ) &&
                ! empty( $this->components[$ckey]->unparsed )) {
                $this->components[$ckey]->parse();
            }
        } // end foreach
    }

    /**
     * return self with (replaced) populated Vtimezone component
     *
     * @param string|null   $timezone valid timezone acceptable by PHP5 DateTimeZone
     * @param null|mixed[]  $xProp *[x-propName => x-propValue]
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
     * @since 2.41.4 - 2019-03-17
     */
    public function participants2Attendees() : self
    {
        foreach( array_keys( $this->components ) as $cix ) {
            if( in_array( $this->components[$cix]->getCompType(), self::$VCOMBS, true ) ) {
                $this->components[$cix]->participants2Attendees();
            }
        }
        return $this;
    }

    /**
     * Return formatted output for calendar object instance
     *
     * @return string
     * @throws Exception
     * @since  2.29.05 - 2019-07-02
     */
    public function createCalendar() : string
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
        $this->resetCompCounter();
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
}
