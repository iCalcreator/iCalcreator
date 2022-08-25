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

use Kigkonsult\Icalcreator\Util\StringFactory;

use function array_keys;
use function in_array;
use function mb_strtolower;

trait SubCompsGetTrait
{
    /**
     * Return (compType) subComp detail contents and (opt) type value
     *
     * @param string $compType
     * @param string $detailPropName
     * @param string $xParamRefId
     * @param string $xPropTypeName
     * @param string|string[] $xParamTypeRef
     * @return array[]
     * @since 2.41.34 - 2022-03-28
     */
    protected function getSubCompsDetailType(
        string $compType,
        string $detailPropName,
        string $xParamRefId,
        string $xPropTypeName,
        string|array $xParamTypeRef
    ) : array
    {
        $vSubValues = $lcArr = [];
        $method1    = StringFactory::getGetMethodName( $detailPropName );
        $method2    = StringFactory::getGetMethodName( $xPropTypeName );
        foreach( array_keys( $this->components ) as $cix ) {
            if( $compType !== $this->components[$cix]->getCompType()) {
                continue;
            }
            if(( false === ( $content = $this->components[$cix]->{$method1}( true ))) ||
                empty( $content->value )) {
                continue;
            }
            $value   = $content->value;
            $lcValue = mb_strtolower( $value );
            if( in_array( $lcValue, $lcArr, true )) { // no dupl.
                continue;
            }
            $vSubValues[$value] = $content->params;
            $vSubValues[$value][$xParamRefId]  = $this->components[$cix]->getUID();
            if( false !== ( $type = $this->components[$cix]->{$method2}())) {
                foreach((array) $xParamTypeRef as $pKey ) {
                    $vSubValues[$value][$pKey] = $type;
                }
            }
            $lcArr[$value] = $lcValue;
        } // end foreach
        return [ $vSubValues, $lcArr ];
    }

    /**
     * Update properties from subCompData, create new ones for all but found
     *
     * @param string   $propName
     * @param bool     $multi       if prop may occur multiple times in component
     * @param array $subCompsData
     * @param array $lcArr
     * @return void
     * @since 2.41.17 - 2022-02-18
     */
    protected function comPropUpdFromSub( string $propName, bool $multi, array $subCompsData, array $lcArr ) : void
    {
        // get all component property values
        $method       = StringFactory::getGetMethodName( $propName );
        $compPropData = [];
        while( false !== ( $content = $this->{$method}( null, true ))) {
            $value = $content->value;
            if( ! empty( $value )) {
                $compPropData[$value] = $content->params;
            }
            if( ! $multi ) {
                break; // the only one is found
            }
        } // end while
        // check for hits (ignore case), if hit then update this::hit and remove current $subCompsData
        $update = false;
        foreach( array_keys( $compPropData ) as $cValue ) {
            if( in_array( mb_strtolower( $cValue ), $lcArr, true )) {
                $update = true;
                foreach( $subCompsData[$cValue] as $vParamKey => $vParamValue ) {
                    if( ! isset( $compPropData[$cValue][$vParamKey] )) {
                        $compPropData[$cValue][$vParamKey] = $vParamValue;
                    }
                } // end foreach
                if(  $multi ) {
                    unset( $subCompsData[$cValue] );
                }
                else {
                    $subCompsData = [ $cValue => $compPropData[$cValue] ]; // upd the only one
                    $compPropData = [];
                    break;
                }
            } // end if
        } // end foreach
        if( $update ) {
            // remove all
            $method = StringFactory::getDeleteMethodName( $propName );
            while( false !== $this->{$method}()) {}
            // force write back
            $subCompsData = array_merge( $subCompsData, $compPropData );
        }
        // create new properties from subdata
        if( ! empty( $subCompsData )) {
            $method = StringFactory::getSetMethodName( $propName );
            foreach( $subCompsData as $value => $params ) {
                $this->{$method}( $value, $params );
            }
        } // end if
    }
}
