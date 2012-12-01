/**
 * iCalcreator class v2.10
 * copyright (c) 2007-2011 Kjell-Inge Gustafsson, kigkonsult
 * www.kigkonsult.se/iCalcreator/index.php
 * ical@kigkonsult.se
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
You can run and edit (simple) test files for calendar properties, one file
for every calendar property.

Open 'index.php' in your (local) webserver. Make sure iCalcreator.class.php
points to the right folder.

The testSuite 'file folder' folder must be writable for webserver user,
the index.php script saves files during execution.

There are three run options: run-ical, run-xcal, edit. In script edit mode
where is an option to save the script.

When You select run-ical/run-xcal, You can also select to display or redirect
output in browser.

Each file tests input formats allowed for specific property, an iCal file
is created and saved. That file is then parsed into a new calendar object,
a new iCal-file is created and later displayed or redirected. Both create
AND parse is tested for each calendar component property.

If display option is selected the two files are compared (filesize and unix
cmd diff) and diff output is displayed (linux env. only)


Do NOT remove the last lines in the scripts:
echo $str;
.. .
// $c->returnCalendar( FALSE, 'test.ics' );

alt.
// echo $str;
.. .
$c->returnCalendar( FALSE, 'test.ics' );

(auto edited by index.php script).


Only for testing, evaluating and showing iCal/xCal
property input format and output.

Feedback is welcome; ical@kigkonsult.se!!!
