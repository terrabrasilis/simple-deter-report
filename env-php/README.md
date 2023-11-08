# Docker image

To build the Docker image, use the "docker-build.sh" script. Atention with the tag version of the code repository because that version is used to tag the output image.

# Deploy

The deploy is based on Docker and the sensitive data is putting on external volume defined on Docker Stack and located directly on server directory.

Create a file called config.ini.php with the following content:

```php
<?php
$host = "<host name or IP>";
$port = "<port>";
$dbname = "<database name>";
$user = "<user name>";
$password = "<secret password>";
?>
```

This file is required to provide the Database connection parameters to the main script. The location defined within the container, to place this file, must be /var/www/html/config/

Inject this file via volume inside the container like this Docker compose example.

```yaml
version: '2'
services:
  deter_pantanal_report:
    image: terrabrasilis/deter-pantanal-app:vx.y.z
    ports:
      - "80"
    volumes:
      - /data/deter-app:/var/www/html/config
    restart: always
```

The vx.y.z is the image tag, based on the code version in the Git repository.