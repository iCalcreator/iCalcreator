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

use Exception;
use Kigkonsult\Icalcreator\Formatter\Vtimzone as Formatter;

use function array_keys;
use function array_slice;
use function array_unshift;
use function key;
use function ksort;

/**
 * iCalcreator VTIMEZONE component class
 *
 * @since  2.41.56 - 2022-08-13
 */
final class Vtimezone extends CalendarComponent
{
    use Traits\TZIDtrait;
    use Traits\LAST_MODIFIEDtrait;
    use Traits\TZURLtrait;
    use Traits\TZUNTILrfc7808trait;
    use Traits\TZID_ALIAS_OFrfc7808trait;

    /**
     * @var string
     */
    protected static string $compSgn = 'tz';

    /**
     * Return Vtimezone object instance
     *
     * @param null|array $config
     * @param null|string $tzid
     * @return Vtimezone
     * @since  2.41.53 - 2022-08-08
     */
    public static function factory( ? array $config = [], ? string $tzid = null ) : Vtimezone
    {
        $instance = new Vtimezone( $config );
        if( null !== $tzid ) {
            $instance->setTzid( $tzid );
        }
        return $instance;
    }

    /**
     * Destructor
     *
     * @since  2.41.56 - 2022-08-12
     */
    public function __destruct()
    {
        if( ! empty( $this->components )) {
            foreach( array_keys( $this->components ) as $cix ) {
                $this->components[$cix]->__destruct();
            }
        }
        unset(
            $this->compType,
            $this->xprop,
            $this->components,
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
            $this->tzid,
            $this->lastmodified,
            $this->tzurl,
            $this->tzuntil,
            $this->tzidAliasOf
        );
    }

    /**
     * Return formatted output for calendar component VTIMEZONE object instance
     *
     * @return string
     * @throws Exception  (on Rdate err)
     * @since  2.41.55 - 2022-08-13
     */
    public function createComponent() : string
    {
        return Formatter::format( $this );
    }

    /**
     * Sort Vtimezones subComponents
     *
     * sort : standard, daylight, in dtstart order
     * @since  2.29.1 - 2019-06-28
     */
    public function sortVtimezonesSubComponents() : void
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
            if( self::STANDARD === $this->components[$cix]->getCompType()) {
                while( isset( $stdArr[$key] )) {
                    ++$key;
                }
                $stdArr[$key] = $this->components[$cix];
            }
            elseif( self::DAYLIGHT === $this->components[$cix]->getCompType()) {
                while( isset( $dlArr[$key] )) {
                    ++$key;
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
    public function newStandard() : Standard
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
    public function newDaylight() : Daylight
    {
        $ix = ( empty( $this->components ))
            ? 0
            : (int) key( array_slice( $this->components, -1, 1, true )) + 1;
        $this->components[$ix] = new Daylight( $this->getConfig());
        return $this->components[$ix];
    }
}
