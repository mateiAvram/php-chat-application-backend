# PHP Chat Application Backend

A simple chat backend written in PHP using the Slim Framework.

## Features

* **User creation** (returns a `userId` cookie)
* **Group creation**
* **Users joining groups**
* **Sending messages** in groups
* **Retrieving all** messages in a group or **polling for new** messages and **infinite scroll**
* Full request validation with appropriate HTTP error codes (400, 401, 403, 500)

## Requirements

* PHP 8.4 or higher
* [Composer](https://getcomposer.org/)

## Installation

1. Clone the repository:

   ```bash
   git clone git@github.com:mateiAvram/php-chat-application-backend.git
   cd php-chat-application-backend
   ```

2. Install dependencies via Composer:

   ```bash
   composer install
   ```

## Configuration

* The project uses a SQLite database by default, configured in your DI container.
* Ensure `database.db` exists and is writable by your web server.

## Running the Server

Start the built-in PHP web server for local development from projec root directory:

```bash
composer start
```

Visit `http://localhost:8000/hello_world` to verify the server is running.

## API Endpoints

* **POST** `/user/create`

  * Body: `{ "username": "alice" }`
  * Response: `201 Created`, sets `userId` cookie

* **POST** `/group/create`

  * Cookie: `userId`
  * Body: `{ "groupName": "My Group" }`
  * Response: `201 Created`, JSON `{ "success": true, "data": { "id": 1, "name": "My Group" } }`

* **POST** `/group/{id}/join`

  * Cookie: `userId`
  * Response: `200 OK`

* **POST** `/group/{id}/send`

  * Cookie: `userId`
  * Body: `{ "content": "Hello world!" }`
  * Response: `200 OK`, JSON `{ "id": 1, "content": "Hello world!", "timestamp": "<ISO8601>", "sender": { "id": 1, "name": "alice" } }`

* **GET** `/group/{id}/messages/all`

  * Cookie: `userId`
  * Response: `200 OK`, JSON `{ "success": true, "data": [ ... ] }`

* **GET** `/group/{id}/messages/new?lastUpdate=<unixEpoch>`

  * Cookie: `userId`
  * Response: `200 OK`, JSON `{ "success": true, "data": [ ... ] }`

## Running Tests

Run PHPUnit to execute the test suite:

```bash
vendor/bin/phpunit
```

You should see:

```
OK (10 tests, 27 assertions)
```

---

Feel free to adjust any paths or settings to match your environment!
