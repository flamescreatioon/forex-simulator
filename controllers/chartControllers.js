// Export an initializer so other modules can import and control when the chart is created.
// Usage: import { initChart } from './controllers/chartControllers.js';
// then call initChart() after the DOM is ready.

export async function initChart(containerSelectorOrElement) {
    // Default selector targets the specific chart container within #chart section
    const defaultSelector = '#chart .chart-container';
    const selectorUsed = typeof containerSelectorOrElement === 'string' && containerSelectorOrElement
        ? containerSelectorOrElement
        : defaultSelector;
    console.log('initChart called with selector/element:', selectorUsed);

    // Resolve container from selector or element
    const resolveContainer = (selOrEl) => {
        if (selOrEl instanceof HTMLElement) return selOrEl;
        const sel = typeof selOrEl === 'string' ? selOrEl : defaultSelector;
        let el = document.querySelector(sel);
        if (!el) {
            // Fallback: create a .chart-container inside #chart if #chart exists
            const chartSection = document.getElementById('chart');
            if (chartSection) {
                el = document.createElement('div');
                el.className = 'chart-container';
                el.style.minHeight = '400px';
                chartSection.appendChild(el);
                console.warn('Created missing .chart-container inside #chart');
                return el;
            }
        }
        return el;
    };

    const chartContainer = resolveContainer(containerSelectorOrElement);
    if (!chartContainer) {
        console.error('Chart container not found for selector:', selectorUsed);
        throw new Error(`Chart container not found for selector: ${selectorUsed}`);
    }
    
    console.log('Chart container found:', chartContainer);
    console.log('Container dimensions:', chartContainer.getBoundingClientRect());

    // If the container has zero height (rare), give it a sensible default so the chart can render.
    const rect = chartContainer.getBoundingClientRect();
    if (rect.height === 0) {
        console.warn('Container has zero height, setting minHeight to 400px');
        chartContainer.style.minHeight = '400px';
    }
    if (rect.width === 0) {
        console.warn('Container width is 0; attempting to expand to parent width');
        chartContainer.style.width = '100%';
    }

    // If container is hidden (display:none) or still size-less, wait until visible
    const isRenderable = (el) => el && el.offsetParent !== null && (el.clientWidth > 0 && el.clientHeight > 0);
    if (!isRenderable(chartContainer)) {
        console.warn('Chart container not renderable yet; waiting for layout...');
        await new Promise((resolve) => requestAnimationFrame(() => setTimeout(resolve, 50)));
    }

    // The library global is `LightweightCharts` (not "LightweightsCharts"). Ensure the lib is loaded
    // before calling this function (index.php will include the CDN script before the module).
    if (typeof LightweightCharts === 'undefined') {
        console.error('LightweightCharts library is not loaded!');
        throw new Error('LightweightCharts is not loaded. Make sure the library script is included before this module.');
    }
    
    console.log('LightweightCharts library detected:', typeof LightweightCharts);

    // Lock a stable height to prevent feedback loops that grow the container
    const measured = chartContainer.getBoundingClientRect();
    const initialHeight = Math.max(300, Math.floor(measured.height) || 400);
    // Explicitly set container height to a stable pixel value
    chartContainer.style.height = initialHeight + 'px';

    const createChartInstance = (container) => LightweightCharts.createChart(container, {
        layout: {
            background: { color: '#ffffff' },
            textColor: '#333',
        },
        grid: {
            vertLines: { color: '#f0f0f0' },
            horzLines: { color: '#f0f0f0' },
        },
        timeScale: {
            timeVisible: true,
            secondsVisible: false,
        },
        width: container.clientWidth,
        height: initialHeight,
    });
    
    console.log('Chart created successfully');

    // Build absolute URLs relative to the current page directory.
    // This works no matter how deeply nested the app is (e.g., /, /new_forex/, /apps/forex/web/).
    const origin = window.location.origin;
    const baseDir = window.location.pathname.replace(/\/[^\/]*$/, '/'); // ensure trailing slash
    
    const getConfiguredPairs = () => {
        const m = document.cookie.match(/(?:^|; )pairs=([^;]+)/);
        if (!m) return [];
        try {
            const decoded = decodeURIComponent(m[1]);
            return decoded.split(',').map(s => s.trim()).filter(Boolean);
        } catch {
            return [];
        }
    };

    const pairs = getConfiguredPairs();

    const fetchCandles = async (pair) => {
        const url = `${origin}${baseDir}helpers/candles.php?pair=${encodeURIComponent(pair)}`;
        const res = await fetch(url);
        if (!res.ok) throw new Error(`Failed to fetch chart data: ${res.status}`);
        return res.json();
    };

    // The JSON file stores data under `data` and uses `timestamp` keys.
    // LightweightCharts expects an array of points with a `time` field
    // (either a unix timestamp in seconds or a date string) and OHLC keys.
    const formatPayload = (payload) => {
      const raw = Array.isArray(payload) ? payload : (payload && payload.data) || [];
      return raw.map(item => {
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
      }).filter(pt => typeof pt.time === 'number' && isFinite(pt.time) &&
                       typeof pt.open === 'number' && typeof pt.high === 'number' &&
                       typeof pt.low === 'number' && typeof pt.close === 'number');
    };

    const chartInstances = [];
    const containerForCharts = chartContainer;

    // If multiple pairs are configured, render a small stack of charts (one per pair)
    const pairsToRender = pairs.length ? pairs : ['EUR/USD'];

    // Clear any existing content (if fallback or previous charts)
    while (containerForCharts.firstChild) containerForCharts.removeChild(containerForCharts.firstChild);

    for (const pair of pairsToRender) {
        const wrapper = document.createElement('div');
        wrapper.style.marginBottom = '16px';
        // Label with edit button
        const labelRow = document.createElement('div');
        labelRow.className = 'chart-pair-label';
        labelRow.style.display = 'flex';
        labelRow.style.alignItems = 'center';
        labelRow.style.justifyContent = 'space-between';
        
        const labelText = document.createElement('span');
        labelText.textContent = pair;
        labelRow.appendChild(labelText);
        
        const editBtn = document.createElement('button');
        editBtn.className = 'icon-btn chart-edit-btn';
        editBtn.setAttribute('aria-label', 'Edit');
        editBtn.setAttribute('title', 'Edit');
        editBtn.onclick = () => {
            const pairsModal = document.getElementById('pairsModal');
            if (pairsModal) pairsModal.style.display = 'block';
        };
        const penIcon = document.createElement('span');
        penIcon.className = 'icon-glyph';
        penIcon.textContent = '✎';
        editBtn.appendChild(penIcon);
        labelRow.appendChild(editBtn);
        
        wrapper.appendChild(labelRow);
        // Chart container
        const sub = document.createElement('div');
        sub.style.height = initialHeight + 'px';
        sub.style.width = '100%';
        sub.className = 'chart-subcontainer';
        wrapper.appendChild(sub);
        containerForCharts.appendChild(wrapper);

        const chart = createChartInstance(sub);
        const series = chart.addSeries(LightweightCharts.CandlestickSeries);
        try {
            const payload = await fetchCandles(pair);
            const formatted = formatPayload(payload);
            if (formatted.length) series.setData(formatted);
        } catch (e) {
            console.warn('Fetch failed for pair', pair, e);
        }
        chartInstances.push({ chart, series, pair, container: sub });
    }

    // Hide fallback helper if present
    const fallback = document.getElementById('chart-fallback');
    if (fallback) fallback.style.display = 'none';

    // Auto-resize chart when window resizes
    const resizeObserver = new ResizeObserver(entries => {
        if (entries.length === 0 || entries[0].target !== chartContainer) {
            return;
        }
        const newRect = chartContainer.getBoundingClientRect();
        // Only adjust width on resize; keep height stable to avoid growth feedback
        chartInstances.forEach(inst => inst.chart.applyOptions({ width: newRect.width }));
    });
    resizeObserver.observe(chartContainer);

    // Start continuous candle generation
    console.log('Starting continuous updates...');
    chartInstances.forEach(inst => startContinuousUpdates(inst.series, inst.pair));

    return chartInstances;
}

// Function to continuously generate and add new candles
function startContinuousUpdates(candlestickSeries, pair) {
    // Get simulation speed from sessionStorage
    const getUpdateInterval = () => {
        // Prefer sessionStorage, fallback to cookie set via settings
        const cookieMatch = document.cookie.match(/(?:^|; )sim_speed=([^;]+)/);
        const simSpeed = sessionStorage.getItem('sim_speed') || (cookieMatch ? decodeURIComponent(cookieMatch[1]) : 'normal');
        const intervalMap = {
            'slow': 10000,   // 10 seconds
            'normal': 5000,  // 5 seconds
            'fast': 2000     // 2 seconds
        };
        return intervalMap[simSpeed] || 5000;
    };

    let updateInterval = getUpdateInterval();
    let intervalId;

    const updateCandle = async () => {
        try {
            const origin = window.location.origin;
            const baseDir = window.location.pathname.replace(/\/[^\/]*$/, '/');
            const nextCandleUrl = `${origin}${baseDir}helpers/next_candle.php?pair=${encodeURIComponent(pair)}`;
            const response = await fetch(nextCandleUrl, { cache: 'no-store' });
            
            if (!response.ok) {
                console.warn('Failed to fetch next candle:', response.status);
                return;
            }
            
            const newCandle = await response.json();
            
            // Check if it's an error response
            if (newCandle.error) {
                console.warn('Candle generation error:', newCandle.error);
                return;
            }
            
            // Add the new candle to the chart
            if (newCandle.time && newCandle.open && newCandle.close) {
                candlestickSeries.update(newCandle);
            }
            
        } catch (error) {
            console.error('Error updating candle:', error);
        }
    };

    // Initial update
    updateCandle();

    // Set up interval with dynamic speed checking
    const startInterval = () => {
        if (intervalId) clearInterval(intervalId);
        updateInterval = getUpdateInterval();
        intervalId = setInterval(updateCandle, updateInterval);
    };

    startInterval();

    // Listen for speed changes
    window.addEventListener('storage', (e) => {
        if (e.key === 'sim_speed') {
            startInterval(); // Restart with new interval
        }
    });
}



