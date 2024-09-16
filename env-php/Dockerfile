# To build image for this dockerfile use this command:
#   docker build -t terrabrasilis/deter-pantanal-app:vx.y -f env-php/Dockerfile --no-cache .
#
# To run without compose and without shell terminal use this command:
#   docker run -d --rm --name terrabrasilis_deter_sync_client terrabrasilis/deter-pantanal-app:vx.y
#
#--------- Generic stuff all our Dockerfiles should start with so we get caching ------------
FROM php:7-apache

LABEL "br.inpe.dpi"="INPE/DPI-TerraBrasilis" \
br.inpe.dpi.terrabrasilis="App DETER" \
author="Andre Carvalho" \
author.email="andre.carvalho@inpe.br" \
description="Simple App report for DETER"
#-------------Application Specific Stuff ----------------------------------------------------

RUN apt-get -o Acquire::ForceIPv4=true update \
    && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pgsql \
    && rm -rf /var/lib/apt/lists/*


ARG TARGET_IMAGE=""

COPY . /var/www/html/

RUN echo "<?php \$target_image=\"${TARGET_IMAGE}\"; ?>" >> "/var/www/html/target_image.php"

RUN chown -R www-data:www-data /var/www

VOLUME ["/var/www/html/config"]

CMD ["apachectl", "-D", "FOREGROUND"]