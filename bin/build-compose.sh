#!/bin/sh

if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# You may need to run this initial setup
#brew install nvm
#mkdir -p ~/.nvm
#cd ~/.nvm
#rm nvm.sh
#rm nvm-exec
#ln -s $(brew --prefix nvm)/nvm.sh
#ln -s $(brew --prefix nvm)/nvm-exec

# To clear the existing database just run
#rm database/database.sqlite

# Create the database if it doesn't already exist
touch database/database.sqlite


docker-compose -f docker-compose.yml down
docker run -e GITHUB_ACCESS_TOKEN="$GITHUB_ACCESS_TOKEN" --rm -it -v "$PWD:/app:Z" dockyard.visualsoft.io/vs-platform/shopify-app-release:latest npm install
docker run -e GITHUB_ACCESS_TOKEN="$GITHUB_ACCESS_TOKEN" --rm -it -v "$PWD:/app:Z" dockyard.visualsoft.io/vs-platform/shopify-app-release:latest npm run shopify-ext-prod
docker run -e GITHUB_ACCESS_TOKEN="$GITHUB_ACCESS_TOKEN" --rm -it -v "$PWD:/app:Z" dockyard.visualsoft.io/vs-platform/shopify-app-release:latest npm run shopify-build
docker run -e GITHUB_ACCESS_TOKEN="$GITHUB_ACCESS_TOKEN" --rm -it -v "$PWD:/app:Z" dockyard.visualsoft.io/vs-platform/php-cli-8.5:latest composer-install-dev
docker run --rm -it -v "$PWD:/app:Z" dockyard.visualsoft.io/vs-platform/php-cli-8.5:latest php artisan migrate
docker run -e GITHUB_ACCESS_TOKEN="$GITHUB_ACCESS_TOKEN" --rm -it -v "$PWD:/app:Z" dockyard.visualsoft.io/vs-platform/node-22:latest npm-run-prod
docker-compose -f docker-compose.yml build
docker-compose -f docker-compose.yml up -d
docker run --rm -it -v "$PWD:/app:Z" dockyard.visualsoft.io/vs-platform/node-22:latest npm run shopify-dev
