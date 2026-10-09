#!/usr/bin/env bash
set -euo pipefail

echo "Starting build failure notification..."

TAG="${SEMAPHORE_GIT_TAG_NAME:-}"
BRANCH="${SEMAPHORE_GIT_WORKING_BRANCH:-${SEMAPHORE_GIT_BRANCH:-}}"
ENVIRONMENT=""

if [[ "$TAG" =~ ^v[0-9]+\.[0-9]+\.[0-9]+-rc[0-9]+$ ]]; then
  ENVIRONMENT="acceptance"
elif [[ "$TAG" =~ ^v[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  ENVIRONMENT="production"
elif [ "$BRANCH" = "main" ]; then
  ENVIRONMENT="testing"
fi

if [ -z "$ENVIRONMENT" ]; then
  echo "Skipping failure notification: git ref is not main, an rc tag, or a release tag."
  exit 0
fi

case "$ENVIRONMENT" in
  testing)
    ENVIRONMENT_LABEL="Testing"
    NOTIFICATION_WEBHOOK_URL="${WEBHOOK_URL_TESTING:-}"
    ;;
  acceptance)
    ENVIRONMENT_LABEL="Acceptance"
    NOTIFICATION_WEBHOOK_URL="${WEBHOOK_URL_ACCEPTANCE:-}"
    ;;
  production)
    ENVIRONMENT_LABEL="Production"
    NOTIFICATION_WEBHOOK_URL="${WEBHOOK_URL_PRODUCTION:-}"
    ;;
esac

if [ -z "${NOTIFICATION_WEBHOOK_URL}" ]; then
  echo "Warning: environment-specific webhook URL is missing for ${ENVIRONMENT}; falling back to WEBHOOK_URL."
  NOTIFICATION_WEBHOOK_URL="${WEBHOOK_URL:-}"
fi

if [ -z "${NOTIFICATION_WEBHOOK_URL}" ]; then
  echo "Error: webhook URL is missing for ${ENVIRONMENT}."
  exit 1
fi

COMMIT_SHA="${SEMAPHORE_GIT_SHA:-}"
COMMIT_SHORT="${COMMIT_SHA:0:7}"
if [ -z "$COMMIT_SHORT" ]; then
  COMMIT_SHORT="N/A"
fi

PAYLOAD=$(cat <<EOF
{
  "cardsV2": [
    {
      "cardId": "semaphoreFailureNotification",
      "card": {
        "header": {
          "title": "🚨 Build Pipeline Failed",
          "subtitle": "Semaphore CI/CD · ${ENVIRONMENT_LABEL}",
          "imageUrl": "https://api.iconify.design/material-symbols/crisis-alert.svg?color=%23ff0000&height=48",
          "imageType": "CIRCLE"
        },
        "sections": [
          {
            "widgets": [
              {
                "decoratedText": {
                  "topLabel": "Job Name",
                  "text": "<b>${SEMAPHORE_JOB_NAME:-Unknown Job}</b>"
                }
              },
              {
                "decoratedText": {
                  "topLabel": "Repository",
                  "text": "<b>${SEMAPHORE_GIT_REPO_SLUG:-N/A}</b>"
                }
              },
              {
                "decoratedText": {
                  "topLabel": "Branch / Commit",
                  "text": "<code>${BRANCH:-N/A}</code> (${COMMIT_SHORT})"
                }
              },
              {
                "decoratedText": {
                  "topLabel": "Triggered By",
                  "text": "${SEMAPHORE_GIT_COMMITTER:-Unknown}"
                }
              },
              {
                "buttonList": {
                  "buttons": [
                    {
                      "text": "Open Semaphore Logs",
                      "onClick": {
                        "openLink": {
                          "url": "${SEMAPHORE_ORGANIZATION_URL:-}/jobs/${SEMAPHORE_JOB_ID:-}"
                        }
                      }
                    }
                  ]
                }
              }
            ]
          }
        ]
      }
    }
  ]
}
EOF
)

echo "Sending failure notification to Google Chat (${ENVIRONMENT})..."
curl -X POST -H 'Content-Type: application/json; charset=UTF-8' \
  --data "$PAYLOAD" \
  "$NOTIFICATION_WEBHOOK_URL"
