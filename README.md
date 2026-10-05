# FlowDesk Pro

FlowDesk Pro is a custom WordPress CRM and Lead Management plugin built to capture, organize, track, and manage website leads directly inside the WordPress dashboard.

## Version

1.0.0
## Features

- Custom Lead Management System
- Frontend Lead Capture Form
- AJAX Form Submission
- Custom Lead Statuses
- Drag-and-Drop Kanban Pipeline
- Follow-up Date Management
- Internal Notes
- Activity History
- Lead Search and Filters
- Custom Admin Dashboard
- Plugin Settings
- CSV Export
- Email Notification Support
- Spam Protection with Honeypot
- Basic Rate Limiting
- WordPress Security Checks
- Responsive Admin Interface


## Installation

1. Download or clone the `flowdesk-pro` plugin folder.
2. Upload it to:

   `wp-content/plugins/`

3. Go to WordPress Dashboard → Plugins.
4. Activate **FlowDesk Pro**.
5. Open or create any WordPress page.
6. Add the shortcode:

   `[flowdesk_form]`

7. Publish the page.

The frontend lead form will now appear on that page.

## Shortcode

Use this shortcode anywhere shortcode output is supported:

```text
[flowdesk_form]

## Lead Workflow

FlowDesk Pro helps manage a lead through a simple sales pipeline:

```text
New
↓
Contacted
↓
Qualified
↓
Won / Lost

## Security

FlowDesk Pro follows common WordPress security practices, including:

- Nonce verification
- User capability checks
- Input sanitization
- Output escaping
- Allowed status validation
- Honeypot spam protection
- Basic rate limiting
- Protected AJAX requests
- CSV export permission checks

## Project Structure

```text
flowdesk-pro/
│
├── flowdesk-pro.php
│
├── admin/
│   ├── admin-menu.php
│   ├── dashboard-page.php
│   ├── pipeline-page.php
│   └── settings.php
│
├── includes/
│   ├── activity.php
│   ├── ajax.php
│   ├── export.php
│   ├── lead-columns.php
│   ├── lead-meta-boxes.php
│   ├── lead-notes.php
│   └── lead-post-type.php
│
├── public/
│   └── form.php
│
└── assets/
    ├── css/
    │   ├── admin-style.css
    │   └── style.css
    └── js/
        ├── form.js
        └── kanban.js

        ## Author

Saif

## License

GPL v2 or later

## Project Status

Stable / Completed

Current Version: `1.0.0`