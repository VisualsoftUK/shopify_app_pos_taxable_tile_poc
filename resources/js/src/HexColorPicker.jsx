import convert from 'color-convert';
import { BlockStack, ColorPicker, TextField } from '@shopify/polaris';
import { useState } from 'react';

export default function HexColorPicker({ label, value, error, onChange }) {
    // Shopify's colour picker does not support RGB/Hex output therefore some magic
    // will need to occur to convert between HSV and Hex
    // NOTE: generally we should avoid having two states representing the same property
    // however Shopify in its infinite wisdom, only allows storing Hex colours yet the
    // colour picker only returns HSV, and attempting to convert between Hex and HSV on
    // every page load causes uncomfortable behaviour with the colour picker itself
    const initialColor = convert.hex.hsv(value);

    const [ colorPicker, setColorPicker ] = useState({
        hue: initialColor[0],
        saturation: initialColor[1] / 100,
        brightness: initialColor[2] / 100,
    });

    const colorPickerChange = (data) => {
        if (data) {
            onChange('#' + convert.hsv.hex(data.hue, data.saturation * 100, data.brightness * 100));
        }

        return setColorPicker(data);
    };

    const colorTextChange = (hex) => {
        const newColor = convert.hex.hsv(hex);

        setColorPicker({
            hue: newColor[0],
            saturation: newColor[1] / 100,
            brightness: newColor[2] / 100,
        });

        onChange(hex);
    };

    return (
        <BlockStack gap="400">
            <TextField label={label} value={value} error={error} onChange={colorTextChange} />
            <ColorPicker onChange={colorPickerChange} color={colorPicker} />
        </BlockStack>
    );
}
