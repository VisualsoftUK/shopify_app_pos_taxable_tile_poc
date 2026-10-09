/**
 *  StagedFile (and its descendents) is a utility class that handles uploading
 *  a file to Shopify, or other file hosting service
 */
export default class StagedFile {
    /**
     *  File object
     */
    #file;

    /**
     *  File object URL (used for previews)
     */
    #fileObjectUrl;

    /**
     *  Assigned & uploaded resource ID (value)
     */
    #uploadedResourceId;

    /**
     *  Uploaded resource URL
     */
    #uploadedResourceUrl;

    /**
     *  Constructor
     */
    constructor(file) {
        this.#file = file;
    }

    /**
     *  Get file
     */
    get file() {
        return this.#file;
    }

    /**
     *  Get resource type
     */
    get resourceType() {
        return 'FILE';
    }

    /**
     *  Get resource URL
     *
     *  If the file has not yet been uploaded to an external service yet, it will
     *  attempt to create a local URL that can be used on the browser
     */
    get resourceUrl() {
        if (this.#uploadedResourceUrl) {
            return this.#uploadedResourceUrl;
        }
        if (this.#file) {
            if (!this.#fileObjectUrl) {
                this.#fileObjectUrl = URL.createObjectURL(this.#file);
            }
            return this.#fileObjectUrl;
        }

        return null;
    }

    /**
     *  Set resource URL
     */
    set resourceUrl(resourceUrl) {
        if (this.#uploadedResourceUrl) {
            throw new Error('Cannot reassign resource URL');
        }
        if (this.#fileObjectUrl) {
            URL.revokeObjectURL(this.#fileObjectUrl);
        }
        
        this.#fileObjectUrl = undefined;
        this.#uploadedResourceUrl = resourceUrl;
    }

    /**
     *  Determine if file has been uploaded
     */
    get uploaded() {
        return !!this.#uploadedResourceUrl;
    }

    /**
     *  Get resource ID
     */
    get value() {
        return this.#uploadedResourceId || null;
    }

    /**
     *  Set resource ID
     */
    set value(uploadedResourceId) {
        if (this.value) {
            throw new Error('Cannot reassign resource ID');
        }

        return this.#uploadedResourceId = uploadedResourceId;
    }

    /**
     *  Get attributes for staging this file
     */
    stagedFileAttributes() {
        return {
            httpMethod: 'POST',
            mimeType: this.#file.type,
            filename: this.#file.name,
            fileSize: this.#file.size.toString(),
            resource: this.resourceType,
        };
    }

    /**
     *  Get attributes for creating and assigning this file
     *
     *  @see https://shopify.dev/docs/api/admin-graphql/latest/mutations/fileCreate
     */
    createFileAttributes() {
        if (!this.uploaded) {
            throw new Error('File needs to be uploaded before it can be assigned');
        }

        return {
            originalSource: this.#uploadedResourceUrl,
        };
    }

    /**
     *  Convert to array
     */
    toArray() {
        return {
            value: this.value,
            resourceUrl: this.resourceUrl,
        };
    }

    /**
     *  JSON encodable value
     */
    toJSON() {
        return this.toArray();
    }
}
