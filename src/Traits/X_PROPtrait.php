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

use Kigkonsult\Icalcreator\Formatter\Property\Xproperty;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

use function count;
use function is_array;
use function sprintf;
use function strtoupper;

/**
 * X-property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait X_PROPtrait
{
    /**
     * @var null|array component property X-properties  ( name => value )
     */
    protected ? array $xprop = [];

    /**
     * Return formatted output for calendar/component property x-prop
     *
     * @return string
     */
    public function createXprop() : string
    {
        return Xproperty::format(
            $this->getAllXprop( true ),
            $this->getConfig( self::ALLOWEMPTY ),
            $this->getConfig( self::LANGUAGE )
        );
    }

    /**
     * Delete component property X-prop value
     *
     * @param null|string $propName
     * @param null|int    $currPropDelIx removal index
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteXprop( ? string $propName = null, ? int $currPropDelIx = null ) : bool
    {
        $propName = ( $propName ) ? strtoupper( $propName ) : self::X_PROP;
        if( empty( $this->xprop )) {
            foreach( $this->propDelIx as $propName2 => $v ) {
                if( StringFactory::isXprefixed( $propName2 )) {
                    unset( $this->propDelIx[$propName2] );
                }
            }
            return false;
        }
        if( null === $currPropDelIx ) {
            $currPropDelIx = ( isset( $this->propDelIx[$propName] ) && ( self::X_PROP !== $propName ))
                ? $this->propDelIx[$propName] + 2
                : 1;
        }
        $this->propDelIx[$propName] = --$currPropDelIx;
        $reduced = [];
        if( $propName !== self::X_PROP ) {
            if( ! isset( $this->xprop[$propName] )) {
                unset( $this->propDelIx[$propName] );
                return false;
            }
            foreach( $this->xprop as $k => $xValue ) {
                if( $k !== $propName ) {
                    $reduced[$k] = $xValue;
                }
            }
        } // end if
        else {
            if( count( $this->xprop ) <= $currPropDelIx ) {
                unset( $this->propDelIx[$propName] );
                return false;
            }
            $xpropNo = 0;
            foreach( $this->xprop as $xpropKey => $xpropValue ) {
                if( $currPropDelIx !== $xpropNo ) {
                    $reduced[$xpropKey] = $xpropValue;
                }
                $xpropNo++;
            }
        } // end else
        $this->xprop = $reduced;
        if( empty( $this->xprop )) {
            unset( $this->propDelIx[$propName] );
            return false;
        }
        return true;
    }

    /**
     * Get calendar component property x-prop
     *
     * @param null|string $propName
     * @param null|int    $currPropIx    specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return bool|array  [ propName, string/Pc ]
     * @since 2.41.36 2022-04-03
     */
    public function getXprop(
        ? string $propName = null,
        ? int    $currPropIx = null,
        ? bool   $inclParam = false
    ) : bool | array
    {
        if( empty( $this->xprop )) {
            foreach( $this->propIx as $propName2 => $v ) {
                if( StringFactory::isXprefixed( $propName2 )) {
                    unset( $this->propIx[$propName2] );
                }
            }
            return false;
        }
        $propName = ( $propName ) ? strtoupper( $propName ) : self::X_PROP;
        if( $propName !== self::X_PROP ) {
            if( ! isset( $this->xprop[$propName] )) {
                return false;
            }
            return $inclParam
                ? [ $propName, clone $this->xprop[$propName], ]
                : [ $propName, $this->xprop[$propName]->value, ];
        }
        //  $propName == self::X_PROP i.e. any
        if( null === $currPropIx ) {
            $currPropIx = ( isset( $this->propIx[$propName] ))
                ? $this->propIx[$propName] + 2
                : 1;
        }
        $this->propIx[$propName] = --$currPropIx;
        $xpropNo = 0;
        foreach( $this->xprop as $xpropName2 => $xpropValue ) {
            if( $currPropIx === $xpropNo ) {
                return $inclParam
                    ? [ $xpropName2, clone $this->xprop[$xpropName2], ]
                    : [ $xpropName2, $this->xprop[$xpropName2]->value, ];
            }
            $xpropNo++;
        } // end foreach
        unset( $this->propIx[$propName] );
        return false; // not found
    }

    /**
     * Return array, all calendar component X-properties
     *
     * @param null|bool   $inclParam
     * @return array   [ *( xPropName, Pc/value ) ]
     * @since 2.41.51 2022-08-09
     */
    public function getAllXprop( ? bool $inclParam = false ) : array
    {
        if( empty( $this->xprop )) {
            return [];
        }
        $output = [];
        foreach( $this->xprop as $xPropName => $xPropValue ) {
            $output[] =  [
                $xPropName,
                ( $inclParam ? clone $this->xprop[$xPropName] : $this->xprop[$xPropName]->value )
            ];
        } // end foreach
        return $output;
    }
    /**
     * Return bool true if spec xPropName or any set (also empty)
     *
     * @param null|string $xPropName
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isXpropSet( ? string $xPropName = null ) : bool
    {
        return empty( $xPropName ) ? ( ! empty( $this->xprop )) : ! empty( $this->xprop[$xPropName] );
    }

    /**
     * Set calendar property x-prop
     *
     * @param string        $xPropName
     * @param null|int|float|string|Pc  $value
     * @param null|array $params     optional
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setXprop( string $xPropName, null|int|float|string|Pc $value = null, ? array $params = [] ) : static
    {
        static $MSG = 'Invalid X-property name : \'%s\'';
        if( empty( $xPropName ) || ! StringFactory::isXprefixed( $xPropName )) {
            throw new InvalidArgumentException( sprintf( $MSG, $xPropName ));
        }
        $xPropName = strtoupper( $xPropName );
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( null === $value->value ) {
            $this->assertEmptyValue( $value->value, $xPropName );
            $value->setEmpty();
        }
        if( ! $value->hasParamKey( self::VALUE ) ||
            $value->hasParamValue( self::TEXT )) {
            $value->value = Util::assertString( $value->value, $xPropName );
            $value->value = StringFactory::trimTrailNL( $value->value );
        }
        if( ! is_array( $this->xprop )) {
            $this->xprop = [];
        }
        $this->xprop[$xPropName] = $value;
        return $this;
    }
}
