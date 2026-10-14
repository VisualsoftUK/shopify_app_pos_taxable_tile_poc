/**
 * Format an integer minor-unit amount as currency.
 *
 * @param {number} amountMinor - Amount in minor units (e.g. pence).
 * @param {string} currencyCode - ISO 4217 currency code.
 * @param {string} [locale] - BCP 47 locale, defaults to the browser locale.
 * @returns {string} Formatted currency string.
 * @example
 * formatMinorUnits(138, 'GBP', 'en-GB') // "£1.38"
 */
export function formatMinorUnits(amountMinor, currencyCode, locale = undefined) {
    const formatter = new Intl.NumberFormat(locale, { style: 'currency', currency: currencyCode });
    const fractionDigits = formatter.resolvedOptions().maximumFractionDigits ?? 2;

    return formatter.format((amountMinor ?? 0) / (10 ** fractionDigits));
}

/**
 * Format an ISO 8601 date (or date-time) string for display.
 *
 * @param {string} value - ISO 8601 date or date-time string.
 * @param {boolean} [shouldIncludeTime] - Whether to include the time of day.
 * @returns {string} Localised date string.
 */
export function formatReportDate(value, shouldIncludeTime = false) {
    const options = shouldIncludeTime === true
        ? { dateStyle: 'medium', timeStyle: 'short' }
        : { dateStyle: 'medium' };

    // Date-only strings are parsed as UTC midnight; pin the time zone so the calendar day does not shift.
    if (shouldIncludeTime === false) {
        options.timeZone = 'UTC';
    }

    return new Intl.DateTimeFormat(undefined, options).format(new Date(value));
}

/**
 * Convert a Date to a YYYY-MM-DD string using its local calendar day.
 *
 * @param {Date} date - The date to convert.
 * @returns {string} The date as YYYY-MM-DD.
 */
export function toDateParam(date) {
    const month = `${date.getMonth() + 1}`.padStart(2, '0');
    const day = `${date.getDate()}`.padStart(2, '0');

    return `${date.getFullYear()}-${month}-${day}`;
}

/**
 * Parse a YYYY-MM-DD string into a local Date (midnight).
 *
 * @param {string|null} value - The date string.
 * @returns {Date|null} The parsed date, or null when empty.
 */
export function fromDateParam(value) {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const [year, month, day] = value.split('-').map(Number);

    return new Date(year, month - 1, day);
}
