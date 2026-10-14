import assert from 'node:assert/strict';
import { describe, it } from 'node:test';
import {
    buildOrderTypeCartProperties,
    getCartOrderType,
    isValidOrderType,
    parseDefaultOrderType,
    resolveOrderType,
    toggleOrderType,
} from './orderType.js';

describe('isValidOrderType', () => {
    it('accepts the supported order types', () => {
        assert.equal(isValidOrderType('eat_in'), true);
        assert.equal(isValidOrderType('takeaway'), true);
    });

    it('rejects anything else', () => {
        assert.equal(isValidOrderType('dine_in'), false);
        assert.equal(isValidOrderType(''), false);
        assert.equal(isValidOrderType(undefined), false);
        assert.equal(isValidOrderType(null), false);
    });
});

describe('getCartOrderType', () => {
    it('returns the order type stored on the cart', () => {
        assert.equal(getCartOrderType({ _order_type: 'eat_in' }), 'eat_in');
    });

    it('returns null when the property is absent or invalid', () => {
        assert.equal(getCartOrderType({}), null);
        assert.equal(getCartOrderType({ _order_type: 'unknown' }), null);
        assert.equal(getCartOrderType(undefined), null);
    });
});

describe('resolveOrderType', () => {
    it('prefers the cart property over the shop default', () => {
        assert.equal(resolveOrderType({ _order_type: 'takeaway' }, 'eat_in'), 'takeaway');
    });

    it('uses the shop default when the cart has no order type', () => {
        assert.equal(resolveOrderType({}, 'eat_in'), 'eat_in');
    });

    it('falls back to takeaway when nothing valid is available', () => {
        assert.equal(resolveOrderType({ _order_type: 'bogus' }, 'bogus'), 'takeaway');
        assert.equal(resolveOrderType(undefined, undefined), 'takeaway');
    });
});

describe('toggleOrderType', () => {
    it('switches between eat in and takeaway', () => {
        assert.equal(toggleOrderType('eat_in'), 'takeaway');
        assert.equal(toggleOrderType('takeaway'), 'eat_in');
    });
});

describe('buildOrderTypeCartProperties', () => {
    it('builds the hidden and visible properties from the contract', () => {
        assert.deepEqual(buildOrderTypeCartProperties('eat_in'), {
            _order_type: 'eat_in',
            'Order type': 'Eat In',
        });
        assert.deepEqual(buildOrderTypeCartProperties('takeaway'), {
            _order_type: 'takeaway',
            'Order type': 'Takeaway',
        });
    });
});

describe('parseDefaultOrderType', () => {
    it('reads a valid default from the config response', () => {
        assert.equal(parseDefaultOrderType({ defaultOrderType: 'eat_in' }), 'eat_in');
    });

    it('returns null for missing or invalid values', () => {
        assert.equal(parseDefaultOrderType({ defaultOrderType: 'other' }), null);
        assert.equal(parseDefaultOrderType({}), null);
        assert.equal(parseDefaultOrderType(null), null);
    });
});
