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
namespace Kigkonsult\Icalcreator\Formatter\Property;

use Kigkonsult\Icalcreator\Pc;

use function in_array;

/**
 * Format CATEGORIES, COMMENT, CONFERENCE, CONTACT, DESCRIPTION, LOCATION,
 *        NAME, RELATED_TO, RESOURCES,
 *        TZID_ALIAS_OF, TZNAME
 * Format STYLED_DESCRIPTION, STRUCTURED_DATA,
 * Format ATTACH, IMAGE
 *
 * 15
 * @since 2.41.55 - 2022-08-12
 */
final class MultiProps extends PropertyBase
{
    /**
     * @param string $propName
     * @param bool|Pc[] $values
     * @param null|bool $allowEmpty
     * @param null|bool|string $lang
     * @return string
     */
    public static function format(
        string $propName,
        bool|array $values,
        ? bool $allowEmpty = true,
        null|bool|string $lang = false
    ) : string
    {
        static $ATTCONFIMG = [ self::ATTACH, self::CONFERENCE, self::IMAGE ]; // URI
        if( empty( $values )) {
            return self::$SP0;
        }
        [ $specKeys, $lang ] = self::getSpeckeys1Lang1( $propName, $lang );
        $output = self::$SP0;
        foreach( $values as $pc ) {
            if( ! empty( $pc->value )) {
                [ $specKeys2, $lang2 ] = self::getSpeckeys2Lang2(
                    $propName,
                    $pc->getParams( self::VALUE ),
                    $specKeys,
                    $lang
                );
                $output .= self::createElement(
                    $propName,
                    self::createParams( $pc->params, $specKeys2, $lang2 ),
                    ( in_array( $propName, $ATTCONFIMG, true ) ? $pc->value : self::strrep( $pc->value ))
                );
            } // end if
            elseif( $allowEmpty ) {
                $output .= self::createElement( $propName );
            }
        } // end foreach
        return $output;
    }

    /**
     * Init specKeys and lang
     * @param string $propName
     * @param null|bool|string $lang
     * @return array
     */
    private static function getSpeckeys1Lang1( string $propName, null|bool|string $lang ) : array
    {
        static $langProps     = [ self::CATEGORIES, self::TZNAME ];
        static $langkey       = [ self::LANGUAGE ];
        static $altLangProps  = [
            self::COMMENT,
            self::CONTACT,
            self::DESCRIPTION,
            self::LOCATION,
            self::NAME,
            self::RESOURCES
        ];
        static $noLangProps   = [ self::ATTACH, self::RELATED_TO, self::TZID_ALIAS_OF ];
        static $CONFPKEYS     = [ self::FEATURE, self::LABEL, self::LANGUAGE ];
        static $IMAGEPKEYS    = [ self::ALTREP, self::DISPLAY ];
        static $STYDESCR1     = [ self::ALTREP, self::LANGUAGE, self::FMTTYPE, self::DERIVED ];
        switch( true ) {
            case ( self::CONFERENCE === $propName ) :
                $specKeys = $CONFPKEYS;
                break;
            case ( self::IMAGE === $propName ) :
                $specKeys = $IMAGEPKEYS;
                $lang = null;
                break;
            case ( self::STYLED_DESCRIPTION === $propName ) :
                $specKeys = $STYDESCR1;
                break;
            case in_array( $propName, $langProps, true ) :
                $specKeys = $langkey;
                break;
            case in_array( $propName, $altLangProps, true ) :
                $specKeys = self::$ALTRPLANGARR;
                break;
            case in_array( $propName, $noLangProps, true ) :
                $specKeys = [];
                $lang = null;
                break;
            default :
                $specKeys = [];
        } // end switch
        return [ $specKeys, $lang ];
    }

    /**
     * Finetune specKeys and lang
     *
     * @param string $propName
     * @param null|string $paramValue
     * @param array $specKeys
     * @param null|bool|string $lang
     * @return array
     */
    private static function getSpeckeys2Lang2(
        string $propName,
        ? string $paramValue,
        array $specKeys,
        null|bool|string $lang
    ) : array
    {
        static $STRDTAvalues  = [ self::TEXT, self::BINARY ];
        static $STRDTATXTBIN  = [ self::FMTTYPE, self::SCHEMA ];
        static $STYDESCR2     = [ self::ALTREP, self::FMTTYPE, self::DERIVED ];
        switch( true ) {
            case (( self::STYLED_DESCRIPTION === $propName ) &&
                ( self::TEXT !== $paramValue )) :
                $specKeys2 = $STYDESCR2;
                $lang2     = null;
                break;
            case (( self::STRUCTURED_DATA === $propName ) &&
                in_array( $paramValue, $STRDTAvalues, true )) :
                $specKeys2 = $STRDTATXTBIN;
                $lang2     = null;
                break;
            default :
                $specKeys2 = $specKeys;
                $lang2     = $lang;
        } // end switch
        return [ $specKeys2, $lang2 ];
    }
}
