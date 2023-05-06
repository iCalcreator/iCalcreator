# iCalcreator

is the PHP class package managing

> iCal calendar information
<br><br>[rfc5545] - Internet Calendaring and Scheduling Core Object Specification (iCalendar) 

__supporting__<br>
[rfc2445] - Internet Calendaring and Scheduling Core Object Specification (iCalendar)<br>
[rfc5870] - A Uniform Resource Identifier for Geographic Locations ('geo' URI)<br>
[rfc6321] - xCal: The XML Format for iCalendar<br>
[rfc6868] - Parameter Value Encoding in iCalendar and vCard<br>
[rfc7529] - Non-Gregorian Recurrence Rules in the Internet Calendaring and Scheduling Core Object Specification (iCalendar)<br>
[rfc7808] - Time Zone Data Distribution Service<br>
[rfc7953] - Calendar Availability<br>
[rfc7986] - New Properties for iCalendar<br>
[rfc9073] - Event Publishing Extensions to iCalendar<br>
[rfc9074] - VALARM Extensions for iCalendar<br>

operating on calendar and
calendar event, todo, journal, freebusy, participant, location, resource, availability and timezone data.

~~~~~~~~
iCalcreator supports systems like

* calendars
* CMS
* project management systems
* other applications...
~~~~~~~~

Please review 
- [demo] for a short demo 
- releaseNotes for release brief overview,
- docs/summary and docs/using for details.

For iCal json (JSCalendar, [rfc8984]) export and import, use [github.com/PhpJsCalendar].

###### Builds

v2.41.76 - v2.42 pre-release

v2.40 - stable PHP8

v2.39 - PHP7

To support the development, maintenance and test process 
[PHPCompatibility], [PHPStan] and [php-arguments-detector] are included.

###### Support

For support use [github.com/iCalcreator]. Non-emergence support issues are, unless sponsored, fixed in due time.


###### Sponsorship

Donations using _[buy me a coffee]_ or _[paypal me]_ are appreciated.
For invoice, please e-mail.

###### Installation

Composer

From the Command Line:

```
composer require kigkonsult/icalcreator
```

In your composer.json:

```
{
    "require": {
        "kigkonsult/icalcreator": ">=2.40"
    }
}
```

###### License

iCalcreator is licensed under the LGPLv3 License.

[buy me a coffee]:https://www.buymeacoffee.com/kigkonsult
[paypal me]:https://paypal.me/kigkonsult
[demo]:docs/demoUsage.md
[github.com/iCalcreator]:https://github.com/iCalcreator/iCalcreator/issues
[github.com/PhpJsCalendar]:https://github.com/iCalcreator/PhpJsCalendar
[paypal.me/kigkonsult]:https://paypal.me/kigkonsult
[PHPCompatibility]:https://github.com/PHPCompatibility/PHPCompatibility
[PHPStan]:https://github.com/phpstan/phpstan
[php-arguments-detector]:https://github.com/DeGraciaMathieu/php-arguments-detector
[rfc2445]:https://www.rfc-editor.org/info/rfc2445
[rfc5545]:https://www.rfc-editor.org/info/rfc5545
[rfc5870]:https://www.rfc-editor.org/info/rfc5870
[rfc6321]:https://www.rfc-editor.org/info/rfc6321
[rfc6868]:https://www.rfc-editor.org/info/rfc6868
[rfc7529]:https://www.rfc-editor.org/info/rfc7529
[rfc7808]:https://www.rfc-editor.org/info/rfc7808
[rfc7953]:https://www.rfc-editor.org/info/rfc7953
[rfc7986]:https://www.rfc-editor.org/info/rfc7986
[rfc8984]:https://www.rfc-editor.org/info/rfc8984
[rfc9073]:https://www.rfc-editor.org/info/rfc9073
[rfc9074]:https://www.rfc-editor.org/info/rfc9074
