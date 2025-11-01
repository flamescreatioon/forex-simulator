// Use relative path from assets/js/ to controllers/ to work on any server
// Avoid query params to prevent module import edge-cases on some servers
import { initChart } from '../../controllers/chartControllers.js';

console.log('app.js module loaded');
console.log('initChart imported:', typeof initChart);

// Expose UI functions to window so inline handlers in the PHP templates keep working.
window.openOrderModal = function openOrderModal() {
    const orderModal = document.getElementById('orderModal');
    if (orderModal) {
        orderModal.classList.add('active');
        // Update SL/TP price calculations when modal opens
        updateSlTpPrices();
    }
};

window.closeOrderModal = function closeOrderModal() {
    const orderModal = document.getElementById('orderModal');
    if (orderModal) orderModal.classList.remove('active');
};

// Calculate actual price levels from pips
function updateSlTpPrices() {
    const stopLossInput = document.getElementById('stopLossInput');
    const takeProfitInput = document.getElementById('takeProfitInput');
    const slPrice = document.getElementById('slPrice');
    const tpPrice = document.getElementById('tpPrice');
    
    if (!stopLossInput || !takeProfitInput || !slPrice || !tpPrice) return;
    
    // Get current market prices from modal (you can adjust this based on buy/sell)
    const sellBtn = document.querySelector('.sell-btn .price-value');
    const buyBtn = document.querySelector('.buy-btn .price-value');
    
    if (!sellBtn || !buyBtn) return;
    
    const currentSellPrice = parseFloat(sellBtn.textContent);
    const currentBuyPrice = parseFloat(buyBtn.textContent);
    const avgPrice = (currentSellPrice + currentBuyPrice) / 2;
    
    // Calculate pip value (for most pairs, 1 pip = 0.0001, for JPY pairs = 0.01)
    const pipValue = 0.0001; // Adjust if pair contains JPY
    
    // Update SL price
    const slPips = parseFloat(stopLossInput.value) || 0;
    if (slPips > 0) {
        const slLevel = avgPrice - (slPips * pipValue);
        slPrice.textContent = `≈ ${slLevel.toFixed(5)}`;
    } else {
        slPrice.textContent = '';
    }
    
    // Update TP price
    const tpPips = parseFloat(takeProfitInput.value) || 0;
    if (tpPips > 0) {
        const tpLevel = avgPrice + (tpPips * pipValue);
        tpPrice.textContent = `≈ ${tpLevel.toFixed(5)}`;
    } else {
        tpPrice.textContent = '';
    }
}

window.selectSymbol = function selectSymbol(symbol) {
    console.log('Selected symbol:', symbol);
    // TODO: wire symbol change into chart update when that feature is added
};

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded fired');
    console.log('LightweightCharts available:', typeof LightweightCharts);
    
    // Close modal when clicking outside
    const orderModal = document.getElementById('orderModal');
    if (orderModal) {
        orderModal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });
    }

    // Symbol search functionality
    const symbolSearch = document.getElementById('symbolSearch');
    if (symbolSearch) {
        symbolSearch.addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.symbol-item');

            items.forEach(item => {
                const nameEl = item.querySelector('.symbol-name');
                const symbol = nameEl ? nameEl.textContent.toLowerCase() : '';
                if (symbol.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    // Add event listeners to SL/TP inputs to update price calculations
    const stopLossInput = document.getElementById('stopLossInput');
    const takeProfitInput = document.getElementById('takeProfitInput');
    if (stopLossInput) stopLossInput.addEventListener('input', updateSlTpPrices);
    if (takeProfitInput) takeProfitInput.addEventListener('input', updateSlTpPrices);

    // Simulate real-time price updates
    setInterval(() => {
        document.querySelectorAll('.price-bid, .price-ask').forEach(el => {
            const currentPrice = parseFloat(el.textContent);
            if (Number.isFinite(currentPrice)) {
                const change = (Math.random() - 0.5) * 0.0001;
                const newPrice = currentPrice + change;
                el.textContent = newPrice.toFixed(5);
            }
        });
    }, 3000);

    const panel_tabs = document.querySelectorAll('.panel-tab');
    panel_tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            // set active class on tabs
            panel_tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // hide all tables that are part of the panel (only those in .panel-content)
            const panelTables = document.querySelectorAll('.panel-content table');
            panelTables.forEach(tbl => tbl.style.display = 'none');

            // Also hide the no-positions message
            const noPositions = document.querySelector('.no-positions');
            if (noPositions) noPositions.style.display = 'none';

            // determine which table to show
            let targetEl = null;

            // 1) explicit selector via data-target (e.g. data-target=".positions-table")
            if (this.dataset && this.dataset.target) {
                targetEl = document.querySelector(this.dataset.target);
            } else {
                // 2) fallback to tab text matching
                const txt = (this.textContent || '').toLowerCase();
                if (txt.includes('position')) {
                    targetEl = document.querySelector('.positions-table');
                    // If no positions table, show the no-positions message
                    if (!targetEl && noPositions) {
                        noPositions.style.display = 'block';
                    }
                } else if (txt.includes('order')) {
                    targetEl = document.querySelector('.trades-history');
                } else if (txt.includes('deal')) {
                    // Deals tab - could show a different table or message
                    targetEl = document.querySelector('.deals-table');
                }
            }

            // show the target table (restore default display)
            if (targetEl) {
                targetEl.style.display = 'table'; // explicitly show as table
            } else {
                console.warn('No table found for tab:', this);
            }
        });
    });

    // activate a default tab (first .panel-tab with .active or the first tab)
    (document.querySelector('.panel-tab.active') || panel_tabs[0])?.click();

    // Mobile: Toggle symbol list visibility
    const chartHeader = document.querySelector('.chart-header');
    const symbolList = document.querySelector('.symbol-list');
    if (chartHeader && symbolList && window.innerWidth <= 768) {
        chartHeader.style.cursor = 'pointer';
        chartHeader.addEventListener('click', () => {
            symbolList.classList.toggle('show');
        });
    }

    // Initialize chart after DOM is ready. Errors are logged to console.
    console.log('About to initialize chart...');
    console.log('Chart container exists:', !!document.querySelector('#chart .chart-container'));
    initChart() // use default selector inside the chart module for robustness
        .then(result => console.log('Chart initialized successfully:', result))
        .catch(err => console.error('Chart init failed:', err));
});