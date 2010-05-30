=== SyntaxHighlighter2 ===
Contributors: mohanjith
Tags: code, sourcecode, php, xhtml, html, css
Requires at least: 2.0
Stable tag: trunk
Tested up to: 3.0.0
Donate link: http://mohanjith.com/c/wordpress

Easily post source code such as PHP or HTML and display it in a styled box.

== Description ==

SyntaxHighlighter2 allows you to easily post syntax highlighted code all without loosing it's formatting or making an manual changes.

It supports the following languages (the alias for use in the post is listed next to the name):

* AS3 -- `as`, `as3`
* C++ -- `cpp`, `c`, `c++`
* C# -- `c#`, `c-sharp`, `csharp`
* ColdFusion - `cf`, `coldfusion`
* CSS -- `css`
* Delphi -- `delphi`, `pascal`
* Diff/Patches -- `diff`
* Erlang -- `erlang`
* Groovy -- `groovy`
* Java -- `java`
* JavaFX -- `javafx`
* JavaScript -- `js`, `jscript`, `javascript`
* Perl -- `perl`
* PHP -- `php`
* Plain text -- `plain`
* PowerShell -- `powershell`
* Python -- `py`, `python`
* Ruby -- `rb`, `ruby`, `rails`, `ror`
* Scala -- `scala`
* SQL -- `sql`
* VB -- `vb`, `vb.net`
* XML/HTML -- `xml`, `html`, `xhtml`, `xslt`

This plugin uses the [SyntaxHighlighter JavaScript package by Alex Gorbatchev](http://alexgorbatchev.com/wiki/SyntaxHighlighter).

== Installation ==

###Updgrading From A Previous Version###

To upgrade from a previous version of this plugin, delete the entire folder and files from the previous version of the plugin and then follow the installation instructions below.

###Uploading The Plugin###

Extract all files from the ZIP file, making sure to keep the file structure intact, and then upload it to `/wp-content/plugins/`.

This should result in the following file structure:

`- wp-content
    - plugins
        - syntaxhighlighter2
            | readme.txt
            | screenshot-1.png
            | syntaxhighlighter2.php
            - files
                | clipboard.swf
                | shBrushCpp.js
                | shBrushCSharp.js
                | [...]
                | shCore.js
                | wrapping.png`

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

###Plugin Activation###

Go to the admin area of your WordPress install and click on the "Plugins" menu. Click on "Activate" for the "SyntaxHighlighter" plugin.

###Plugin Usage###

Just wrap your code in `[sourcecode language='css']code here[/sourcecode]`. The language attribute is **required**! See the [plugin's description](http://wordpress.org/extend/plugins/syntaxhighlighter/) for a list of valid language attributes.

== Frequently Asked Questions ==

= The BBCode in my post is being replaced with &lt;pre&gt;'s just fine, but I don't see the syntax highlighting! =

Make sure your theme's footer has `<?php wp_footer(); ?>` somewhere in it, otherwise the JavaScript highlighting files won't be loaded.

= I still see the BBCode in my post. What gives? =

Make sure you correctly use the BBCode with a valid language attribute. A malformed usage of it won't result in replacement.

= Is this plugin licensed under GPL? =

Yes, like most plugins in the WordPress plugin directory, if not all. However the SyntaxHighligter javascript library is licensed under LGPLv3.

= Can I hide the toolbar, ruler, etc? =

Yes, you can give any of the options mentioned in http://alexgorbatchev.com/wiki/SyntaxHighlighter:Configuration#SyntaxHighlighter.defaults
as option attribute. See example bellow.

`[sourcecode language='css' option='toolbar: false;']code here[/sourcecode]`

== Screenshots ==

1. Example code display. Theme default.
2. Example code display. Theme django.
3. Example code display. Theme emacs.
4. Example code display. Theme fadetogrey.
5. Example code display. Theme midnight.
6. Example code display. Theme rdark.
7. Example code display. Theme eclipse.

== Other BBCode Methods ==

Find `[sourcecode language='css']code here[/sourcecode]` too long to type? Here's some alternative examples:

* `[source language='css']code here[/source]`
* `[code language='css']code here[/code]`


* `[sourcecode lang='css']code here[/sourcecode]`
* `[source lang='css']code here[/source]`
* `[code lang='css']code here[/code]`


* `[sourcecode='css']code here[/sourcecode]`
* `[source='css']code here[/source]`
* `[code='css']code here[/code]`

== PHP Version ==

PHP 5+

== ChangeLog ==

**Version 2.1.2**

* Compatibility with WordPress 3.0

**Version 2.1.1**

* Styling issue after upgrade
* Theme Eclipse added

**Version 2.1.0**

* Upgraded to SyntaxHighlighter 2.1.364

**Version 2.0.6**

* Options are not always picked up properly

**Version 2.0.5**

* Brush not found alert shown in admin section when there is code in user comment
* If there is no space between ' and ] parser fails

**Version 2.0.4**

* Allow for options. Hide the toolbar, ruler, etc.
* Select whether to apply the code highlighting to user comments in 'Settings' -> 'SyntaxHighlighter2'

**Version 2.0.3**

* Added bash (shell, sh) and patch syntax

**Version 2.0.2**

* Mention license in readme.txt

**Version 2.0.1**

* GPL credits

**Version 2.0.0**

* Added support for theming
* Plugin options page to choose the theme
* Added visibility to properties and methods

**Version 1.1.1 (SyntaxHighlighter)**

* Encode single quotes so `wptexturize()` doesn't transform them into fancy quotes and screw up code.

**Version 1.1.0 (SyntaxHighlighter)**

* mdawaffe [fixed](http://dev.wp-plugins.org/ticket/703) an encoding issue relating to kses and users without the `unfiltered_html` capability. Mad props to mdawaffe.

**Version 1.0.1 (SyntaxHighlighter)**

* Minor CSS fixes.
* Filter text widgets to allow posting of code.

**Version 1.0.0 (SyntaxHighlighter)**

* Initial release!
