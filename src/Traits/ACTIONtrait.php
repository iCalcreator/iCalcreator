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

use function strtoupper;

/**
 * ACTION property functions
 *
 * @since 2.29.14 2019-09-03
 */
trait ACTIONtrait
{
    /**
     * @var array component property ACTION value
     */
    protected $action = null;

    /**
     * Return formatted output for calendar component property action
     *
     * @return string
     */
    public function createAction() : string
    {
        if( empty( $this->action )) {
            return Util::$SP0;
        }
        if( empty( $this->action[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::ACTION )
                : Util::$SP0;
        }
        return StringFactory::createElement(
            self::ACTION,
            ParameterFactory::createParams( $this->action[Util::$LCparams] ),
            $this->action[Util::$LCvalue]
        );
    }

    /**
     * Delete calendar component property action
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteAction() : bool
    {
        $this->action = null;
        return true;
    }

    /**
     * Get calendar component property action
     *
     * @param null|bool   $inclParam
     * @return bool|array
     * @since  2.27.1 - 2018-12-13
     */
    public function getAction( $inclParam = false )
    {
        if( empty( $this->action )) {
            return false;
        }
        return ( $inclParam ) ? $this->action : $this->action[Util::$LCvalue];
    }

    /**
     * Set calendar component property action
     *
     * @param null|string $value "AUDIO" / "DISPLAY" / "EMAIL" / "PROCEDURE"  / iana-token / x-name ??
     * @param null|mixed  $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.29.14 2019-09-03
     */
    public function setAction( $value = null, $params = [] ) : self
    {
        static $STDVALUES = [
            self::AUDIO,
            self::DISPLAY,
            self::EMAIL,
            self::PROCEDURE  // deprecated in rfc5545
        ];
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::ACTION );
            $value  = Util::$SP0;
            $params = [];
        }
        elseif( Util::isPropInList( $value, $STDVALUES )) {
            $value = strtoupper( $value );
        }
        Util::assertString( $value, self::ACTION );
        $this->action = [
            Util::$LCvalue  => strtoupper( StringFactory::trimTrailNL((string) $value )),
            Util::$LCparams => ParameterFactory::setParams( $params ?? [] ),
        ];
        return $this;
    }
}
