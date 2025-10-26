// Export an initializer so other modules can import and control when the chart is created.
// Usage: import { initChart } from './controllers/chartControllers.js';
// then call initChart() after the DOM is ready.

export async function initChart(containerSelector = '.chart-container') {
    const chartContainer = document.querySelector(containerSelector);
    if (!chartContainer) {
        throw new Error(`Chart container not found for selector: ${containerSelector}`);
    }

    // If the container has zero height (rare), give it a sensible default so the chart can render.
    const rect = chartContainer.getBoundingClientRect();
    if (rect.height === 0) {
        chartContainer.style.minHeight = '400px';
    }

    // The library global is `LightweightCharts` (not "LightweightsCharts"). Ensure the lib is loaded
    // before calling this function (index.php will include the CDN script before the module).
    if (typeof LightweightCharts === 'undefined') {
        throw new Error('LightweightCharts is not loaded. Make sure the library script is included before this module.');
    }

    const chart = LightweightCharts.createChart(chartContainer);

    // Resolve JSON path relative to this module file so it works irrespective of how the page was loaded.
    const dataUrl = new URL('../data/forex_candlestick_data.json', import.meta.url).href;
    const res = await fetch(dataUrl);
    if (!res.ok) {
        throw new Error(`Failed to fetch chart data: ${res.status} ${res.statusText}`);
    }
    const payload = await res.json();

    // The JSON file stores data under `data` and uses `timestamp` keys.
    // LightweightCharts expects an array of points with a `time` field
    // (either a unix timestamp in seconds or a date string) and OHLC keys.
    const raw = Array.isArray(payload) ? payload : payload.data || [];
    const formatted = raw.map(item => {
        // Support both `timestamp` (ISO string) or numeric `time` values.
        let time;
        if (item.timestamp) {
            // convert ISO timestamp to unix seconds
            const ms = Date.parse(item.timestamp);
            if (!Number.isFinite(ms)) {
                // fallback: if timestamp is already a number in seconds
                time = Number(item.timestamp);
            } else {
                time = Math.floor(ms / 1000);
            }
        } else if (item.time) {
            time = item.time;
        }

        return {
            time,
            open: item.open,
            high: item.high,
            low: item.low,
            close: item.close,
        };
    });

    const candlestickSeries = chart.addSeries(LightweightCharts.CandlestickSeries);
    candlestickSeries.setData(formatted);

    return { chart, candlestickSeries };
}


