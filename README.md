#### phonebook api app

###### Setup:

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
###### Todo:
 - Change volume to add directive to remove UID:GUID hack
 - Auth middleware
 - Write tests
 - Todo's inside the code 

