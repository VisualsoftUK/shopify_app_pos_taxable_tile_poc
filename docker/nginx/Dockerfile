# Use the Visualsoft Nginx container as our base image
FROM dockyard.visualsoft.io/vs-platform/nginx-php-fpm:latest

# Copy our **public** files into the container, owned by www-data, the user that nginx runs under
COPY --chown=www-data:www-data public /app
