=== Plugin Name ===
Tags: client, callback plugin, google calendar
Requires at least: 3.0.0
Tested up to: 3.9.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

- CallMeBack generates a basic Callback Form on your page via shortcode <code>[cmb]</code>. Within your theme php-file, use <code>`<?= do_shortcode('[cmb]') ?>`</code>
- If a client fills out the Form and sends you a Request, it will be automatically inserted into your custom Google Calendar as an event

You can setup your mobile or something to remind you of that events, and you will never miss a callback request of a client again.

== Installation ==

1. Unpack callbackevent.zip
2. Move the folder into /wp-content/plugins
3. Activate the Plugin "CallbackEvent" in the Backend
4. In the Sidebar of the backend appears "CMB Settings"
5. There, you have to insert 3 Values: CliendID, ClientSecret, CalendarID and, if you like, change the default Thank-you Text :)


Create ClientID & ClientSecret
-------------------------------
1. https://console.developers.google.com
2. "Create Project"
3. Go to the Menu APIS & auth => APIs and enable "Calendar API"
4. Go to the Menu APIS & auth => Credentials
5. On the right side unter "OAauth", press the Button "Create new Client ID"
6. Chose "Web Application"
7. Fill in the address of your page under "AUTHORIZED REDIRECT URI".
8. After a few seconds, the data appears, including ClientID and ClientSecret

CalendarID
----------
1. https://www.google.com/calendar
2. Create a calendar, e.g. "Callbacks" and go to it's calendar-settings.
3. In the settings-page, paragraph "Calendar Address", there is the calendar id.
4. The calendar id has the form "rih2k42k4jh24kj24h24kh2@group.calendar.google.com"

Insert the 3 Values you got in the steps above into the Form and press Save.

Afterwards, a link "Authenticate" appears. Click that link, to authenticate at google-services.

That's it!

To show the callback-form in your page, use the "[cmb]" shortcode.

Now, if there is a request, it automatically appears as an event in your created "Callbacks" Calender at Google Calendar
and you can setup up your smartphone or similar to remind you.

== Screenshots ==

1. Create the Shortcode to display the Callback Form. It's style can be easily overwritten with css #cmb_form
2. Enter ClientID, ClientSecret from GoogleDeveloper Console and CalenderID from Google Calender.
3. The Form that's being generated. It's style can easily being overwritten.
4. DateTime Picker included (http://xdsoft.net/jqplugins/datetimepicker/)
5. The Request appears in the Google Calendar.










