#!/bin/bash

TARGET_IMAGE=""
echo "Enter a target name to build: pantanal OR nf?" ; read TARGET_IMAGE
if [[ ! "$TARGET_IMAGE" = "pantanal" && ! "$TARGET_IMAGE" = "nf" ]]; then
    echo "Need a target name for build an image."
    echo "Use: 'pantanal' or 'nf' "
    exit
fi

NO_CACHE=""
echo "Do you want to build using docker cache from previous build? Type yes to use cache." ; read BUILD_CACHE
if [[ ! "$BUILD_CACHE" = "yes" ]]; then
    echo "Using --no-cache to build the image."
    echo "It will be slower than use docker cache."
    NO_CACHE="--no-cache"
else
    echo "Using cache to build the image."
    echo "Nice, it will be faster than use no-cache option."
fi

VERSION=$(git describe --tags --abbrev=0)

echo 
echo "/######################################################################/"
echo " Build new image terrabrasilis/deter-${TARGET_IMAGE}-app:$VERSION "
echo "/######################################################################/"
echo

docker build $NO_CACHE -t "terrabrasilis/deter-${TARGET_IMAGE}-app:$VERSION" --build-arg TARGET_IMAGE=${TARGET_IMAGE} -f env-php/Dockerfile app/

# send to dockerhub
echo 
echo "The building was finished! Do you want sending these new images to Docker HUB? Type yes to continue." ; read SEND_TO_HUB
if [[ ! "$SEND_TO_HUB" = "yes" ]]; then
    echo "Ok, not send the images."
else
    echo "Nice, sending the images!"
    docker push "terrabrasilis/deter-${TARGET_IMAGE}-app:$VERSION"
fi