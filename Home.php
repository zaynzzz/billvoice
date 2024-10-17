<?php
include('header.php');
include('functions.php');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .stat {
            width: 23%;
            min-width: 200px;
            background-color: #f1f1f1;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin-bottom: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .stat h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .stat p {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .charts {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .chart-container {
            width: 48%;
            min-width: 300px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        canvas {
            width: 100% !important;
            height: 300px !important;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Dashboard</h1>
        <p></p>
    </div>
    <div class="stats">
        <div class="stat">
            <h3>Total Customers</h3>
            <p id="total-customers">0</p>
        </div>
        <div class="stat">
            <h3>Total Packages</h3>
            <p id="total-packages">0</p>
        </div>
        <div class="stat">
            <h3>Total Invoices</h3>
            <p id="total-invoices">0</p>
        </div>
        <div class="stat">
            <h3>Total Revenue (IDR)</h3>
            <p id="total-revenue">0</p>
        </div>
    </div>

    <div class="charts">
        <div class="chart-container">
            <canvas id="overallStatsChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="revenueChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="packageChart"></canvas>
        </div>
    </div>
</div>

<script>
    let overallStatsChartInstance = null;
    let revenueChartInstance = null;
    let packageChartInstance = null;

    function renderOverallStatsChart(data) {
        // Destroy previous instance if it exists
        if (overallStatsChartInstance) {
            overallStatsChartInstance.destroy();
            overallStatsChartInstance = null;
        }

        const ctx = document.getElementById('overallStatsChart').getContext('2d');
        overallStatsChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Customers', 'Packages', 'Invoices', 'Revenue'],
                datasets: [{
                    label: 'Overall Statistics',
                    data: [
                        data.totalCustomers,
                        data.totalPackages,
                        data.totalInvoices,
                        data.totalRevenue
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.5)', // Customers
                        'rgba(153, 102, 255, 0.5)', // Packages
                        'rgba(255, 159, 64, 0.5)', // Invoices
                        'rgba(54, 162, 235, 0.5)'  // Revenue
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Existing function for rendering other charts
    function renderCharts(data) {
        // Destroy previous instances if they exist
        if (revenueChartInstance) {
            revenueChartInstance.destroy();
            revenueChartInstance = null;
        }
        if (packageChartInstance) {
            packageChartInstance.destroy();
            packageChartInstance = null;
        }

        // Monthly Revenue Bar Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        revenueChartInstance = new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Monthly Revenue (IDR)',
                    data: data.monthlyRevenue,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Package Distribution Bar Chart (replaces doughnut chart)
        const packageCtx = document.getElementById('packageChart').getContext('2d');
        const labels = data.packageDistribution.map(item => item.label);
        const values = data.packageDistribution.map(item => item.count);

        packageChartInstance = new Chart(packageCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Package Distribution',
                    data: values,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 159, 64, 0.5)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Fetch data and populate charts
    fetch('data.php')
        .then(response => response.json())
        .then(data => {
            // Update Statistics
            document.getElementById('total-customers').innerText = data.totalCustomers;
            document.getElementById('total-packages').innerText = data.totalPackages;
            document.getElementById('total-invoices').innerText = data.totalInvoices;
            document.getElementById('total-revenue').innerText = data.totalRevenue.toLocaleString();

            // Render the charts
            renderOverallStatsChart(data);
            renderCharts(data);
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            // Optional: show error message on the page for easier debugging
            document.body.innerHTML += `<p style="color: red;">Error: ${error.message}</p>`;
        });
</script>

</body>
</html>


<?php
include('footer.php');
?>
