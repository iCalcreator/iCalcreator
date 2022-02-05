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

use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Vcalendar;

/**
 * NAME property functions
 *
 * May occur multiply times in Vcalendar but once in Vlocation/Vresource
 *
 * @since 2.41.5 2022-01-21
 */
trait NAMErfc7986trait
{
    /**
     * @var null|mixed[] component property NAME value
     */
    protected ? array $name = null;

    /**
     * Return formatted output for calendar component property name
     *
     * @return string
     * @since 2.29.5 2019-06-16
     */
    public function createName() : string
    {
        if( empty( $this->name )) {
            return Util::$SP0;
        }
        $output = Util::$SP0;
        $lang   = $this->getConfig( self::LANGUAGE );
        foreach( $this->name as $namePart ) {
            if( empty( $namePart[Util::$LCvalue] )) {
                if( $this->getConfig( self::ALLOWEMPTY )) {
                    $output .= StringFactory::createElement( self::NAME );
                }
                continue;
            }
            $output .= StringFactory::createElement(
                self::NAME,
                ParameterFactory::createParams(
                    $namePart[Util::$LCparams],
                    self::$ALTRPLANGARR,
                    $lang
                ),
                StringFactory::strrep( $namePart[Util::$LCvalue] )
            );
        }
        return $output;
    }

    /**
     * Delete calendar component property name
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since 2.41.5 2022-01-21
     */
    public function deleteName( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->name )) {
            unset( $this->propDelIx[self::NAME] );
            return false;
        }
        if( Vcalendar::VCALENDAR !== $this->getCompType()) {
            $propDelIx = null;
        }
        return CalendarComponent::deletePropertyM(
            $this->name,
            self::NAME,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property name
     *
     * @param null|bool|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return bool|string|mixed[]
     * @since 2.41.5 2022-01-21
     */
    public function getName( null|bool|int $propIx = null, ? bool $inclParam = false ) : array | bool | string
    {
        if( empty( $this->name )) {
            unset( $this->propIx[self::NAME] );
            return false;
        }
        if( Vcalendar::VCALENDAR !== $this->getCompType()) {
            if( is_bool( $propIx )) {
                $inclParam = $propIx;
            }
            $propIx = null;
        }
        return CalendarComponent::getPropertyM(
            $this->name,
            self::NAME,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Set calendar component property name
     *
     * @param null|string   $value
     * @param null|mixed[]  $params
     * @param null|integer  $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.5 2022-01-21
     */
    public function setName( ? string $value = null, ? array $params = [], ? int $index = null ) : static
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::NAME );
            $value  = Util::$SP0;
            $params = [];
        }
        else {
            Util::assertString( $value, self::NAME );
        }
        if( Vcalendar::VCALENDAR !== $this->getCompType()) {
            $index = 1;
        }
        CalendarComponent::setMval( $this->name, $value, $params, null, $index );
        return $this;
    }
}
