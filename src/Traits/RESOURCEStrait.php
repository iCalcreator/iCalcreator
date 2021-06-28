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

use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use InvalidArgumentException;

/**
 * RESOURCES property functions
 *
 * @since 2.29.14 2019-09-03
 */
trait RESOURCEStrait
{
    /**
     * @var array component property RESOURCES value
     */
    protected $resources = null;

    /**
     * Return formatted output for calendar component property resources
     *
     * @return string
     * @since  2.29.11 - 2019-08-30
     */
    public function createResources() : string
    {
        return self::createCatRes(
            self::RESOURCES,
            $this->resources,
            $this->getConfig( self::LANGUAGE ),
            $this->getConfig( self::ALLOWEMPTY ),
            self::$ALTRPLANGARR
        );
    }

    /**
     * Delete calendar component property resources
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteResources( $propDelIx = null ) : bool
    {
        if( empty( $this->resources )) {
            unset( $this->propDelIx[self::RESOURCES] );
            return false;
        }
        return  self::deletePropertyM(
            $this->resources,
            self::RESOURCES,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property resources
     *
     * @param null|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return bool|array
     * @since  2.27.1 - 2018-12-12
     */
    public function getResources( $propIx = null, $inclParam = false )
    {
        if( empty( $this->resources )) {
            unset( $this->propIx[self::RESOURCES] );
            return false;
        }
        return  self::getPropertyM(
            $this->resources,
            self::RESOURCES,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Set calendar component property resources
     *
     * @param null|mixed   $value
     * @param null|array   $params
     * @param null|integer $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.29.14 2019-09-03
     */
    public function setResources( $value = null, $params = [], $index = null ) : self
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::RESOURCES );
            $value  = Util::$SP0;
            $params = [];
        }
        else {
            Util::assertString( $value, self::RESOURCES );
            $value = StringFactory::trimTrailNL( $value );
        }
         self::setMval( $this->resources, $value, $params, null, $index );
        return $this;
    }
}
