  function openOrderModal() {
            document.getElementById('orderModal').classList.add('active');
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.remove('active');
        }

        function selectSymbol(symbol) {
            console.log('Selected symbol:', symbol);
            // Update chart and order modal with selected symbol
        }

        // Close modal when clicking outside
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });

        // Symbol search functionality
        document.getElementById('symbolSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.symbol-item');
            
            items.forEach(item => {
                const symbol = item.querySelector('.symbol-name').textContent.toLowerCase();
                if (symbol.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Simulate real-time price updates
        setInterval(() => {
            document.querySelectorAll('.price-bid, .price-ask').forEach(el => {
                const currentPrice = parseFloat(el.textContent);
                const change = (Math.random() - 0.5) * 0.0001;
                const newPrice = currentPrice + change;
                el.textContent = newPrice.toFixed(5);
            });
        }, 3000);