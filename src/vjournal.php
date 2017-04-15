<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * @copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      http://kigkonsult.se/iCalcreator/index.php
 * @package   iCalcreator
 * @version   2.23.7
 * @license   Part 1. This software is for
 *                    individual evaluation use and evaluation result use only;
 *                    non assignable, non-transferable, non-distributable,
 *                    non-commercial and non-public rights, use and result use.
 *            Part 2. Creative Commons
 *                    Attribution-NonCommercial-NoDerivatives 4.0 International License
 *                    (http://creativecommons.org/licenses/by-nc-nd/4.0/)
 *            In case of conflict, Part 1 supercede Part 2.
 *
 * This file is a part of iCalcreator.
 */
namespace kigkonsult\iCalcreator;
use kigkonsult\iCalcreator\util\util;
use kigkonsult\iCalcreator\util\utilAttendee;
use kigkonsult\iCalcreator\util\utilRecur;
use kigkonsult\iCalcreator\util\utilRexdate;
/**
 * iCalcreator VJOURNAL component class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-01
 */
class vjournal extends calendarComponent {
  use traits\ATTACHtrait,
      traits\ATTENDEEtrait,
      traits\CATEGORIEStrait,
      traits\CLASStrait,
      traits\COMMENTtrait,
      traits\CONTACTtrait,
      traits\CREATEDtrait,
      traits\DESCRIPTIONtrait,
      traits\DTSTAMPtrait,
      traits\DTSTARTtrait,
      traits\EXDATEtrait,
      traits\EXRULEtrait,
      traits\LAST_MODIFIEDtrait,
      traits\ORGANIZERtrait,
      traits\RDATEtrait,
      traits\RECURRENCE_IDtrait,
      traits\RELATED_TOtrait,
      traits\REQUEST_STATUStrait,
      traits\RRULEtrait,
      traits\SEQUENCEtrait,
      traits\STATUStrait,
      traits\SUMMARYtrait,
      traits\UIDtrait,
      traits\URLtrait;
/**
 * Constructor for calendar component VJOURNAL object
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.20 - 2017-02-01
 * @param array $config
 * @uses calendarComponent::__contruct()
 * @uses calendarComponent::setConfig()
 * @uses util::initConfig()
 */
  public function __construct( $config = array()) {
    static $J = 'j';
    parent::__construct();
    $this->setConfig( util::initConfig( $config ));
    $this->cno = $J . parent::getObjectNo();
  }
/**
 * Destructor
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-03-18
 */
  public function __destruct() {
    unset( $this->xprop,
           $this->components,
           $this->unparsed,
           $this->config );
    unset( $this->objName,
           $this->cno,
           $this->propix,
           $this->propdelix );
    unset( $this->attach,
           $this->attendee,
           $this->categories,
           $this->class,
           $this->comment,
           $this->contact,
           $this->created,
           $this->description,
           $this->dtstamp,
           $this->dtstart,
           $this->exdate,
           $this->exrule,
           $this->lastmodified,
           $this->organizer,
           $this->rdate,
           $this->recurrenceid,
           $this->relatedto,
           $this->requeststatus,
           $this->rrule,
           $this->sequence,
           $this->status,
           $this->summary,
           $this->uid,
           $this->url );
  }
/**
 * Return formatted output for calendar component VJOURNAL object instance
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.5.1 - 2008-10-12
 * @return string
 * @uses calendarComponent::createUid()
 * @uses calendarComponent::createDtstamp()
 * @uses vjournal::createAttach()
 * @uses vjournal::createAttendee()
 * @uses vjournal::createCategories()
 * @uses vjournal::createClass()
 * @uses vjournal::createComment()
 * @uses vjournal::createContact()
 * @uses vjournal::createDescription()
 * @uses vjournal::createDtstart()
 * @uses vjournal::createExdate()
 * @uses vjournal::createExrule()
 * @uses vjournal::createLastModified()
 * @uses vjournal::createOrganizer()
 * @uses vjournal::createRdate()
 * @uses vjournal::createRelatedTo()
 * @uses vjournal::createRequestStatus()
 * @uses vjournal::createRecurrenceid()
 * @uses vjournal::createRrule()
 * @uses vjournal::createSequence()
 * @uses vjournal::createStatus()
 * @uses vjournal::createSummary()
 * @uses vjournal::createUrl()
 * @uses calendarComponent::createXprop()
 */
  public function createComponent() {
    $objectname =  strtoupper( $this->objName );
    $component  = sprintf( util::$FMTBEGIN, $objectname );
    $component .= $this->createUid();
    $component .= $this->createDtstamp();
    $component .= $this->createAttach();
    $component .= $this->createAttendee();
    $component .= $this->createCategories();
    $component .= $this->createClass();
    $component .= $this->createComment();
    $component .= $this->createContact();
    $component .= $this->createCreated();
    $component .= $this->createDescription();
    $component .= $this->createDtstart();
    $component .= $this->createExdate();
    $component .= $this->createExrule();
    $component .= $this->createLastModified();
    $component .= $this->createOrganizer();
    $component .= $this->createRdate();
    $component .= $this->createRequestStatus();
    $component .= $this->createRecurrenceid();
    $component .= $this->createRelatedTo();
    $component .= $this->createRrule();
    $component .= $this->createSequence();
    $component .= $this->createStatus();
    $component .= $this->createSummary();
    $component .= $this->createUrl();
    $component .= $this->createXprop();
    return $component . sprintf( util::$FMTEND, $objectname );
  }
}
