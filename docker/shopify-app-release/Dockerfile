FROM dockyard.visualsoft.io/vs-platform/shopify-app-release:latest

COPY --chown=www-data:www-data ./ /app

# See https://shopify.dev/docs/api/shopify-cli/app/app-release for how the Shopify CLI release command works
# Via the magic of Semaphore secrets, and Semaphore environment variables, the following environment variables
# are made available from the Semaphore build process and are recognised by the Shopify CLI:
# - SHOPIFY_FLAG_VERSION (used for the --version flag) comes from the semantic version tag of the current release we have deployed and is being confirmed as released. Comes from the Semaphore build promotion being run.
# - SHOPIFY_FLAG_CLIENT_ID (used for the --client flag) is the client ID of the Shopify app being released. Comes from Semaphore secret.
# The --verbose and --force flags are used to ensure the release is processed correctly, without manual intervention.
ARG SHOPIFY_FLAG_VERSION=0
ENV SHOPIFY_FLAG_VERSION ${SHOPIFY_FLAG_VERSION}
ARG SHOPIFY_FLAG_CLIENT_ID=0
ENV SHOPIFY_FLAG_CLIENT_ID ${SHOPIFY_FLAG_CLIENT_ID}
ARG SHOPIFY_APP_AUTOMATION_TOKEN=0
ENV SHOPIFY_APP_AUTOMATION_TOKEN ${SHOPIFY_APP_AUTOMATION_TOKEN}
