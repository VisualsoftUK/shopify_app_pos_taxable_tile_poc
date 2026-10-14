import assert from 'node:assert/strict';
import { describe, it } from 'node:test';
import {
    buildOrderVatSummary,
    formatMoney,
    formatProductName,
    parseBreakdown,
    parseMoneyMetafield,
} from './orderVatSummary.js';

describe('parseMoneyMetafield', () => {
    it('parses a money metafield jsonValue', () => {
        assert.deepEqual(parseMoneyMetafield({ amount: '1.38', currency_code: 'GBP' }), {
            amount: 1.38,
            currencyCode: 'GBP',
        });
    });

    it('returns null for missing or malformed values', () => {
        assert.equal(parseMoneyMetafield(null), null);
        assert.equal(parseMoneyMetafield(undefined), null);
        assert.equal(parseMoneyMetafield({ amount: 'abc', currency_code: 'GBP' }), null);
        assert.equal(parseMoneyMetafield({ amount: '1.00' }), null);
    });
});

describe('formatProductName', () => {
    it('omits the default variant title', () => {
        assert.equal(formatProductName('Cheese Sandwich', 'Default Title'), 'Cheese Sandwich');
    });

    it('joins product and variant titles', () => {
        assert.equal(formatProductName('Latte', 'Large'), 'Latte - Large');
    });
});

describe('parseBreakdown', () => {
    it('maps breakdown entries to rows', () => {
        const rows = parseBreakdown([{
            variantId: 'gid://shopify/ProductVariant/1',
            productTitle: 'Cheese Sandwich',
            variantTitle: 'Default Title',
            sku: 'SAND-1',
            quantity: 2,
            lineTotal: '7.90',
            eatInCharge: '1.58',
            upliftPercent: '20.00',
        }]);

        assert.deepEqual(rows, [{
            key: 'gid://shopify/ProductVariant/1-0',
            productName: 'Cheese Sandwich',
            sku: 'SAND-1',
            quantity: 2,
            lineTotal: 7.9,
            eatInCharge: 1.58,
            upliftPercent: '20.00',
        }]);
    });

    it('returns an empty list for non-array values and skips malformed entries', () => {
        assert.deepEqual(parseBreakdown(null), []);
        assert.deepEqual(parseBreakdown({}), []);
        assert.equal(parseBreakdown([null, 'x', {}]).length, 1);
    });
});

describe('formatMoney', () => {
    it('formats using the currency code', () => {
        assert.equal(formatMoney(1.38, 'GBP', 'en-GB'), '£1.38');
    });

    it('falls back to a plain format for invalid currency codes', () => {
        assert.equal(formatMoney(1.5, 'NOT-A-CODE', 'en-GB'), '1.50 NOT-A-CODE');
    });
});

describe('buildOrderVatSummary', () => {
    it('returns null when the order has not been processed', () => {
        assert.equal(buildOrderVatSummary({ orderType: null }), null);
        assert.equal(buildOrderVatSummary(null), null);
    });

    it('builds a summary from the metafields', () => {
        const summary = buildOrderVatSummary({
            currencyCode: 'EUR',
            orderType: { value: 'eat_in' },
            eatInTotal: { jsonValue: { amount: '1.38', currency_code: 'GBP' } },
            zeroRatedTotal: { jsonValue: { amount: '0.00', currency_code: 'GBP' } },
            vatBreakdown: { jsonValue: [] },
        });

        assert.deepEqual(summary, {
            orderType: 'eat_in',
            currencyCode: 'GBP',
            eatInTotal: 1.38,
            zeroRatedTotal: 0,
            breakdownRows: [],
        });
    });

    it('falls back to the order currency when money metafields are missing', () => {
        const summary = buildOrderVatSummary({
            currencyCode: 'EUR',
            orderType: { value: 'takeaway' },
        });

        assert.equal(summary?.currencyCode, 'EUR');
        assert.equal(summary?.eatInTotal, null);
    });
});
