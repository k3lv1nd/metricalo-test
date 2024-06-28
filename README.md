# Metricalo Test Symfony Project
#### This is a Symfony application with an endpoint and a cli interface to make payment requests to specified external systems

## Table of content
1. [Description](#description)
2. [Requirements](#requirements)
3. [Setup and installations using docker](#setup-and-installations-using-docker)
4. [Setup and installations Without docker](#setup-and-installations-without-docker)
5. [Payment Request API endpoint](#payment-request-endpoint)
6. [Payment Request CLI command](#payment-request-cli-command)
7. [Running phpunit tests](#running-phpunit-tests)



## Description
This is a Symfony application with an endpoint and a cli interface to make payment requests to the specified external systems

## Requirements
* Docker Machine/ Docker Desktop with Docker Compose plugin or a machine that is properly set up to run a symfony 6 application

## Setup and installations using docker
* Clone Project to your machine
* `docker-compose up --build`
* access the php container using the command `docker exec -it {php_container_id} bash` and run `composer install` to install project dependencies
* ensure that the db container is also properly created and running[refer to docker-compose.yml]
* execute the database migrations while still in the php container to update the schema
* The app should be up & running on localhost:8000

## Setup and installations without docker
* Clone Project to your machine
* run `composer install`
* create the project database and update the .env DATABASE_URL environment variable accordingly
* execute migrations
* run the application using `symfony server:start` command

## Payment Request Endpoint
* The payment request endpoint is `/payment/request/{type}` POST
* The type parameter is the external service you wish to call for the payment service and should be `aci` or `shift4` for now
* The endpoint accepts payload which should take the following form
```
{
  "amount" : 33,
  "currency" : "EUR",
  "card_number" : "4200000000000000",
  "card_exp_month" : "11",
  "card_exp_year" : "2027",
  "card_cvv" : 333
  }
  ```
* On success, the endpoint returns a response as shown below
```
{
    "transaction_id": "8ac7a4a0905d23a601905da010ec2f67",
    "created_at": "2024-06-28T06:54:49+00:00",
    "amount": "33.00",
    "currency": "EUR",
    "card_bin": "420000"
}

```
* On failure, the endpoint will return the corresponding status code and an error message
## Payment Request CLI Command
* The CLI command is `metricalo:payment:request` and can be run as `bin/console metricalo:payment:request {type}` where
type is the external service you wish to call for the payment service and should be `aci` or `shift4`
* After running the command, it will prompt you for the other required input parameters to be able to make a payment request
* The command displays the respective unified response in the console 
## Running phpunit tests
* When using docker, access the database container using the command `docker exec -it {database_container_id} bash` and run the following 2
command to adjust file permissions and ownerships for the mysql user `chmod -R 775 /var/lib/mysql` && `chown -R mysql:mysql /var/lib/mysql`
* In the same container, access the mysql console [refer to docker-compose.yml for credentials] and create the metricalo_test database if not exists
*  access the php container using the command `docker exec -it {php_container_id} bash` and run `bin/console doctrine:schema:update --no-interaction --env=test --force` to update the test database schema
* In the same php container, run `bin/phpunit` to execute the phpunit tests
* If not using docker, ensure that the metricalo_test db is created and update the .env.test DATABASE_URL environment variable accordingly and run `bin/phpunit` to execute the tests