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
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Vevent as Formatter;

use function array_keys;

/**
 * iCalcreator VEVENT component class
 *
 * @since  2.41.55 - 2022-08-13
 */
final class Vevent extends V3component
{
    use Traits\ATTACHtrait;
    use Traits\ATTENDEEtrait;
    use Traits\CATEGORIEStrait;
    use Traits\CLASStrait;
    use Traits\COLORrfc7986trait;
    use Traits\COMMENTtrait;
    use Traits\CONFERENCErfc7986trait;
    use Traits\CONTACTtrait;
    use Traits\CREATEDtrait;
    use Traits\DESCRIPTIONtrait;
    use Traits\DTENDtrait;
    use Traits\DTSTARTtrait;
    use Traits\DURATIONtrait;
    use Traits\EXDATEtrait;
    use Traits\EXRULEtrait;
    use Traits\GEOtrait;
    use Traits\IMAGErfc7986trait;
    use Traits\LAST_MODIFIEDtrait;
    use Traits\LOCATIONtrait;
    use Traits\ORGANIZERtrait;
    use Traits\PRIORITYtrait;
    use Traits\RDATEtrait;
    use Traits\RECURRENCE_IDtrait;
    use Traits\RELATED_TOtrait;
    use Traits\REQUEST_STATUStrait;
    use Traits\RESOURCEStrait;
    use Traits\RRULEtrait;
    use Traits\SEQUENCEtrait;
    use Traits\STATUStrait;
    use Traits\STRUCTURED_DATArfc9073trait;
    use Traits\STYLED_DESCRIPTIONrfc9073trait;
    use Traits\SUMMARYtrait;
    use Traits\TRANSPtrait;
    use Traits\UIDrfc7986trait;
    use Traits\URLtrait;

    /**
     * @var string
     */
    protected static string $compSgn = 'e';

    /**
     * Constructor
     *
     * @param null|array $config
     * @throws Exception
     * @since  2.41.53 - 2022-08-11
     */
    public function __construct( ? array $config = [] )
    {
        parent::__construct( $config );
        $this->setDtstamp();
        $this->setUid();
    }

    /**
     * Return Vevent object instance
     *
     * @param null|array $config
     * @param null|string|DateTimeInterface $dtstart
     * @param null|string|DateTimeInterface $dtend   one of dtend or duration
     * @param null|string|DateInterval $duration
     * @param null|string $summary
     * @return Vevent
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.41.53 - 2022-08-08
     */
    public static function factory(
        ? array $config = [],
        null|string|DateTimeInterface $dtstart = null,
        null|string|DateTimeInterface $dtend = null,
        null|string|DateInterval $duration = null,
        ? string $summary = null
    ) : Vevent
    {
        $instance = new Vevent( $config );
        if( null !== $dtstart ) {
            $instance->setDtstart( $dtstart );
        }
        if( null !== $dtend ) {
            $instance->setDtend( $dtend );
        }
        elseif( null !== $duration ) {
            $instance->setDuration( $duration );
        }
        if( null !== $summary ) {
            $instance->setSummary( $summary );
        }
        return $instance;
    }

    /**
     * Destructor
     *
     * @since 2.41.3 2022-01-17
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
            $this->compix,
            $this->propIx,
            $this->propDelIx
        );
        unset(
            $this->cno,
            $this->srtk
        );
        unset(
            $this->attach,
            $this->attendee,
            $this->categories,
            $this->class,
            $this->comment,
            $this->color,
            $this->conference,
            $this->contact,
            $this->created,
            $this->description,
            $this->dtend,
            $this->dtstamp,
            $this->dtstart,
            $this->duration,
            $this->image,
            $this->exdate,
            $this->exrule,
            $this->geo,
            $this->lastmodified,
            $this->location,
            $this->organizer,
            $this->priority,
            $this->rdate,
            $this->recurrenceid,
            $this->relatedto,
            $this->requeststatus,
            $this->resources,
            $this->rrule,
            $this->sequence,
            $this->status,
            $this->structureddata,
            $this->styleddescription,
            $this->summary,
            $this->transp,
            $this->uid,
            $this->url
        );
    }

    /**
     * Return formatted output for calendar component VEVENT object instance
     *
     * @return string
     * @throws Exception  (on Duration/Rdate err)
     * @since 2.41.55 2022-08-13
     */
    public function createComponent() : string
    {
        return Formatter::format( $this );
    }
}
