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

setInterval(updateDashboard, 5000); // Update every 5 seconds
updateDashboard();