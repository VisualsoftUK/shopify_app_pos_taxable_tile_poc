# Use the Visualsoft PHP CLI Reverb container as our base image
FROM dockyard.visualsoft.io/vs-platform/php-cli-8.5-reverb:latest

# Copy our files into the container, owned by www-data, the user that php runs under
COPY --chown=www-data:www-data ./app /app/app
COPY --chown=www-data:www-data ./artisan /app/artisan
COPY --chown=www-data:www-data ./bootstrap /app/bootstrap
COPY --chown=www-data:www-data ./composer.json /app
COPY --chown=www-data:www-data ./composer.lock /app
COPY --chown=www-data:www-data ./config /app/config
COPY --chown=www-data:www-data ./database /app/database
COPY --chown=www-data:www-data ./public /app/public
COPY --chown=www-data:www-data ./resources /app/resources
COPY --chown=www-data:www-data ./routes /app/routes
COPY --chown=www-data:www-data ./storage /app/storage
COPY --chown=www-data:www-data ./vendor /app/vendor

RUN chmod -R ugo+rwx /app/bootstrap/
RUN chmod -R ugo+rwx /app/storage/

# Take in build arguments and set them as environment variables for the version and build of code
ARG APP_VERSION
ENV APP_VERSION=$APP_VERSION
ARG APP_BUILD
ENV APP_BUILD=$APP_BUILD
ARG APP_BUILD_VERSION
ENV APP_BUILD_VERSION=$APP_BUILD_VERSION

# `/app/command.sh` is the command that this container will run
# Run `php artisan optimize` to cache configs before launch
# Run `php artisan reverb:start` to start the Reverb WebSocket server
RUN echo "php artisan optimize; php artisan reverb:start --debug 2>&1" > /app/command.sh
