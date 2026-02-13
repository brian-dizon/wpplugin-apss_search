# Project Log: APSS Search Overlay

## Project Overview
A high-performance, secure, and professional live search overlay for WordPress, designed for the All Pumps portfolio website.

---

## 2026-02-13: Initial Development & Architecture Refactor

### 1. Plugin Foundation
- Created the main plugin boilerplate with security checks.
- Registered the `[apss_search_trigger]` shortcode to render the search icon.
- Implemented conditional asset loading to ensure CSS/JS only load when needed.

### 2. Secure Backend (REST API)
- Registered a custom REST API endpoint: `wp-json/apss/v1/search`.
- Integrated `WP_Query` to search across `post`, `page`, `portfolio`, and `product` types.
- **Security:** Implemented `sanitize_text_field()` for inputs and `esc_html()`/`esc_url()` for outputs.
- **Performance:** Added 60-minute transient caching based on MD5 hashes of search terms.

### 3. Frontend UI & UX
- Created a full-screen overlay with a `98%` white opacity and `8px` backdrop blur.
- Implemented a responsive 3-column grid for desktop and 1-column for mobile.
- **Improvements:** Adjusted top padding (`15vh`) and title margins to ensure clearance from the site header.

### 4. Frontend Logic (JavaScript)
- **Debouncing:** Implemented a 300ms delay to prevent excessive API calls.
- **Request Cancellation:** Utilized the `AbortController` API to cancel pending fetch requests when a user continues typing.
- **Keyboard Support:** Added `ESC` key functionality to close the overlay and auto-focus on the search input upon opening.

### 5. OOP Refactor & Namespacing
- **Architecture:** Refactored procedural code into a modular OOP structure.
- **Namespacing:** Implemented the `APSS_Search` namespace to prevent collisions.
- **Autoloading:** Developed a PSR-4 style autoloader to automatically manage class loading from the `includes/` directory.
- **Components:**
    - `API`: Handles REST routes and search logic.
    - `Assets`: Manages script and style registration.
    - `UI`: Manages shortcodes and template rendering.
    - `Settings`: Manages plugin configuration via WordPress Tools menu.
    - `Plugin`: Main bootstrap class using the Singleton pattern.

## 2026-02-13: Settings API & Customization

### 1. Settings Page Implementation
- Added a new submenu page under **Tools > Search Overlay**.
- Implemented the WordPress Settings API for secure data management.
- Features added to the settings page:
    - **Searchable Post Types**: Multi-checkbox selection for public post types.
    - **Overlay Title**: Custom text field for the search interface title.
    - **Show Overlay Title**: Toggle to display or hide the search overlay title.
    - **Search Input Placeholder**: Customizable placeholder text for the search input.
    - **Search Input Font Size**: Adjustable font size (px) with a fluid `clamp()` (min 30px) for mobile responsiveness.
    - **Show Default Icon**: Toggle for the [apss_search_trigger] SVG icon.
    - **Results Max Width**: Added setting to control the container width (up to 1800px).
    - **Post Type Labels**: Added customizable section titles for each post type.
    - **Usage Guide**: Added an in-plugin "How to Use" section with shortcode and class instructions.

## 2026-02-13: Sectioned Search Results

### 1. Grouped Search API
- Refactored the `API` class to return results grouped by `post_type`.
- The frontend now receives an associative array rather than a flat list.

### 2. Custom Labels & UI
- **Settings**: Implemented a new "Post Type Labels" section to allow admins to override default post type names (e.g., "Products" instead of "product").
- **Card UI**: Refactored search results into horizontal cards with square thumbnails, line-clamped titles/excerpts, and meta information (date with icon).
- **Responsive Grid**: Updated grid system to support 4 columns (1200px+), 2 columns (600px+), and 1 column (mobile).
- **JavaScript**: Updated rendering logic to loop through grouped results and inject section headers with customizable titles.
- **CSS**: Added styling for `.apss-section-header`, including uppercase typography and a subtle visual separator (`hr`).

### 2. Integration
- **API**: Updated search query to dynamically fetch allowed post types from settings.
- **UI**: Updated shortcode and overlay template to respect user-defined title and icon visibility.
- **Autoloader**: Successfully handles the new `Settings` class.
