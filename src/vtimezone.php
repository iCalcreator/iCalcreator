<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * link      http://kigkonsult.se/iCalcreator/index.php
 * package   iCalcreator
 * version   2.23.16
 * license   By obtaining and/or copying the Software, iCalcreator,
 *           you (the licensee) agree that you have read, understood,
 *           and will comply with the following terms and conditions.
 *           a. The above copyright, link, package and version notices,
 *              this licence notice and
 *              the [rfc5545] PRODID as implemented and invoked in the software
 *              shall be included in all copies or substantial portions of the Software.
 *           b. The Software, iCalcreator, is for
 *              individual evaluation use and evaluation result use only;
 *              non assignable, non-transferable, non-distributable,
 *              non-commercial and non-public rights, use and result use.
 *           c. Creative Commons
 *              Attribution-NonCommercial-NoDerivatives 4.0 International License
 *              (http://creativecommons.org/licenses/by-nc-nd/4.0/)
 *           In case of conflict, a and b supercede c.
 *
 * This file is a part of iCalcreator.
 */
namespace kigkonsult\iCalcreator;
use kigkonsult\iCalcreator\util\util;
/**
 * iCalcreator VTIMEZONE component class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-01
 */
class vtimezone extends calendarComponent {
  use traits\COMMENTtrait,
      traits\DTSTARTtrait,
      traits\LAST_MODIFIEDtrait,
      traits\RDATEtrait,
      traits\RRULEtrait,
      traits\TZIDtrait,
      traits\TZNAMEtrait,
      traits\TZOFFSETFROMtrait,
      traits\TZOFFSETTOtrait,
      traits\TZURLtrait;
/**
 * @var string $timezonetype  vtimezone type value
 * @access protected
 */
  protected $timezonetype;
/**
 * Constructor for calendar component VTIMEZONE object
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-01
 * @param mixed $timezonetype  default false ( STANDARD / DAYLIGHT )
 * @param array $config
 * @uses calendarComponent::__contruct()
 * @uses vtimezone::setConfig()
 * @uses util::initConfig(
 */
  public function __construct( $timezonetype=null, $config = []) {
    static $TZ = 'tz';
    if( is_array( $timezonetype )) {
      $config       = $timezonetype;
      $timezonetype = null;
    }
    $this->timezonetype = ( empty( $timezonetype ))
                        ? util::$LCVTIMEZONE : strtolower( $timezonetype );
    parent::__construct();
    $this->setConfig( util::initConfig( $config ));
    $prf = ( empty( $timezonetype )) ? $TZ : substr( $timezonetype, 0, 1 );
    $this->cno = $prf . parent::getObjectNo();
  }
/**
 * Destructor
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-03-17
 */
  public function __destruct() {
    if( ! empty( $this->components ))
      foreach( $this->components as $cix => $component )
        $this->components[$cix]->__destruct();
    unset( $this->xprop,
           $this->components,
           $this->unparsed,
           $this->config );
    unset( $this->objName,
           $this->cno,
           $this->propix,
           $this->compix,
           $this->propdelix );
    unset( $this->comment,
           $this->dtstart,
           $this->lastmodified,
           $this->rdate,
           $this->rrule,
           $this->tzid,
           $this->tzname,
           $this->tzoffsetfrom,
           $this->tzoffsetto,
           $this->tzurl,
           $this->timezonetype );
  }
/**
 * Return formatted output for calendar component VTIMEZONE object instance
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.5.1 - 2008-10-25
 * @return string
 * @uses vtimezone::createTzid()
 * @uses vtimezone::createLastModified()
 * @uses vtimezone::createTzurl()
 * @uses vtimezone::createDtstart()
 * @uses vtimezone::createTzoffsetfrom()
 * @uses vtimezone::createTzoffsetto()
 * @uses vtimezone::createComment()
 * @uses vtimezone::createRdate()
 * @uses vtimezone::createRrule()
 * @uses vtimezone::createTzname()
 * @uses vtimezone::createXprop()
 * @uses calendarComponent::createXprop()
 * @uses calendarComponent::createSubComponent()
 */
  public function createComponent() {
    $objectname = strtoupper(( isset( $this->timezonetype )) ? $this->timezonetype : $this->objName );
    $component  = sprintf( util::$FMTBEGIN, $objectname );
    $component .= $this->createTzid();
    $component .= $this->createLastModified();
    $component .= $this->createTzurl();
    $component .= $this->createDtstart();
    $component .= $this->createTzoffsetfrom();
    $component .= $this->createTzoffsetto();
    $component .= $this->createComment();
    $component .= $this->createRdate();
    $component .= $this->createRrule();
    $component .= $this->createTzname();
    $component .= $this->createXprop();
    $component .= $this->createSubComponent();
    return $component . sprintf( util::$FMTEND, $objectname );
  }
/**
 * Get vtimezone component property value/params
 *
 * If arg $inclParam, return array with keys VALUE/PARAMS
 * @param string  $propName
 * @param int     $propix   specific property in case of multiply occurences
 * @param bool    $inclParam
 * @param bool    $specform
 * @return mixed
 * @uses vtimezone::$tzid
 * @uses vtimezone::$tzoffsetfrom
 * @uses vtimezone::$tzoffsetto
 * @uses vtimezone::$tzurl
 * @uses calendarComponent::getProperty()
 */
  public function getProperty( $propName=null,
                               $propix=null,
                               $inclParam=false,
                               $specform=false ) {
    switch( strtoupper( $propName )) {
      case util::$TZID:
        if( isset( $this->tzid[util::$LCvalue] ))
          return ( $inclParam ) ? $this->tzid
                                : $this->tzid[util::$LCvalue];
        break;
      case util::$TZOFFSETFROM:
        if( isset( $this->tzoffsetfrom[util::$LCvalue] ))
          return ( $inclParam ) ? $this->tzoffsetfrom
                                : $this->tzoffsetfrom[util::$LCvalue];
        break;
      case util::$TZOFFSETTO:
        if( isset( $this->tzoffsetto[util::$LCvalue] ))
          return ( $inclParam ) ? $this->tzoffsetto
                                : $this->tzoffsetto[util::$LCvalue];
        break;
      case util::$TZURL:
        if( isset( $this->tzurl[util::$LCvalue] ))
          return ( $inclParam ) ? $this->tzurl
                                : $this->tzurl[util::$LCvalue];
        break;
      default:
        return parent::getProperty( $propName,
                                    $propix,
                                    $inclParam,
                                    $specform );
        break;
    }
    return false;
  }
}
