# iCalcreator

is the PHP class package managing

> iCal calendar information

| |supporting|
|---|---|
|[rfc2445]|Internet Calendaring and Scheduling Core Object Specification (iCalendar)|
|[rfc5545]|Internet Calendaring and Scheduling Core Object Specification (iCalendar)|
|[rfc5870]|A Uniform Resource Identifier for Geographic Locations ('geo' URI)|
|[rfc6321]|xCal: The XML Format for iCalendar|
|[rfc6868]|Parameter Value Encoding in iCalendar and vCard|
|[rfc7529]|Non-Gregorian Recurrence Rules in the Internet Calendaring and Scheduling Core Object Specification (iCalendar)|
|[rfc7808]|Time Zone Data Distribution Service|
|[rfc7953]|Calendar Availability|
|[rfc7986]|New Properties for iCalendar|
|[rfc9073]|Event Publishing Extensions to iCalendar|
|[rfc9074]|VALARM Extensions for iCalendar|

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

v2.41.64 - v2.42 pre-release

v2.40 - stable PHP8

v2.39 - PHP7

Asserting PHP compability using [PHPCompatibility] and [PHPStan].

###### Support

For support use [github.com/iCalcreator]. Non-emergence support issues are, unless sponsored, fixed in due time.


###### Sponsorship

Donation using [paypal.me/kigkonsult] are appreciated.
For invoice, please e-mail</a>.

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

[demo]:docs/demoUsage.md
[github.com/iCalcreator]:https://github.com/iCalcreator/iCalcreator/issues
[github.com/PhpJsCalendar]:https://github.com/iCalcreator/PhpJsCalendar
[paypal.me/kigkonsult]:https://paypal.me/kigkonsult
[PHPCompatibility]:https://github.com/PHPCompatibility/PHPCompatibility
[PHPStan]:https://github.com/phpstan/phpstan
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
