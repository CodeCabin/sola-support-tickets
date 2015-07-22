=== Sola Support Tickets ===
Contributors: SolaPlugins, NickDuncan
Donate link: http://www.solaplugins.com
Tags: support ticket, support tickets, support, support plugin, ticket plugin, tickets, helpdesk, help desk, support desk
Requires at least: 3.5
Tested up to: 4.2.2
Stable tag: trunk
License: GPLv2

Create a support help desk and support ticket system in minutes with Sola Support Tickets.

== Description ==

The easiest to use Help Desk & Support Ticket plugin. Create a support help desk quickly and easily with Sola Support Tickets.

= Features =
* Manage support tickets (pending, open, solved)
* Adds a Submit Ticket page to your website
* Receive email notifications for new support tickets
* Receive email notifications for support ticket responses
* One support agent
* Priorities - Add priorities to your support tickets (low, high, urgent, critical)
* Internal notes 

= Premium Add-on =
* Custom front-end help desk showing new, open and closed tickets
* Multiple support agents
* Support tickets can be made private or public
* Add popular tickets to your help desk page
* Macros (add predefined responses to your support tickets)
* Assign tickets to other support agents
* Allow guests to submit support tickets
* Choose a default ticket status
* Upload files to support tickets and responses (HTML 5)
* Link your help desk to an email address and automatically create support tickets when emails are received
* Themes for the front-end help desk page (coming soon)
* Customize your help desk through the use of shortcodes
* Enable CAPTCHA on your support ticket submission form
* Departments
* Get the [Sola Support Tickets Premium Version](http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=wordpress&utm_medium=click&utm_campaign=st_readme) for only $29.99

= Translations =
Get a free copy of the Sola Support Tickets Premium version in exchange for translating our plugin!

* English (default)
* German (Michael Schulz)
* French (Raymond Radet) (Etienne Couturier)
* Spanish (Io)
* Dutch (Johan Omlo)
* Italian
* Croatian (Sanjin Barac)
* Bengali (Mayeenul Islam)
* Portuguese - Portugal (Miguel Madeira Rodrigues)
* Danish (Kenneth Wagner)

== Installation ==

1. Once activated, click the "Support Tickets" in your left navigation menu
2. Edit the settings to your preference.
3. Wait for your first support ticket

== Frequently Asked Questions ==

= How does the plugin work? =
When activated, the plugin automatically creates a "Submit Ticket" page where users can submit support tickets. Once a support ticket has been submitted, you will be notified via an email. To view the support ticket, log in to your wordpress admin section, click on "Support Tickets" in the left navigation and then click on "Edit". At the top right, you should notice options to change your support ticket status as well as assign a ticket priority. Notifications are sent out when there is a new support ticket as well as when there are new support ticket responses - these can be changed in the settings page.

= How do I edit the Submit Ticket page? =
The Submit Ticket page is created automatically upon activation. To edit, please go to Pages in your left navigation and edit the relevant page. Please remember to keep the shortcode on the page so that the submit ticket form shows correctly.

= How do I add a support desk / help desk to my website? =
In the basic version of the plugin, only a "Submit ticket" page is created. Should you wish to add a fully customizable help desk to your front end that shows public open/closed tickets and a search feature, please consider purchasing the premium version of the plugin for only $29.99 / year. Updates included forever.

== Screenshots ==

1. Submit a support ticket
2. Add a response to a support ticket
3. Support ticket settings

== Upgrade Notice ==

Not applicable

== Changelog  ==

= 3.09 2015-07-22 - Low priority =
* Added a pending support ticket count to the menu in wp-admin

= 3.08 2015-05-28 - Low Priority =
* Translations added:
*  Danish (Thank you Kenneth Wagner)
* Improvement: Minor UI change (Pro)
* Accessibility Improvements on submission form

= 3.07 2015-04-14 = 
* Bug fix: Localization bug fixed. Some strings were not being translated.
* Enhancement: French translation has been updated to support the Customer Satisfaction Surveys add-on (Etienne Couturier)

= 3.06 2015-04-13 =
* Fixed bug where CAPTCHA setting was not utilized in front-end along with PHP notice (code refactored)

= 3.05 2015-04-12 =
 * Fixed character-set encoding (SMTP e-mail)
 * Updated French translation file (Etienne Couturier)

= 3.04 2015-04-08 =
* Made the warning to update both the premium and basic plugin dynamic
* Fixed a compatibility issue with the Customer Satisfaction Survey add-on

= 3.03 2015-03-30 =
* Enhancement: Can now also enter a "From Name" for automated e-mails as a from header. The From Name and From E-mail headers are now also used for the Reply-To e-mail headers. (Premium)
* Enhancement: Can select to use wp_mail() or SMTP (valid SMTP settings are required). Headers set (From Name and From E-mail - available when using the premium version) are incorporated. (Premium and Basic)
* Enhancement: Can now set the Cron Frequency for checking a selected E-mail inbox for new support tickets. This has to do with the checking of a mailbox for new mails and the mails are automatically converted to support tickets. (Premium)
* Enhancement: Response text given for a ticket is now included in the automated notification e-mail. (Premium and Basic)
* Enhancement: A single function has been created to send out all automated e-mails. This function takes into account settings such as custom From Email and From Name headers (can be set when using premium version) and whether to use smtp settings or the wordpress email function. (Premium and Basic)
* Enhancement: All automated e-mails are now HTML with UTF-8 character set. (Premium and Basic)
* Enhancement: Support for the Customer Satisfaction Surveys add-on has been added. (Premium with activated Customer Satisfaction Surveys add-on)
* Enhancement: Customer Satisfaction Survey email settings added - The text to use for the stars rating and a setting to enable/disable sending of the surveys created using the Customer Satisfaction Survey add-on. (Premium with activated Customer Satisfaction Surveys add-on)
* Enhancement: Customer Satisfaction overall rating per ticket has been added to the Support Tickets Dashboard. (Premium with activated Customer Satisfaction Surveys add-on)
* Enhancement: Customer Satisfaction Survey results can be viewed per ticket on the Support Tickets Dashboard. (Premium with activated Customer Satisfaction Surveys add-on)
* Enhancement: The responder of a ticket no longer receives a notification e-mail stating that there was a response. Notifications to other recipients are still sent. (Premium and Basic)
* Enhancement: Added translation - Portuguese - Portugal (Miguel Madeira Rodrigues)
* Bug fix: Default priority setting in the back-end was not being saved (Premium)
* Bug fix: Default priority setting in the front-end was not being used (Premium and Basic)
* Bug fix: The setting for whether a user should be logged in to submit a support ticket was ignored on the public support desk page. (Premium)
* Bug fix: The search functionality on the public support desk page did not work as expected. Additional data was included in the result set HTML. (Premium)
* Bug fix: Fixed notice when captcha entered incorrectly in the front-end (Premium)
* Bug fix: Fixed the ticket counts at the top of the Support Tickets Dashboard (Premium and Basic)

= 3.02 2015-03-17 =
* Enhancement: Added From e-mail headers to automated notification e-mail - A user can now enter a From E-mail that will be used when sending notification e-mails. (Premium)
* Ehancement: When a file upload is done in the ticket editor view and no response text is given, a message is added as a response stating that a file upload was done. (Premium)
* Enhancement: More strings are now translateable (Premium and Basic)
* Bug Fix: Replaced deprecated function. Replaced get_settings() with get_option() (Premium and Basic)
* Bug Fix: White space in ticket status removed (Premium and Basic)
* Bug Fix: User roles weren't showing in ticket responses
* Bug Fix: Encoding has been fixed for ticket responses and notes - encoding between javascript and PHP was incorrect with AJAX requests (Premium and Basic)
* Bug Fix: Font Awesome and Bootstrap disable functionality PHP notices fixed (Premium)
* Bug Fix: Fixed Departments setting PHP notices
* Bug Fix: Fixed generic naming of classes and IDs in the plugin stylesheets to ensure compatability with themes. (Premium and Basic)

= 3.01 19-02-2015 =
* Files can now be uploaded and linked to support tickets (For browsers that support the HTML 5 file API only) (Premium)
* Sola Support Tickets Languages added: Bengali, Croatian
* New feature: Automatic support ticket closure after x days (Premium)
* New feature: Notification e-mails now contain the ticket content
* New feature: The e-mail address of the author is now visible within the ticket editor
* You can now disable/enable the use of bootstrap on the support desk page (Premium)
* You can now disable/enable the use of fontawesome on the support desk page (Premium)
* Bug fix: incorrect db prefix was used previously

= 3.0 16-01-2015 =
* Fixed submit support ticket page bug
* Fixed a bug that may have shown support tickets on the front end even when marked as private
* Support ticket status change notifications (new feature)
* Bug fixes
* Help desk improvements
* Menu changes

= 2.9 11-11-2014 = 
* Bug Fixes:
*  Fixed PHP Errors
*  Support Tickets do not show in normal site search, only when searched through the help desk
*  Private Support Tickets are not displayed to other users except the author and agents. 

= 2.8 24-10-2014 = 
* Bug Fixes:
*  Fixed PHP Errors

= 2.7 24-10-2014 =
* New Features: 
*  Show or hide the departments dropdown.
*  Choose a default department.
*  Allow guests to submit a support ticket
*  You can now notify the default agent or all agents when a new support ticket is received.
*  Choose a default support ticket status
*  Enable CAPTCHA on your support ticket submission form
* 
* Improvements:
*  Output Buffering enabled.
*  Uses user's Display name instead of login name

= 2.6 15-10-2014 =
* New Features:
*  Internal notes can be created
*  Departments are now available (premium version)
*  Assign a ticket to an agent (premium version)
*  Force the collection of new mails (premium version)
* Languages Added:
*  German (Thank you Michael Schulz)
*  French (Thank you Raymond Radet)
*  Spanish (Thank you Io)

= 2.5 01-08-2014 =
* Fixed a bug that stopped showing the responses in the front end
* Code improvements (PHP Warnings)
* Dutch translation added - thank you Johan Omlo

= 2.4 15-07-2014 =
* Performance improvement - Fixed PHP warnings
* Performance improvement - Corrected the use of flush_rewrite_rules

= 2.3 08-07-2014 =
* Closed support topics now show responses
* Screenshots added

= 2.2 08-07-2014 =
* Typo fix
* New layout for support ticket responses
* New feature: You can now choose whether users are allowed to submit HTML in their support tickets and responses
* Macro support functionality added for the pro version

= 2.1 29-06-2014 =
* Small bug fix

= 2.0 29-06-2014 =
* New feature: Priorities - set a default ticket priority aswell as give your users the ability to add a priority to their support ticket
* New feature: You can now filter by priority, status and support agent on the support tickets admin page
* When user logs in, they are now redirected to the "submit support ticket" page
* Better support ticket page UI (author details, extra styling)
* Bug fix: 'Last response by' column is now working correctly
* Better error handling

= 1.3 =
* New features - Add a custom message when someone sends a support ticket

= 1.2 =
* Language file added
* Documentation link added

= 1.1 =
* Bug fixes
* Welcome page added
* More CSS classes added to the submit support ticket page

= 1.0 =
* First release
