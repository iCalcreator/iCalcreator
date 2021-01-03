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

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

use function array_change_key_case;
use function sprintf;

/**
 * IMAGE property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.21 2019-06-17
 */
trait IMAGErfc7986trait
{
    /**
     * @var array component property IMAGE value
     */
    protected $image = null;

    /**
     * Return formatted output for calendar component property image
     *
     * @return string
     */
    public function createImage()
    {
        if( empty( $this->image )) {
            return null;
        }
        $output = null;
        foreach( $this->image as $aix => $imagePart ) {
            if( ! empty( $imagePart[Util::$LCvalue] )) {
                $output .= StringFactory::createElement(
                    self::IMAGE,
                    ParameterFactory::createParams(
                        $imagePart[Util::$LCparams],
                        [ self::ALTREP, self::DISPLAY ]
                    ),
                    $imagePart[Util::$LCvalue]
                );
            }
            elseif( $this->getConfig( self::ALLOWEMPTY )) {
                $output .= StringFactory::createElement( self::IMAGE );
            }
        } // end foreach
        return $output;
    }

    /**
     * Delete calendar component property image
     *
     * @param int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     */
    public function deleteImage( $propDelIx = null )
    {
        if( empty( $this->image )) {
            unset( $this->propDelIx[self::IMAGE] );
            return false;
        }
        return $this->deletePropertyM( $this->image, self::IMAGE, $propDelIx );
    }

    /**
     * Get calendar component property image
     *
     * @param int    $propIx specific property in case of multiply occurrence
     * @param bool   $inclParam
     * @return bool|array
     */
    public function getImage( $propIx = null, $inclParam = false )
    {
        if( empty( $this->image )) {
            unset( $this->propIx[self::IMAGE] );
            return false;
        }
        return $this->getPropertyM( $this->image, self::IMAGE, $propIx, $inclParam );
    }

    /**
     * Set calendar component property image
     *
     * @param string  $value
     * @param array   $params
     * @param integer $index
     * @return static
     * @throws InvalidArgumentException
     */
    public function setImage( $value = null, $params = [], $index = null )
    {
        static $FMTERR2 = 'Unknown parameter VALUE %s';
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::IMAGE );
            $value  = Util::$SP0;
            $params = [];
            $this->setMval( $this->image, $value, $params, null, $index );
            return $this;
        }
        $params     = array_change_key_case( $params, CASE_UPPER );
        switch( true ) {
            case isset( $params[self::ENCODING] ) :
                $params[self::VALUE] = self::BINARY;
                break;
            case ( ! isset( $params[self::VALUE] )) :
                $params[self::VALUE] = self::URI;
                break;
            case ( self::URI === $params[self::VALUE] ) :
                break;
            case ( self::BINARY === $params[self::VALUE] ) :
                $params[self::ENCODING] = self::BASE64;
                break;
            default :
                throw new InvalidArgumentException(
                    sprintf( $FMTERR2, $params[self::VALUE] )
                );
                break;
        } // end switch
        // remove defaults
        ParameterFactory::ifExistRemove(
            $params,
            self::DISPLAY,
            self::BADGE
        );
        $this->setMval( $this->image, $value, $params, null, $index );
        return $this;
    }
}
