#!/bin/bash

# import environment variables if the environment we are currently running
# from is not CI/CD
if [ -z "$CI" ]; then
    if [ ! -f ".env" ]; then
        cp .env.example .env
    fi

    source .env
fi

# basic sanity check
if [ -z "$SHOPIFY_API_KEY" ]; then
    echo "Missing Shopify API key - are environment variables set?"
    exit 1
fi

# declare critical environment variables
export SHOPIFY_APP_NAME=${SHOPIFY_APP_NAME:-$APP_NAME}
export SHOPIFY_API_KEY=${SHOPIFY_API_KEY}
export SHOPIFY_APP_URL=${SHOPIFY_APP_URL:-"$APP_URL/app"}
export SHOPIFY_API_VERSION=${SHOPIFY_API_VERSION:-"2026-04"}

# declare optional environment variables
export SHOPIFY_APP_HANDLE=${SHOPIFY_APP_HANDLE:-"main"}
export SHOPIFY_APP_REDIRECT_URL=${SHOPIFY_APP_REDIRECT_URL:-"$APP_URL/authenticate"}
export SHOPIFY_APP_PREFERENCES_URL=${SHOPIFY_APP_PREFERENCES_URL:-"$APP_URL/settings"}
export SHOPIFY_API_SCOPES=${SHOPIFY_API_SCOPES:-"read_files,write_files"}
export SHOPIFY_WEBHOOKS_URL=${SHOPIFY_WEBHOOKS_URL:-"$APP_URL/webhook"}

# substitute into config file
envsubst < shopify.app.toml.envsubst | tee shopify.app.toml

# Process extension templates for each environment
echo
echo "Starting extension UID TOML flip-flopping..."
environments=("local" "testing" "acceptance" "production")

for extension_dir in extensions/*/; do
    # Skip if no directories found (app might not have any extensions)
    [ ! -d "$extension_dir" ] && continue

    extension_name=$(basename "$extension_dir")
    template_file="${extension_dir}shopify.extension.tomlsubst"

    echo "Handling extension: $extension_name"

    if [ -f "$template_file" ]; then
        echo "  → Processing template: $template_file"

        # Generate files for each environment
        for env in "${environments[@]}"; do
            env_label="$env"

            # For local environment, use base TOML filename to work with straight away; for others, add environment suffix and relevant Semaphore pipeline can swap them out as required
            if [ "$env" = "local" ]; then
                output_file="${extension_dir}shopify.extension.toml"
            else
                output_file="${extension_dir}shopify.extension.toml.${env}"
            fi

            # Check if file already exists for the environment, if not then generate it
            if [ -f "$output_file" ]; then
                echo "    ! $(basename "$output_file") already exists (skipping)"
            else
                # For production environment, we don't want to add any environment-based suffix to the name or handle
                if [ "$env" = "production" ]; then
                    export SHOPIFY_EXTENSION_ENVIRONMENT_NAME_SUFFIX=""
                    export SHOPIFY_EXTENSION_ENVIRONMENT_HANDLE_SUFFIX=""
                else
                    export SHOPIFY_EXTENSION_ENVIRONMENT_NAME_SUFFIX=" ($env)"
                    export SHOPIFY_EXTENSION_ENVIRONMENT_HANDLE_SUFFIX="-$env"
                fi

                if envsubst < "$template_file" | tee "$output_file" > /dev/null; then
                    echo "    ✓ Generated: $output_file"
                else
                    echo "    ✗ Failed to generate $env_label file for: $extension_name"
                    exit 1
                fi
            fi
        done
    else
        echo "  ! No template file found (${template_file}), skipping"
    fi
done

echo "Extension UID TOML flip-flopping complete"

exit 0
