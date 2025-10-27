function updateDashboard(){
    fetch('../simulate.php')
        .then(response => response.json())
        .then(data => {
        document.getElementById('balance').textContent = data.balance;
        document.getElementById('equity').textContent = data.equity;
        document.getElementById('margin').textContent = data.margin;
        document.getElementById('freemargin').textContent = data.freemargin;
        document.getElementById('marginlevel').textContent = data.marginlevel + '%';
        document.getElementById('profit').textContent = data.profit;
        document.getElementById('goal').textContent = data.goal;
    })
        .catch(err=>console.error("Error fetching data: ", err));
}


function updateTrades(){
    fetch('../trades.php')
        .then(response => response.json())
        .then(data => {
            const tradesTableBody = document.getElementById('tradesTableBody');
            tradesTableBody.innerHTML = ''; // Clear existing rows
            data.forEach(trade => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${trade.pair}</td>
                    <td>${trade.profit}</td>
                    <td>${trade.timestamp}</td>
                `;
                tradesTableBody.appendChild(row);
            });
        })
        .catch(err => console.error("Error fetching trades: ", err));
}
setInterval(updateTrades, 5000); // Update every 5 seconds
setInterval(updateDashboard, 5000); // Update every 5 seconds
updateDashboard();