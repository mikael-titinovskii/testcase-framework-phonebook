### Phonebook api app

this is a testcase, the task is to create a basic framework and a phonebook on top

Framework features delegated to libs: 

- caching
- routing
- request handling
- db migrations
- logs
- DI container
- dotenv variable loading
- ORM

##### Requirements:

Docker or PHP 7.*, composer, mysql 5.7

##### Setup:

Run the containers
```shell script
# run compose in attached moode
docker-compose up 
# or detached mode, to free the terminal
docker-compose up -d
```
Bootstrap application
```shell script
# Download Composer
wget https://getcomposer.org/composer.phar

# Get the php7-fpm container id
docker ps #to get the container "php7-fpm" id

# Get inside the container  
docker exec -it [CONTAINER ID] bash

# Install dependencies
php composer.phar install

# Migrate database
php phinx.php migrate

# Optional: load test data
php phinx.php seed:run
```
##### Todo:
 - Change volume to add directive to remove UID:GUID hack
 - Auth middleware
 - Write tests
 - Todo's inside the code 

