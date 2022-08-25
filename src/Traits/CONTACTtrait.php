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
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * CONTACT property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait CONTACTtrait
{
    /**
     * @var null|Pc[] component property CONTACT value
     */
    protected ? array $contact = null;

    /**
     * Return formatted output for calendar component property contact
     *
     * @return string
     */
    public function createContact() : string
    {
        return MultiProps::format(
            self::CONTACT,
            $this->contact ?? [],
            $this->getConfig( self::ALLOWEMPTY ),
            $this->getConfig( self::LANGUAGE )
        );
    }

    /**
     * Delete calendar component property contact
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since 2.41.32 2022-03-17
     */
    public function deleteContact( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->contact )) {
            unset( $this->propDelIx[self::CONTACT] );
            return false;
        }
        if( self::isContactSingleProp( $this->getCompType())) {
            $propDelIx = null;
        }
        return self::deletePropertyM(
            $this->contact,
            self::CONTACT,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property contact
     *
     * @param null|bool|int $propIx specific property in case of multiply occurrence
     * @param null|bool     $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-11
     */
    public function getContact( null|bool|int $propIx = null, ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->contact )) {
            unset( $this->propIx[self::CONTACT] );
            return false;
        }
        $isSingleType = self::isContactSingleProp( $this->getCompType());
        if( $isSingleType ) {
            if( is_bool( $propIx )) {
                $inclParam = $propIx;
            }
            $propIx = null;
        }
        $result = self::getMvalProperty(
            $this->contact,
            self::CONTACT,
            $this,
            $propIx,
            $inclParam
        );
        if( $isSingleType ) {
            unset( $this->propIx[self::VFREEBUSY] );
        }
        return $result;
    }

    /**
     * Return array, all calendar component property contact
     *
     * @param null|bool   $inclParam
     * @return array|Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllContact( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->contact, $inclParam );
    }

    /**
     * Return bool true if Contact property may only occur once in component
     *
     * @param string $compName
     * @return bool
     * @since 2.41.36 2022-04-11
     */
    public static function isContactSingleProp( string $compName ) : bool
    {
        return ( self::VFREEBUSY === $compName );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isContactSet() : bool
    {
        return self::isMvalSet( $this->contact );
    }

    /**
     * Set calendar component property contact
     *
     * @param null|string|Pc   $value
     * @param null|int|array $params
     * @param null|int         $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-11
     */
    public function setContact(
        null|string|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $value = self::marshallInputMval( $value, $params, $index );
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::CONTACT );
            $value->setEmpty();
        }
        else {
            $value->value = Util::assertString( $value->value, self::CONTACT );
            $value->value = StringFactory::trimTrailNL( $value->value );
        }
        if( self::isContactSingleProp( $this->getCompType())) {
            $index = 1;
        }
        self::setMval( $this->contact, $value, $index );
        return $this;
    }
}
