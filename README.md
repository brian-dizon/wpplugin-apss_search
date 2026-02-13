# APSS Search Overlay

APSS Search Overlay is a high-performance, secure, and professional live search solution for WordPress. Specifically designed for the All Pumps portfolio website, it provides a premium, full-screen search experience with instant results, a responsive grid layout, and deep customization options.

## 🚀 Key Features

- **Live Search Overlay:** A sleek, full-screen search interface with a 98% opacity background and an 8px backdrop blur.
- **Sectioned Results:** Search results are automatically grouped by post type (e.g., Products, Posts, Portfolio) for better organization.
- **Modern Card UI:** Results are displayed as professional cards featuring:
  - Square thumbnails (100x100px).
  - Line-clamped titles and excerpts.
  - Published dates with calendar icons.
- **Responsive Grid System:** Dynamically adjusts the layout to show 4 columns on desktop, 2 columns on tablets, and 1 column on mobile.
- **Performance Optimized:**
  - **Debouncing:** 300ms delay to prevent server hammering.
  - **Request Cancellation:** Uses `AbortController` to cancel stale fetch requests.
  - **Server-Side Caching:** Utilizes the WordPress Transients API (60-minute cache) for high-speed delivery.
- **Highly Customizable:** Manage everything via **Tools > Search Overlay** in the WordPress dashboard.
- **Mobile First:** Features fluid typography using CSS `clamp()` to ensure readability on all devices.

## 🛠 Installation

1. Upload the `apss-search-overlay` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure your desired post types and labels under **Tools > Search Overlay**.

## 📖 Usage

### Using the Shortcode
Place the following shortcode in your header, footer, or any page content:
```text
[apss_search_trigger]
```

### Using a Custom Trigger
Add the CSS class `apss-trigger` to any HTML element (button, link, image) to open the search overlay:
```html
<a href="#" class="apss-trigger">Open Search</a>
```

## ⚙️ Configuration

The plugin provides a comprehensive settings page under **Tools > Search Overlay**:

- **Searchable Post Types:** Select which public post types should be included in search results.
- **Post Type Labels:** Customize the section headings for each post type (e.g., rename "product" to "Our Products").
- **Overlay Title:** Set a custom main heading for the search interface.
- **Search Input Placeholder:** Define the ghost text shown in the search field.
- **Typography & Layout:** Adjust the input font size and the maximum width of the results container (up to 1800px).
- **Icon Visibility:** Toggle the default magnifying glass icon for the shortcode trigger.

## 🏗 Technical Architecture

The plugin is built with a modern, modular PHP architecture:

- **Namespace:** `APSS_Search`
- **Autoloader:** PSR-4 compliant autoloader for class management.
- **Singleton Pattern:** Used for the main plugin bootstrap class.
- **REST API:** Custom endpoint at `wp-json/apss/v1/search` for secure, non-blocking data retrieval.
- **Z-Index Management:** The overlay uses `z-index: 999`. It is recommended to set your site header to `z-index: 1000` to keep it visible above the search results.

## 🔒 Security

- **Input Sanitization:** All queries are sanitized using `sanitize_text_field()`.
- **Output Escaping:** Dynamic data is escaped using `esc_html()` and `esc_url()` before rendering.
- **Nonce Validation:** REST API requests are authenticated using `X-WP-Nonce` headers.
- **Direct Access Prevention:** Security checks ensure no direct file access is allowed.

---
*Developed for All Pumps Portfolio Website.*
