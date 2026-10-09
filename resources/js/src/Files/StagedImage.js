import StagedFile from './StagedFile.js';

export default class StagedImage extends StagedFile {
    get resourceType() {
        return 'IMAGE';
    }
}
