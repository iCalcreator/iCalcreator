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
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

/**
 * ATTACH property functions
 *
 * @since 2.27.3 2018-12-20
 */
trait ATTACHtrait
{
    /**
     * @var array component property ATTACH value
     */
    protected $attach = null;

    /**
     * Return formatted output for calendar component property attach
     *
     * @return string
     */
    public function createAttach() : string
    {
        if( empty( $this->attach )) {
            return Util::$SP0;
        }
        $output = Util::$SP0;
        foreach( $this->attach as $aix => $attachPart ) {
            if( ! empty( $attachPart[Util::$LCvalue] )) {
                $output .= StringFactory::createElement(
                    self::ATTACH,
                    ParameterFactory::createParams( $attachPart[Util::$LCparams] ),
                    $attachPart[Util::$LCvalue]
                );
            }
            elseif( $this->getConfig( self::ALLOWEMPTY )) {
                $output .= StringFactory::createElement( self::ATTACH );
            }
        } // end foreach
        return $output;
    }

    /**
     * Delete calendar component property attach
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteAttach( $propDelIx = null ) : bool
    {
        if( empty( $this->attach )) {
            unset( $this->propDelIx[self::ATTACH] );
            return false;
        }
        return  self::deletePropertyM(
            $this->attach,
            self::ATTACH,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property attach
     *
     * @param null|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return bool|array
     * @since  2.27.1 - 2018-12-16
     */
    public function getAttach( $propIx = null, $inclParam = false )
    {
        if( empty( $this->attach )) {
            unset( $this->propIx[self::ATTACH] );
            return false;
        }
        return  self::getPropertyM(
            $this->attach,
            self::ATTACH,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Set calendar component property attach
     *
     * @param null|string  $value
     * @param null|array   $params
     * @param null|integer $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.27.3 2018-12-20
     */
    public function setAttach( $value = null, $params = [], $index = null ) : self
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::ATTACH );
            $value  = Util::$SP0;
            $params = [];
        }
         self::setMval( $this->attach, $value, $params, null, $index );
        return $this;
    }
}
