# SecureCart — Diagrams Specification & Visual Models

This document contains the visual architectural diagrams for the **SecureCart** system, formatted in **Mermaid.js**, **PlantUML**, and **Draw.io XML** for easy viewing, editing, and submission.

---

## 1. System Use Case Diagram

The Use Case Diagram illustrates the interactions between system actors (**Customer**, **Administrator**, and **Unauthenticated Visitor**) and the application modules.

```mermaid
graph TD
    subgraph SecureCart E-Commerce System
        UC1[Browse Catalog & Search Products]
        UC2[Register Account]
        UC3[Log In / Authenticate]
        UC4[Manage Shopping Cart]
        UC5[Execute Transactional Checkout]
        UC6[View Private Order Receipts]
        UC7[Submit Product Reviews]
        UC8[Manage Inventory & Products]
        UC9[Inspect Customer Orders]
        UC10[Audit Security & Brute-Force Logs]
    end

    Visitor((Unauthenticated Visitor)) --> UC1
    Visitor --> UC2
    Visitor --> UC3

    Customer((Authenticated Customer)) --> UC1
    Customer --> UC4
    Customer --> UC5
    Customer --> UC6
    Customer --> UC7

    Admin((Administrator)) --> UC8
    Admin --> UC9
    Admin --> UC10
```

### PlantUML Format (Use Case Diagram)
```plantuml
@startuml
left to right direction
actor "Unauthenticated Visitor" as Visitor
actor "Authenticated Customer" as Customer
actor "Administrator" as Admin

rectangle "SecureCart Web Application" {
  usecase "Browse & Search Catalog" as UC1
  usecase "Register Account" as UC2
  usecase "Log In & Authenticate" as UC3
  usecase "Manage Shopping Cart" as UC4
  usecase "Transactional Checkout" as UC5
  usecase "View Order Receipts (IDOR Protected)" as UC6
  usecase "Submit Product Review" as UC7
  usecase "Manage Product Inventory" as UC8
  usecase "Inspect Global Orders" as UC9
  usecase "Audit Security & Lockout Logs" as UC10
}

Visitor --> UC1
Visitor --> UC2
Visitor --> UC3

Customer --> UC1
Customer --> UC4
Customer --> UC5
Customer --> UC6
Customer --> UC7

Admin --> UC8
Admin --> UC9
Admin --> UC10
@enduml
```

---

## 2. Data Flow Diagrams (DFD)

### Level 0 DFD (Context Diagram)
Shows the high-level data flow between external entities and the SecureCart system boundary.

```mermaid
flowchart TD
    User([Customer / Visitor]) <-->|Registration, Login, Cart, Orders| System[0.0 SecureCart Web Platform]
    AdminUser([Administrator]) <-->|Inventory Updates, Global Orders, Security Audit| System
    System <-->|ACID Transactions, Prepared Queries| DB[(MySQL 8.0 Database)]
```

### Level 1 DFD (Sub-System Process Flow)
Decomposes the system into 4 primary process modules: Authentication, Catalog, Transactional Checkout, and Administration.

```mermaid
flowchart TD
    User([Customer]) -->|1. Credentials| P1[1.0 Auth Process]
    P1 -->|Validate & Hash| D1[(users Table)]
    
    User -->|2. Search & Select| P2[2.0 Catalog & Cart Process]
    P2 <-->|Read Items / Update Cart| D2[(products & cart Tables)]
    
    User -->|3. Place Order| P3[3.0 Transactional Checkout Engine]
    P3 <-->|Atomic Transaction / Stock Reduction| D3[(orders & order_items Tables)]
    
    AdminUser([Admin]) -->|4. Inventory & Logs| P4[4.0 Admin Management]
    P4 <-->|Audit Queries| D4[(login_attempts & products Tables)]
```

### Level 2 DFD (Checkout & Security Process Flow)
Detailing process 3.0 (Transactional Checkout) with OWASP security controls.

```mermaid
flowchart TD
    Customer([Customer]) -->|Submit Order POST + CSRF Token| P3_1[3.1 CSRF Validation]
    P3_1 -->|Valid Token| P3_2[3.2 Server-Side Price Lookup]
    P3_2 -->|Recalculate Totals from DB| P3_3[3.3 Inventory Stock Lock & Validation]
    P3_3 -->|Stock Available| P3_4[3.4 Atomic Insert Order & Clear Cart]
    P3_4 -->|Commit Transaction| DB[(MySQL Orders & Order Items)]
```

---

## 3. Module Connection & Interaction Diagram

Illustrating how presentation modules depend on security middleware and data access layers.

```mermaid
graph TD
    subgraph Presentation Layer
        AUTH[auth/login.php]
        CART[cart/index.php]
        CHECKOUT[checkout/place_order.php]
        ADMIN[admin/index.php]
        ORDERS[orders/view.php]
    end

    subgraph Security & Utility Middleware
        SEC[includes/security.php - XSS Escaping]
        CSRF[includes/csrf.php - CSRF Engine]
        SESS[includes/session.php - Session Management]
        RBAC[includes/auth.php - Authorization]
    end

    subgraph Data Access Layer
        DB[config/database.php - PDO Connection]
    end

    AUTH --> SESS
    AUTH --> CSRF
    AUTH --> SEC
    AUTH --> DB

    CART --> SESS
    CART --> CSRF
    CART --> DB

    CHECKOUT --> SESS
    CHECKOUT --> CSRF
    CHECKOUT --> SEC
    CHECKOUT --> DB

    ADMIN --> SESS
    ADMIN --> RBAC
    ADMIN --> DB

    ORDERS --> SESS
    ORDERS --> SEC
    ORDERS --> DB
```
