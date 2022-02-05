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
namespace Kigkonsult\Icalcreator;

use DateInterval;
use Exception;

/**
 * iCalcreator VEVENT/VTODO component base class
 *
 * @since  2.41.2 - 2022-01-15
 */
abstract class V3component extends V2component
{
    /**
     * Return Valarm object instance
     *
     * @param null|string $action property ACTION value
     * @param null|string|DateInterval $trigger property TRIGGER value
     *                                           only DateInterval or (string) DateInterval value, related start
     * @return Valarm
     * @throws Exception
     * @since  2.41.8 - 2022-01-20
     */
    public function newValarm( ? string $action = null, null|string|DateInterval $trigger = null ) : Valarm
    {
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = new Valarm( $this->getConfig());
        $this->components[$ix]->getUid();
        if( null !== $action ) {
            $this->components[$ix]->setAction( $action );
        }
        if( null !== $trigger ) {
            $this->components[$ix]->setTrigger( $trigger );
        }
        return $this->components[$ix];
    }
}
