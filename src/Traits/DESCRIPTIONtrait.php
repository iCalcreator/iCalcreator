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
use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * DESCRIPTION property functions
 *
 * DESCRIPTION may occur multiply times i Vcalendar and Vjournal, once otherwise
 *
 * @since 2.41.55 2022-08-13
 */
trait DESCRIPTIONtrait
{
    /**
     * @var null|Pc[] component property DESCRIPTION value
     */
    protected ? array $description = null;

    /**
     * Return formatted output for calendar component property description
     *
     * @return string
     * @since 2.41.36 2022-04-03
     */
    public function createDescription() : string
    {
        return MultiProps::format(
            self::DESCRIPTION,
            $this->description ?? [],
            $this->getConfig( self::ALLOWEMPTY ),
            $this->getConfig( self::LANGUAGE )
        );
    }

    /**
     * Delete calendar component property description
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since 2.41.36 2022-04-11
     */
    public function deleteDescription( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->description )) {
            unset( $this->propDelIx[self::DESCRIPTION] );
            return false;
        }
        if( self::isDescriptionSingleProp( $this->getCompType())) {
            $propDelIx = null;
        }
        return self::deletePropertyM(
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
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-11
     */
    public function getDescription( null|bool|int $propIx = null, ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->description )) {
            unset( $this->propIx[self::DESCRIPTION] );
            return false;
        }
        $isSingleType = self::isDescriptionSingleProp( $this->getCompType());
        if( $isSingleType ) {
            if( is_bool( $propIx )) {
                $inclParam = $propIx;
            }
            $propIx = null;
        }
        $result = self::getMvalProperty(
            $this->description,
            self::DESCRIPTION,
            $this,
            $propIx,
            $inclParam
        );
        if( $isSingleType ) {
            unset( $this->propIx[self::DESCRIPTION] );
        }
        return $result;
    }

    /**
     * Return array, all calendar component property description
     *
     * @param null|bool   $inclParam
     * @return array|Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllDescription( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->description, $inclParam );
    }

    /**
     * Return bool true if DESCRIPTION property may only occur once in component
     *
     * @param string $compName
     * @return bool
     * @since 2.41.36 2022-04-11
     */
    public static function isDescriptionSingleProp( string $compName ) : bool
    {
        static $MULTIDESCRCOMPS = [ Vcalendar::VCALENDAR,self::VJOURNAL ];
        return ( ! in_array( $compName, $MULTIDESCRCOMPS, true ) );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isDescriptionSet() : bool
    {
        return self::isMvalSet( $this->description );
    }

    /**
     * Set calendar component property description
     *
     * @param null|string|Pc $value
     * @param null|int|array $params
     * @param null|int       $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-11
     */
    public function setDescription(
        null|string|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $value = self::marshallInputMval( $value, $params, $index );
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::DESCRIPTION );
            $value->setEmpty();
        }
        else{
            $value->value = Util::assertString( $value->value, self::DESCRIPTION );
            $value->value = StringFactory::trimTrailNL( $value->value );
        }
        if( self::isDescriptionSingleProp( $this->getCompType())) {
            $index = 1;
        }
        self::setMval( $this->description, $value, $index );
        return $this;
    }
}
