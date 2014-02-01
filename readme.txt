=== Zanto ===
Contributors: brooksX,SimonSimCity
Donate link: https://stage.wepay.com/donations/support-zanto
Tags: translation, multilingual, localization, multisite, language switcher, languages
Requires at least: 3.0
Tested up to: 3.8
Stable tag: 0.2.3
License: GPLv2 or later

Zanto helps you run a multilingual site by providing linkage between content in blogs of different languages in a WordPress multisite.

== Description ==

Zanto enables you to convert blogs in a multisite into translations of each other. It provides a language sitcher to switch between the different translations
of pages, posts, categories, custom types and custom taxonomies. Zanto keeps track of what has been translated and what has not and provides an intuitive interface
that allows you to carry out translation. The number of languages you can run are unlimitted.


Features:

* Translation of posts, categories, custom taxonomies, custom types.
* Browser Language re-direct. i.e re-direct users to their prefered language in their browser language settings.
* An easily customizable language switcher.
* Ability to use custom made Language switcher themes.
* Add a language parameter to the URL for SEO purposes
* Ability to create multiple translation networks within the same multiste. i.e blog A is a translation of Blog B and C. Blog X a translation of blog Z, while all blogs are in the same multisite.
* Different languages for both the front and back end.
* Each admin will have his admin language preferences stored
* Over 60 in-built languages and flags.
* Ability for users to add their own native languages i.e from the ones not included.
* Intergrated support for domain mapping plugin
* Translated posts highliting to prevent double translation
== Installation ==

Upload the Zanto plugin to your blog, Activate it for each blog you want to do translations on or Network-wide if you want to do translation on all blogs in the multisite.

== Screenshots ==

1. Default Front end Language switcher added using either the inbuilt language switcher widget or custom code provided in Zanto settings that you place any where in your theme template.
2. Settings Section for downloading .mo files, changing your admin language or changing the Front end language settings.
3. Part of the blog Zanto settings page
4. Admin Language Switcher
5. Setting up a translation network from available blogs in the multisite

== Changelog ==
= 0.2.3 =
*Fixed an if statement bug that was causing some options not to save
= 0.2.2 =
*Fixed technical bugs related to php opening tags
*Added filters to allow hooking into and modifying how zanto handles content without translation
= 0.2.1 =
*Fixed bugs that eluded us in 0.2.0
*Added gray highlighting for translated posts so you can tell the difference between the translated posts and un-translated posts when assoicating the posts
on the post edit page. You can read about the new changes here http://zanto.org/zanto-0-2-1-starting-new-year-high-spirits/

= 0.2.0 =
* Fixed language download bug when version number is only 2 levels 
* On downloading languages, missing translation files will be searched for two versions back instead of one
* Fixed front page language switcher bug when using URL's whith language in directories or added as a parameter Zanto feature
* Intergrated support for domain mapping for the language switcher when using the domain mapping plugin
* Improved interface to better suite the wordpress admin (some interfaces were not displaying properly in version 3.8)
* Fixed Language switcher settings being over-written when general settings are saved

== Frequently Asked Questions ==
= Does Zanto work for single site installs =

Zanto works spicifically for multisite installs. to convert your single site to a multisite, follow <a href="http://codex.wordpress.org/Create_A_Network">this tutorial</a>

= Does Zanto have support for RTL Languages =

Yes, Zanto will work perfectly when a RTL language is detected

= Has Zanto been tested with large amounts of data =
Yes, Zanto has been subjected to performance tests when large amounts of data are involved and it passed without breaking a sweat.
For that very purpose, we created this testing plugin <a href="http://wordpress.org/plugins/multilingual-demo-data-creator/">multilingual demo data creator</a>

= Who runs Zanto? =
Glad you asked that :) Zanto is meant to be a free communitiy plugin, we intend to move it to Github soon so we can have all who want to get involved not to miss out
on the fun. We hope to translate the Zanto website itself to other languages once we get volunteer individuals who are upto the task.

= Is Zanto support free =
Yes, we provide free support for all our plugins users who have been kind enough to use them :) We also have a <a href="http://zanto.org/support/">dedicated support forum</a> to make sure
you are never alone and stuck while using Zanto.

= Is there more to Zanto? =
Yes, we have so many features in store under developement, keep tuned in at <a href="http://zanto.org/blog/">Our blog</a> and subscribe to our posts on to get the latest information on wordpress translation, new feature developements,
and perticipate in our forum. Get to decide what you want in your favourite free multilingual plugin and we'll fold our shirt sleeves to get it ready for the next version.

== Upgrade Notice ==

= 0.2.3 =
Upgrade to fix issue of some options not saving

= 0.2.2 =
Upgrade to fix bug from previous version 

= 0.2.1 =
Upgrade to fix bugs from previous version and better translation process by gray highlighting for translated posts so you can tell the difference between the translated posts and un-translated posts when assoicating the posts
on the post edit page. 

= 0.2.0 =
Upgrade to fix all bugs from the previous version and better integration with the domain mapping plugin.