const chartContainer = document.getElementById('chart-container');

const chart = LightweightsCharts.createChart(
    chartContainer
)

fetch('../data/forex_candlestick_data.json')
    .then(response => response.json())
    .then(data => {
        const candlestickSeries = chart.addCandlestickSeries();
        candlestickSeries.setData(data);  
    });


