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

use Kigkonsult\Icalcreator\IcalBase;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\ParameterFactory;

use function array_slice;
use function is_int;
use function key;
use function ksort;

/**
 * Vcalendar/CalendarComponent multi-properties help methods
 */
trait MvalTrait
{
    /**
     * Recount property propIx, used at consecutive getProperty calls
     *
     * @param string[] $propArr   component (multi-)property
     * @param int   $propIx getter counter
     * @return bool
     * @since  2.41.39 - 2022-04-09
     */
    protected static function recountMvalPropix( array $propArr, int & $propIx ) : bool
    {
        if( empty( $propArr )) {
            return false;
        }
        $last = key( array_slice( $propArr, -1, 1, true ));
        while( ! isset( $propArr[$propIx] ) && ( $last > $propIx )) {
            $propIx++;
        }
        return true;
    }

    /**
     * Delete calendar component multiProp property[ix]
     *
     * @param array $multiProp component (multi-)property
     * @param string    $propName
     * @param IcalBase  $instance
     * @param null|int  $propDelIx specific property in case of multiply occurrence
     * @return bool   true on success
     * @since  2.27.1 - 2018-12-15
     */
    protected static function deletePropertyM(
        array & $multiProp,
        string $propName,
        IcalBase $instance,
        ? int $propDelIx = null
    ) : bool
    {
        if( empty( $multiProp )) {
            unset( $instance->propDelIx[$propName] );
            return false;
        }
        $propDelIx = self::getIndex( $instance->propDelIx, $propName, $propDelIx );
        if( isset( $multiProp[$propDelIx] )) {
            unset( $multiProp[$propDelIx] );
        }
        if( empty( $multiProp )) {
            $multiProp = [];
            unset( $instance->propDelIx[$propName] );
            return false;
        }
        return true;
    }

    /**
     * Return propName index
     *
     * @param array $indexArr
     * @param string $propName
     * @param int|null $index
     * @return int
     * @since  2.27.1 - 2018-12-15
     */
    protected static function getIndex(
        array & $indexArr,
        string $propName,
        ? int $index = null
    ) : int
    {
        if( null === $index ) {
            $index = ( isset( $indexArr[$propName] )) ? $indexArr[$propName] + 2 : 1;
        }
        --$index;
        $indexArr[$propName] = $index;
        return $index;
    }

    /**
     * Return array, all calendar component multiProp properties
     *
     * @param null|array $multiProp component (multi-)property
     * @param bool     $inclParam
     * @return array
     * @since  2.41.51 - 2022-08-09
     */
    protected static function getMvalProperties(
        ? array $multiProp = [],
        ? bool $inclParam = false
    ) : array
    {
        if( empty( $multiProp )) {
            return [];
        }
        if( $inclParam ) {
            return $multiProp;
        }
        $output = [];
        foreach( $multiProp as $property ) {
            $output[] = $property->value;
        }
        return $output;
    }

    /**
     * Get calendar component multpProp property
     *
     * @param array $multiProp component (multi-)property
     * @param string   $propName
     * @param IcalBase $instance
     * @param null|int $propIx    specific property in case of multiply occurrence
     * @param bool     $inclParam
     * @return bool|string|array|Pc
     * @since  2.41.36 - 2022-04-09
     */
    protected static function getMvalProperty(
        array $multiProp,
        string $propName,
        IcalBase $instance,
        ? int $propIx = null,
        ? bool $inclParam = false
    ) : bool | string | array | Pc
    {
        if( empty( $multiProp )) {
            unset( $instance->propIx[$propName] );
            return false;
        }
        $propIx = self::getIndex( $instance->propIx, $propName, $propIx );
        if( ! self::recountMvalPropix( $multiProp, $propIx )) {
            unset( $instance->propIx[$propName] );
            return false;
        }
        $instance->propIx[$propName] = $propIx;
        if( ! isset( $multiProp[$propIx] )) {
            unset( $instance->propIx[$propName] );
            return false;
        }
        return ( $inclParam )
            ? clone $multiProp[$propIx]
            : $multiProp[$propIx]->value;
    }

    /**
     * Return bool true if any multi property value is not empty
     *
     * @param null|Pc[] $valueArr
     * @return bool
     * @since 2.41.36 2022-04-09
     */
    protected static function isMvalSet( ? array $valueArr = [] ) : bool
    {
        if( empty( $valueArr )) {
            return false;
        }
        foreach( $valueArr as $value ) {
            if( ! empty( $value->value )) {
                return true;
            }
        }
        return false;

    }

    /**
     * Marshall input for multi-value properties
     *
     * @param mixed            $value
     * @param null|int|array $params
     * @param null|int         $index
     * @return Pc
     * @since 2.41.36 2022-04-09
     */
    protected static function marshallInputMval( mixed $value, null|int|array $params, ? int & $index) : Pc
    {
        if( $value instanceof Pc ) {
            $value = clone $value;
            if( is_int( $params )) {
                $index = $params;
            }
        }
        else {
            $value = Pc::factory( $value, ParameterFactory::setParams( $params ));
        }
        return $value;
    }

    /**
     * Check index and set (an indexed) content in a multiple value array
     *
     * @param null|array $valueArr
     * @param Pc            $value
     * @param null|int      $index
     * @return void
     * @since 2.41.36 2022-04-09
     */
    protected static function setMval(
        ? array & $valueArr,
        Pc $value,
        ? int $index = null
    ) : void
    {
        if( empty( $valueArr )) {
            $valueArr = [];
        }
        if( null === $index ) { // i.e. next
            $valueArr[] = $value;
            return;
        }
        --$index;
        if( isset( $valueArr[$index] )) { // replace
            $valueArr[$index] = $value;
            return;
        }
        $valueArr[$index] = $value;
        ksort( $valueArr ); // order
    }
}
