=== WP-PagesNav ===
Tags: navigation, pages
Contributors: adsworth

WP-PagesNav is a plugin which can be used to display a navigation bar using the 
static pages in your blog. When the plugin is activated, the function 
wp_pages_nav() is available in the Themes. To change the behaviour of the plugin
you can pass the function a query string. The following parameters are 
recognized:
- show_all_parents
  If show_all_parents is set then the navigation will get larger and larger 
  the further down the page hierarchy you navigate.
- show_root
  If show_root is set then also the top most pages are displayed.
- current
  If current is set then wp_pages_nav doesn't choose the currently viewwed page.
  It uses what is passed in current as the currently viewed page ID.

Also all of the parameters of the get_pages function are supported. Which are:
- child_of
  Only select pages which are a child of child_of. Default: 0
- sort_column
  Database column by which to sort the pages. Default: post_title
- sort_order
  Wether to sort ascending or descending. Default: ASC
- exclude
  Pages to exclude Default: none

== Installation ==

1. Upload to your plugins folder, usually `wp-content/plugins/`
2. Activate the plugin on the plugin screen
3. Add the sample styles from wp-pagesnav/styles.css
4. Add the code from wp-pagesnav/template-source.php to your Theme
5. Change the settings to fit your needs.

== Frequently Asked Questions ==

== Screenshots ==

1. This is what the Plugin looks like using the supplied settings, in the default WordPress theme. Since I'm not much of a designer it doesn't look pretty.