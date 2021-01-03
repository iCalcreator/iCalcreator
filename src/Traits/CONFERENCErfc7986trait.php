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

use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

/**
 * CONFERENCE property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.21 2019-06-17
 */
trait CONFERENCErfc7986trait
{
    /**
     * @var array component property CONFERENCE value
     */
    protected $conference = null;

    /**
     * Return formatted output for calendar component property conference
     *
     * @return string
     */
    public function createConference()
    {
        if( empty( $this->conference )) {
            return null;
        }
        $output = null;
        $lang   = $this->getConfig( self::LANGUAGE );
        foreach( $this->conference as $aix => $conferencePart ) {
            if( ! empty( $conferencePart[Util::$LCvalue] )) {
                $output .= StringFactory::createElement(
                    self::CONFERENCE,
                    ParameterFactory::createParams(
                        $conferencePart[Util::$LCparams],
                        [ self::FEATURE, self::LABEL, self::LANGUAGE ],
                        $lang
                    ),
                    $conferencePart[Util::$LCvalue]
                );
            }
            elseif( $this->getConfig( self::ALLOWEMPTY )) {
                $output .= StringFactory::createElement( self::CONFERENCE );
            }
        } // end foreach
        return $output;
    }

    /**
     * Delete calendar component property conference
     *
     * @param int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     */
    public function deleteConference( $propDelIx = null )
    {
        if( empty( $this->conference )) {
            unset( $this->propDelIx[self::CONFERENCE] );
            return false;
        }
        return $this->deletePropertyM(
            $this->conference,
            self::CONFERENCE,
            $propDelIx
        );
    }

    /**
     * Get calendar component property conference
     *
     * @param int    $propIx specific property in case of multiply occurrence
     * @param bool   $inclParam
     * @return bool|array
     */
    public function getConference( $propIx = null, $inclParam = false )
    {
        if( empty( $this->conference )) {
            unset( $this->propIx[self::CONFERENCE] );
            return false;
        }
        return $this->getPropertyM(
            $this->conference,
            self::CONFERENCE,
            $propIx,
            $inclParam
        );
    }

    /**
     * Set calendar component property conference
     *
     * @param string  $value
     * @param array   $params
     * @param integer $index
     * @return static
     * @throws InvalidArgumentException
     * @todo fix featureparam - AUDIO, CHAT, FEED, MODERATOR, PHONE, SCREEN, VIDEO, x-name, iana-token
     * @todo fix labelparam   - LABEL
     */
    public function setConference( $value = null, $params = [], $index = null )
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::CONFERENCE );
            $value  = Util::$SP0;
            $params = [];
        }
        else {
            $params = array_change_key_case( $params, CASE_UPPER );
            if( ! isset( $param[self::VALUE] ) ) { // required
                $params[self::VALUE] = self::URI;
            }
        }
        $this->setMval( $this->conference, $value, $params, null, $index );
        return $this;
    }
}
