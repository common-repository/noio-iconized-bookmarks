=== Plugin Name ===
Contributors: noiodotnl
Donate link: http://tinyurl.com/donatenoio
Tags: blogroll, bookmarks, favicons, icons, links, images
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: 1.0.1

Plugin that allows you to automatically add favicons to your blog's links.

== Description ==

Noio Iconized Bookmarks has 3 functions:

= List bookmarks with favicons =
Upon activation there is a function available that works just like WordPress' built-in `wp_list_bookmarks`. The function is called `list_iconized_bookmarks()`, and it takes the same arguments as the original. The function takes images from the *link_image* field of each link. The images are displayed in front of the link, with `class="favicon"`, and it is up to the user to define a proper style.

= Iconized Links Widget = 
There is also a replacement Links widget available, to get iconized bookmarks into your blogroll. This widget has one option, which is the arguments string, with the same [arguments](http://codex.wordpress.org/Template_Tags/wp_list_bookmarks") that are passed to `wp_list_bookmarks`. 

= Iconizing =
Iconizing might be a bit of a stupid name, but what this function does is add favicon-URLs to each of your links' image fields! So you can just add a bunch of links to your blogroll, then run the plugin, and all favicons will be correctly set. You can start the plugin from the N.I.B. (Noio Iconized Bookmarks) panel in the settings panel.

== Installation ==

1. Read the description of this plugin.
1. Upload `noio-iconized-bookmarks.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. To find favicons for each of your links, go to the Settings Panel > N.I.B. 

To display your links with favicons, use either:

* The function `list_iconized_bookmarks()` .
* The Iconized Links widget, that should now be available in your sidebar widgets panel. 

== Frequently Asked Questions ==

= I can see icons, but the text of all my links has disappeared! =

You are probably still using wordpress' default Links widget, or a call to the default `wp_list_bookmarks()`. To see **both** text and links, you will have to use the new `list_iconized_bookmarks()` function, or the Iconized Links widget.

= Do I have to use N.I.B. to display the bookmarks with icons? =

No, you can use your own plugin. Though if you use the default `wp_list_bookmarks`, you will not be able to show **both** favicons and the link texts.

If you want to display the bookmarks with favicons, you can use either the new function `list_iconized_bookmarks` or the Iconized Links widget that should have appeared in your widget list.

= I have uninstalled N.I.B., but the icons are still showing in my sidebar =

If you are using the standard Links widget, images will be displayed if their address is available. If you have used N.I.B. to find favicon addresses, those will still be saved in your Links' *Image Address* field. N.I.B. does not **clear** your images when it's uninstalled, because that would be too severe a change in your database. You can either disable the `show_images` option for `wp_list_bookmarks` or manually remove the favicons from the *Image Address* fields. 

== Screenshots ==

1. Admin panel, after setting the favicons.
2. What bookmarks + favicons could look like.

== Changelog ==

= 1.0.1 = 
* Used the new class-based widget API to allow multiple instances of the Iconized Bookmarks Widget.
* Fixed timeouts occurring when finding favicons for nonexistent URLs.

== Upgrade Notice ==

This small patch fixes timeouts when finding favicons. Also, the widget now allows multiple instances, but you will have to re-add it to your sidebars.


