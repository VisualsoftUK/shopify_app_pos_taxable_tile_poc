#!/bin/bash

# Script to swap extension environment files that will have been committed
# to the repository with UID values, out to be shopify.extension.toml files
# with the correct UID values for the environment of the build.
# Usage: ./bin/swap-extension-toml-environments.sh <targetEnvironment>
# Where targetEnvironment is one of: testing, acceptance, production

set -e

TARGET_ENV="$1"

# Validate the target environment
case "$TARGET_ENV" in
    testing|acceptance|production)
        # Valid environment
        ;;
    *)
        echo "Error: Invalid target environment '$TARGET_ENV'"
        echo "Must be one of: testing, acceptance, production"
        exit 1
        ;;
esac

echo "Swapping extension files to '$TARGET_ENV' environment..."

# Function to validate UID in a TOML file
validate_uid() {
    local file="$1"

    if [ ! -f "$file" ]; then
        echo "  ! File does not exist: $(basename "$file")"
        return 1
    fi

    # Look for uid line and extract the value
    local uid_line=$(grep -E '^[[:space:]]*uid[[:space:]]*=' "$file" || true)
    if [ -z "$uid_line" ]; then
        echo "  ! No UID line found in $(basename "$file")"
        return 1
    fi

    # Extract the UID value (everything after = and between quotes) and check if it is not empty
    local uid_value=$(echo "$uid_line" | sed -E 's/^[[:space:]]*uid[[:space:]]*=[[:space:]]*["\x27]([^"\x27]*)["\x27].*/\1/')
    if [ -z "$uid_value" ]; then
        echo "  ! Empty UID value found in $(basename "$file"): $uid_line"
        return 1
    fi

    echo "  ✓ Valid UID found: $uid_value"
    return 0
}

# Iterate through extension directories
for extension_dir in extensions/*/; do
    # Skip if no directories found
    [ ! -d "$extension_dir" ] && continue

    extension_name=$(basename "$extension_dir")

    # Look for the source environment file and target base file
    source_file="${extension_dir}shopify.extension.toml.${TARGET_ENV}"
    target_file="${extension_dir}shopify.extension.toml"

    echo "Processing extension: $extension_name"

    if [ -f "$source_file" ]; then
        if mv "$source_file" "$target_file"; then
            echo "  → Moved $(basename "$source_file") to $(basename "$target_file")"

            # Validate the UID in the target file
            if ! validate_uid "$target_file"; then
                echo "  ! UID validation failed for $extension_name"
                exit 1
            fi
        else
            echo "  ! Failed to move $(basename "$source_file") to $(basename "$target_file")"
            exit 1
        fi
    else
        echo "  ! No $TARGET_ENV environment file found ($(basename "$source_file"))"
        exit 1
    fi
done

echo "All extension files swapped and UID validation passed!"
