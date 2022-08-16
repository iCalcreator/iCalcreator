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
namespace Kigkonsult\Icalcreator\Formatter;

use Exception;
use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Vcalendar;

abstract class FormatBase implements IcalInterface
{
    /**
     * @var string
     */
    protected static string $FMTBEGIN = "BEGIN:%s\r\n";

    /**
     * @var string
     */
    protected static string $FMTEND   = "END:%s\r\n";

    /**
     * @var string
     */
    protected static string$SP0       = '';

    /**
     * @param CalendarComponent $component
     * @return string
     * @throws Exception
     */
    protected static function formatComponent( CalendarComponent $component ) : string
    {
        switch( $component->getCompType()) {
            case self::AVAILABLE :     return Available::format( $component );
            case self::DAYLIGHT :      return DScomponent::format( $component );
            case self::PARTICIPANT :   return Participant::format( $component );
            case self::STANDARD :      return DScomponent::format( $component );
            case self::VALARM :        return Valarm::format( $component );
            case self::VAVAILABILITY : return Vavailability::format( $component );
            case self::VEVENT :        return Vevent::format( $component );
            case self::VFREEBUSY :     return Vfreebusy::format( $component );
            case self::VJOURNAL :      return Vjournal::format( $component );
            case self::VLOCATION :     return Vlocation::format( $component );
            case self::VRESOURCE :     return Vresource::format( $component );
            case self::VTIMEZONE :     return Vtimzone::format( $component );
            case self::VTODO :         return Vtodo::format( $component );
        } // end switch
        return self::$SP0;
    }

    /**
     * Return formatted output for subcomponents
     *
     * @param Vcalendar|CalendarComponent $component
     * @return string
     * @since 2.41.55 2022-08-13
     * @throws Exception  (on Valarm/Standard/Daylight) err)
     */
    protected static function formatSubComponents( Vcalendar|CalendarComponent $component ) : string
    {
        $config      = $component->getConfig();
        $output      = self::$SP0;
        foreach( $component->getComponents() as $subComp ) {
            $output .= self::formatComponent( $subComp->setConfig( $config, false, true ));
        }
        return $output;
    }
}
