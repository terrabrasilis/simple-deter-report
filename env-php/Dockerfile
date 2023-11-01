# To build image for this dockerfile use this command:
#   docker build -t terrabrasilis/deter-pantanal-app:vx.y -f env-php/Dockerfile --no-cache .
#
# To run without compose and without shell terminal use this command:
#   docker run -d --rm --name terrabrasilis_deter_sync_client terrabrasilis/deter-pantanal-app:vx.y
#
#--------- Generic stuff all our Dockerfiles should start with so we get caching ------------
FROM php:7-apache

LABEL "br.inpe.dpi"="INPE/DPI-TerraBrasilis" \
br.inpe.dpi.terrabrasilis="App DETER Pantanal" \
author="Andre Carvalho" \
author.email="andre.carvalho@inpe.br" \
description="Simple App report for DETER Pantanal"
#-------------Application Specific Stuff ----------------------------------------------------

RUN apt-get -o Acquire::ForceIPv4=true update \
    && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pgsql \
    && rm -rf /var/lib/apt/lists/*


COPY app/ /var/www/html/

VOLUME ["/usr/local/phpconf"]

CMD [ "service", "apache2", "start" ]