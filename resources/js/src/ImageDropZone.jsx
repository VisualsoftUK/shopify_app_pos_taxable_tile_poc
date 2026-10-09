import StagedImage from '@/Files/StagedImage.js';
import { 
    BlockStack,
    Button,
    DropZone,
    InlineError,
    InlineGrid,
    InlineStack,
    Text,
    Thumbnail
} from '@shopify/polaris';
import { DeleteIcon } from '@shopify/polaris-icons';
import { useId } from 'react';

function ImageThumbnail({ item }) {
    return <Thumbnail source={item.resourceUrl} size="large" />
}

export default function ImageDropZone({ label, values, error, onChange, t }) {
    const dropZoneId = useId();

    // ensure we remove any empty values
    values = values.filter((item) => !!item);

    // dev note: at present, this component only accepts one file, despite everything
    // relating to this component being an array. please assume for the moment that
    // allowMultiple will always be falsy
    const handleDrop = (files) => {
        onChange(files.map((file) => new StagedImage(file)));
    };

    const uploadButton = <DropZone.FileUpload actionTitle={t('dropzone.upload_image')} />;

    const listFiles = (
        <InlineStack>
            {values.map((item, index) => <ImageThumbnail key={index} item={item} /> )}
        </InlineStack>
    );

    // handle resetting of values
    const clearValues = () => {
        onChange([]);
    };

    // display error message
    const errorMessage = error && <InlineError message={error} fieldID={dropZoneId} />

    // render
    return (
        <BlockStack gap="200">
            <InlineGrid columns="2">
                <Text as="legend">{label}</Text>

                <InlineStack align="end">
                    <Button icon={DeleteIcon} onClick={clearValues}>
                        {t('dropzone.remove_images')}
                    </Button>
                </InlineStack>
            </InlineGrid>

            <DropZone
                id={dropZoneId}
                accept="image/*"
                type="image"
                allowMultiple={false}
                onDrop={handleDrop}
            >
                {!values.length ? uploadButton : listFiles}
            </DropZone>

            {errorMessage}
        </BlockStack>
    );
}
