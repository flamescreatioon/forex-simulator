import { initChart } from '../../controllers/chartControllers.js';

// Expose UI functions to window so inline handlers in the PHP templates keep working.
window.openOrderModal = function openOrderModal() {
    const orderModal = document.getElementById('orderModal');
    if (orderModal) orderModal.classList.add('active');
};

window.closeOrderModal = function closeOrderModal() {
    const orderModal = document.getElementById('orderModal');
    if (orderModal) orderModal.classList.remove('active');
};

window.selectSymbol = function selectSymbol(symbol) {
    console.log('Selected symbol:', symbol);
    // TODO: wire symbol change into chart update when that feature is added
};

document.addEventListener('DOMContentLoaded', () => {
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

    // Initialize chart after DOM is ready. Errors are logged to console.
    initChart('.chart-container').catch(err => console.error('Chart init failed:', err));
});