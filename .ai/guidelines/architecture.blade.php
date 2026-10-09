## Architecture
This application is built with a Domain-Driven Design (DDD) approach, using Laravel on the backend and a React frontend. As a Shopify App, the architecture has multiple integration points and a clear separation of concerns.

### Backend (Laravel/PHP)
The backend structure contains layered components:

- Application Layer: Manages HTTP requests, controllers, middleware, and console commands.

- Domain Layer: This layer holds the core business logic, models, actions, data objects, and domain services.

- Framework Layer: Provides shared services like providers and exception handling.

### Frontend (React/JavaScript)
The frontend has both merchant and customer-facing components:

- Admin Dashboard: A React interface styled with Shopify Polaris for merchants.

- Theme Extensions:

    - Main Extension: Customer-facing theme app extension blocks built with Liquid templates and JavaScript (see `extensions/main/`).

