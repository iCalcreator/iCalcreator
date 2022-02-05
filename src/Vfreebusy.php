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
 * iCalcreator VFREEBUSY component class
 *
 * @since 2.41.3 2022-01-17
 */
final class Vfreebusy extends V2component
{
    use Traits\ATTENDEEtrait;
    use Traits\Participants2AttendeesTrait;
    use Traits\COMMENTtrait;
    use Traits\CONTACTtrait;
    use Traits\DTENDtrait;
    use Traits\DTSTARTtrait;
    use Traits\DURATIONtrait;   // Deprecated in rfc5545
    use Traits\FREEBUSYtrait;
    use Traits\ORGANIZERtrait;
    use Traits\REQUEST_STATUStrait;
    use Traits\STYLED_DESCRIPTIONrfc9073trait;
    use Traits\UIDrfc7986trait;
    use Traits\URLtrait;

    /**
     * @var string
     */
    protected static string $compSgn = 'f';

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
            $this->compix,
            $this->propDelIx
        );
        unset(
            $this->cno,
            $this->srtk
        );
        unset(
            $this->attendee,
            $this->comment,
            $this->contact,
            $this->dtend,
            $this->dtstamp,
            $this->dtstart,
            $this->duration,
            $this->freebusy,
            $this->organizer,
            $this->requeststatus,
            $this->styleddescription,
            $this->uid,
            $this->url
        );
    }

    /**
     * Return formatted output for calendar component VFREEBUSY object instance
     *
     * @return string
     * @throws Exception  (on Duration/Freebusy err)
     * @since 2.41.3 2022-01-17
     */
    public function createComponent() : string
    {
        $compType    = strtoupper( $this->getCompType());
        $component   = sprintf( self::$FMTBEGIN, $compType );
        $component  .= $this->createUid();
        $component  .= $this->createDtstamp();
        $component  .= $this->createAttendee();
        $component  .= $this->createStyleddescription();
        $component  .= $this->createComment();
        $component  .= $this->createContact();
        $component  .= $this->createDtstart();
        $component  .= $this->createDtend();
        $component  .= $this->createDuration();
        $component  .= $this->createFreebusy();
        $component  .= $this->createOrganizer();
        $component  .= $this->createRequeststatus();
        $component  .= $this->createUrl();
        $component  .= $this->createXprop();
        return $component . sprintf( self::$FMTEND, $compType );
    }
}
