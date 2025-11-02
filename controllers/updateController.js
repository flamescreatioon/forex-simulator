function updateDashboard(){
    // Use relative paths so this works under any base URL (e.g., http://localhost/new_forex/)
    fetch('simulate.php', { cache: 'no-store' })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status} on simulate.php`);
            return response.json();
        })
        .then(data => {
            const balanceEl = document.getElementById('balance');
            if (balanceEl && data.balance != null) balanceEl.textContent = data.balance;

            // Also update top-of-page balance/equity displays if present
            const balanceTopEl = document.getElementById('balanceTop');
            if (balanceTopEl && data.balance != null) balanceTopEl.textContent = data.balance + ' USD';
            const equityTopEl = document.getElementById('equityTop');
            if (equityTopEl && data.equity != null) equityTopEl.textContent = data.equity + ' USD';

            const equityEl = document.getElementById('equity');
            if (equityEl && data.equity != null) equityEl.textContent = data.equity;

            const marginEl = document.getElementById('margin');
            if (marginEl && data.margin != null) marginEl.textContent = data.margin;

            const freeMarginEl = document.getElementById('freemargin');
            if (freeMarginEl && data.freemargin != null) freeMarginEl.textContent = data.freemargin;

            const marginLevelEl = document.getElementById('marginlevel');
            // API already returns % in marginlevel, so don't append another '%'
            if (marginLevelEl && data.marginlevel != null) marginLevelEl.textContent = data.marginlevel;

            const profitEl = document.getElementById('profit');
            if (profitEl && data.profit != null) profitEl.textContent = data.profit;

            const goalEl = document.getElementById('goal');
            if (goalEl && data.goal != null) goalEl.textContent = data.goal;
        })
        .catch(err=>console.error("Error fetching data: ", err));
}


function updateTrades(){
    fetch('trades.php', { cache: 'no-store' })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status} on trades.php`);
            return response.json();
        })
        .then(data => {
            const tradesTableBody = document.getElementById('tradesTableBody');
            if (!tradesTableBody) return;

            tradesTableBody.innerHTML = ''; // Clear existing rows
            (Array.isArray(data) ? data : []).forEach(trade => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${trade.pair ?? '-'}</td>
                    <td>${trade.profit ?? '-'}</td>
                    <td>${trade.timestamp ?? '-'}</td>
                `;
                tradesTableBody.appendChild(row);
            });
        })
        .catch(err => console.error("Error fetching trades: ", err));
}

// Get update interval based on simulation speed
const simSpeed = sessionStorage.getItem('sim_speed') || 'normal';
const intervalMap = { 'slow': 5000, 'normal': 3000, 'fast': 1000 };
const updateInterval = intervalMap[simSpeed] || 3000;

setInterval(updateTrades, updateInterval);
setInterval(updateDashboard, updateInterval);
updateDashboard();