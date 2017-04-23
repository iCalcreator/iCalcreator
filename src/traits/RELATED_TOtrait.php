<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * link      http://kigkonsult.se/iCalcreator/index.php
 * package   iCalcreator
 * version   2.23.10
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
namespace kigkonsult\iCalcreator\traits;
use kigkonsult\iCalcreator\util\util;
/**
 * RELATED-TO property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-17
 */
trait RELATED_TOtrait {
/**
 * @var array component property RELATED_TO value
 * @access protected
 */
  protected $relatedto = null;
/**
 * Return formatted output for calendar component property related-to
 *
 * @return string
 * @uses util::createElement()
 * @uses util::createParams()
 * @uses util::strrep()
 * @uses calendarComponent::getConfig()
 */
  public function createRelatedTo() {
    if( empty( $this->relatedto ))
      return null;
    $output = null;
    foreach( $this->relatedto as $rx => $relation ) {
      if( ! empty( $relation[util::$LCvalue] ))
        $output .= util::createElement( util::$RELATED_TO,
                                        util::createParams( $relation[util::$LCparams] ),
                                        util::strrep( $relation[util::$LCvalue] ));
      elseif( $this->getConfig( util::$ALLOWEMPTY ))
        $output .= util::createElement( util::$RELATED_TO );
    }
    return $output;
  }
/**
 * Set calendar component property related-to
 *
 * @param string  $value
 * @param array   $params
 * @param int     $index
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses util::existRem()
 * @uses util::setMval()
 * @uses util::trimTrailNL()
 */
  public function setRelatedTo( $value, $params=null, $index=null ) {
    static $RELTYPE = 'RELTYPE';
    static $PARENT  = 'PARENT';
    if( empty( $value )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $value = util::$EMPTYPROPERTY;
      else
        return false;
    }
    util::existRem( $params, $RELTYPE, $PARENT, true ); // remove default
    util::setMval( $this->relatedto,
                   util::trimTrailNL( $value ),
                   $params,
                   false,
                   $index );
    return true;
  }
}
