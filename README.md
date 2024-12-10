# Service with modules and Domain-Driven Design

## Key goals and requirements

1. **Architecture: service with modules.**
2. Modules separated according to functional boundaries (verbs).
3. **Domain-Driven Design** applied at module level.
4. Command Query Responsibility Segregation (**CQRS**) in its basic form (without events).

## Tactical objectives

1. Application built using the Symfony framework.
2. Using Symfony Serializer and Symfony Validator to process input data received by the API.
3. Using Symfony Security. It allows relatively simple use of ready-made solutions for connecting to various identity providers (e.g. OAuth2, SAML).
4. Multi tenant with separate database for each tenant and one common database for system management (users, tenants definition, etc.).
5. Tenant database logically divided into modules (by prefix in table name).
6. DDD aggregates.
7. Entity versioning (know also as "optimistic locking") for checking consistency between data presented to user (frontend side) and actual state of entity in database (backend side).
8. Database deadlock avoidance.
9. Zero CRUD, zero PUT/PATCH/DELETE.
10. Using ready-made solutions to create API documentation based on data structures prepared for the Serializer and constraints needed for the Validator.
11. Limiting setters and getters to the necessary minimum.
12. High coverage by unit tests.

## Architecture

<img src="docs/img/service-with-modules.svg" alt="service with modules">

<img src="docs/img/module-structure.svg" alt="module structure">

Each module implements a group of related functionalities and creates a "bounded context" in the sense of DDD.

Module exposes their Actions (Command and Query) to "external world" through UserInterface layer.

Other modules may call module's Actions (Command and Query) and Services defined at Application layer.

## Some implementation details

### Directory structure

At level 1 below `src/` directory, each directory represents a module.\
In this example implementation we have:
```text
src/
   Admin/
   Api/
   Auth/
   Customer/
   Order/
   Printer/
```

At level 2 below `src/`, directories are arranged with DDD tactical pattern:
```text
src/
   ...
   Order/
      Application/
      Domain/
      Infrastructure/
      UserInterface/
```

### Module boundary checking

In typical scenario, other module will call module's Command or Query or Service defined at Application layer supplying parameters as primitive types or DTO defined also on Application layer.\
And will receive result as primitive type or DTO (from Application layer).

For some rare cases, other module may reach directly objects from Domain layer. Example of such object is `CustomerBag`.

Moreover, some methods in classes in Application layer have to be defined as `public` according to PHP access rules, but in fact they are internal to the module. Example of such method is `validateLoadingAddressForCreate()`.

Taking it all under consideration, it is not easy to define rules which parts (classes, enums, DTOs, exceptions, etc.) of given module are available for other modules.

## "Zero CRUD, zero PUT/PATCH/DELETE" - why?

At first reading of this sentence you may think "This guy is crazy! He rejects the foundations of REST API."

Here is the explanation of this architectural decision.

I saw a lot of projects, where domain model is mapped 1-to-1 to database table end exposed directly to external world (mostly frontend SPA).

With such design, frontend SPA can directly manipulate model stored in database - by changing whole entity (PUT action) or only part of the entity (PATCH action).\
Orders coming from frontend SPA contain commands PUT/PATCH, that the backend and database should execute without hesitation.\
Source of the orders is a user, which makes decision based on what he/she see on the screen.

This approach is valid for application designed for direct database manipulation (like phpMyAdmin) or simple blogpost.

Main drawback of this approach is, that the user makes a decision based on data which **was** in database at moment when data was sent to presentation layer. Ignoring the fact, that the same entity/model at the moment the user makes a decision **may already be different**.

Some projects simply ignore this inconsistency between user view and real application state.\
Other project tries to solve this problem - with more or less successful results.

Suppose we build application for transportation of domestic shipments.\
Users see on the screen shipment in state "NEW". Then user goes to grab a coffee.\
Whe he comes back, he decides to send shipment for acceptance by carrier. So he chooses on the screen new shipment state "SENT" and press "Submit" button. Frontend sends PATCH request with "{state = SENT}".\
But it was urgent shipment. In the meantime, another user sent the shipment for acceptance and the carrier responded with confirmation for execution. And current shipment state is "CONFIRMED".

Should we accept PATCH request from coffee lover and change shipment state to "SENT"?\
Technically - yes, it is fundamental rule of REST/PATCH.\
From business point of view - definitely not.

We can easily avoid such dilemmas if we abandon PUT/PATCH/DELETE and focus on CQRS approach with **actions**.

Let's go back to domestic shipments example.\
User comes back with coffee and sends command "I would like to send this shipment for acceptance". By POST request.\
Backend checks command pre-condition and see, that shipment is already confirmed. So backend may answer with user-friendly message with explanation why users' command cannot be done.

This new approach allow to encapsulate domain logic related to the command "I would like to send this shipment for acceptance" in PHP class designed strictly for this one command.


## Usage:

    docker compose up

It will download required images, build custom image for PHP and execute script `start-app.sh` containing commands: 

    composer install
    composer run-script apidoc

    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    php bin/console admin:init-common
    php bin/console admin:migrate-tenants
    php bin/console admin:init-tenants

    php -S 0.0.0.0:8000 -t public/

Testing and checking (run the command inside `php-api` container):

    composer run-script test
    composer run-script check

API documentation:

    http://127.0.0.1:8000/api.html
    http://127.0.0.1:8000/api.yaml
    http://127.0.0.1:8000/api.json

Sample query:

    curl --location 'http://127.0.0.1:8000/order/address/list' --header 'Authorization: Bearer user-1'

    curl --location 'http://127.0.0.1:8000/order/address/1' --header 'Authorization: Bearer user-1'

    curl --location 'http://127.0.0.1:8000/order/order/1' --header 'Authorization: Bearer user-1'

Sample command:

    curl --location 'http://127.0.0.1:8000/order/address/create' \
    --header 'Content-Type: application/json' \
    --header 'Authorization: Bearer admin' \
    --data '{
    "customerId": 2,
    "externalId": "W3",
    "nameCompanyOrPerson": "Acme Company Warehouse 3",
    "address": "ul. Wschodnia",
    "city": "Poznań",
    "zipCode": "61-001"
    }'

    curl --location 'http://127.0.0.1:8000/order/order/send' \
    --header 'Content-Type: application/json' \
    --header 'Authorization: Bearer user-1' \
    --data '{
    "orderId": 1,
    "version": 1
    }'

    curl --location 'http://127.0.0.1:8000/order/order/print-label' \
    --header 'Content-Type: application/json' \
    --header 'Authorization: Bearer user-1' \
    --data '{
    "orderId": 1
    }'

    curl --location 'http://127.0.0.1:8000/order/order/create' \
    --header 'Content-Type: application/json' \
    --header 'Authorization: Bearer user-1' \
    --data-raw '{
    "loadingDate": "2024-05-19",
    "loadingFixedAddressExternalId": null,
    "loadingAddress": {
    "nameCompanyOrPerson": "Acme Company",
    "address": "ul. Garbary 125",
    "city": "Poznań",
    "zipCode": "61-719"
    },
    "loadingContact": {
    "contactPerson": "Contact Person ",
    "contactPhone": "+48-603-978-106",
    "contactEmail": "person@email.com"
    },
    "deliveryAddress": {
    "nameCompanyOrPerson": "Receiver Company",
    "address": "ul. Wschodnia",
    "city": "Poznań",
    "zipCode": "61-001"
    },
    "deliveryContact": {
    "contactPerson": "Person2",
    "contactPhone": "+48-111-111-111",
    "contactEmail": null
    },
    "lines": [
    {
    "quantity": 3,
    "length": 120,
    "width": 80,
    "height": 100,
    "weightOnePallet": 200,
    "goodsDescription": "computers"
    },
    {
    "quantity": 5,
    "length": 120,
    "width": 80,
    "height": 200,
    "weightOnePallet": 200,
    "goodsDescription": "heavy printers"
    }
    ]
    }'
