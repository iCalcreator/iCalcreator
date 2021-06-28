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
 * CONTACT property functions
 *
 * @throws InvalidArgumentException
 * @since 2.29.14 2019-09-03
 */
trait CONTACTtrait
{
    /**
     * @var array component property CONTACT value
     */
    protected $contact = null;

    /**
     * Return formatted output for calendar component property contact
     *
     * @return string
     */
    public function createContact() : string
    {
        if( empty( $this->contact )) {
            return Util::$SP0;
        }
        $output = Util::$SP0;
        $lang   = $this->getConfig( self::LANGUAGE );
        foreach( $this->contact as $cx => $contact ) {
            if( ! empty( $contact[Util::$LCvalue] )) {
                $output .= StringFactory::createElement(
                    self::CONTACT,
                    ParameterFactory::createParams(
                        $contact[Util::$LCparams],
                        self::$ALTRPLANGARR,
                        $lang
                    ),
                    StringFactory::strrep( $contact[Util::$LCvalue] )
                );
            }
            elseif( $this->getConfig( self::ALLOWEMPTY )) {
                $output .= StringFactory::createElement( self::CONTACT );
            }
        } // end foreach
        return $output;
    }

    /**
     * Delete calendar component property contact
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteContact( $propDelIx = null ) : bool
    {
        if( empty( $this->contact )) {
            unset( $this->propDelIx[self::CONTACT] );
            return false;
        }
        return  self::deletePropertyM(
            $this->contact,
            self::CONTACT,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property contact
     *
     * @param null|int    $propIx specific property in case of multiply occurrence
     * @param bool   $inclParam
     * @return bool|array
     * @since  2.27.1 - 2018-12-12
     */
    public function getContact( $propIx = null, $inclParam = false )
    {
        if( empty( $this->contact )) {
            unset( $this->propIx[self::CONTACT] );
            return false;
        }
        return  self::getPropertyM(
            $this->contact,
            self::CONTACT,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Set calendar component property contact
     *
     * @param string  $value
     * @param array   $params
     * @param integer $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.29.14 2019-09-03
     */
    public function setContact( $value = null, $params = [], $index = null ) : self
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::CONTACT );
            $value  = Util::$SP0;
            $params = [];
        }
        Util::assertString( $value, self::CONTACT );
         self::setMval(
            $this->contact,
            StringFactory::trimTrailNL( $value ),
            $params,
            null,
            $index
        );
        return $this;
    }
}
