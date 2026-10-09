# Use the Visualsoft PHP FPM container as our base image
FROM dockyard.visualsoft.io/vs-platform/php-fpm-8.5:latest

# Copy our files into the container, owned by www-data, the user that php-fpm runs under
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

# Take in build arguments for `sentry_deploy.php` to use
ARG SENTRY_URL
ARG SENTRY_ORG
ARG SENTRY_REF
ARG SENTRY_AUTH_TOKEN
ARG SENTRY_PROJECT

# `/app/command.sh` will run before php-fpm starts
# Run `sentry_deploy.php` on launch to tell sentry this release has been deployed - this is baked into the container
# Run `php artisan optimize` to cache configs before launch
# APP_ENV is defined by DevOps on deploy - it is not baked into the container
RUN echo "php sentry_deploy.php --SENTRY_URL=$SENTRY_URL --SENTRY_ORG=$SENTRY_ORG --SENTRY_REF=$SENTRY_REF --SENTRY_AUTH_TOKEN=$SENTRY_AUTH_TOKEN --APP_ENV=\$APP_ENV; php artisan migrate --force; php artisan optimize; php artisan schedule-monitor:sync" > /app/command.sh

