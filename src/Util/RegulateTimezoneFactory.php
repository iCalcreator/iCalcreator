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

use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Parser\ComponentParser;
use Kigkonsult\Icalcreator\Parser\VcalendarParser;
use RuntimeException;
use UnexpectedValueException;

use function array_keys;
use function array_reverse;
use function arsort;
use function explode;
use function implode;
use function in_array;
use function key;
use function reset;
use function sprintf;
use function str_replace;
use function str_starts_with;
use function timezone_name_from_abbr;

/**
 * Class RegulateTimezoneFactory
 *
 * Review timezones, opt. alter to PHP timezones
 *
 * @see https://docs.microsoft.com/en-us/windows-hardware/manufacture/desktop/default-time-zones
 * Cover Vtimezone property TZID and component date properties DTSTART, DTEND, DUE, RECURRENCE-ID, EXDATE, RDATE
 *
 * @since  2.29.10 - 2019-09-02
 * @deprecated (2.41.57)
 */
class RegulateTimezoneFactory
{
    /**
     * Add MS timezone and offset to (internal) MStimezoneToOffset
     *
     * @param string $msTz
     * @param string $offset   (+/-)HH:mm
     * @return void
     */
    public static function addMStimezoneToOffset( string $msTz, string $offset ) : void
    {
        self::$MStimezoneToOffset[$msTz] = $offset;
    }

    /**
     * 4 GMT/UTC(-suffixed)
     * 59 matches on NOT dst, OK
     * 5 hits on dst (aka daylight saving time)...
     * 5 PHP timezones specified
     *
     * @var string[]  MS timezones with corr. UTC offset, 73 items
     */
    public static array $MStimezoneToOffset = [
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
     * @return void
     * @throws InvalidArgumentException
     */
    public static function addOtherTzMapToPhpTz( string $otherTz, string $phpTz ) : void
    {
        DateTimeZoneFactory::assertDateTimeZone( $phpTz );
        self::$otherTzToPhpTz[$otherTz] = $phpTz;
    }

    /**
     * @var string[]  7 MS timezones to PHP timezones
     */
    public static array $otherTzToPhpTz = [
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
     * @var string[]
     */
    private array $inputiCal = [];

    /**
     * @var string
     */
    private string $outputiCal;

    /**
     * @var string[]
     */
    private array $vtimezoneRows = [];

    /**
     * @var string[]
     */
    private array $otherTzPhpRelations = [];

    /**
     * Class constructor
     *
     * @param null|string|string[] $inputiCal strict rfc2445 formatted calendar
     * @param null|string[] $otherTzPhpRelations [ other => phpTz ]
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function __construct( null|string|array $inputiCal = null, ? array $otherTzPhpRelations = [] )
    {
        $this->outputiCal = Util::$SP0;
        if( ! empty( $inputiCal )) {
            $this->setInputiCal( $inputiCal );
        }
        $this->addOtherTzPhpRelations( self::$otherTzToPhpTz );
        foreach((array) $otherTzPhpRelations as $otherTz => $phpTz ) {
            $this->addOtherTzPhpRelation( $otherTz, $phpTz );
        }
    }

    /**
     * Class factory method
     *
     * @param null|string|string[] $inputiCal strict rfc2445 formatted calendar
     * @param null|string[] $otherTzPhpRelations [ other => phpTz ]
     * @return self
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function factory( null|string|array $inputiCal = null, ? array $otherTzPhpRelations = [] ) : self
    {
        return new self( $inputiCal, $otherTzPhpRelations );
    }


    /**
     * Short static all-in-one method
     *
     * @param string|string[] $inputiCal    strict rfc2445 formatted calendar
     * @param null|string[]   $otherTzPhpRelations  [ other => phpTz ]
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public static function process( string | array $inputiCal, ? array $otherTzPhpRelations = [] ) : string
    {
        return self::factory( $inputiCal, $otherTzPhpRelations )
                   ->processCalendar()
                   ->getOutputiCal();
    }


    /**
     * @param null|string|string[] $inputiCal    strict rfc2445 formatted calendar
     * @return self
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function processCalendar( null | string | array $inputiCal = null ) : self
    {
        $FMTERR = 'Calendar content required!';
        if( ! empty( $inputiCal )) {
            $this->setInputiCal( $inputiCal );
        }
        if( ! $this->isInputiCalSet()) {
            throw new InvalidArgumentException( $FMTERR );
        }
        $vtSwitch = false;
        foreach( $this->getInputiCal() as $row ) {
            if( str_starts_with( $row, self::$BEGINVTIMEZONE )) {
                $this->setVtimezoneRow( $row );
                $vtSwitch = true;
                continue;
            }
            if( str_starts_with( $row, self::$ENDVTIMEZONE )) {
                $this->setVtimezoneRow( $row );
                $this->processVtimezone();
                $this->setEmptyVtimezoneRows();
                $vtSwitch = false;
                continue;
            }
            if( $vtSwitch ) {
                $this->setVtimezoneRow( $row );
                continue;
            }
            if( str_starts_with( $row, self::$BEGIN ) ||
                str_starts_with( $row, self::$END )) {
                $this->setOutputiCalRow( $row );
                continue;
            }
            /* split property name  and  opt.params and value */
            [$propName, $row2] = StringFactory::getPropName( $row );
            if( ! in_array( $propName, self::$TZIDPROPS, true )) {
                $this->setOutputiCalRow( $row );
                continue;
            }
            /* Now we have only properties with propAttr TZID */
            /* separate attributes from value */
            [ $value, $propAttr ] = self::splitContent( $row2 );
            if( ! isset( $propAttr[IcalInterface::TZID] )) {
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
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function processVtimezone() : void
    {
        $currTzId      = null;                 // empty if Vtimezone TZID is found else troublesome one
        $currTzIdFound = false;                // true if PHP Vtimezone TZID found
        $stdSwitch     = $dlghtSwitch = false; // process STANDARD/DAYLIGHT or not
        $stdArr        = $dlghtArr = [];       // TZOFFSETTO values (in  STANDARD/DAYLIGHT)
        foreach( $this->getVtimezoneRows() as $row ) {
            switch( true ) {
                case str_starts_with( $row, self::$BEGINVTIMEZONE ) :
                    $this->setOutputiCalRow( $row );
                    continue 2;
                case str_starts_with( $row, self::$ENDVTIMEZONE ) :
                    if( ! empty( $currTzId )) {
                        $this->processCurrTzId( $currTzId, $stdArr, $dlghtArr );
                    }
                    $this->setOutputiCalRow( $row );
                    $currTzId      = null;
                    $currTzIdFound = false;
                    $stdSwitch     = $dlghtSwitch = false; // process STANDARD/DAYLIGHT or not
                    $stdArr        = $dlghtArr = [];       // TZOFFSETTO values (in STANDARD/DAYLIGHT)
                    continue 2;
                case str_starts_with( $row, self::$BEGINSTANDARD ) :
                    $this->setOutputiCalRow( $row );
                    $stdSwitch = true;
                    continue 2;
                case str_starts_with( $row, self::$ENDSTANDARD ) :
                    $this->setOutputiCalRow( $row );
                    $stdSwitch = false;
                    continue 2;
                case str_starts_with( $row, self::$BEGINDAYLIGHT ) :
                    $this->setOutputiCalRow( $row );
                    $dlghtSwitch = true;
                    continue 2;
                case str_starts_with( $row, self::$ENDDAYLIGHT ) :
                    $this->setOutputiCalRow( $row );
                    $dlghtSwitch = false;
                    continue 2;
                case $currTzIdFound : // Vtimezone TZID is found, write whatever row it is
                    $this->setOutputiCalRow( $row );
                    continue 2;
                default :
                    break; // now we go on with property rows
            } // end switch
            /* split property name  and  opt.params and value */
            [ $propName, $row2 ] = StringFactory::getPropName( $row );
            if( IcalInterface::TZOFFSETTO === $propName ) { // save offset if...
                if( $stdSwitch ) {
                    $stdArr[] = StringFactory::afterLast( Util::$COLON, $row2 );
                }
                elseif( $dlghtSwitch ) {
                    $dlghtArr[] = StringFactory::afterLast( Util::$COLON, $row2 );
                }
            }
            if( IcalInterface::TZID !== $propName ) {  // skip all but Vtimezone TZID
                $this->setOutputiCalRow( $row );
                continue;
            }
            /* separate attributes from value */
            [ $value, $propAttr ] = ComponentParser::splitContent( $row2 );
            $currTzId = $value;
            $valueNew = null;
            switch( true ) {
                case ( $this->hasOtherTzPHPtzMap( $value )) :
                    $valueNew = (string) $this->getOtherTzPhpRelations( $value );
                    break;
                case ( isset( self::$MStimezoneToOffset[$value] )) :
                    $msTzOffset = self::$MStimezoneToOffset[$value];
                    if( empty( $msTzOffset )) {
                        $valueNew = IcalInterface::UTC;
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
     * @param string   $currTzId
     * @param array $stdArr
     * @param array $dlghtArr
     * @throws RuntimeException
     */
    private function processCurrTzId( string $currTzId, array $stdArr, array $dlghtArr ) : void
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
     * @param string  $propName
     * @param string  $value
     * @param array $propAttr
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @todo properties RDATE, EXDATE
     */
    private function processDtProp( string $propName, string $value, array $propAttr ) : void
    {
        $tzId = $propAttr[IcalInterface::TZID];
        switch( true ) {
            case ( $this->hasOtherTzPHPtzMap( $tzId ) ) :
                $propAttr[IcalInterface::TZID] = $this->getOtherTzPhpRelations( $tzId );
                break;
            case ( isset( self::$MStimezoneToOffset[$tzId] ) &&
                empty( self::$MStimezoneToOffset[$tzId] )) :
                $this->addOtherTzPhpRelation( $tzId, IcalInterface::UTC, false );
                $propAttr[IcalInterface::TZID] = IcalInterface::UTC;
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
     * @param string  $propName
     * @param string  $value
     * @param array $propAttr
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function processDatePropsTZIDattribute( string $propName, string $value, array $propAttr ) : void
    {
        $tzId = $tzIdIn = $propAttr[IcalInterface::TZID];
        if( isset( self::$MStimezoneToOffset[$tzIdIn] )) {
            $tzId = self::getTimeZoneNameFromOffset(
                self::$MStimezoneToOffset[$tzIdIn]
            );
            $this->addOtherTzPhpRelation( $tzIdIn, $tzId, false );
            $propAttr[IcalInterface::TZID] = $tzId;
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
     * @return array  [ value, propAttr ]
     */
    private static function splitContent( string $row2 ) : array
    {
        /* separate attributes from value */
        [ $value, $propAttr ] = ComponentParser::splitContent( $row2 );
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
    private static function getTimeZoneNameFromOffset( string $offset, ? bool $throwException = true ) : string
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
        if( $throwException ?? true ) {
            throw new RuntimeException( sprintf( $ERR, $offset, $seconds ));
        }
        return Util::$SP0;
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
    private static function getTimezoneListFromOffset( string $offset, int $dst ) : array
    {
        static $DST        = 'dst';
        static $OFFSET     = 'offset';
        static $TIMEZONEID = 'timezone_id';
        static $FMTERR     = 'Can\'t get offset from timezone %s';
        $seconds = DateTimeZoneFactory::offsetToSeconds( $offset );
        $output  = [];
        foreach( array_reverse( DateTimeZone::listAbbreviations()) as $tzAbbrList ) {
            foreach( $tzAbbrList as $tzAbbrCity ) {
                if(((bool) $tzAbbrCity[$DST] !== (bool) $dst ) ||
                    ( $tzAbbrCity[$OFFSET] !== $seconds ) ||
                    empty( $tzAbbrCity[$TIMEZONEID] )) {
                    continue;
                }
                try {
                    $date = new DateTime(
                        DateTimeFactory::$NOW,
                        new DateTimeZone( $tzAbbrCity[$TIMEZONEID] )
                    );
                    $dateTimeOffsetNow = $date->getOffset();
                }
                catch( Exception $e ) {
                    throw new RuntimeException(
                        sprintf( $FMTERR, $tzAbbrCity[$TIMEZONEID] ),
                        12345,
                        $e
                    );
                }
                if( $seconds === $dateTimeOffsetNow ) {
                    $tzId = $tzAbbrCity[$TIMEZONEID];
                    if( isset( $output[$tzId] )) {
                        ++$output[$tzId];
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
     * @param string  $value
     * @param array $propAttr
     * @return void
     */
    private static function checkTzidForUTC( string & $value, array & $propAttr ) : void
    {
        if( ! DateTimeZoneFactory::isUTCtimeZone( $propAttr[IcalInterface::TZID] )) {
            return;
        }
        unset( $propAttr[IcalInterface::TZID] );
        $values = explode( Util::$COMMA, $value );
        foreach( array_keys( $values ) as $x ) {
            if( isset( $propAttr[IcalInterface::VALUE] ) &&
                ( IcalInterface::PERIOD === $propAttr[IcalInterface::VALUE] )) { // RDATE
                $thePeriods     = explode( Util::$SLASH, $values[$x] );
                $thePeriods[0] .= IcalInterface::Z;
                if( ! DateIntervalFactory::isStringAndDuration( $thePeriods[1] )) {
                    $thePeriods[1] .= IcalInterface::Z;
                }
                $values[$x] = implode( Util::$SLASH, $thePeriods );
            }
            else {
                $values[$x] .= IcalInterface::Z;
            }
        }
        $value = implode( Util::$COMMA, $values );
    }

    /**
     * Fix StringFactory::splitContent UTC* bug for MS list UTC-related timezones
     *
     * Note, here $propAttr[Vcalendar::TZID] exists
     *
     * @param string  $row2
     * @param string  $value
     * @param array $propAttr
     */
    private static function fixUTCx( string $row2, string & $value, array & $propAttr ) : void
    {
        static $UTZx = [ 'UTC-02', 'UTC-11', 'UTC+12' ];
        foreach( $UTZx as $theUTC ) {
            if( !str_contains( $row2, $theUTC ) ) {
                continue;
            }
            if( str_contains( $propAttr[IcalInterface::TZID], Util::$COLON ) ) {
                $propAttr[IcalInterface::TZID] =
                    StringFactory::beforeLast(
                        Util::$COLON,
                        $propAttr[IcalInterface::TZID]
                    );
            }
            if( str_contains( $value, Util::$COLON ) ) {
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
    public function getInputiCal() : array
    {
        return $this->inputiCal;
    }

    /**
     * @return bool
     */
    public function isInputiCalSet() : bool
    {
        return ( ! empty( $this->inputiCal ));
    }

    /**
     * @param string|array $inputiCal
     * @return self
     * @throws UnexpectedValueException
     * @throws Exception
     */
    public function setInputiCal( string | array $inputiCal ) : self
    {
        /* get rows to parse */
        $rows = VcalendarParser::conformParseInput( $inputiCal );
        /* concatenate property values spread over several rows */
        $this->inputiCal = VcalendarParser::concatRows( $rows );
        /* Initiate output */
        $this->setEmptyVtimezoneRows();
        return $this;
    }


    /**
     * @return array
     */
    private function getVtimezoneRows() : array
    {
        return $this->vtimezoneRows;
    }

    /**
     * @param string $vtimezoneRow
     * @return void
     */
    private function setVtimezoneRow( string $vtimezoneRow ) : void
    {
        $this->vtimezoneRows[] = $vtimezoneRow;
    }

    /**
     * @return void
     */
    private function setEmptyVtimezoneRows() : void
    {
        $this->vtimezoneRows = [];
    }


    /**
     * @return string
     */
    public function getOutputiCal() : string
    {
        return $this->outputiCal;
    }

    /**
     * Replace tz in outputiCal
     *
     * @param string $tzidOld
     * @param string $tzidNew
     * @return void
     */
    private function replaceTzidInOutputiCal( string $tzidOld, string $tzidNew ) : void
    {
        $this->outputiCal = str_replace( $tzidOld, $tzidNew, $this->outputiCal );
    }

    /**
     * Append outputiCal from row
     *
     * @param string $row
     * @return void
     */
    private function setOutputiCalRow( string $row ) : void
    {
        $this->outputiCal .= Property::size75( $row );
    }

    /**
     * Append outputiCal row, built from propName, value, propAttr
     *
     * @param string  $propName
     * @param string  $value
     * @param array $propAttr
     * @return void
     */
    private function setOutputiCalRowElements( string $propName, string $value, array $propAttr ) : void
    {
        $this->outputiCal .= Property::renderProperty(
            $propName,
            Property::formatParams( $propAttr ),
            $value
        );
    }


    /**
     * @param null|string $otherTz
     * @return string|bool|array    bool false on key not found
     */
    public function getOtherTzPhpRelations( ? string $otherTz = null ) : bool | array | string
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
    public function hasOtherTzPHPtzMap( string $otherTzKey ) : bool
    {
        return ( isset( $this->otherTzPhpRelations[$otherTzKey] ));
    }

    /**
     * @param string $otherTzKey
     * @param string $phpTz
     * @param bool   $doTzAssert
     * @return self
     * @throws InvalidArgumentException
     * @return self
     */
    public function addOtherTzPhpRelation(
        string $otherTzKey,
        string $phpTz,
        ? bool $doTzAssert = true
    ) : self
    {
        if( $doTzAssert ) {
            DateTimeZoneFactory::assertDateTimeZone( $phpTz );
        }
        $this->otherTzPhpRelations[$otherTzKey] = $phpTz;
        return $this;
    }

    /**
     * @param array $otherTzPhpRelations
     * @return void
     */
    private function addOtherTzPhpRelations( array $otherTzPhpRelations ) : void
    {
        $this->otherTzPhpRelations = $otherTzPhpRelations;
    }


    /**
     * @var string[]  iCal component non-UTC date-property collection
     */
    private static array $TZIDPROPS  = [
        IcalInterface::DTSTART,
        IcalInterface::DTEND,
        IcalInterface::DUE,
        IcalInterface::RECURRENCE_ID,
        IcalInterface::EXDATE,
        IcalInterface::RDATE
    ];

    /**
     * @var string
     */
    private static string $BEGIN          = 'BEGIN';

    /**
     * @var string
     */
    private static string $BEGINVTIMEZONE = 'BEGIN:VTIMEZONE';

    /**
     * @var string
     */
    private static string $BEGINSTANDARD  = 'BEGIN:STANDARD';

    /**
     * @var string
     */
    private static string $BEGINDAYLIGHT  = 'BEGIN:DAYLIGHT';

    /**
     * @var string
     */
    private static string $END            = 'END';

    /**
     * @var string
     */
    private static string $ENDVTIMEZONE   = 'END:VTIMEZONE';

    /**
     * @var string
     */
    private static string $ENDSTANDARD    = 'END:STANDARD';

    /**
     * @var string
     */
    private static string $ENDDAYLIGHT    = 'END:DAYLIGHT';
}
