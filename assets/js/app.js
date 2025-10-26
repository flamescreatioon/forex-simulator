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

    // Initialize chart after DOM is ready. Errors are logged to console.
    initChart('.chart-container').catch(err => console.error('Chart init failed:', err));
});