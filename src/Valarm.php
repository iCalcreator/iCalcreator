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

use function sprintf;
use function strtoupper;

/**
 * iCalcreator VALARM component class
 *
 * @since 2.41.29 2022-02-24
 */
final class Valarm extends CalendarComponent
{
    use Traits\UIDrfc7986trait;
    use Traits\RELATED_TOtrait;
    use Traits\ACTIONtrait;
    use Traits\ATTACHtrait;
    use Traits\ATTENDEEtrait; // Valarm::emailprop
    use Traits\DESCRIPTIONtrait;
    use Traits\DURATIONtrait;
    use Traits\PROXIMITYrfc9074trait;
    use Traits\REPEATtrait;
    use Traits\STYLED_DESCRIPTIONrfc9073trait;
    use Traits\SUMMARYtrait;
    use Traits\TRIGGERtrait;
    use Traits\ACKNOWLEDGEDrfc9074trait;

    /**
     * @var string
     */
    protected static string $compSgn = 'a';

    /**
     * Destructor
     *
     * @since 2.41.3 2022-01-17
     */
    public function __destruct()
    {
        unset(
            $this->compType,
            $this->xprop,
            $this->components,
            $this->unparsed,
            $this->config,
            $this->propIx,
            $this->propDelIx
        );
        unset(
            $this->cno,
            $this->srtk
        );
        unset(
            $this->uid,
            $this->relatedto,
            $this->action,
            $this->attach,
            $this->attendee,
            $this->description,
            $this->duration,
            $this->proximity,
            $this->repeat,
            $this->styleddescription,
            $this->summary,
            $this->trigger,
            $this->acknowledged
        );
    }

    /**
     * Return Vlocation object instance
     */
    use Traits\NewVlocationTrait;

    /**
     * Return formatted output for calendar component VALARM object instance
     *
     * @return string
     * @throws Exception  (on Duration/Trigger err)
     * @since 2.41.29 2022-02-24
     */
    public function createComponent() : string
    {
        $compType    = strtoupper( $this->getCompType());
        return
            sprintf( self::$FMTBEGIN, $compType ) .
            $this->createUid() .
            $this->createRelatedto().
            $this->createAction() .
            $this->createAttach() .
            $this->createAttendee() .
            $this->createDescription() .
            $this->createStyleddescription() .
            $this->createProximity() .
            $this->createDuration() .
            $this->createRepeat() .
            $this->createSummary() .
            $this->createTrigger() .
            $this->createAcknowledged() .
            $this->createXprop() .
            $this->createSubComponent() .
            sprintf( self::$FMTEND, $compType );
    }
}
