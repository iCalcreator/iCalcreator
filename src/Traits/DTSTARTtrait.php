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
namespace Kigkonsult\Icalcreator\Traits;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\Dt1Property;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\VAcomponent;

/**
 * DTSTART property functions
 *
 * @since 2.41.85 2024-01-18
 */
trait DTSTARTtrait
{
    /**
     * @var null|Pc component property DTSTART value
     */
    protected ? Pc $dtstart = null;

    /**
     * Return formatted output for calendar component property dtstart
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function createDtstart() : string
    {
        return  Dt1Property::format(
            self::DTSTART,
            $this->dtstart,
            $this->getConfig( self::ALLOWEMPTY ),
            Dt1Property::getIsDate( $this->dtstart ),
            Dt1Property::getIsLocalTime( $this->dtstart )
        );
    }

    /**
     * Delete calendar component property dtstart
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteDtstart() : bool
    {
        $this->dtstart = null;
        return true;
    }

    /**
     * Return calendar component property dtstart
     *
     * @param null|bool   $inclParam
     * @return bool|string|DateTime|Pc
     * @since 2.41.85 2024-01-18
     */
    public function getDtstart( ? bool $inclParam = false ) : DateTime | bool | string | Pc
    {
        if( empty( $this->dtstart )) {
            return false;
        }
        return $inclParam ? clone $this->dtstart : $this->dtstart->getValue();
    }

    /**
     * Get calendar component property dtstart params, opt TZID only
     *
     * @param null|bool $tzid   if true, only params TZID, if exists
     * @return string[]
     * @since 2.41.85 2024-01-18
     */
    protected function getDtstartParams( ? bool $tzid = true ) : array
    {
        $params = (array) $this->dtstart->getParams();
        if( ! $tzid ) {
            return $params;
        }
        if( empty( $this->dtstart ) ||
            empty( $params ) ||
            ! $this->dtstart->hasParamKey( self::TZID )) {
            return [];
        }
        return $this->dtstart->hasParamKey( self::TZID )
            ? [ self::TZID => $params[self::TZID] ]
            : [];
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isDtstartSet() : bool
    {
        return self::isPropSet( $this->dtstart );
    }

    /**
     * Set calendar component property dtstart
     *
     * @param null|string|Pc|DateTimeInterface  $value
     * @param null|mixed[] $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
     */
    public function setDtstart( null|string|DateTimeInterface|Pc $value = null, ? array $params = [] ) : static
    {
        $pc = Pc::factory( $value, $params );
        $pcValue = $pc->getValue();
        if( empty( $pcValue )) {
            $this->assertEmptyValue( $pcValue, self::DTSTART );
            $this->dtstart = $pc->setEmpty();
            return $this;
        }
        $compType = $this->getCompType();
        switch( true ) {
            case ( $this instanceof VAcomponent ) :
                $pc->addParamValue( self::DATE_TIME ); // req, rfc7953
                break;
            case self::isTzComp( $compType ) :
                $pc->addParam( self::ISLOCALTIME, true );
                $pc->addParamValue( self::DATE_TIME ); // required
                break;
            default :
                $pc->addParamValue( self::DATE_TIME, false );
        } // end switch
        $this->dtstart = DateTimeFactory::setDate( $pc, ( self::VFREEBUSY === $compType )); // $forceUTC
        return $this;
    }
}
