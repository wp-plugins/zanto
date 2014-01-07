=== Zanto ===
Contributors: brooksX
Donate link: https://stage.wepay.com/donations/support-zanto
Tags: translation, multilingual, localization, multisite, language switcher, languages
Requires at least: 3.0
Tested up to: 3.8
Stable tag: 0.2.1
License: GPLv2 or later

Zanto manages translation of your whole wordpress site to other languages and provides a language switcher to switch between translations.

== Description ==

Zanto manages translation of your wordpress site to other languages and provides a language switcher to switch between translations of sites, posts, 
categories, custom taxonomies custom posts e.tc. It takes advantage of the multisite architecture to efficiently manage this.


Features:

* Translation of posts, categories, custom taxonomies, custom types.
* Browser Language re-direct. i.e re-direct users to their prefered language in their browser language settings.
* An easily customizable language switcher.
* Ability to use custom mande Language switcher themes.
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

= 0.2.0 =
* Fixed language download bug when version number is only 2 levels 
* On downloading languages, missing translation files will be searched for two versions back instead of one
* Fixed front page language switcher bug when using URL's whith language in directories or added as a parameter Zanto feature
* Intergrated support for domain mapping for the language switcher when using the domain mapping plugin
* Improved interface to better suite the wordpress admin (some interfaces were not displaying properly in version 3.8)
* Fixed Language switcher settings being over-written when general settings are saved

== Upgrade Notice ==

= 0.2.0 =
Upgrade to fix all bugs from the previous version and better integration with the domain mapping plugin. 
=0.2.1=
Added gray highlighting for translated posts so you can tell the difference between the translated posts and un-translated posts when assoicating the posts
on the post edit page.