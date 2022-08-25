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
namespace Kigkonsult\Icalcreator\Traits;

use DateTimeInterface;
use DateInterval;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\Freebusy;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;

use function count;
use function in_array;
use function is_array;
use function reset;
use function sprintf;
use function var_export;

/**
 * FREEBUSY property functions
 *
 * @since 2.41.55 - 2022-08-13
 */
trait FREEBUSYtrait
{
    /**
     * @var null|Pc[] component property FREEBUSY value
     */
    protected ? array $freebusy = null;

    /**
     * @var string[] FREEBUSY param keywords
     */
    private static array $FREEBUSYKEYS = [
        self::FREE,
        self::BUSY,
        self::BUSY_UNAVAILABLE,
        self::BUSY_TENTATIVE
    ];

    /**
     * Return formatted output for calendar component property freebusy
     *
     * @return string
     * @throws Exception
     * @since 2.41.55 - 2022-08-13
     */
    public function createFreebusy() : string
    {
        return Freebusy::format(
            self::FREEBUSY,
            $this->freebusy ?? [],
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property freebusy
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteFreebusy( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->freebusy )) {
            unset( $this->propDelIx[self::FREEBUSY] );
            return false;
        }
        return self::deletePropertyM(
            $this->freebusy,
            self::FREEBUSY,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property freebusy
     *
     * @param null|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return string|bool|Pc
     * @throws Exception
     * @since 2.41.36 2022-04-03
     */
    public function getFreebusy( ? int $propIx = null, ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->freebusy )) {
            unset( $this->propIx[self::FREEBUSY] );
            return false;
        }
        $output = self::getMvalProperty(
            $this->freebusy,
            self::FREEBUSY,
            $this,
            $propIx,
            $inclParam
        );
        if( empty( $output )) {
            return false;
        }
        return $output;
    }

    /**
     * Return array, all calendar component property freebusy
     *
     * @param null|bool   $inclParam
     * @return array|Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllFreebusy( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->freebusy, $inclParam );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isFreebusySet() : bool
    {
        return self::isMvalSet( $this->freebusy );
    }

    /**
     * Set calendar component property freebusy
     *
     * @param null|string|Pc  $fbType
     * @param null|int|string|DateTimeInterface|array $fbValues
     * @param null|array $params
     * @param null|int        $index
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-09
     * @todo Applications MUST treat x-name and iana-token(?) values they don't recognize
     *       the same way as they would the BUSY value.
     */
    public function setFreebusy(
        null|string|Pc $fbType = null,
        null|int|string|DateTimeInterface|array $fbValues = null,
        ? array $params = [],
        ? int $index = null
    ) : static
    {
        if( $fbType instanceof Pc ) {
            $value    = clone $fbType;
            if( is_int( $fbValues )) {
                $index = $fbValues;
            }
        }
        else {
            $fbType = ( empty( $fbType )) ? self::BUSY : strtoupper( $fbType );
            if( ! in_array( $fbType, self::$FREEBUSYKEYS ) && ! StringFactory::isXprefixed( $fbType )) {
                $fbType = self::BUSY;
            }
            $value  = Pc::factory( $fbValues, ParameterFactory::setParams( $params ))
                ->addParam( self::FBTYPE, $fbType );
        }
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::FREEBUSY );
            self::setMval( $this->freebusy, $value->setEmpty(), $index );
            return $this;
        }
        $value->addParam( self::FBTYPE, self::BUSY, false ); // req
        $input        = self::checkSingleValues( $value->value );
        $value->value = [];
        foreach( $input as $fbix1 => $fbPeriod ) {     // periods => period
            if( ! empty( $fbPeriod )) {
                $value->value[] = self::marshallFreebusyPeriod( $fbix1, $fbPeriod );
            }
        }
        self::setMval( $this->freebusy, $value, $index );
        return $this;
    }

    /**
     * Check for single (date-time) values and, if so, put into array
     *
     * @param string|array $fbValues
     * @return string|array
     * @since 2.41.16 2022-08-18
     */
    private static function checkSingleValues( string | array $fbValues ) : string|array
    {
        if( ! is_array( $fbValues )) {
            return $fbValues;
        }
        if( 2 !== count( $fbValues )) {
            return $fbValues;
        }
        $first = reset( $fbValues );
        if( $first instanceof DateTimeInterface ) {
            return [ $fbValues ];
        }
        if( DateTimeFactory::isStringAndDate( $first)) {
            return [ $fbValues ];
        }
        return $fbValues;
    }

    /**
     * Marshall freebusy periods
     *
     * @param int $fbix1
     * @param array $fbPeriod
     * @return array
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.57 2022-08-17
     */
    private static function marshallFreebusyPeriod( int $fbix1, array $fbPeriod ) : array
    {
        static $ERR2    = 'Unknown freebusy value (#%d/%d) : \'%s\'';
        $freebusyPeriod = [];
        foreach( $fbPeriod as $fbix2 => $fbMember ) { // pairs => singlepart
            switch( true ) {
                case ( $fbMember instanceof DateTimeInterface ) : // datetime
                    $freebusyPeriod[$fbix2] =
                        DateTimeFactory::setDateTimeTimeZone(
                            DateTimeFactory::toDateTime( $fbMember ),
                            self::UTC
                        );
                    break;
                case ( $fbMember instanceof DateInterval ) :
                    // interval (always 2nd part)
                    $freebusyPeriod[$fbix2] = $fbMember;
                    break;
                case DateTimeFactory::isStringAndDate( $fbMember ) :
                    // text date ex. 2006-08-03 10:12:18
                    [ $dateStr, $timezonePart ] =
                        DateTimeFactory::splitIntoDateStrAndTimezone( $fbMember );
                    $dateTime = DateTimeFactory::getDateTimeWithTimezoneFromString(
                        $dateStr,
                        $timezonePart,
                        self::UTC,
                        true
                    );
                    $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime,self::UTC );
                    $freebusyPeriod[$fbix2] = $dateTime;
                    break;
                case DateIntervalFactory::isStringAndDuration( $fbMember ) :
                    // duration string (always 2nd part)
                    $fbMember = DateIntervalFactory::removePlusMinusPrefix( $fbMember ); // can only be positive
                    // fix pre 7.0.5 bug
                    $freebusyPeriod[$fbix2] =
                        DateIntervalFactory::conformDateInterval(
                            DateIntervalFactory::factory( $fbMember )
                        );
                    break;
                default :
                    throw new InvalidArgumentException(
                        sprintf( $ERR2, $fbix1, $fbix2, var_export( $fbMember, true ))
                    );
            } // end switch
        } // end foreach
        return $freebusyPeriod;
    }
}
