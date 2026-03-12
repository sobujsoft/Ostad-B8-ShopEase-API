# Product Requirements Document (PRD)

## ShopEase — Simple Ecommerce Platform

| Field                | Detail                             |
| -------------------- | ---------------------------------- |
| **Document Version** | 1.0                                |
| **Created**          | March 3, 2026                      |
| **Author**           | Project Management Office          |
| **Status**           | Draft — Pending Stakeholder Review |
| **Classification**   | Internal / Confidential            |

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Project Overview](#2-project-overview)
3. [Goals & Objectives](#3-goals--objectives)
4. [System Architecture](#4-system-architecture)
5. [User Personas](#5-user-personas)
6. [Feature Requirements — Storefront](#6-feature-requirements--storefront)
7. [Feature Requirements — Admin Panel](#7-feature-requirements--admin-panel)
8. [Feature Requirements — API Layer](#8-feature-requirements--api-layer)
9. [Non-Functional Requirements](#9-non-functional-requirements)
10. [Database Schema (High-Level)](#10-database-schema-high-level)
11. [API Endpoint Inventory](#11-api-endpoint-inventory)
12. [UI/UX Wireframe Descriptions](#12-uiux-wireframe-descriptions)
13. [Payment Gateway Integration](#13-payment-gateway-integration)
14. [Reporting & Analytics](#14-reporting--analytics)
15. [Milestones & Release Plan](#15-milestones--release-plan)
16. [Risks & Mitigations](#16-risks--mitigations)
17. [Acceptance Criteria](#17-acceptance-criteria)
18. [Glossary](#18-glossary)

---

## 1. Executive Summary

ShopEase is a lightweight, professionally designed ecommerce platform composed of **three independent projects**:

| Project         | Role                             | Stack                        |
| --------------- | -------------------------------- | ---------------------------- |
| **API**         | Central RESTful backend          | PHP 8.2+ / Laravel 12        |
| **Storefront**  | Customer-facing online store     | Vue 3 + Inertia.js + Laravel |
| **Admin Panel** | Back-office management dashboard | Vue 3 + Inertia.js + Laravel |

The Storefront and Admin Panel each run as separate Laravel + Inertia.js applications responsible for **server-side page rendering** (via Inertia), while all data operations (CRUD, search, reporting) are handled through the central **API project** using Axios HTTP calls.

The platform targets small-to-medium businesses that need a clean, fast, and easy-to-manage online store without the overhead of complex inventory management.

---

## 2. Project Overview

### 2.1 Problem Statement

Small businesses need an affordable, easy-to-operate ecommerce presence without the complexity of enterprise-grade platforms. Existing solutions are either too bloated or too rigid for simple catalog-based selling.

### 2.2 Proposed Solution

Build a clean, modular ecommerce system with:

- A polished, conversion-optimized **storefront** for customers.
- A powerful yet intuitive **admin panel** for business operators.
- A decoupled **REST API** enabling future scalability (mobile apps, third-party integrations).

### 2.3 Scope

**In Scope:**

- Product catalog with categories, variants, and images.
- Simple stock status management (In Stock / Out of Stock — no inventory counting).
- Shopping cart, checkout, and payment gateway integration.
- Customer accounts and order history.
- Admin-managed curated sections (Best Sellers, New Arrivals, etc.).
- Order lifecycle management with invoice generation.
- Basic business reporting and dashboard analytics.

**Out of Scope (v1):**

- Multi-vendor / marketplace functionality.
- Warehouse / inventory quantity management.
- Wishlist, reviews, and ratings.
- Multi-language / multi-currency support.
- Coupon / voucher system.
- Shipping carrier API integrations.
- Mobile applications (API-ready for future).

---

## 3. Goals & Objectives

| #   | Objective                                | Success Metric                                 |
| --- | ---------------------------------------- | ---------------------------------------------- |
| G1  | Launch a fully functional storefront     | All storefront pages live and tested           |
| G2  | Enable end-to-end purchase flow          | Customer can browse → add to cart → pay        |
| G3  | Provide admin product & order management | Admin can CRUD products and manage orders      |
| G4  | Deliver curated homepage sections        | Admin can assign products to featured sections |
| G5  | Generate basic business insights         | Dashboard shows revenue, orders, top products  |
| G6  | Maintain clean separation of concerns    | Three independent deployable projects          |

---

## 4. System Architecture

### 4.1 High-Level Architecture Diagram

```
┌─────────────────────┐     ┌─────────────────────┐
│                     │     │                     │
│   STOREFRONT APP    │     │   ADMIN PANEL APP   │
│                     │     │                     │
│  Laravel + Vue 3    │     │  Laravel + Vue 3    │
│  + Inertia.js       │     │  + Inertia.js       │
│                     │     │                     │
│  (Page Rendering)   │     │  (Page Rendering)   │
│                     │     │                     │
└────────┬────────────┘     └────────┬────────────┘
         │  Axios                    │  Axios
         │  HTTP Calls               │  HTTP Calls
         ▼                           ▼
┌─────────────────────────────────────────────────┐
│                                                 │
│              CENTRAL REST API                   │
│                                                 │
│            PHP 8.2+ / Laravel 12                │
│                                                 │
│  ┌───────────┐ ┌──────────┐ ┌────────────────┐ │
│  │ Auth      │ │ Products │ │ Orders         │ │
│  │ Module    │ │ Module   │ │ Module         │ │
│  └───────────┘ └──────────┘ └────────────────┘ │
│  ┌───────────┐ ┌──────────┐ ┌────────────────┐ │
│  │ Category  │ │ Cart     │ │ Reporting      │ │
│  │ Module    │ │ Module   │ │ Module         │ │
│  └───────────┘ └──────────┘ └────────────────┘ │
│                                                 │
└──────────────────┬──────────────────────────────┘
                   │
                   ▼
          ┌────────────────┐     ┌────────────────┐
          │   MySQL 8.0    │     │  File Storage   │
          │   Database     │     │  (Local)        │
          └────────────────┘     └────────────────┘
```

### 4.2 Technology Stack

| Layer              | Technology                                          |
| ------------------ | --------------------------------------------------- |
| **Backend API**    | PHP 8.2+, Laravel 12, Laravel Sanctum               |
| **Storefront**     | Laravel 12, Vue 3 (Composition API), Inertia.js 2.x |
| **Admin Panel**    | Laravel 12, Vue 3 (Composition API), Inertia.js 2.x |
| **CSS Framework**  | Tailwind CSS 3.x                                    |
| **Database**       | MySQL 8.0                                           |
| **Authentication** | Laravel Sanctum (Token-based for API)               |
| **Payment**        | SSLCommerz (configurable)                           |
| **File Storage**   | Laravel Filesystem (local disk)                     |
| **HTTP Client**    | Axios                                               |
| **Build Tool**     | Vite                                                |
| **PDF Generation** | DomPDF                                              |

### 4.3 Communication Pattern

```
Inertia.js → Responsible for server-side page rendering and initial page data
Axios      → Responsible for all subsequent data operations (CRUD, search, filters, cart, etc.)
```

- Each frontend app (Storefront & Admin) uses **Inertia.js** to render Vue pages through Laravel routes.
- Once a page is loaded, all dynamic data fetching, form submissions, and state mutations go through the **central API** via **Axios** HTTP requests.
- The API authenticates requests using **Laravel Sanctum** tokens.

### 4.4 Project Repository Structure

```
shopease/
├── shopease-api/            # Central REST API (Laravel)
│   ├── app/
│   ├── routes/api.php
│   ├── database/
│   └── ...
│
├── shopease-storefront/     # Customer-facing store (Laravel + Vue + Inertia)
│   ├── app/
│   ├── resources/js/        # Vue 3 components & pages
│   ├── routes/web.php       # Inertia page routes
│   └── ...
│
└── shopease-admin/          # Admin panel (Laravel + Vue + Inertia)
    ├── app/
    ├── resources/js/        # Vue 3 components & pages
    ├── routes/web.php       # Inertia page routes
    └── ...
```

---

## 5. User Personas

### 5.1 Customer (End User)

| Attribute      | Detail                                                      |
| -------------- | ----------------------------------------------------------- |
| **Name**       | Sarah, 28                                                   |
| **Role**       | Online Shopper                                              |
| **Goal**       | Browse products, compare variants, purchase quickly         |
| **Pain Point** | Cluttered UIs, slow checkout, lack of product information   |
| **Behavior**   | Primarily mobile, expects fast load times and clear pricing |

### 5.2 Business Admin

| Attribute      | Detail                                                          |
| -------------- | --------------------------------------------------------------- |
| **Name**       | Rahim, 35                                                       |
| **Role**       | Store Owner / Manager                                           |
| **Goal**       | Manage catalog, fulfill orders, understand sales performance    |
| **Pain Point** | Overly complex dashboards, manual invoice creation              |
| **Behavior**   | Desktop-first, needs quick access to orders and product updates |

---

## 6. Feature Requirements — Storefront

### 6.1 Homepage

| ID     | Feature              | Description                                                                                    | Priority |
| ------ | -------------------- | ---------------------------------------------------------------------------------------------- | -------- |
| SF-001 | Hero Banner Section  | Full-width hero banner with image, headline text, CTA button. Admin-configurable.              | P0       |
| SF-002 | Categories Section   | Horizontally scrollable category cards with image and name. Clicking navigates to shop filter. | P0       |
| SF-003 | Best Sellers Section | Product card grid showing admin-curated "Best Seller" products (max 8).                        | P0       |
| SF-004 | New Arrivals Section | Product card grid showing admin-curated "New Arrival" products (max 8).                        | P0       |
| SF-005 | Footer               | Standard footer with company info, quick links, contact details, social media icons.           | P0       |
| SF-006 | Header / Navbar      | Logo, navigation links (Home, Shop, Cart icon with badge, Profile/Login).                      | P0       |

**Product Card Specification:**

Each product card displays:
- Product thumbnail image
- Product name
- Price (with discount price struck-through if applicable)
- "Add to Cart" quick action button

### 6.2 Shop Page

| ID     | Feature                | Description                                                                                      | Priority |
| ------ | ---------------------- | ------------------------------------------------------------------------------------------------ | -------- |
| SF-010 | Product Grid           | Responsive grid displaying product cards with onscroll load  (12 per page).                      | P0       |
| SF-011 | Category Filter        | Sidebar or dropdown filter to filter products by category.                                       | P0       |
| SF-012 | Search by Name/Code    | Search input field that filters products by name or product code. Debounced (300ms) live search. | P0       |
| SF-013 | Sort Options           | Dropdown sort selector with options: Name (A→Z), Name (Z→A), Price (Low→High), Price (High→Low). | P0       |
| SF-014 | Active Filters Display | Show active filter tags with "×" remove option. "Clear All" button.                              | P1       |
| SF-015 | No Results State       | Friendly empty state with illustration when no products match filters.                           | P1       |

**Sort Options Enum:**

```
name_asc      → Name (A → Z)
name_desc     → Name (Z → A)
price_asc     → Price (Low → High)
price_desc    → Price (High → Low)
newest        → Newest First
```

### 6.3 Product Details Page

| ID     | Feature             | Description                                                                                | Priority |
| ------ | ------------------- | ------------------------------------------------------------------------------------------ | -------- |
| SF-020 | Image Gallery       | Multiple product images with thumbnail strip. Main image zoom on hover. Lightbox on click. | P0       |
| SF-021 | Product Information | Product name, code/SKU, price, discount price (with % off badge), short description.       | P0       |
| SF-022 | Variant Selection   | Dropdown  selectors for each variant type (e.g., Size, Color).                             | P0       |
| SF-023 | Quantity Selector   | Numeric stepper input (min: 1, max: configurable or 10 default). Validates stock status.   | P0       |
| SF-024 | Add to Cart Button  | Prominent CTA button. Disabled state when out of stock. Shows success toast on action.     | P0       |
| SF-025 | Buy Now Button      | Secondary CTA. Adds to cart and redirects immediately to checkout.                         | P0       |
| SF-026 | Stock Status Badge  | Displays "In Stock" (green) or "Out of Stock" (red) badge. Out of stock disables purchase. | P0       |
| SF-027 | Product Description | Full product description rendered below the fold (supports rich text/HTML).                | P0       |
| SF-028 | Related Products    | "You may also like" section showing 4 products from the same category.                     | P1       |

**Price Display Logic:**

```
If discount_price exists and discount_price < price:
    Show discount_price as current price (bold, colored)
    Show original price with strikethrough
    Show discount percentage badge: round((price - discount_price) / price * 100) + "% OFF"
Else:
    Show price normally
```

### 6.4 Shopping Cart

The shopping cart will be simple. A cart icon with a number badge will be displayed in the navbar, and when the user clicks on it, they will be redirected directly to the checkout page.

### 6.5 Checkout Page

| ID     | Feature               | Description                                                                                   | Priority |
| ------ | --------------------- | --------------------------------------------------------------------------------------------- | -------- |
| SF-040 | Shipping Information  | Form: Full Name, Email, Phone, Address Line, Upozila, District                                | P0       |
| SF-041 | Order Summary         | Editable summary of cart items, quantities, and prices. User can remove an item or change qty | P0       |
| SF-042 | Payment Method Select | Radio selection for available payment methods (e.g., SSL COmmerx, Cash on Delivery).          | P0       |
| SF-043 | Place Order Button    | Final CTA. Validates all fields, creates order, initiates payment.                            | P0       |
| SF-044 | Order Confirmation    | Success page with order number, summary, and estimated delivery info.                         | P0       |


**Checkout Flow:**

```
Cart Page → Checkout Page → Payment Processing → Order Confirmation Page
                                    │
                                    ├── SSL COmmerz → Redirect to SSL Commerz Checkout → IPN confirms
                                    └── COD → Order placed immediately with "Pending" status
```

### 6.6 Customer Authentication & Profile

| ID     | Feature                 | Description                                                                                    | Priority |
| ------ | ----------------------- | ---------------------------------------------------------------------------------------------- | -------- |
| SF-050 | Registration            | Sign-up form: Name, Email, Password, Confirm Password.                                         | P0       |
| SF-051 | Login                   | Email + Password login. "Remember Me" option. "Forgot Password" link.                          | P0       |
| SF-052 | Forgot / Reset Password | Email-based password reset flow with secure token link.                                        | P0       |
| SF-053 | Profile Dashboard       | Overview: Name, Email, Total Orders, Active Orders count.                                      | P0       |
| SF-054 | Profile Edit            | Update Name, Email, Phone, Password. Avatar upload optional.                                   | P0       |
| SF-055 | Order History           | Paginated list of past orders with: Order #, Date, Status badge, Total.                        | P0       |
| SF-056 | Order Detail View       | Detailed view of a single order with items, shipping address, payment method, status timeline. | P0       |


---

## 7. Feature Requirements — Admin Panel

### 7.1 Dashboard

| ID     | Feature              | Description                                                                                 | Priority |
| ------ | -------------------- | ------------------------------------------------------------------------------------------- | -------- |
| AP-001 | Revenue Overview     | Total revenue: Today, This Week, This Month, All Time.                                      | P0       |
| AP-002 | Order Statistics     | Total Orders, Pending Orders, Completed Orders, Cancelled Orders — as stat cards.           | P0       |
| AP-003 | Recent Orders Table  | Last 10 orders with: Order #, Customer Name, Total, Status, Date. Quick link to detail.     | P0       |
| AP-004 | Top Selling Products | Top 5 products by order count in current month. Product name + units sold.                  | P0       |
| AP-005 | Revenue Chart        | Line/bar chart showing daily revenue for the current month and need a filter for month wise | P1       |
| AP-006 | Order Status Chart   | Pie/donut chart showing distribution of order statuses.                                     | P1       |

### 7.2 Category Management

| ID     | Feature          | Description                                                                                      | Priority |
| ------ | ---------------- | ------------------------------------------------------------------------------------------------ | -------- |
| AP-010 | Category Listing | Paginated table: Thumbnail, Name, Slug, Product Count, Status, position,Actions (Edit/Delete).   | P0       |
| AP-011 | Create Category  | Form: Name, Slug (auto-generated), Image Upload, Description, Status ,position(Active/Inactive). | P0       |
| AP-012 | Edit Category    | Pre-filled form with existing data. Image preview with replace option.                           | P0       |
| AP-013 | Delete Category  | Soft delete with confirmation modal. Prevents deletion if products are assigned.                 | P0       |
| AP-014 | Category Image   | Single image upload per category. Recommended size displayed.                                    | P0       |

### 7.3 Product Management

| ID     | Feature                | Description                                                                                 | Priority |
| ------ | ---------------------- | ------------------------------------------------------------------------------------------- | -------- |
| AP-020 | Product Listing        | Paginated table: Thumbnail, Name, Code, Category, Price, Stock Status, Active, Actions.     | P0       |
| AP-021 | Product Filters        | Filter by: Category, Stock Status (In Stock/Out of Stock), Active Status (Active/Inactive). | P0       |
| AP-022 | Product Search         | Search by product name or code.                                                             | P0       |
| AP-023 | Create Product         | Detailed form (see Product Form Specification below).                                       | P0       |
| AP-024 | Edit Product           | Pre-filled form with all existing data. Image management with drag-to-reorder.              | P0       |
| AP-025 | Delete Product         | Delete with confirmation. Warns if product has active orders.                               | P0       |
| AP-026 | Toggle Active/Inactive | Quick toggle switch in listing table. Inactive products hidden from storefront.             | P0       |
| AP-027 | Stock Status Toggle    | Quick toggle: "In Stock" ↔ "Out of Stock" directly from listing.                            | P0       |

**Product Form Specification:**

```
Basic Information:
  - Product Name *            (text, max 255)
  - Product Code / SKU *      (text, unique, max 50)
  - Category *                (dropdown, single select)
  - Short Description         (textarea, max 500)
  - Full Description          (rich text editor)

Pricing:
  - Price *                   (decimal, min 0)
  - Discount Price            (decimal, must be < Price)

Media:
  - Product Images *          (multiple upload, drag to reorder, min 1, max 10)
  - Primary Image             (first image or manually selected)

Variants:
  - Variant             (fix "Size", "Color")
    - Variant Options          (user will input e.g., "S", "M", "L", "XL" or "Red", "Blue")

Status:
  - Active / Inactive *       (toggle, default: Active)
  - Stock Status *            (dropdown: "In Stock" / "Out of Stock", default: In Stock)

SEO (Optional):
  - Meta Title                (text, max 70)
  - Meta Description          (textarea, max 160)
  - URL Slug                  (auto-generated from name, editable)
```

### 7.4 Curated Section Management

| ID     | Feature                      | Description                                                                       | Priority |
| ------ | ---------------------------- | --------------------------------------------------------------------------------- | -------- |
| AP-030 | Section Configuration Page   | Manage homepage sections: "Best Sellers", "New Arrivals", and custom sections.    | P0       |
| AP-031 | Product Selector for Section | Searchable dropdown/modal to select products for each section. Max 8 per section. | P0       |
| AP-033 | Remove from Section          | Remove a product from a curated section without deleting the product.             | P0       |

**Section Types:**

| Section Key    | Display Name | Max Products | Description                    |
| -------------- | ------------ | ------------ | ------------------------------ |
| `best_sellers` | Best Sellers | 8            | Admin-picked top-selling items |
| `new_arrivals` | New Arrivals | 8            | Admin-picked newest items      |

### 7.5 Order Management

| ID     | Feature              | Description                                                                                    | Priority |
| ------ | -------------------- | ---------------------------------------------------------------------------------------------- | -------- |
| AP-040 | Order Listing        | Paginated table: Order #, Customer, Items Count, Total, Payment Method, Status, Date, Actions. | P0       |
| AP-041 | Order Filters        | Filter by: Status, Date Range, Payment Method.                                                 | P0       |
| AP-042 | Order Search         | Search by Order # or Customer Name/Email.                                                      | P0       |
| AP-043 | Order Detail View    | Full order detail: Customer info, shipping address, items list, pricing breakdown, status log. | P0       |
| AP-044 | Change Order Status  | Dropdown to update status with options defined below. Status change logged with timestamp.     | P0       |
| AP-045 | Print Invoice        | Generate and print browser-friendly invoice layout. Opens in new tab for printing.             | P0       |
| AP-046 | Download Invoice PDF | Generate and download invoice as PDF file.                                                     | P0       |
| AP-047 | Order Notes          | Internal notes field for admin remarks per order. Not visible to customer.                     | P1       |

**Order Status Flow:**

```
Pending → Confirmed → Processing → Shipped → Delivered
                                           → Cancelled (from any status except Delivered)
                                           → Returned (only from Delivered)
```

| Status       | Color  | Description                         |
| ------------ | ------ | ----------------------------------- |
| `pending`    | Yellow | Order placed, awaiting confirmation |
| `confirmed`  | Blue   | Order confirmed by admin            |
| `processing` | Orange | Order being prepared                |
| `shipped`    | Purple | Order handed to delivery            |
| `delivered`  | Green  | Order successfully delivered        |
| `cancelled`  | Red    | Order cancelled                     |
| `returned`   | Gray   | Order returned after delivery       |

**Invoice Template Fields:**

```
Header:
  - Company Logo, Name, Address, Phone, Email
  - Invoice Number (= Order Number)
  - Invoice Date
  - Due Date (= Order Date for prepaid, N/A for COD)

Customer:
  - Name, Email, Phone
  - Shipping Address

Items Table:
  - #, Product Name, Variant, Code, Qty, Unit Price, Total

Summary:
  - Subtotal
  - Shipping Fee
  - Discount (if any)
  - Grand Total
  - Payment Method
  - Payment Status

Footer:
  - Thank you note
  - Terms & conditions (optional)
```

### 7.6 Reports

| ID     | Feature              | Description                                                                                | Priority |
| ------ | -------------------- | ------------------------------------------------------------------------------------------ | -------- |
| AP-050 | Sales Report         | Revenue by date range with daily/weekly/monthly breakdown. Exportable to CSV.              | P0       |
| AP-051 | Order Report         | Order count and status distribution by date range. Tabular + chart view.                   | P0       |
| AP-052 | Top Products Report  | Top products by revenue and by order count. Configurable date range and limit (Top 10/20). | P1       |
| AP-053 | Category Performance | Revenue and order count per category. Bar chart comparison.                                | P1       |

### 7.7 Settings (Basic)

| ID     | Feature               | Description                                                              | Priority |
| ------ | --------------------- | ------------------------------------------------------------------------ | -------- |
| AP-060 | Store Settings        | Store Name, Logo, Address, Phone, Email,                                 | P0       |
| AP-061 | Banner Management     | Upload/replace hero banner image. Set headline and CTA text/URL.         | P0       |
| AP-062 | Shipping Settings     | Flat-rate shipping fee. Free shipping threshold (orders above X amount). | P0       |
| AP-063 | Payment Configuration | Enable/disable payment methods. API keys for Stripe/gateway.             | P0       |
| AP-064 | Admin Profile         | Update admin name, email, password.                                      | P0       |

---

## 8. Feature Requirements — API Layer

### 8.1 Authentication Endpoints

| Method | Endpoint                    | Description                   | Auth     |
| ------ | --------------------------- | ----------------------------- | -------- |
| POST   | `/api/auth/register`        | Customer registration         | Public   |
| POST   | `/api/auth/login`           | Login (returns Sanctum token) | Public   |
| POST   | `/api/auth/logout`          | Revoke current token          | Customer |
| POST   | `/api/auth/forgot-password` | Send reset email              | Public   |
| POST   | `/api/auth/reset-password`  | Reset password with token     | Public   |
| GET    | `/api/auth/me`              | Get authenticated user        | Customer |
| POST   | `/api/admin/auth/login`     | Admin login                   | Public   |

### 8.2 Storefront API Endpoints

| Method | Endpoint                          | Description                                  | Auth   |
| ------ | --------------------------------- | -------------------------------------------- | ------ |
| GET    | `/api/storefront/home`            | Homepage data (banner, sections, categories) | Public |
| GET    | `/api/storefront/categories`      | All active categories                        | Public |
| GET    | `/api/storefront/products`        | Product listing with filters & sorting       | Public |
| GET    | `/api/storefront/products/{slug}` | Product detail                               | Public |
| GET    | `/api/storefront/sections/{key}`  | Products for a curated section               | Public |

### 8.3 Cart & Checkout API Endpoints

| Method | Endpoint                | Description                | Auth     |
| ------ | ----------------------- | -------------------------- | -------- |
| GET    | `/api/cart`             | Get current cart           | Customer |
| POST   | `/api/cart/items`       | Add item to cart           | Customer |
| PUT    | `/api/cart/items/{id}`  | Update cart item quantity  | Customer |
| DELETE | `/api/cart/items/{id}`  | Remove item from cart      | Customer |
| DELETE | `/api/cart`             | Clear entire cart          | Customer |
| POST   | `/api/checkout`         | Place order                | Customer |
| POST   | `/api/checkout/payment` | Initiate payment for order | Customer |
| POST   | `/api/webhooks/stripe`  | Stripe payment webhook     | Public   |

### 8.4 Customer API Endpoints

| Method | Endpoint                          | Description                    | Auth     |
| ------ | --------------------------------- | ------------------------------ | -------- |
| GET    | `/api/customer/profile`           | Get customer profile           | Customer |
| PUT    | `/api/customer/profile`           | Update profile                 | Customer |
| PUT    | `/api/customer/password`          | Change password                | Customer |
| GET    | `/api/customer/orders`            | Order history (paginated)      | Customer |
| GET    | `/api/customer/orders/{id}`       | Order detail                   | Customer |
| GET    | `/api/customer/addresses`         | Saved addresses                | Customer |
| POST   | `/api/customer/addresses`         | Add address                    | Customer |
| PUT    | `/api/customer/addresses/{id}`    | Update address                 | Customer |
| DELETE | `/api/customer/addresses/{id}`    | Delete address                 | Customer |

### 8.5 Admin API Endpoints

| Method | Endpoint                                   | Description                          | Auth  |
| ------ | ------------------------------------------ | ------------------------------------ | ----- |
| GET    | `/api/admin/dashboard`                     | Dashboard statistics                 | Admin |
| GET    | `/api/admin/categories`                    | List categories (paginated)          | Admin |
| POST   | `/api/admin/categories`                    | Create category                      | Admin |
| PUT    | `/api/admin/categories/{id}`               | Update category                      | Admin |
| DELETE | `/api/admin/categories/{id}`               | Delete category                      | Admin |
| GET    | `/api/admin/products`                      | List products (paginated, filterable)| Admin |
| POST   | `/api/admin/products`                      | Create product                       | Admin |
| GET    | `/api/admin/products/{id}`                 | Get product detail                   | Admin |
| PUT    | `/api/admin/products/{id}`                 | Update product                       | Admin |
| DELETE | `/api/admin/products/{id}`                 | Delete product (soft)                | Admin |
| PATCH  | `/api/admin/products/{id}/toggle-active`   | Toggle active status                 | Admin |
| PATCH  | `/api/admin/products/{id}/toggle-stock`    | Toggle stock status                  | Admin |
| GET    | `/api/admin/sections`                      | List all sections with products      | Admin |
| PUT    | `/api/admin/sections/{key}`                | Update section products              | Admin |
| GET    | `/api/admin/orders`                        | List orders (paginated, filterable)  | Admin |
| GET    | `/api/admin/orders/{id}`                   | Order detail                         | Admin |
| PATCH  | `/api/admin/orders/{id}/status`            | Update order status                  | Admin |
| GET    | `/api/admin/orders/{id}/invoice`           | Generate invoice PDF                 | Admin |
| GET    | `/api/admin/reports/sales`                 | Sales report data                    | Admin |
| GET    | `/api/admin/reports/orders`                | Order report data                    | Admin |
| GET    | `/api/admin/reports/top-products`          | Top products report                  | Admin |
| GET    | `/api/admin/reports/categories`            | Category performance report          | Admin |
| GET    | `/api/admin/settings`                      | Get store settings                   | Admin |
| PUT    | `/api/admin/settings`                      | Update store settings                | Admin |

### 8.6 API Response Standards

**Success Response:**

```json
{
  "success": true,
  "message": "Products retrieved successfully.",
  "data": { ... },
  "meta": {
    "current_page": 1,
    "per_page": 12,
    "total": 48,
    "last_page": 4
  }
}
```

**Error Response:**

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price must be a positive number."]
  }
}
```

**HTTP Status Codes Used:**

| Code | Usage                                   |
| ---- | --------------------------------------- |
| 200  | Success                                 |
| 201  | Resource created                        |
| 204  | Deleted successfully (no content)       |
| 400  | Bad request                             |
| 401  | Unauthenticated                         |
| 403  | Unauthorized (insufficient permissions) |
| 404  | Resource not found                      |
| 422  | Validation error                        |
| 500  | Server error                            |

---

## 9. Non-Functional Requirements

### 9.1 Performance

| Metric                   | Target                                     |
| ------------------------ | ------------------------------------------ |
| Page Load Time           | < 2 seconds (first contentful paint)       |
| API Response Time        | < 300ms for standard queries               |
| Image Load (Lazy)        | Lazy-loaded below fold, WebP format where possible |
| Database Queries per Request | < 10 (use eager loading)               |

### 9.2 Security

| Requirement                    | Implementation                                        |
| ------------------------------ | ----------------------------------------------------- |
| Authentication                 | Laravel Sanctum token-based auth                      |
| Password Hashing               | bcrypt (Laravel default)                              |
| CSRF Protection                | Handled by Inertia.js (Storefront/Admin)              |
| API Rate Limiting              | 60 requests/min (public), 120 requests/min (auth)     |
| Input Validation               | Server-side Laravel Form Requests on every endpoint   |
| SQL Injection Prevention       | Eloquent ORM / parameterized queries                  |
| XSS Prevention                 | Vue.js auto-escaping + Content Security Policy        |
| File Upload Validation         | MIME type, file size (max 2MB images), extension check |
| CORS                           | Configured for Storefront and Admin domains only      |
| HTTPS                          | Required in production                                |

### 9.3 Scalability

- Stateless API design enables horizontal scaling.
- Database indexing on frequently queried columns (see Section 10).
- Image storage abstracted via Laravel Filesystem (swap to S3 without code changes).
- Cache layer ready (Redis) for category lists, homepage sections, product counts.

### 9.4 Reliability

| Requirement          | Target                            |
| -------------------- | --------------------------------- |
| Uptime               | 99.5%                             |
| Data Backups         | Daily automated MySQL backups     |
| Error Logging        | Centralized via Laravel Log / Sentry (optional) |
| Graceful Degradation | Friendly error pages for 404/500  |

### 9.5 Browser Support

| Browser         | Minimum Version |
| --------------- | --------------- |
| Chrome          | 90+             |
| Firefox         | 90+             |
| Safari          | 14+             |
| Edge            | 90+             |
| Mobile Chrome   | 90+             |
| Mobile Safari   | 14+             |

### 9.6 Responsiveness

- Storefront: Fully responsive (mobile-first design). Breakpoints: 640px, 768px, 1024px, 1280px.
- Admin Panel: Optimized for desktop (min-width: 1024px). Functional on tablet.

---

## 10. Database Schema (High-Level)

### 10.1 Entity Relationship Overview

```
users (customers + admins)
  │
  ├── addresses (1:N)
  ├── orders (1:N)
  │     ├── order_items (1:N)
  │     └── order_status_logs (1:N)
  └── carts (1:1)
        └── cart_items (1:N)

categories (1:N products)
  │
  └── products
        ├── product_images (1:N)
        ├── product_variants (1:N)
        │     └── variant_options (1:N)
        └── section_products (N:M via pivot)

sections
  └── section_products (pivot → products)

settings (key-value store)
```

### 10.2 Table Definitions

#### `users`

| Column            | Type            | Constraints            |
| ----------------- | --------------- | ---------------------- |
| id                | BIGINT UNSIGNED | PK, Auto Increment     |
| name              | VARCHAR(255)    | NOT NULL               |
| email             | VARCHAR(255)    | NOT NULL, UNIQUE       |
| email_verified_at | TIMESTAMP       | NULLABLE               |
| password          | VARCHAR(255)    | NOT NULL               |
| phone             | VARCHAR(20)     | NULLABLE               |
| avatar            | VARCHAR(255)    | NULLABLE               |
| role              | VARCHAR(255)    | NULLABLE               |
| is_active         | BOOLEAN         | DEFAULT true           |
| remember_token    | VARCHAR(100)    | NULLABLE               |
| created_at        | TIMESTAMP       |                        |
| updated_at        | TIMESTAMP       |                        |
| deleted_at        | TIMESTAMP       | NULLABLE (Soft Delete) |

#### `categories`

| Column      | Type           | Constraints                  |
| ----------- | -------------- | ---------------------------- |
| id          | BIGINT UNSIGNED| PK, Auto Increment           |
| name        | VARCHAR(255)   | NOT NULL                     |
| slug        | VARCHAR(255)   | NOT NULL, UNIQUE             |
| image       | VARCHAR(255)   | NULLABLE                     |
| description | TEXT           | NULLABLE                     |
| is_active   | BOOLEAN        | DEFAULT true                 |
| sort_order  | INT            | DEFAULT 0                    |
| created_at  | TIMESTAMP      |                              |
| updated_at  | TIMESTAMP      |                              |
| deleted_at  | TIMESTAMP      | NULLABLE                     |

**Index:** `idx_categories_slug` on `slug`, `idx_categories_active` on `is_active`.

#### `products`

| Column            | Type                            | Constraints                  |
| ----------------- | ------------------------------- | ---------------------------- |
| id                | BIGINT UNSIGNED                 | PK, Auto Increment           |
| category_id       | BIGINT UNSIGNED                 | FK → categories.id, NOT NULL |
| name              | VARCHAR(255)                    | NOT NULL                     |
| slug              | VARCHAR(255)                    | NOT NULL, UNIQUE             |
| code              | VARCHAR(50)                     | NOT NULL, UNIQUE             |
| color             | VARCHAR(255)                    | NULLABLE                     |
| size              | VARCHAR(255)                    | NULLABLE                     |
| short_description | VARCHAR(500)                    | NULLABLE                     |
| description       | TEXT                            | NULLABLE                     |
| price             | DECIMAL(10,2)                   | NOT NULL                     |
| discount_price    | DECIMAL(10,2)                   | NULLABLE                     |
| stock_status      | ENUM('in_stock','out_of_stock') | DEFAULT 'in_stock'           |
| is_active         | BOOLEAN                         | DEFAULT true                 |
| meta_title        | VARCHAR(70)                     | NULLABLE                     |
| meta_description  | VARCHAR(160)                    | NULLABLE                     |
| created_at        | TIMESTAMP                       |                              |
| updated_at        | TIMESTAMP                       |                              |

**Indexes:**
- `idx_products_slug` on `slug`
- `idx_products_code` on `code`
- `idx_products_category` on `category_id`
- `idx_products_active_stock` on (`is_active`, `stock_status`)

#### `product_images`

| Column     | Type            | Constraints                |
| ---------- | --------------- | -------------------------- |
| id         | BIGINT UNSIGNED | PK, Auto Increment         |
| product_id | BIGINT UNSIGNED | FK → products.id, NOT NULL |
| image_path | VARCHAR(255)    | NOT NULL                   |
| sort_order | INT             | DEFAULT 0                  |
| is_primary | BOOLEAN         | DEFAULT false              |
| created_at | TIMESTAMP       |                            |
| updated_at | TIMESTAMP       |                            |


#### `section_products`

| Column       | Type            | Constraints                |
| ------------ | --------------- | -------------------------- |
| id           | BIGINT UNSIGNED | PK, Auto Increment         |
| section_name | VARCHAR(255)    | DEFAULT new                |
| product_id   | BIGINT UNSIGNED | FK → products.id, NOT NULL |
| sort_order   | INT             | DEFAULT 0                  |
| created_at   | TIMESTAMP       |                            |

**Unique Constraint:** (`product_id`)


#### `carts`

| Column     | Type            | Constraints                     |
| ---------- | --------------- | ------------------------------- |
| id         | BIGINT UNSIGNED | PK, Auto Increment              |
| user_id    | BIGINT UNSIGNED | FK → users.id, NULLABLE, UNIQUE |
| product_id | BIGINT UNSIGNED | FK → products.id, NOT NULL      |
| quantity   | INT UNSIGNED    | NOT NULL, DEFAULT 1             |
| unit_price | DECIMAL(10,2)   | NOT NULL                        |
| created_at | TIMESTAMP       |                                 |
| updated_at | TIMESTAMP       |                                 |

#### `orders`

| Column           | Type            | Constraints             |
| ---------------- | --------------- | ----------------------- |
| id               | BIGINT UNSIGNED | PK, Auto Increment      |
| user_id          | BIGINT UNSIGNED | FK → users.id, NULLABLE |
| order_number     | VARCHAR(20)     | NOT NULL, UNIQUE        |
| status           | VARCHAR(50)     | DEFAULT 'pending'       |
| subtotal         | DECIMAL(10,2)   | NOT NULL                |
| shipping_fee     | DECIMAL(10,2)   | DEFAULT 0               |
| discount_amount  | DECIMAL(10,2)   | DEFAULT 0               |
| total            | DECIMAL(10,2)   | NOT NULL                |
| payment_method   | VARCHAR(50)     | NOT NULL                |
| payment_status   | VARCHAR(100)    | DEFAULT 'pending'       |
| customer_name    | VARCHAR(255)    | NOT NULL                |
| customer_email   | VARCHAR(255)    | NOT NULL                |
| customer_phone   | VARCHAR(20)     | NULLABLE                |
| shipping_address | JSON            | NOT NULL                |
| admin_notes      | TEXT            | NULLABLE                |
| created_at       | TIMESTAMP       |                         |
| updated_at       | TIMESTAMP       |                         |
| deleted_at       | TIMESTAMP       | NULLABLE                |

**Indexes:**
- `idx_orders_number` on `order_number`
- `idx_orders_user` on `user_id`
- `idx_orders_status` on `status`
- `idx_orders_created` on `created_at`

**Order Number Format:** `ORD-{YYYYMMDD}-{4-digit-sequence}` (e.g., `ORD-20260303-0001`)

#### `order_items`

| Column          | Type            | Constraints                              |
| --------------- | --------------- | ---------------------------------------- |
| id              | BIGINT UNSIGNED | PK, Auto Increment                       |
| order_id        | BIGINT UNSIGNED | FK → orders.id, NOT NULL                 |
| product_id      | BIGINT UNSIGNED | FK → products.id, NOT NULL               |
| product_name    | VARCHAR(255)    | NOT NULL (snapshot)                      |
| product_code    | VARCHAR(50)     | NOT NULL (snapshot)                      |
| variant_details | JSON            | NULLABLE (snapshot of selected variants) |
| quantity        | INT UNSIGNED    | NOT NULL                                 |
| unit_price      | DECIMAL(10,2)   | NOT NULL                                 |
| total_price     | DECIMAL(10,2)   | NOT NULL                                 |
| created_at      | TIMESTAMP       |                                          |
| updated_at      | TIMESTAMP       |                                          |

#### `order_status_logs`

| Column      | Type            | Constraints              |
| ----------- | --------------- | ------------------------ |
| id          | BIGINT UNSIGNED | PK, Auto Increment       |
| order_id    | BIGINT UNSIGNED | FK → orders.id, NOT NULL |
| from_status | VARCHAR(20)     | NULLABLE                 |
| to_status   | VARCHAR(20)     | NOT NULL                 |
| changed_by  | BIGINT UNSIGNED | FK → users.id, NULLABLE  |
| note        | TEXT            | NULLABLE                 |
| created_at  | TIMESTAMP       |                          |




---

## 12. UI/UX Wireframe Descriptions

### 12.1 Storefront — Homepage Layout

```
┌──────────────────────────────────────────────────────┐
│  HEADER: Logo | Home | Shop | Cart(3) | Profile      │
├──────────────────────────────────────────────────────┤
│                                                      │
│             HERO BANNER (Full Width)                 │
│         Headline + CTA Button on overlay             │
│                                                      │
├──────────────────────────────────────────────────────┤
│                                                      │
│  CATEGORIES — "Shop by Category"                     │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐      │
│  │ IMG  │ │ IMG  │ │ IMG  │ │ IMG  │ │ IMG  │ →    │
│  │ Name │ │ Name │ │ Name │ │ Name │ │ Name │      │
│  └──────┘ └──────┘ └──────┘ └──────┘ └──────┘      │
│                                                      │
├──────────────────────────────────────────────────────┤
│                                                      │
│  BEST SELLERS — "Best Sellers"          [View All →] │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐               │
│  │ IMG  │ │ IMG  │ │ IMG  │ │ IMG  │               │
│  │ Name │ │ Name │ │ Name │ │ Name │               │
│  │ Price│ │ Price│ │ Price│ │ Price│               │
│  │ [Cart]│ │ [Cart]│ │ [Cart]│ │ [Cart]│              │
│  └──────┘ └──────┘ └──────┘ └──────┘               │
│                                                      │
├──────────────────────────────────────────────────────┤
│                                                      │
│  NEW ARRIVALS — "New Arrivals"          [View All →] │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐               │
│  │ IMG  │ │ IMG  │ │ IMG  │ │ IMG  │               │
│  │ Name │ │ Name │ │ Name │ │ Name │               │
│  │ Price│ │ Price│ │ Price│ │ Price│               │
│  │ [Cart]│ │ [Cart]│ │ [Cart]│ │ [Cart]│              │
│  └──────┘ └──────┘ └──────┘ └──────┘               │
│                                                      │
├──────────────────────────────────────────────────────┤
│  FOOTER                                              │
│  Company Info | Quick Links | Contact | Social       │
└──────────────────────────────────────────────────────┘
```

### 12.2 Storefront — Shop Page Layout

```
┌──────────────────────────────────────────────────────┐
│  HEADER                                              │
├──────────┬───────────────────────────────────────────┤
│          │  ┌─────────────────┐  Sort: [Dropdown ▼]  │
│ FILTERS  │  │  Search by name or code...  🔍       │  │
│          │  └─────────────────┘                      │
│ Category │  Active Filters: [Tag ×] [Tag ×] [Clear] │
│ ☐ Cat 1  │                                          │
│ ☐ Cat 2  │  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐   │
│ ☐ Cat 3  │  │ Prod │ │ Prod │ │ Prod │ │ Prod │   │
│ ☐ Cat 4  │  │ Card │ │ Card │ │ Card │ │ Card │   │
│          │  └──────┘ └──────┘ └──────┘ └──────┘   │
│          │  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐   │
│          │  │ Prod │ │ Prod │ │ Prod │ │ Prod │   │
│          │  │ Card │ │ Card │ │ Card │ │ Card │   │
│          │  └──────┘ └──────┘ └──────┘ └──────┘   │
│          │                                          │
│          │                                           │
├──────────┴───────────────────────────────────────────┤
│  FOOTER                                              │
└──────────────────────────────────────────────────────┘
```

### 12.3 Storefront — Product Detail Layout

```
┌──────────────────────────────────────────────────────┐
│  HEADER                                              │
├────────────────────┬─────────────────────────────────┤
│                    │                                 │
│  ┌──────────────┐  │  Product Name                   │
│  │              │  │  Code: PRD-001                  │
│  │  MAIN IMAGE  │  │  ★ In Stock                    │
│  │              │  │                                 │
│  │              │  │  ̶$̶1̶2̶0̶.̶0̶0̶  $89.99  (25% OFF)  │
│  └──────────────┘  │                                 │
│  [img][img][img]   │  Size:   [S] [M] [L] [XL]     │
│                    │  Color:  [Red ▼]                │
│                    │                                 │
│                    │  Qty:  [- 1 +]                  │
│                    │                                 │
│                    │  ┌─────────────┐ ┌────────────┐ │
│                    │  │ ADD TO CART │ │  BUY NOW   │ │
│                    │  └─────────────┘ └────────────┘ │
│                    │                                 │
│                    │  Short description text here... │
├────────────────────┴─────────────────────────────────┤
│                                                      │
│  PRODUCT DESCRIPTION (Full)                          │
│  Detailed product description with rich text...      │
│                                                      │
├──────────────────────────────────────────────────────┤
│                                                      │
│  YOU MAY ALSO LIKE                                   │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐               │
│  │ Prod │ │ Prod │ │ Prod │ │ Prod │               │
│  └──────┘ └──────┘ └──────┘ └──────┘               │
│                                                      │
├──────────────────────────────────────────────────────┤
│  FOOTER                                              │
└──────────────────────────────────────────────────────┘
```

### 12.4 Admin Panel — Dashboard Layout

```
┌──────┬───────────────────────────────────────────────┐
│      │  Dashboard                           Admin ▼  │
│ SIDE ├───────────────────────────────────────────────┤
│ BAR  │                                               │
│      │  ┌─────────┐┌─────────┐┌─────────┐┌────────┐ │
│ ◉ Dash│  │ Revenue ││ Orders  ││ Pending ││Products│ │
│ ◯ Cat │  │ $12,450 ││   156   ││   12    ││  89    │ │
│ ◯ Prod│  │ +12% ↑  ││ +8% ↑  ││ -3 ↓   ││ +5 ↑  │ │
│ ◯ Sect│  └─────────┘└─────────┘└─────────┘└────────┘ │
│ ◯ Ord │                                              │
│ ◯ Rpt │  ┌─────────────────────────────────────────┐ │
│ ◯ Set │  │         Revenue Chart (30 days)         │ │
│      │  │         ▁▂▃▅▆▇█▆▅▃▂▁▂▃▅▇           │ │
│      │  └─────────────────────────────────────────┘ │
│      │                                               │
│      │  Recent Orders              Order Status Dist │
│      │  ┌─────────────────────┐   ┌───────────────┐ │
│      │  │ #ORD-001 | $89  |P │   │   PIE CHART   │ │
│      │  │ #ORD-002 | $245 |C │   │  ◉ Delivered  │ │
│      │  │ #ORD-003 | $67  |S │   │  ◉ Pending    │ │
│      │  │ ...                 │   │  ◉ Processing │ │
│      │  └─────────────────────┘   └───────────────┘ │
│      │                                               │
└──────┴───────────────────────────────────────────────┘
```

---

## 13. Payment Gateway Integration

### 13.1 Supported Gateways

| Gateway    | Type             | Priority | Notes                               |
| ---------- | ---------------- | -------- | ----------------------------------- |
| COD        | Cash on Delivery | P0       | No online processing needed         |
| SSLCommerz | Online Payment   | P1       | Bangladesh local gateway (optional) |

### 13.2 SSL Commerz Integration Flow

```
1. Customer clicks "Place Order"
2. API creates Order with status "pending" and payment_status "pending"
3. API creates SSL PaymentIntent with order total
4. Client receives client_secret
```

---

## 14. Reporting & Analytics

### 14.1 Dashboard Metrics (Real-Time)

| Metric               | Calculation                                                                  |
| -------------------- | ---------------------------------------------------------------------------- |
| Today's Revenue      | SUM(orders.total) WHERE DATE(created_at) = TODAY AND payment_status = 'paid' |
| Weekly Revenue       | Same, for current week                                                       |
| Monthly Revenue      | Same, for current month                                                      |
| Total Orders         | COUNT(orders) for selected period                                            |
| Pending Orders       | COUNT(orders) WHERE status = 'pending'                                       |
| Top Selling Products | ORDER BY SUM(order_items.quantity) DESC LIMIT 5                              |

### 14.2 Sales Report

**Filters:** Date From, Date To, Granularity (Daily/Weekly/Monthly)

**Output:**

| Date       | Orders | Revenue    | Avg Order Value |
| ---------- | ------ | ---------- | --------------- |
| 2026-03-01 | 12     | $1,245.00  | $103.75         |
| 2026-03-02 | 8      | $892.00    | $111.50         |
| ...        | ...    | ...        | ...             |
| **Total**  | **156**| **$12,450**| **$79.81**      |

**Export:** CSV download button.

### 14.3 Order Report

**Filters:** Date From, Date To

**Output:** Order count by status (table + pie chart).

### 14.4 Top Products Report

**Filters:** Date From, Date To, Limit (10/20/50)

**Output:**

| Rank | Product Name    | Code     | Qty Sold | Revenue   |
| ---- | --------------- | -------- | -------- | --------- |
| 1    | Product Alpha   | PRD-001  | 45       | $4,050.00 |
| 2    | Product Beta    | PRD-015  | 38       | $3,420.00 |
| ...  | ...             | ...      | ...      | ...       |

### 14.5 Category Performance Report

**Output:** Revenue and order count per category (table + bar chart).

---

## 15. Milestones & Release Plan

### 15.1 Development Phases

| Phase | Name                                                | Duration  | Key Deliverables                                                                                  |
| ----- | --------------------------------------------------- | --------- | ------------------------------------------------------------------------------------------------- |
| 1     | UI/UX                                               | 1.5 weeks | Storefront and Admin CMS                                                                          |
| 2     | Foundation & API Core                               | 1 weeks   | Project setup, DB migrations, Auth, Category & Product CRUD APIs                                  |
| 3     | Admin Panel — Core                                  | 1 weeks   | Admin auth, Category mgmt, Product mgmt, Section mgmt                                             |
| 4     | Storefront — Browsing                               | 1 weeks   | Homepage, Shop page (filters/sort/search), Product detail                                         |
| 5     | Customer Accounts,Cart, Checkout & Payment & Orders | 2 weeks   | Registration/Login, Profile, Cart API & UI, Checkout flow, SSL integration, COD and Order history |
| 6     | Admin — Orders & Reporting                          | 1 weeks   | Order management, Invoice PDF, Dashboard, Reports                                                 |
| 7     | Polish & QA                                         | 1 week    | Bug fixes, responsive testing, performance optimization, UAT                                      |

**Total Estimated Duration: ~8.5 weeks**

### 15.2 Milestone Checkpoints

| Milestone | Date (Estimated) | Gate Criteria                                                                    |
| --------- | ---------------- | -------------------------------------------------------------------------------- |
| M0        | End of Week 1.5  | UI/UX Confirmation                                                               |
| M1        | End of Week 2.5  | API returns categories & products, auth works                                    |
| M2        | End of Week 3.5  | Admin can manage full catalog                                                    |
| M3        | End of Week 4.5  | Customer can browse and view products                                            |
| M4        | End of Week 6.5  | Customer accounts and order history complete,End-to-end purchase flow functional |
| M5        | End of Week 7.5  | Admin order mgmt, invoices, and reports functional                               |
| M6        | End of Week 8.5  | UAT passed, production-ready                                                     |

---

## 16. Risks & Mitigations

| #   | Risk                                        | Impact | Probability | Mitigation                                                                     |
| --- | ------------------------------------------- | ------ | ----------- | ------------------------------------------------------------------------------ |
| R1  | Payment gateway integration complexity      | High   | Medium      | Start SSL integration early in Phase 5. Use test mode extensively.             |
| R2  | Cross-project API contract drift            | Medium | High        | Version API routes. Shared Postman collection.                                 |
| R3  | Performance degradation with product volume | Medium | Low         | Database indexing, eager loading, pagination. Load test with 1000+ products.   |
| R4  | Scope creep (feature requests mid-sprint)   | High   | High        | Strict scope document sign-off. New features go to v2 backlog.                 |
| R5  | File upload security vulnerabilities        | High   | Medium      | Server-side MIME validation, size limits, virus scanning consideration.        |
| R6  | Three-project deployment complexity         | Medium | Medium      | Dockerize all projects. CI/CD pipeline per project. Shared environment config. |

---

## 17. Acceptance Criteria

### 17.1 Storefront Acceptance

| #   | Criteria                                                               | Status |
| --- | ---------------------------------------------------------------------- | ------ |
| AC1 | Homepage loads with banner, categories, best sellers, and new arrivals | ☐      |
| AC2 | Shop page filters by category, searches by name/code, sorts correctly  | ☐      |
| AC3 | Product detail shows images, price, variants, stock, and add-to-cart   | ☐      |
| AC4 | Cart correctly manages items, quantities, and totals                   | ☐      |
| AC5 | Checkout collects shipping info and processes payment (SSL + COD)      | ☐      |
| AC6 | Customer can register, login, view profile, and see order history      | ☐      |
| AC7 | All pages are responsive on mobile (375px+), tablet, and desktop       | ☐      |

### 17.2 Admin Panel Acceptance

| #    | Criteria                                                             | Status |
| ---- | -------------------------------------------------------------------- | ------ |
| AC8  | Admin can CRUD categories with images                                | ☐      |
| AC9  | Admin can CRUD products with images, variants, pricing, and status   | ☐      |
| AC10 | Admin can toggle product active/inactive and stock status            | ☐      |
| AC11 | Admin can assign products to Best Sellers and New Arrivals sections  | ☐      |
| AC12 | Admin can view, filter, and search orders                            | ☐      |
| AC13 | Admin can change order status with status log                        | ☐      |
| AC14 | Admin can print and download invoice PDF                             | ☐      |
| AC15 | Dashboard displays revenue, order stats, charts, and top products    | ☐      |
| AC16 | Reports section shows sales, orders, top products, and category data | ☐      |

### 17.3 API Acceptance

| #    | Criteria                                                            | Status |
| ---- | ------------------------------------------------------------------- | ------ |
| AC17 | All endpoints return standardized JSON response format              | ☐      |
| AC18 | Authentication via Sanctum tokens works for both customer and admin | ☐      |
| AC19 | API rate limiting is enforced                                       | ☐      |
| AC20 | All inputs are validated server-side with proper error messages     | ☐      |
| AC21 | SSL IPN correctly updates payment status                            | ☐      |

---

## 18. Glossary

| Term                | Definition                                                               |
| ------------------- | ------------------------------------------------------------------------ |
| **Storefront**      | The customer-facing ecommerce website                                    |
| **Admin Panel**     | The back-office management application                                   |
| **API**             | The central REST backend serving both Storefront and Admin               |
| **Inertia.js**      | Server-side rendering adapter connecting Laravel routes to Vue pages     |
| **Sanctum**         | Laravel's lightweight API authentication package using tokens            |
| **Curated Section** | Admin-managed homepage product section (e.g., Best Sellers)              |
| **Variant**         | A product attribute variation (e.g., Size: S/M/L, Color: Red/Blue)       |
| **Stock Status**    | Binary availability indicator: "In Stock" or "Out of Stock"              |
| **COD**             | Cash on Delivery payment method                                          |
| **SKU**             | Stock Keeping Unit — unique product identifier code                      |
| **Soft Delete**     | Record marked as deleted but retained in database for data integrity     |
| **Eager Loading**   | Loading related data in a single query to prevent N+1 performance issues |

---

**Document End**

*This PRD is a living document. All changes must be reviewed and versioned. Stakeholder sign-off is required before development begins.*

| Role            | Name | Signature | Date |
| --------------- | ---- | --------- | ---- |
| Project Manager |      |           |      |
| Tech Lead       |      |           |      |
| Product Owner   |      |           |      |
| QA Lead         |      |           |      |
