# Money Transfer Project

This is the companion code for the tutorial [Run your first Temporal Application with PHP](https://learn.temporal.io/getting_started/php/hello_world_in_php/).

## Getting Started

Before running this application, ensure you have the following installed:
- [PHP 8.1 or later](https://www.php.net/downloads.php) with the [`ext-grpc`](https://pecl.php.net/package/grpc) extension.
- [Composer](https://getcomposer.org) for managing dependencies.

### Install the project with Composer

```bash
composer create-project --prefer-dist -sdev temporal/money-transfer-project
cd money-transfer-project
```

This command will create a new project from the template.
All dependencies and necessary files will be installed automatically, including RoadRunner and Temporal.

### Install the project with Git

Clone the repository to your local machine:

```bash
git clone https://github.com/temporalio/money-transfer-project-template-php
cd money-transfer-project-template-php
```

Run the command to install dependencies:

```bash
composer install
```

## Run the project

Start the Temporal Server in development mode:

```bash
./temporal server start-dev --log-level error --color always
```

In another terminal, start RoadRunner:

```bash
./rr serve
```

### Run the workflow

To execute the money transfer workflow, you can use the provided PHP script.
This script will initiate a transfer between two accounts.

```bash
php src/transfer.php
```

### Open the Web UI

To monitor and inspect the progress and status of your money transfer workflows, open the Temporal UI in your browser.
This allows you to view running workflows, completed workflows, and detailed execution histories.

Go to http://localhost:8233
