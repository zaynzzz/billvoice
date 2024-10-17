let revenueChartInstance = null;
let packageChartInstance = null;

function renderCharts(data) {
    // Destroy previous instances if they exist
    if (revenueChartInstance !== null) {
        revenueChartInstance.destroy();
        revenueChartInstance = null;
    }
    if (packageChartInstance !== null) {
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
            responsive: true,
            maintainAspectRatio: false, // Disable to allow height customization
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Package Distribution Pie Chart
    const packageCtx = document.getElementById('packageChart').getContext('2d');
    const labels = data.packageDistribution.map(item => item.label);
    const values = data.packageDistribution.map(item => item.count);

    packageChartInstance = new Chart(packageCtx, {
        type: 'doughnut',
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
            maintainAspectRatio: false // Disable to allow height customization
        }
    });
}
fetch('data.php')
    .then(response => response.json())
    .then(data => {
        // Update Statistics
        document.getElementById('total-customers').innerText = data.totalCustomers;
        document.getElementById('total-packages').innerText = data.totalPackages;
        document.getElementById('total-invoices').innerText = data.totalInvoices;

        // Convert totalRevenue to a number and format it as currency (IDR)
        let totalRevenue = parseInt(data.totalRevenue, 10); // Ensure it's treated as a number
        document.getElementById('total-revenue').innerText = totalRevenue.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0 // No decimal places
        });

        // Render the charts
        renderOverallStatsChart(data);
        renderCharts(data);
    })
    .catch(error => {
        console.error('Error fetching data:', error);
        // Optional: show error message on the page for easier debugging
        document.body.innerHTML += `<p style="color: red;">Error: ${error.message}</p>`;
    });
