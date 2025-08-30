# Overview

LimpaBrasil is a professional cleaning services website built as a static frontend application. The site provides information about cleaning services, allows customers to view service offerings, learn about the company, and contact them for scheduling. The website is designed to be a marketing and lead generation tool for a cleaning business, with pages for services, about, contact, and scheduling.

# User Preferences

Preferred communication style: Simple, everyday language.

# System Architecture

## Frontend Architecture
The application uses a **static HTML/CSS/JavaScript architecture** with the following design decisions:

- **Multi-page structure**: Separate HTML files for each main section (index, services, about, contact, scheduling)
- **Tailwind CSS framework**: Chosen for rapid UI development and consistent styling with custom color schemes
- **Component-based styling**: Custom CSS animations and Tailwind configuration for brand consistency
- **Responsive design**: Mobile-first approach using Tailwind's responsive utilities

## Styling and UI Framework
- **Tailwind CSS via CDN**: Provides utility-first CSS framework for quick development
- **Custom color palette**: Primary (blue tones) and secondary (gray tones) color systems defined in Tailwind config
- **Feather Icons**: Lightweight icon library for consistent iconography
- **Custom CSS animations**: Fade-in effects and smooth transitions for enhanced user experience

## JavaScript Architecture
- **Vanilla JavaScript**: No heavy frameworks, keeping the site lightweight and fast
- **Modular approach**: Separate JS files for main functionality (`main.js`) and admin features (`admin.js`)
- **Progressive enhancement**: Core functionality works without JavaScript, enhanced with JS features
- **Event-driven interactions**: Mobile menu toggles, form validations, and scroll animations

## File Organization
```
/
├── *.html (main pages)
├── assets/
│   ├── css/
│   │   └── style.css (custom styles)
│   └── js/
│       ├── main.js (main functionality)
│       └── admin.js (admin features)
```

## Key Features
- **Mobile-responsive navigation** with hamburger menu
- **Smooth scrolling** between page sections
- **Form validation** for contact and scheduling forms
- **Scroll-triggered animations** for improved user engagement
- **Admin functionality** for data management (tables, charts, exports)

# External Dependencies

## CDN-based Libraries
- **Tailwind CSS** (via cdn.tailwindcss.com): Primary CSS framework for styling and responsive design
- **Feather Icons** (via unpkg.com): Icon library for consistent visual elements throughout the site

## Third-party Services
The current implementation appears to be a frontend-only solution without backend integrations. Future implementations may require:
- **Form handling service** for contact and scheduling submissions
- **Email service integration** for automated responses
- **Analytics platform** for tracking user interactions
- **Hosting service** for static file deployment

## Browser Dependencies
- **Modern JavaScript support**: ES6+ features used in main.js and admin.js
- **CSS Grid and Flexbox**: For layout systems via Tailwind
- **Local Storage**: Potentially used for admin panel data persistence