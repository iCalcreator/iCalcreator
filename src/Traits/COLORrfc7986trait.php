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

namespace Kigkonsult\Icalcreator\Traits;

use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * COLOR property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.14 2019-09-03
 * @see https://www.w3.org/TR/css-color-3/#svg-color
 */
trait COLORrfc7986trait
{
    /**
     * @var array component property COLOR value
     */
    protected $color = null;

    /**
     * Return formatted output for calendar (component property color
     *
     * @return string
     * @since 2.29.5 2019-06-16
     */
    public function createColor()
    {
        if( empty( $this->color )) {
            return null;
        }
        if( empty( $this->color[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::COLOR )
                : null;
        }
        return StringFactory::createElement(
            self::COLOR,
            ParameterFactory::createParams( $this->color[Util::$LCparams] ),
            $this->color[Util::$LCvalue]
        );
    }

    /**
     * Delete calendar component property color
     *
     * @return bool
     * @since 2.29.5 2019-06-16
     */
    public function deleteColor()
    {
        $this->color = null;
        return true;
    }

    /**
     * Get calendar component property color
     *
     * @param bool   $inclParam
     * @return bool|array
     * @since 2.29.5 2019-06-16
     */
    public function getColor( $inclParam = false )
    {
        if( empty( $this->color )) {
            return false;
        }
        return ( $inclParam ) ? $this->color : $this->color[Util::$LCvalue];
    }

    /**
     * Set calendar component property color
     *
     * @param string $value
     * @param array  $params
     * @return static
     * @since 2.29.14 2019-09-03
     */
    public function setColor( $value = null, $params = [] )
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::COLOR );
            $value  = Util::$SP0;
            $params = [];
        }
        else {
            Util::assertString( $value, self::COLOR );
        }
        $this->color = [
            Util::$LCvalue  => StringFactory::trimTrailNL( $value ),
            Util::$LCparams => ParameterFactory::setParams( $params ),
        ];
        return $this;
    }
}
