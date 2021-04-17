web2rss
=======

Quick and dirty RSS feeds for sites that don't have them.

Requirements
------------

* PHP 7.4+ (that's all I tested with) with the following extensions (you probably already have them):
    * CURL extension
    * SimpleXML extension
    * OpenSSL extension
* Good knowledge of CSS selectors, [especially those used by simplehtmldom](https://simplehtmldom.sourceforge.io/docs/1.9/api/simple_html_dom_node/find/#supported-selectors).
* Good knowledge of [how to parse strings into dates in PHP](https://www.php.net/manual/en/datetime.createfromformat.php).

Installation
------------

This will run on any random shared hosting. Just copy this repo's files
to your public_html. That's all, there's no database or any persistent
state.

Usage
-----

(Disclaimer: this is not at all user-friendly.)

1. Visit index.php in your browser.
2. Paste a URL into the box and click apply.
3. In the Page preview iframe (you might need to scroll) you can see a preview of the
   page. Figure out the selector of the container and paste that into the box
   above, then click Apply.
4. Now the preview window has changed to only show you the first container's contents. There's also a feed preview showing a HTML version of the feed.
5. Figure out the selectors for the title, date, and content. You can leave any of them blank. If you leave the content blank, then whatever's left after removing the title and date becomes the content.
6. For the date, figure out its format, and construct a format string that PHP will accept. If you get it wrong, the next time you click apply there will be an error :-(
7. Optionally, add removal selectors to remove things you don't want in the body.
8. Click apply throughout the process to see changes.
9. When satisfied, copy the RSS link and add it to your reader.

### Hints

* In the page preview frame, I've added a (probably unecessarily verbose) selector to each element you can use as a hint.
* The date formatting is very fiddly. Sorry about that.
* Often times when you have dates on a web page, they're given only as e.g. `2021-01-02` with no time, so you might be tempted to write the date format string as `Y-m-j`. But PHP's date parsing will start with the current system time, so every time you refresh the feed the time will be different! Use the `|` at the end of your string to set everything you didn't specify to 0.
  Yes, the time won't be *exactly* right, but again, this is quick and dirty.
* When you're done, click apply a few times to ensure that the guid doesn't change every time. If it does, then your date string might be wrong for the reason above.

License
-------

Distributed under the same terms as simplehtmldom, which is MIT.
