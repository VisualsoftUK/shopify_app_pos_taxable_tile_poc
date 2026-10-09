import StagedFile from '../Files/StagedFile.js';
import { uploadStagedFile } from '../Files/Utils.js';

/**
 *  Fetch metafields
 */
export function fetchMetafields(callback) {
    fetch('/app/metafields')
        .then((res) => res.json())
        .then((res) => callback(res));
}

/**
 *  Update metafields
 */
export async function updateMetafields(metafields) {
    const body = await processMetafields(metafields);

    const res = await fetch('/app/metafields', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(body),
    });

    if (!res.ok) {
        const error = new Error();
        const data = await res.json();

        if (data.errors) {
            error.metafieldErrors = data.errors;
        }

        throw error;
    }

    return true;
}

/**
 *  Process metafields into something that can be submitted
 */
export async function processMetafields(metafields) {
    const data = { ...metafields };

    // loop through to find any staged files
    const stagedFiles = [];
    const stagedFilesIterator = (item) => {
        // todo: account for arrays and nested objects - not yet needed in
        // this proof of concept but trivial to add later on
        if (item instanceof StagedFile) {
            stagedFiles.push(item);
        }
    };

    Object.values(data).forEach(stagedFilesIterator);

    if (stagedFiles.length) {
        await processStagedFiles(stagedFiles);
    }

    return data;
}

/**
 *  Process staged files
 */
export async function processStagedFiles(stagedFiles) {
    await uploadPendingStagedFiles(stagedFiles);
    await assignPendingStagedFiles(stagedFiles);

    return stagedFiles;
}

/**
 *  Upload staged files
 */
async function uploadPendingStagedFiles(stagedFiles) {
    // we only want to stage things that are files, and which haven't been previously
    // uploaded somewhere else
    const pendingFiles = stagedFiles
        .filter((file) => file instanceof StagedFile)
        .filter((file) => !file.uploaded && !file.value);

    if (!pendingFiles.length) {
        return;
    }

    // build attributes and stage files
    const body = pendingFiles.map((file) => file.stagedFileAttributes());

    const res = await fetch('/app/uploads/stage', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(body),
    });

    const data = await res.json();

    // upload our individually staged files to destination
    return Promise.all(
        pendingFiles.map((file, index) => uploadStagedFile(file, data.results[index]))
    );
}

/**
 *  Create and assign our staged files
 */
async function assignPendingStagedFiles(stagedFiles) {
    // find files that have been uploaded, but have not yet been assigned to
    // the Shopify media library
    const pendingFiles = stagedFiles
        .filter((file) => file instanceof StagedFile)
        .filter((file) => file.uploaded && !file.value);

    if (!pendingFiles.length) {
        return;
    }

    // build attributes and assign files
    const body = pendingFiles.map((file) => file.createFileAttributes());

    const res = await fetch('/app/metafields/files', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(body),
    });

    const data = await res.json();

    // assign our resource ID to our staged file - and complete all the steps
    // needed to turn a staged file asset into something that Shopify can understand
    pendingFiles.forEach((file, index) => {
        file.value = data.results[index];
    });
}
