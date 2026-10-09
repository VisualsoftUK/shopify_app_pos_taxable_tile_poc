# Template: App Skill

File: `.ai/skills/{slug}-{area}/SKILL.md`

Deeper context, loaded on demand.

```markdown
---
name: broadcast-media-uploads
description: Covers the media upload pipeline including storage, virus scanning, and retrieval. Use when working with file uploads, MediaService, S3 storage, or media URLs.
---

# Media Uploads

## Quick reference

- `MediaService::upload(UploadedFile $file, User $owner): Media` — primary entry point
- `ScanUploadedFileJob` — dispatched automatically after upload; sets `Media::$scanned_at`
- Never access `Media::$path` directly; always call `Media::temporaryUrl()`

## Upload flow

1. Controller receives `UploadedRequest`, calls `UploadMediaAction::for($request)->upload()`
2. Action delegates storage to `MediaService`, dispatches `ScanUploadedFileJob`
3. Job updates `scanned_at`; listeners on `MediaScanned` event handle downstream logic

## Common patterns

Generating a temporary URL:
```php
$url = $media->temporaryUrl(); // valid for 60 minutes
```

Checking scan status before serving:
```php
if ($media->scanned_at === null) {
    abort(423, 'File is pending virus scan.');
}
```
```
