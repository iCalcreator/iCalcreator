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

namespace Kigkonsult\Icalcreator;

use Exception;

use function array_keys;
use function sprintf;
use function strtoupper;

/**
 * iCalcreator VTIMEZONE component class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.9 2019-08-05
 */
final class Vtimezone extends CalendarComponent
{
    use Traits\COMMENTtrait,
        Traits\DTSTARTtrait,
        Traits\LAST_MODIFIEDtrait,
        Traits\RDATEtrait,
        Traits\RRULEtrait,
        Traits\TZIDtrait,
        Traits\TZNAMEtrait,
        Traits\TZOFFSETFROMtrait,
        Traits\TZOFFSETTOtrait,
        Traits\TZURLtrait;

    /**
     * @var string
     */
    protected static $compSgn = 'tz';

    /**
     * Destructor
     *
     * @since  2.26 - 2018-11-10
     */
    public function __destruct()
    {
        if( ! empty( $this->components )) {
            foreach( $this->components as $cix => $component ) {
                $this->components[$cix]->__destruct();
            }
        }
        unset(
            $this->compType,
            $this->xprop,
            $this->components,
            $this->unparsed,
            $this->config,
            $this->propIx,
            $this->compix,
            $this->propDelIx
        );
        unset(
            $this->cno,
            $this->srtk
        );
        unset(
            $this->comment,
            $this->dtstart,
            $this->lastmodified,
            $this->rdate,
            $this->rrule,
            $this->tzid,
            $this->tzname,
            $this->tzoffsetfrom,
            $this->tzoffsetto,
            $this->tzurl,
            $this->timezonetype
        );
    }

    /**
     * Return formatted output for calendar component VTIMEZONE object instance
     *
     * @return string
     * @throws Exception  (on Rdate err)
     * @since 2.29.9 2019-08-05
     */
    public function createComponent()
    {
        $compType    = strtoupper( $this->getCompType());
        $component   = sprintf( self::$FMTBEGIN, $compType );
        $component  .= $this->createTzid();
        $component  .= $this->createLastmodified();
        $component  .= $this->createTzurl();
        $component  .= $this->createDtstart();
        $component  .= $this->createTzoffsetfrom();
        $component  .= $this->createTzoffsetto();
        $component  .= $this->createComment();
        $component  .= $this->createRdate();
        $component  .= $this->createRrule();
        $component  .= $this->createTzname();
        $component  .= $this->createXprop();
        $component  .= $this->createSubComponent();
        return $component . sprintf( self::$FMTEND, $compType );
    }

    /**
     * Return formatted output for subcomponents
     *
     * @return string
     * @since  2.27.2 - 2018-12-21
     * @throws Exception  (on Valarm/Standard/Daylight) err)
     */
    public function createSubComponent()
    {
        if( self::VTIMEZONE == $this->getCompType()) {
            $this->sortVtimezonesSubComponents();
        }
        return parent::createSubComponent();
    }

    /**
     * Sort Vtimezones subComponents
     *
     * sort : standard, daylight, in dtstart order
     * @since  2.29.1 - 2019-06-28
     */
    private function sortVtimezonesSubComponents()
    {
        if( empty( $this->components )) {
            return;
        }
        $stdArr = $dlArr = [];
        foreach( array_keys( $this->components ) as $cix ) {
            if( empty( $this->components[$cix] )) {
                continue;
            }
            $key = $this->components[$cix]->getDtstart();
            if( empty( $key )) {
                $key = $cix * 10;
            }
            else {
                $key = $key->getTimestamp();
            }
            if( self::STANDARD == $this->components[$cix]->getCompType()) {
                while( isset( $stdArr[$key] )) {
                    $key += 1;
                }
                $stdArr[$key] = $this->components[$cix];
            }
            elseif( self::DAYLIGHT == $this->components[$cix]->getCompType()) {
                while( isset( $dlArr[$key] )) {
                    $key += 1;
                }
                $dlArr[$key] = $this->components[$cix];
            }
        } // end foreach
        $this->components = [];
        ksort( $stdArr, SORT_NUMERIC );
        foreach( $stdArr as $std ) {
            $this->components[] = $std;
        }
        unset( $stdArr );
        ksort( $dlArr, SORT_NUMERIC );
        foreach( $dlArr as $dl ) {
            $this->components[] = $dl;
        }
        unset( $dlArr );
    }

    /**
     * Return timezone standard object instance
     *
     * @return Standard
     * @since  2.27.2 - 2018-12-21
     */
    public function newStandard()
    {
        array_unshift( $this->components, new Standard( $this->getConfig()));
        return $this->components[0];
    }

    /**
     * Return timezone daylight object instance
     *
     * @return Daylight
     * @since  2.27.2 - 2018-12-21
     */
    public function newDaylight()
    {
        $ix = ( empty( $this->components ))
            ? 0
            : key( array_slice( $this->components, -1, 1, true )) + 1;
        $this->components[$ix] = new Daylight( $this->getConfig());
        return $this->components[$ix];
    }
}
