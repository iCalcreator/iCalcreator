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

namespace Kigkonsult\Icalcreator\Util;

use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Vcalendar;
use RuntimeException;
use UnexpectedValueException;

use function array_keys;
use function arsort;
use function explode;
use function implode;
use function key;
use function reset;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function timezone_abbreviations_list;
use function timezone_name_from_abbr;

/**
 * Class RegulateTimezoneFactory
 *
 * Review timezones, opt. alter to PHP timezones
 *
 * @see https://docs.microsoft.com/en-us/windows-hardware/manufacture/desktop/default-time-zones
 * Cover Vtimezone property TZID and component date properties DTSTART, DTEND, DUE, RECURRENCE-ID, EXDATE, RDATE
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  2.29.10 - 2019-09-02
 */
class RegulateTimezoneFactory
{
    /**
     * Add MS timezone and offset to (internal) MStimezoneToOffset
     *
     * @param string $msTz
     * @param string $offset   (+/-)HH:mm
     */
    public static function addMStimezoneToOffset( $msTz, $offset )
    {
        self::$MStimezoneToOffset[$msTz] = $offset;
    }

    /**
     * 4 GMT/UTC(-suffixed)
     * 59 matches on NOT dst, OK
     * 5 hits on dst (aka daylight saving time)...
     * 5 PHP timezones specified
     *
     * @var array  MS timezones with corr. UTC offset, 73 items
     */
    public static $MStimezoneToOffset = [
        'Afghanistan Standard Time'       => '+04:30',
        'Arab Standard Time'              => '+03:00',
        'Arabian Standard Time'           => '+04:00',
        'Arabic Standard Time'            => '+03:00',
        'Argentina Standard Time'         => '-03:00',
        'Atlantic Standard Time'          => '-04:00',
        'AUS Eastern Standard Time'       => '+10:00',
        'Azerbaijan Standard Time'        => '+04:00',
        'Bangladesh Standard Time'        => '+06:00',
        'Belarus Standard Time'           => '+03:00',
        'Cape Verde Standard Time'        => '-01:00',
        'Caucasus Standard Time'          => '+04:00',
        'Central America Standard Time'   => '-06:00',
        'Central Asia Standard Time'      => '+06:00',
        'Central Europe Standard Time'    => '+01:00',
        'Central European Standard Time'  => '+01:00',
        'Central Pacific Standard Time'   => '+11:00',
        'Central Standard Time (Mexico)'  => '-06:00',
        'China Standard Time'             => '+08:00',
        'E. Africa Standard Time'         => '+03:00',
        'E. Europe Standard Time'         => '+02:00',
        'E. South America Standard Time'  => '-03:00',
        'Eastern Standard Time'           => '-05:00',
        'Egypt Standard Time'             => '+02:00',
        'Fiji Standard Time'              => '+12:00',
        'FLE Standard Time'               => '+02:00',
        'Georgian Standard Time'          => '+04:00',
        'GMT Standard Time'               => '',
        'Greenland Standard Time'         => '-03:00',
        'Greenwich Standard Time'         => '',
        'GTB Standard Time'               => '+02:00',
        'Hawaiian Standard Time'          => '-10:00',
        'India Standard Time'             => '+05:30',
        'Israel Standard Time'            => '+02:00',
        'Jordan Standard Time'            => '+02:00',
        'Korea Standard Time'             => '+09:00',
        'Mauritius Standard Time'         => '+04:00',
        'Middle East Standard Time'       => '+02:00',
        'Montevideo Standard Time'        => '-03:00',
        'Morocco Standard Time'           => '',
        'Myanmar Standard Time'           => '+06:30',
        'Namibia Standard Time'           => '+01:00',
        'Nepal Standard Time'             => '+05:45',
        'New Zealand Standard Time'       => '+12:00',
        'Pacific SA Standard Time'        => '-03:00',
        'Pacific Standard Time'           => '-08:00',
        'Pakistan Standard Time'          => '+05:00',
        'Paraguay Standard Time'          => '-04:00',
        'Romance Standard Time'           => '+01:00',
        'Russian Standard Time'           => '+03:00',
        'SA Eastern Standard Time'        => '-03:00',
        'SA Pacific Standard Time'        => '-05:00',
        'SA Western Standard Time'        => '-04:00',
        'Samoa Standard Time'             => '+13:00',
        'SE Asia Standard Time'           => '+07:00',
        'Singapore Standard Time'         => '+08:00',
        'South Africa Standard Time'      => '+02:00',
        'Sri Lanka Standard Time'         => '+05:30',
        'Syria Standard Time'             => '+02:00',
        'Taipei Standard Time'            => '+08:00',
        'Tokyo Standard Time'             => '+09:00',
        'Tonga Standard Time'             => '+13:00',
        'Turkey Standard Time'            => '+02:00',
        'Ulaanbaatar Standard Time'       => '+08:00',
        'UTC'                             => '',
        'UTC-02'                          => '-02:00',
        'UTC-11'                          => '-11:00',
        'UTC+12'                          => '+12:00',
        'Venezuela Standard Time'         => '-04:30',
        'W. Central Africa Standard Time' => '+01:00',
        'W. Europe Standard Time'         => '+01:00',
        'West Asia Standard Time'         => '+05:00',
        'West Pacific Standard Time'      => '+10:00',
    ];

    /**
     * Add other timezone map to specific PHP timezone
     *
     * @param string $otherTz
     * @param string $phpTz
     * @throws InvalidArgumentException
     */
    public static function addOtherTzMapToPhpTz( $otherTz, $phpTz )
    {
        DateTimeZoneFactory::assertDateTimeZone( $phpTz );
        self::$otherTzToPhpTz[$otherTz] = $phpTz;
    }

    /**
     * @var array  7 MS timezones to PHP timezones
     */
    public static $otherTzToPhpTz = [
        'Afghanistan Standard Time'       => 'Asia/Kabul',
        'Fiji Standard Time'              => 'Pacific/Fiji',
        // also in 'UTC+12', below
        'Myanmar Standard Time'           => 'Asia/Yangon',
        'New Zealand Standard Time'       => 'Pacific/Auckland',
        'UTC-02'                          => 'America/Noronha',
        // also America/Godthab          - Greenland
        //      America/Miquelon         - Saint Pierre and Miquelon
        //      Atlantic/South_Georgia   - South Georgia and the South Sandwich Islands
        'UTC-11'                          => 'Pacific/Pago_Pago',
        // also Pacific/Niue, Pacific/Midway
        'UTC+12'                          => 'Pacific/Auckland',
        // also Antarctica/McMurdo, Asia/Anadyr, Asia/Kamchatka,
        //      Pacific/Fiji,  Pacific/Funafuti, Pacific/Kwajalein, Pacific/Majuro,
        //      Pacific/Nauru, Pacific/Tarawa,   Pacific/Wake,      Pacific/Wallis
    ];

    /**
     * @var array
     */
    private $inputiCal = [];

    /**
     * @var string
     */
    private $outputiCal = null;

    /**
     * @var array
     */
    private $vtimezoneRows = [];

    /**
     * @var array
     */
    private $otherTzPhpRelations = [];

    /**
     * Class constructor
     *
     * @param string|array $inputiCal    strict rfc2445 formatted calendar
     * @param array        $otherTzPhpRelations  [ other => phpTz ]
     * @throws InvalidArgumentException
     */
    public function __construct( $inputiCal = null, array $otherTzPhpRelations = [] )
    {
        if( ! empty( $inputiCal )) {
            $this->setInputiCal( $inputiCal );
        }
        $this->addOtherTzPhpRelations( self::$otherTzToPhpTz );
        foreach( $otherTzPhpRelations as $otherTz => $phpTz ) {
            $this->addOtherTzPhpRelation( $otherTz, $phpTz );
        }
    }

    /**
     * Class factory method
     *
     * @param string|array $inputiCal    strict rfc2445 formatted calendar
     * @param array        $otherTzPhpRelations  [ other => phpTz ]
     * @return static
     * @throws InvalidArgumentException
     */
    public static function factory( $inputiCal = null, array $otherTzPhpRelations = [] )
    {
        return new self( $inputiCal, $otherTzPhpRelations );
    }


    /**
     * Short static all-in-one method
     *
     * @param string|array $inputiCal    strict rfc2445 formatted calendar
     * @param array        $otherTzPhpRelations  [ other => phpTz ]
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public static function process( $inputiCal, array $otherTzPhpRelations = [] )
    {
        return self::factory( $inputiCal, $otherTzPhpRelations )
                   ->processCalendar()
                   ->getOutputiCal();
    }


    /**
     * @param string|array $inputiCal    strict rfc2445 formatted calendar
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function processCalendar( $inputiCal = null )
    {
        $FMTERR = 'Calendar content required!';
        if( ! empty( $inputiCal )) {
            $this->setInputiCal( $inputiCal );
        }
        if( ! $this->isInputiCalSet()) {
            throw new InvalidArgumentException( $FMTERR );
        }
        $vtSwitch = false;
        foreach( $this->getInputiCal() as $lix => $row ) {
            if( StringFactory::startsWith( $row, self::$BEGINVTIMEZONE )) {
                $this->setVtimezoneRow( $row );
                $vtSwitch = true;
                continue;
            }
            if( StringFactory::startsWith( $row, self::$ENDVTIMEZONE )) {
                $this->setVtimezoneRow( $row );
                $this->processVtimezone();
                $this->setVtimezoneRows( [] );
                $vtSwitch = false;
                continue;
            }
            if( $vtSwitch ) {
                $this->setVtimezoneRow( $row );
                continue;
            }
            if( StringFactory::startsWith( $row, self::$BEGIN ) ||
                StringFactory::startsWith( $row, self::$END )) {
                $this->setOutputiCalRow( $row );
                continue;
            }
            /* split property name  and  opt.params and value */
            list( $propName, $row2 ) = StringFactory::getPropName( $row );
            if( ! Util::isPropInList( $propName, self::$TZIDPROPS )) {
                $this->setOutputiCalRow( $row );
                continue;
            }
            /* Now we have only properties with propAttr TZID */
            /* separate attributes from value */
            list( $value, $propAttr ) = self::splitContent( $row2 );
            if( ! isset( $propAttr[Vcalendar::TZID] )) {
                $this->setOutputiCalRow( $row );
                continue;
            }
            $this->processDtProp( $propName, $value, $propAttr );
        } // end foreach
        return $this;
    }

    /**
     * Process VTIMEZONE properties
     *
     * NO UTC here !! ??
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function processVtimezone()
    {
        $currTzId      = null;                 // empty if Vtimezone TZID is found else troublesome one
        $currTzIdFound = false;                // true if PHP Vtimezone TZID found
        $stdSwitch     = $dlghtSwitch = false; // process STANDARD/DAYLIGHT or not
        $stdArr        = $dlghtArr = [];       // TZOFFSETTO values (in  STANDARD/DAYLIGHT)
        foreach( $this->getVtimezoneRows() as $lix => $row ) {
            switch( true ) {
                case ( StringFactory::startsWith( $row, self::$BEGINVTIMEZONE )) :
                    $this->setOutputiCalRow( $row );
                    continue 2;
                    break;
                case ( StringFactory::startsWith( $row, self::$ENDVTIMEZONE )) :
                    if( ! empty( $currTzId )) {
                        $this->processCurrTzId( $currTzId, $stdArr, $dlghtArr );
                    }
                    $this->setOutputiCalRow( $row );
                    $currTzId      = null;
                    $currTzIdFound = false;
                    $stdSwitch     = $dlghtSwitch = false; // process STANDARD/DAYLIGHT or not
                    $stdArr        = $dlghtArr = [];       // TZOFFSETTO values (in STANDARD/DAYLIGHT)
                    continue 2;
                    break;
                case ( StringFactory::startsWith( $row, self::$BEGINSTANDARD )) :
                    $this->setOutputiCalRow( $row );
                    $stdSwitch = true;
                    continue 2;
                    break;
                case ( StringFactory::startsWith( $row, self::$ENDSTANDARD )) :
                    $this->setOutputiCalRow( $row );
                    $stdSwitch = false;
                    continue 2;
                    break;
                case ( StringFactory::startsWith( $row, self::$BEGINDAYLIGHT )) :
                    $this->setOutputiCalRow( $row );
                    $dlghtSwitch = true;
                    continue 2;
                    break;
                case ( StringFactory::startsWith( $row, self::$ENDDAYLIGHT )) :
                    $this->setOutputiCalRow( $row );
                    $dlghtSwitch = false;
                    continue 2;
                    break;
                case $currTzIdFound : // Vtimezone TZID is found, write whatever row it is
                    $this->setOutputiCalRow( $row );
                    continue 2;
                    break;
                default :
                    break; // now we go on with property rows
            } // end switch
            /* split property name  and  opt.params and value */
            list( $propName, $row2 ) = StringFactory::getPropName( $row );
            if( Vcalendar::TZOFFSETTO == $propName ) { // save offset if...
                if( $stdSwitch ) {
                    $stdArr[] = StringFactory::afterLast( Util::$COLON, $row2 );
                }
                elseif( $dlghtSwitch ) {
                    $dlghtArr[] = StringFactory::afterLast( Util::$COLON, $row2 );
                }
            }
            if( Vcalendar::TZID != $propName ) {  // skip all but Vtimezone TZID
                $this->setOutputiCalRow( $row );
                continue;
            }
            /* separate attributes from value */
            list( $value, $propAttr ) = StringFactory::splitContent( $row2 );
            $currTzId = $value;
            $valueNew = null;
            switch( true ) {
                case ( $this->hasOtherTzPHPtzMap( $value )) :
                    $valueNew = $this->getOtherTzPhpRelations( $value );
                    break;
                case ( isset( self::$MStimezoneToOffset[$value] )) :
                    $msTzOffset = self::$MStimezoneToOffset[$value];
                    if( empty( $msTzOffset )) {
                        $valueNew = Vcalendar::UTC;
                    }
                    else {
                        $valueNew = self::getTimeZoneNameFromOffset( $msTzOffset, false );
                    } // $valueNew is null on notFound
                    break;
                default :
                    try {
                        DateTimeZoneFactory::assertDateTimeZone( $value );
                        $currTzId      = null;
                        $currTzIdFound = true;  // NO process of STANDARD/DAYLIGHT offset
                    }
                    catch( InvalidArgumentException $e ) {
                        $valueNew = null;       // DO process of STANDARD/DAYLIGHT offset
                    }
                    break;
            } // end switch
            if( ! empty( $valueNew )) {
                $this->addOtherTzPhpRelation( $currTzId, $valueNew, false );
                $this->setOutputiCalRowElements( $propName, $valueNew, $propAttr );
                $currTzId      = null;
                $currTzIdFound = true;           // NO process of STANDARD/DAYLIGHT
            }
            else {
                $this->setOutputiCalRow( $row ); // DO process of STANDARD/DAYLIGHT
            }
        } // end foreach
    }

    /**
     * Find currTzId replacement using stdArr+dlghtArr offsets
     *
     * @param string $currTzId
     * @param array  $stdArr
     * @param array  $dlghtArr
     * @throws RuntimeException
     */
    private function processCurrTzId( $currTzId, array $stdArr, array $dlghtArr )
    {
        static $ERR = 'Timezone \'%s\' (offset std %s, dlght %s) don\'t match any PHP timezone';
        $stdTzs = $dlghtTzs = [];
        foreach( $stdArr as $offset ) {
            $stdTzs = self::getTimezoneListFromOffset( $offset, 0 ); // standard
        }
        foreach( $dlghtArr as $offset ) {
            $dlghtTzs = self::getTimezoneListFromOffset( $offset, 1 ); // daylight
        }
        foreach( $dlghtTzs as $tz => $cnt ) {
            if( isset( $stdTzs[$tz] )) {
                $dlghtTzs[$tz] += $stdTzs[$tz];
            }
        }
        foreach( $stdTzs as $tz => $cnt ) {
            if( ! isset( $dlghtTzs[$tz] )) {
                $dlghtTzs[$tz] = $cnt;
            }
        }
        if( empty( $dlghtTzs )) {
            throw new RuntimeException(
                sprintf(
                    $ERR,
                    $currTzId,
                    implode( Util::$COMMA, $stdArr ),
                    implode( Util::$COMMA, $dlghtArr )
                )
            );
        }
        arsort( $dlghtTzs ); // reverse sort on number of hits
        reset( $dlghtTzs );
        $tzidNew = key( $dlghtTzs );
        $this->replaceTzidInOutputiCal( $currTzId, $tzidNew );
        $this->addOtherTzPhpRelation( $currTzId, $tzidNew, false );
    }

    /**
     * Process component properties with propAttr TZID
     *
     * @param string $propName
     * @param string $value
     * @param array  $propAttr
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @todo properties RDATE, EXDATE
     */
    private function processDtProp( $propName, $value, array $propAttr )
    {
        $tzId = $propAttr[Vcalendar::TZID];
        switch( true ) {
            case ( $this->hasOtherTzPHPtzMap( $tzId ) ) :
                $propAttr[Vcalendar::TZID] = $this->getOtherTzPhpRelations( $tzId );
                break;
            case ( isset( self::$MStimezoneToOffset[$tzId] ) &&
                empty( self::$MStimezoneToOffset[$tzId] )) :
                $this->addOtherTzPhpRelation( $tzId, Vcalendar::UTC, false );
                $propAttr[Vcalendar::TZID] = Vcalendar::UTC;
                break;
            default : /* check and (opt) alter timezones */
                $this->processDatePropsTZIDattribute( $propName, $value, $propAttr );
                return;
        } // end switch
        self::checkTzidForUTC( $value, $propAttr );
        $this->setOutputiCalRowElements( $propName, $value, $propAttr );
    }

    /**
     * If in array, alter date-properties attribute TZID fixed. PHP-check (all) timezones, throws exception on error
     *
     * @param string $propName
     * @param string $value
     * @param array  $propAttr
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function processDatePropsTZIDattribute( $propName, $value, $propAttr )
    {
        $tzId = $tzIdIn = $propAttr[Vcalendar::TZID];
        if( isset( self::$MStimezoneToOffset[$tzIdIn] )) {
            $tzId = self::getTimeZoneNameFromOffset(
                self::$MStimezoneToOffset[$tzIdIn],
                true
            );
            $this->addOtherTzPhpRelation( $tzIdIn, $tzId, false );
            $propAttr[Vcalendar::TZID] = $tzId;
            self::checkTzidForUTC( $value, $propAttr );
        }
        else {
            DateTimeZoneFactory::assertDateTimeZone( $tzId );
        }
        $this->setOutputiCalRowElements( $propName, $value, $propAttr );
    }

    /**
     * Return array( value, propAttr ) from property row
     *
     * @param string $row2
     * @return array   ( value, propAttr )
     */
    private static function splitContent( $row2 )
    {
        /* separate attributes from value */
        list( $value, $propAttr ) = StringFactory::splitContent( $row2 );
        /* fix splitContent UTC 'bug' */
        self::fixUTCx( $row2, $value, $propAttr );
        return [ $value, $propAttr ];
    }

    /**
     * Return (first found) timezone from offset, search on standard time (ie dst=0) first
     *
     * From DateTimeZoneFactory
     * @param string $offset
     * @param bool   $throwException
     * @return string   tzName
     * @throws RuntimeException    on NOT found
     * @since  2.27.14 - 2019-02-26
     */
    private static function getTimeZoneNameFromOffset( $offset, $throwException = true )
    {
        static $ERR = 'Offset \'%s\' (%+d seconds) don\'t match any PHP timezone';
        $seconds    = DateTimeZoneFactory::offsetToSeconds( $offset );
        $res        = timezone_name_from_abbr( Util::$SP0, $seconds, 0 );
        if( false !== $res ) { // is NO dst
            return $res;
        }
        $res        = timezone_name_from_abbr( Util::$SP0, $seconds );
        if( false !== $res ) { // ignores dst
            return $res;
        }
        $res        = timezone_name_from_abbr( Util::$SP0, $seconds, 1 );
        if( false !== $res ) { // is dst
            return $res;
        }
        if( $throwException ) {
            throw new RuntimeException( sprintf( $ERR, $offset, $seconds ));
        }
        else {
            return null;
        }
    }

    /**
     * Returns (array) timezones that match offset ( and dst)
     *
     * @see https://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
     * @see https://www.php.net/manual/en/datetimezone.listabbreviations.php#114161
     * @param string $offset
     * @param int    $dst
     * @return array
     * @throws RuntimeException
     */
    private static function getTimezoneListFromOffset( $offset, $dst )
    {
        static $DST        = 'dst';
        static $OFFSET     = 'offset';
        static $TIMEZONEID = 'timezone_id';
        static $FMTERR     = 'Can\'t get offset from timezone %s';
        $seconds = DateTimeZoneFactory::offsetToSeconds( $offset );
        $output  = [];
        foreach( timezone_abbreviations_list() as $tzAbbrList ) {
            foreach( $tzAbbrList as $tzAbbrCity ) {
                if(((bool) $tzAbbrCity[$DST] !== (bool) $dst ) ||
                    empty( strlen( $tzAbbrCity[$TIMEZONEID] )) ||
                    ( $tzAbbrCity[$OFFSET] != $seconds )) {
                    continue;
                }
                $dateTimeOffsetNow = 0;
                try {
                    $date = new DateTime(
                        null,
                        new DateTimeZone( $tzAbbrCity[$TIMEZONEID] )
                    );
                    $dateTimeOffsetNow = $date->getOffset();
                }
                catch( Exception $e ) {
                    throw new RuntimeException(
                        sprintf( $FMTERR, $tzAbbrCity[$TIMEZONEID] )
                    );
                }
                if( $seconds == $dateTimeOffsetNow ) {
                    $tzId = $tzAbbrCity[$TIMEZONEID];
                    if( isset( $output[$tzId] )) {
                        $output[$tzId] += 1;
                    }
                    else {
                        $output[$tzId] = 1;
                    }
                } // end if
            } // end foreach
        } // end foreach
        return $output;
    }

    /**
     * Suffix value with 'Z'and remove propAttr TZID, IF propAttr TZID = UTC
     *
     * @param string $value
     * @param array  $propAttr
     */
    private static function checkTzidForUTC( & $value, & $propAttr )
    {
        if( ! DateTimeZoneFactory::isUTCtimeZone( $propAttr[Vcalendar::TZID] )) {
            return;
        }
        unset( $propAttr[Vcalendar::TZID] );
        $values = explode( Util::$COMMA, $value );
        foreach( array_keys( $values ) as $x ) {
            if( ParameterFactory::isParamsValueSet(
                [ Util::$LCparams => $propAttr ],
                Vcalendar::PERIOD
            )) { // RDATE
                $thePeriods     = explode( Util::$SLASH, $values[$x] );
                $thePeriods[0] .= Vcalendar::Z;
                if( ! DateIntervalFactory::isStringAndDuration( $thePeriods[1] )) {
                    $thePeriods[1] .= Vcalendar::Z;
                }
                $values[$x] = implode( Util::$SLASH, $thePeriods );
            }
            else {
                $values[$x] .= Vcalendar::Z;
            }
        }
        $value = implode( Util::$COMMA, $values );
    }

    /**
     * Fix StringFactory::splitContent UTC* bug for MS list UTC-related timezones
     *
     * Note, here $propAttr[Vcalendar::TZID] exists
     * @param string $row2
     * @param string $value
     * @param array  $propAttr
     */
    private static function fixUTCx( $row2, & $value, & $propAttr )
    {
        static $UTZx = [ 'UTC-02', 'UTC-11', 'UTC+12' ];
        foreach( $UTZx as $theUTC ) {
            if( false === strpos( $row2, $theUTC )) {
                continue;
            }
            if( false !== strpos( $propAttr[Vcalendar::TZID], Util::$COLON )) {
                $propAttr[Vcalendar::TZID] =
                    StringFactory::beforeLast(
                        Util::$COLON,
                        $propAttr[Vcalendar::TZID]
                    );
            }
            if( false !== strpos( $value, Util::$COLON )) {
                $value = StringFactory::afterLast( Util::$COLON, $row2 );
            }
            break;
        } // end foreach
    }

    /** ***********************************************************************
     *  Getters and setters etc
     */

    /**
     * @return array
     */
    public function getInputiCal()
    {
        return $this->inputiCal;
    }

    /**
     * @return bool
     */
    public function isInputiCalSet()
    {
        return ( ! empty( $this->inputiCal ));
    }

    /**
     * @param string|array $inputiCal
     * @return static
     * @throws UnexpectedValueException
     */
    public function setInputiCal( $inputiCal )
    {
        /* get rows to parse */
        $rows = StringFactory::conformParseInput( $inputiCal );
        /* concatenate property values spread over several rows */
        $this->inputiCal = StringFactory::concatRows( $rows );
        /* Initiate output */
        $this->setVtimezoneRows( [] );
        return $this;
    }


    /**
     * @return array
     */
    private function getVtimezoneRows()
    {
        return $this->vtimezoneRows;
    }

    /**
     * @param string $vtimezoneRow
     * @return static
     */
    private function setVtimezoneRow( $vtimezoneRow )
    {
        $this->vtimezoneRows[] = $vtimezoneRow;
        return $this;
    }

    /**
     * @param array $vtimezoneRows
     * @return static
     */
    private function setVtimezoneRows( array $vtimezoneRows = [] )
    {
        $this->vtimezoneRows = $vtimezoneRows;
        return $this;
    }


    /**
     * @return string
     */
    public function getOutputiCal()
    {
        return $this->outputiCal;
    }

    /**
     * Replace tz in outputiCal
     *
     * @param string $tzidOld
     * @param string $tzidNew
     * @return static
     */
    private function replaceTzidInOutputiCal( $tzidOld, $tzidNew )
    {
        $this->outputiCal = str_replace( $tzidOld, $tzidNew, $this->outputiCal );
        return $this;
    }

    /**
     * Append outputiCal from row
     *
     * @param string $row
     * @return static
     */
    private function setOutputiCalRow( $row )
    {
        $this->outputiCal .= StringFactory::size75( $row );
        return $this;
    }

    /**
     * Append outputiCal row, built from propName, value, propAttr
     *
     * @param string $propName
     * @param string $value
     * @param array  $propAttr
     * @return static
     */
    private function setOutputiCalRowElements( $propName, $value, $propAttr )
    {
        $params = ParameterFactory::createParams( $propAttr );
        $this->outputiCal .= StringFactory::createElement( $propName, $params, $value );
        return $this;
    }


    /**
     * @param string $otherTz
     * @return string|bool|array    bool false on key not found
     */
    public function getOtherTzPhpRelations( $otherTz = null )
    {
        if( ! empty( $otherTz )) {
            return $this->hasOtherTzPHPtzMap( $otherTz )
                ? $this->otherTzPhpRelations[$otherTz]
                : false;
        }
        return $this->otherTzPhpRelations;
    }

    /**
     * @param string $otherTzKey
     * @return bool
     */
    public function hasOtherTzPHPtzMap( $otherTzKey )
    {
        return ( isset( $this->otherTzPhpRelations[$otherTzKey] ));
    }

    /**
     * @param string $otherTzKey
     * @param string $phpTz
     * @param bool   $doTzAssert
     * @throws InvalidArgumentException
     * @return static
     */
    public function addOtherTzPhpRelation( $otherTzKey, $phpTz, $doTzAssert = true )
    {
        if( $doTzAssert ) {
            DateTimeZoneFactory::assertDateTimeZone( $phpTz );
        }
        $this->otherTzPhpRelations[$otherTzKey] = $phpTz;
        return $this;
    }

    /**
     * @param array $otherTzPhpRelations
     * @return static
     */
    private function addOtherTzPhpRelations( array $otherTzPhpRelations )
    {
        $this->otherTzPhpRelations = $otherTzPhpRelations;
        return $this;
    }


    /**
     * @var array  iCal component non-UTC date-property collection
     */
    private static $TZIDPROPS  = [
        Vcalendar::DTSTART,
        Vcalendar::DTEND,
        Vcalendar::DUE,
        Vcalendar::RECURRENCE_ID,
        Vcalendar::EXDATE,
        Vcalendar::RDATE
    ];

    /**
     * @var string
     */
    private static $BEGIN          = 'BEGIN';
    private static $BEGINVTIMEZONE = 'BEGIN:VTIMEZONE';
    private static $BEGINSTANDARD  = 'BEGIN:STANDARD';
    private static $BEGINDAYLIGHT  = 'BEGIN:DAYLIGHT';
    private static $END            = 'END';
    private static $ENDVTIMEZONE   = 'END:VTIMEZONE';
    private static $ENDSTANDARD    = 'END:STANDARD';
    private static $ENDDAYLIGHT    = 'END:DAYLIGHT';
}
