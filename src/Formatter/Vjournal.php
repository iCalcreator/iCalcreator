<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2023 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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
use Kigkonsult\Icalcreator\Vjournal as Source;

use function strtoupper;
use function sprintf;

/**
 * @since 2.41.68 2022-10-03
 */
final class Vjournal extends FormatBase
{
    /**
     * @param CalendarComponent|Source $source
     * @return string
     * @throws Exception
     */
    public static function format( CalendarComponent|Source $source ) : string
    {
        $compType   = strtoupper( $source->getCompType());
        $allowEmpty = $source->getConfig( self::ALLOWEMPTY );
        $lang       = $source->getConfig( self::LANGUAGE );
        $output     =
            sprintf( self::$FMTBEGIN, $compType ) .
            Property\Property::format(
                self::UID,
                $source->getUid( true ),
                $allowEmpty
            ) .
            Property\DtxProperty::format(
                self::DTSTAMP,
                $source->getDtstamp( true ),
                $allowEmpty
            ) .
            Property\MultiProps::format(
                self::ATTACH,
                $source->getAllAttach( true ),
                $allowEmpty
            ) .
            Property\Attendee::format(
                self::ATTENDEE,
                $source->getAllAttendee( true ),
                $allowEmpty
            ) .
            Property\MultiProps::format(
                self::CATEGORIES,
                $source->getAllCategories( true ),
                $allowEmpty,
                $lang
            ) .
            Property\Property::format(
                self::KLASS,
                $source->getClass( true ),
                $allowEmpty
            ) .
            Property\Property::format(
                self::COLOR,
                $source->getColor( true ),
                $allowEmpty
            ) .
            Property\MultiProps::format(
                self::COMMENT,
                $source->getAllComment( true ),
                $allowEmpty,
                $lang
            ) .
            Property\MultiProps::format(
                self::CONTACT,
                $source->getAllContact( true ),
                $allowEmpty,
                $lang
            ) .
            Property\DtxProperty::format(
                self::CREATED,
                $source->getCreated( true ),
                $allowEmpty
            ) .
            Property\MultiProps::format(
                self::DESCRIPTION,
                $source->getAllDescription( true ),
                $allowEmpty,
                $lang
            ) .
            Property\MultiProps::format(
                self::STYLED_DESCRIPTION,
                $source->getAllStyleddescription( true ),
                $allowEmpty,
                $lang
            ) .
            Property\MultiProps::format(
                self::STRUCTURED_DATA,
                $source->getAllStructureddata( true ),
                $allowEmpty,
                $lang
            );
        $dtStart = $source->getDtstart( true );
        $output .= Property\Dt1Property::format(
                self::DTSTART,
                $dtStart,
                $allowEmpty,
                Property\Dt1Property::getIsDate( $dtStart ),
                Property\Dt1Property::getIsLocalTime( $dtStart )
            ) .
            Property\Exdate::format(
                self::EXDATE,
                $source->getAllExdate( true ),
                $allowEmpty
            ) .
            Property\Recur::format(
                self::EXRULE,
                $source->getExrule( true ),
                $allowEmpty
            ) .
            Property\MultiProps::format(
                self::IMAGE,
                $source->getAllImage( true ),
                $allowEmpty
            ) .
            Property\DtxProperty::format(
                self::LAST_MODIFIED,
                $source->getLastmodified( true ),
                $allowEmpty
            ) .
            Property\SingleProps::format(
                self::ORGANIZER,
                $source->getOrganizer( true ),
                $allowEmpty,
                $lang
            ) .
            Property\Rdate::format(
                self::RDATE,
                $source->getAllRdate( true ),
                $allowEmpty,
                $source->getCompType()
            ) .
            Property\Requeststatus::format(
                self::REQUEST_STATUS,
                $source->getAllRequeststatus( true ),
                $allowEmpty,
                $lang
            );
        $reCurrId = $source->getRecurrenceid( true );
        return $output .
            Property\Dt1Property::format(
                self::RECURRENCE_ID,
                $reCurrId,
                $allowEmpty,
                Property\Dt1Property::getIsDate( $dtStart, $reCurrId ),
                Property\Dt1Property::getIsLocalTime( $reCurrId )
            ) .
            Property\MultiProps::format(
                self::RELATED_TO,
                $source->getAllRelatedto( true ),
                $allowEmpty,
                $lang
            ) .
            Property\Recur::format(
                self::RRULE,
                $source->getRrule( true ),
                $allowEmpty
            ) .
            Property\IntProperty::format(
                self::SEQUENCE,
                $source->getSequence( true ),
                $allowEmpty
            ) .
            Property\Property::format(
                self::STATUS,
                $source->getStatus( true ),
                $allowEmpty
            ) .
            Property\SingleProps::format(
                self::SUMMARY,
                $source->getSummary( true ),
                $allowEmpty,
                $lang
            ) .
            Property\Xproperty::format(
                $source->getAllXprop( true ),
                $allowEmpty,
                $lang
            ) .
            self::formatSubComponents( $source ) .
            sprintf( self::$FMTEND, $compType );
    }
}
