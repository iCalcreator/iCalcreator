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
namespace Kigkonsult\Icalcreator\Traits;

use InvalidArgumentException;
use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

use function is_bool;

/**
 * DESCRIPTION property functions
 *
 * @since 2.29.14 2019-09-03
 */
trait DESCRIPTIONtrait
{
    /**
     * @var array component property DESCRIPTION value
     */
    protected $description = null;

    /**
     * @var array
     */
    private static $MULTIDESCRCOMPS = [ Vcalendar::VCALENDAR, Vcalendar::VJOURNAL ];

    /**
     * Return formatted output for calendar component property description
     *
     * @return string
     * @since 2.27.3 2018-12-22
     */
    public function createDescription() : string
    {
        if( empty( $this->description )) {
            return Util::$SP0;
        }
        $output = Util::$SP0;
        $lang   = $this->getConfig( self::LANGUAGE );
        foreach( $this->description as $dx => $description ) {
            if( ! empty( $description[Util::$LCvalue] )) {
                $output .= StringFactory::createElement(
                    self::DESCRIPTION,
                    ParameterFactory::createParams(
                        $description[Util::$LCparams],
                        self::$ALTRPLANGARR,
                        $lang
                    ),
                    StringFactory::strrep( $description[Util::$LCvalue] )
                );
            }
            elseif( $this->getConfig( self::ALLOWEMPTY )) {
                $output .= StringFactory::createElement( self::DESCRIPTION );
            }
        }
        return $output;
    }

    /**
     * Delete calendar component property description
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since 2.29.5 2019-07-03
     */
    public function deleteDescription( $propDelIx = null ) : bool
    {
        if( empty( $this->description )) {
            unset( $this->propDelIx[self::DESCRIPTION] );
            return false;
        }
        if( ! Util::isCompInList( $this->getCompType(), self::$MULTIDESCRCOMPS )) {
            $propDelIx = 1;
        }
        return CalendarComponent::deletePropertyM(
            $this->description,
            self::DESCRIPTION,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property description
     *
     * @param null|bool|int  $propIx specific property in case of multiply occurrence
     * @param null|bool      $inclParam
     * @return bool|array
     * @since 2.29.5 2019-07-03
     */
    public function getDescription( $propIx = null, $inclParam = null )
    {
        if( empty( $this->description )) {
            unset( $this->propIx[self::DESCRIPTION] );
            return false;
        }
        if( ! Util::isCompInList( $this->getCompType(), self::$MULTIDESCRCOMPS )) {
            if( ! is_bool( $inclParam )) {
                $inclParam = ( true == $propIx ); // note ==
            }
            $propIx = 1;
        }
        return  CalendarComponent::getPropertyM(
            $this->description,
            self::DESCRIPTION,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Set calendar component property description
     *
     * @param null|string  $value
     * @param null|array   $params
     * @param null|integer $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.29.14 2019-09-03
     */
    public function setDescription( $value = null, $params = [], $index = null ) : self
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::DESCRIPTION );
            $value  = Util::$SP0;
            $params = [];
        }
        if( ! Util::isCompInList( $this->getCompType(), self::$MULTIDESCRCOMPS )) {
            $index = 1;
        }
        Util::assertString( $value, self::DESCRIPTION );
        CalendarComponent::setMval(
            $this->description,
            (string) $value,
            $params,
            null,
            $index
        );
        return $this;
    }
}
