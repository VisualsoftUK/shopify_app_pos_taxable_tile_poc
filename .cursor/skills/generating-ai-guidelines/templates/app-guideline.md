# Template: App Guideline

File: `.ai/guidelines/{area}.blade.php`

Brief, always-on. Points to the skill. Nothing more.

```blade
## Media Uploads

- All uploads go through `MediaService`; never write to disk directly.
- Uploaded files are stored in S3 under the `uploads/{tenant}` prefix.
- Virus scanning runs asynchronously via `ScanUploadedFileJob` — do not assume a file is safe immediately after upload.
- Temporary URLs expire after 60 minutes; never cache them.

### Skills
- Activate `media-uploads` when writing or reviewing upload, file storage, or media retrieval code.
```
