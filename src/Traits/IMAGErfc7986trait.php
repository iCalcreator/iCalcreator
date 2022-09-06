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

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\MultiProps;
use Kigkonsult\Icalcreator\Pc;

use function sprintf;

/**
 * IMAGE property functions
 *
 * @ince 2.41.63 - 2022-09-03
 */
trait IMAGErfc7986trait
{
    /**
     * @var null|Pc[] component property IMAGE value
     */
    protected ? array $image = null;

    /**
     * Return formatted output for calendar component property image
     *
     * @return string
     */
    public function createImage() : string
    {
        return MultiProps::format(
            self::IMAGE,
            $this->image ?? [],
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property image
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     */
    public function deleteImage( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->image )) {
            unset( $this->propDelIx[self::IMAGE] );
            return false;
        }
        return self::deletePropertyM(
            $this->image,
            self::IMAGE,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property image
     *
     * @param null|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     */
    public function getImage( ?int $propIx = null, ?bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->image )) {
            unset( $this->propIx[self::IMAGE] );
            return false;
        }
        return self::getMvalProperty(
            $this->image,
            self::IMAGE,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Return array, all calendar component property image
     *
     * @param null|bool   $inclParam
     * @return array|Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllImage( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->image, $inclParam );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isImageSet() : bool
    {
        return self::isMvalSet( $this->image );
    }

    /**
     * Set calendar component property image
     *
     * @param null|string|Pc   $value
     * @param null|int|array $params
     * @param null|int      $index
     * @return static
     * @throws InvalidArgumentException
     * @ince 2.41.63 - 2022-09-03
     */
    public function setImage(
        null|string|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        static $FMTERR2 = 'Unknown parameter VALUE %s';
        $value = self::marshallInputMval( $value, $params, $index );
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::IMAGE );
            self::setMval( $this->image, $value->setEmpty(), $index );
            return $this;
        }
        $inputValueParam = $value->getValueParam();
        switch( true ) {
            case $value->hasParamKey( self::ENCODING ) :
                $value->addParamValue( self::BINARY );
                break;
            case empty( $inputValueParam ) :
                $value->addParamValue( self::URI );
                break;
            case ( self::URI === $inputValueParam ) :
                break;
            case ( self::BINARY === $inputValueParam ) :
                $value->addParam( self::ENCODING, self::BASE64 );
                break;
            default :
                throw new InvalidArgumentException(
                    sprintf( $FMTERR2, $value->getParams( self::VALUE ))
                );
        } // end switch
        $value->removeParam(self::DISPLAY,self::BADGE ); // remove defaults
        self::setMval( $this->image, $value, $index );
        return $this;
    }
}
