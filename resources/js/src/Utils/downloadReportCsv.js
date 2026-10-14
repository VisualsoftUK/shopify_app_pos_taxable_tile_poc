/**
 * Download a report section as CSV. The request goes through the App Bridge
 * authenticated fetch, then the response is saved via a temporary object URL.
 *
 * @param {Object} filters - Current report filters ({ from, to, order_type, channel }).
 * @param {string} type - Report section: 'orders' | 'daily' | 'variants' | 'customers'.
 * @returns {Promise<void>} Resolves once the download has been triggered.
 * @throws {Error} When the export request fails.
 */
export async function downloadReportCsv(filters, type) {
    const params = new URLSearchParams();

    Object.entries({ ...filters, type }).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            params.append(key, value);
        }
    });

    const response = await fetch(`/app/reports/export?${params.toString()}`, {
        headers: { Accept: 'text/csv' },
    });

    if (response.ok === false) {
        throw new Error(`Report export failed with status ${response.status}`);
    }

    const blob = await response.blob();
    const objectUrl = URL.createObjectURL(blob);
    const disposition = response.headers.get('Content-Disposition') ?? '';
    const filename = disposition.match(/filename="?([^"]+)"?/)?.[1] ?? `eat-in-${type}.csv`;

    const anchor = document.createElement('a');
    anchor.href = objectUrl;
    anchor.download = filename;
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();

    URL.revokeObjectURL(objectUrl);
}
