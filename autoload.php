<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.29.30
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
/**
 * autoload.php
 *
 * iCalcreator package autoloader
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.29.30 - 2020-12-09
 */
/**
 *         Do NOT alter or remove the constant!!
 */
define( 'ICALCREATOR_VERSION', 'iCalcreator 2.30' );
/**
 * load iCalcreator src and support classes and Traits
 */
spl_autoload_register(
    function( $class ) {
        static $BS      = '\\';
        static $PHP     = '.php';
        static $PREFIX  = 'Kigkonsult\\Icalcreator\\';
        static $SRC     = 'src';
        static $SRCDIR  = null;
        static $TEST    = 'test';
        static $TESTDIR = null;
        if( is_null( $SRCDIR )) {
            $SRCDIR  = __DIR__ . DIRECTORY_SEPARATOR . $SRC . DIRECTORY_SEPARATOR;
            $TESTDIR = __DIR__ . DIRECTORY_SEPARATOR . $TEST . DIRECTORY_SEPARATOR;
        }
        if( 0 != strncmp( $PREFIX, $class, 23 ))
            return false;
        $class = substr( $class, 23 );
        if( false !== strpos( $class, $BS ))
            $class = str_replace( $BS, DIRECTORY_SEPARATOR, $class );
        $file = $SRCDIR . $class . $PHP;
        if( file_exists( $file )) {
            include $file;
        }
        else {
            $file = $TESTDIR . $class . $PHP;
            if( file_exists( $file )) {
                include $file;
            }
        }
    }
);
