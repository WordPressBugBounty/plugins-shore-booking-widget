=== Shore Booking Widget ===
Contributors: shoregmbh
Tags: booking, appointments, scheduling, shore, widgets
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.5
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Integrate Shore's booking system into your WordPress site with embedded booking, standard button, or floating button display options.

== Description ==

Make booking easy for your customers With Shore Booking Widget, your customers can book appointments directly on your website. No redirects, no complications. Whether you run a hair salon, beauty studio, or any other service business - give your customers the booking experience they deserve.

Perfect for service businesses Hair salons, barbershops, beauty studios, nail salons, massage therapists, personal trainers, consultants - if you take appointments, this widget is for you.

**⚠️ Important: You must have a Shore account to use this plugin.**

Sign up for Shore at: [shore.com/en/booking-marketing](https://signup.shore.com/en/signup/booking?source=onecom_wordpress)

= Features =

* **Three ways to display your booking system, Pick the style that works best for your website:**
  * Embedded Booking Page - The full booking interface lives right on your page. Customers stay on your site from start to finish.
  * Standard Button - A clean button that opens your booking system. Simple and straightforward.
  * Floating Button - A button that stays visible while your customers browse. They can book anytime without scrolling back up.

* **Customize it your way. Make the widget match your brand:**
  * Choose from 16 background colors
  * Pick from 7 text colors
  * Live preview in admin settings

* **Multi-language Support:**
  * The widget automatically detects your WordPress site's language
  * or you can pick manually from: English, German, French, Spanish

* **Easy to use, No coding skills needed:**
  * Use shortcode `[shore_booking]` on any page or post
  * Customize height for embedded view

* **Google Analytics Integration:**
  * Automatic event tracking via Google Tag Manager
  * Track booking interactions and conversions

= Prerequisites =

Before installing this plugin, you need to:

1. Sign up for Shore at: https://signup.shore.com/en/signup/booking?source=onecom_wordpress
2. Create your booking system in your Shore dashboard
3. Get your configuration token from Shore

**⚠️ Without a Shore account and configuration token, the plugin will not work.**

= Usage =

After activation, go to **Settings > Shore Booking** to configure:

1. Enter your Shore configuration token
2. Choose your display type (Embedded, Standard Button, or Floating Button)
3. Select language preference
4. Customize button colors (if applicable)
5. Save settings

Use the shortcode `[shore_booking]` on any page or post to display the booking widget.

**Shortcode Examples:**

* Basic: `[shore_booking]`
* Custom height: `[shore_booking height="600px"]`
* Custom language: `[shore_booking locale="de"]`
* Both: `[shore_booking height="800px" locale="fr"]`

= Support =

For plugin support, visit the support forum.

For Shore platform support, check our [support page](https://help.shore.com/en)

== Installation ==

= From WordPress Admin =

1. Go to **Plugins > Add New**
2. Search for "Shore Booking Widget"
3. Click **Install Now** and then **Activate**
4. Go to **Settings > Shore Booking** to configure

= Manual Installation =

1. Download the plugin ZIP file
2. Upload to `/wp-content/plugins/` directory
3. Activate through the **Plugins** menu
4. Go to **Settings > Shore Booking** to configure

= After Installation =

1. Get your Shore configuration token from https://shore.com
2. In WordPress, go to **Settings > Shore Booking**
3. Enter your configuration token
4. Configure display settings
5. Add `[shore_booking]` shortcode to any page

== Frequently Asked Questions ==

= Do I need a Shore account? =

Yes, you must have an active Shore account with a booking system configured. Sign up at https://signup.shore.com/en/signup/booking?source=onecom_wordpress

= Where do I find my configuration token? =

Your configuration token is available in your Shore dashboard after you create your booking system. It's usually your business name or a custom identifier.

= Can I use multiple widgets on one page? =

Yes! You can add the `[shore_booking]` shortcode multiple times on the same page.

= Does this work with any WordPress theme? =

Yes! The plugin is designed to work with any WordPress theme. The embedded view is fully responsive.

= Can I customize the button colors? =

Yes, you can choose from 7 background colors and 3 text colors in the plugin settings.

= Which languages are supported? =

The widget supports English, German, French, and Spanish. It can auto-detect your WordPress site's language or you can manually select one.

= Does this track Google Analytics events? =

Yes, if you have Google Tag Manager installed on your site, the plugin automatically tracks booking events.

= Can I change the button text? =

Currently, the button text is "Book Now" by default. This may be customizable in future versions.

== Screenshots ==

1. Plugin settings page with live button preview
2. Embedded booking page display
3. Standard button display option
4. Floating button display option
5. Color customization options
6. Shortcode usage example

== Changelog ==

= 1.0.1 =
* Fixed: WordPress.org compliance - updated prefixes to 4+ characters
* Fixed: Proper wp_enqueue_script usage throughout
* Fixed: Welcome banner positioning and modal interactions
* Added: Spanish (es_ES) translation
* Added: French (fr_FR) translation
* Improved: Streamlined codebase - reduced from 75MB to ~150KB
* Improved: Single-file architecture for easier maintenance

= 1.0.0 =
* Initial release
* Three display options: Embedded, Standard Button, Floating Button
* Multi-language support with auto-detection
* Customizable button colors (16 colors each for background and text)
* Google Analytics integration via GTM
* Shortcode support with parameters
* Responsive design

== Upgrade Notice ==

= 1.0.1 =
WordPress.org compliance fixes, Spanish and French translations added, streamlined codebase.

= 1.0.0 =
Initial release of Shore Booking Widget.

== Additional Information ==

= Plugin Requirements =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* Active Shore account
* Configuration token from Shore

= Developer Notes =

The plugin follows WordPress coding standards and is optimized for performance with a streamlined codebase.

= Privacy Policy =

This plugin connects to Shore's booking service (connect.shore.com) to display booking interfaces. When users interact with the booking widget, data is transmitted to Shore's servers according to Shore's privacy policy.

The plugin also integrates with Google Tag Manager (if present on your site) to track booking events for analytics purposes.

No user data is stored by the plugin itself on your WordPress site.

== Credits ==

Developed by Shore GmbH
Website: https://shore.com
Booking Platform: https://shore.com/en/booking-marketing/