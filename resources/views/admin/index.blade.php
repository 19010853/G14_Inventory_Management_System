@extends('admin.admin_master')
@section('admin')
  <div class="content">
    <!-- Start Content-->
    <div class="container-xxl">
      <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
          <h4 class="fs-18 fw-semibold m-0">Dashboard</h4>
        </div>
      </div>

      <!-- start row -->
      <div class="row">
        <div class="col-md-12 col-xl-12">
          <div class="row g-3">
            <div class="col-md-6 col-xl-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="fs-14 mb-1">Total Products</div>
                  </div>

                  <div class="d-flex align-items-baseline mb-2">
                    <div class="fs-22 mb-0 me-2 fw-semibold text-black">
                      {{ $totalProducts }}
                    </div>
                  </div>
                  <div id="website-visitors" class="apex-charts"></div>
                </div>
              </div>
            </div>

            <div class="col-md-6 col-xl-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="fs-14 mb-1">Today's Sales</div>
                  </div>

                  <div class="d-flex align-items-baseline mb-2">
                    <div class="fs-22 mb-0 me-2 fw-semibold text-black">
                      ${{ number_format($totalSales, 2) }}
                    </div>
                  </div>
                  <div id="conversion-visitors" class="apex-charts"></div>
                </div>
              </div>
            </div>

            <div class="col-md-6 col-xl-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="fs-14 mb-1">Today's Purchases</div>
                  </div>

                  <div class="d-flex align-items-baseline mb-2">
                    <div class="fs-22 mb-0 me-2 fw-semibold text-black">
                      ${{ number_format($totalPurchases, 2) }}
                    </div>
                  </div>
                  <div id="session-visitors" class="apex-charts"></div>
                </div>
              </div>
            </div>

            <div class="col-md-6 col-xl-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="fs-14 mb-1">Total Users</div>
                  </div>

                  <div class="d-flex align-items-baseline mb-2">
                    <div class="fs-22 mb-0 me-2 fw-semibold text-black">
                      {{ $totalUsers }}
                    </div>
                  </div>
                  <div id="active-users" class="apex-charts"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- end sales -->
      </div>
      <!-- end row -->

      <!-- Start Monthly Sales -->
      <div class="row">
        <div class="col-md-6 col-xl-8">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-center">
                <div
                  class="border border-dark rounded-2 me-2 widget-icons-sections"
                >
                  <i data-feather="bar-chart" class="widgets-icons"></i>
                </div>
                <h5 class="card-title mb-0">Daily Sales</h5>
              </div>
            </div>

            <div class="card-body">
              <div id="daily-sales" class="apex-charts"></div>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-xl-4">
          <div class="card overflow-hidden">
            <div class="card-header">
              <div class="d-flex align-items-center">
                <div
                  class="border border-dark rounded-2 me-2 widget-icons-sections"
                >
                  <i data-feather="tablet" class="widgets-icons"></i>
                </div>
                <h5 class="card-title mb-0">Best Selling Products</h5>
              </div>
            </div>

            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-traffic mb-0">
                  <tbody>
                    <thead>
                      <tr>
                        <th>Product</th>
                        <th colspan="2">Quantity Sold</th>
                      </tr>
                    </thead>
                    @foreach($bestSellingProducts as $product)
                    <tr>
                      <td>{{ $product->name }}</td>
                      <td>{{ $product->total_quantity }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- End Monthly Sales -->
    </div>
    <!-- container-fluid -->
  </div>

<script>
    // Wait for DOM and existing charts to load, then override with real data
    document.addEventListener('DOMContentLoaded', function() {
        // Destroy existing charts if they exist (from analytics-dashboard.init.js)
        if (window.ApexCharts) {
            // Destroy existing mini charts
            var existingCharts = ['website-visitors', 'conversion-visitors', 'session-visitors', 'active-users'];
            existingCharts.forEach(function(chartId) {
                var chartElement = document.querySelector('#' + chartId);
                if (chartElement && chartElement._apexChart) {
                    chartElement._apexChart.destroy();
                }
            });
        }

        // Daily Sales Chart (Main Chart)
        var dailySalesData = {!! json_encode($salesData) !!};
        var options = {
            chart: {
                type: 'line'
            },
            series: [{
                name: 'sales',
                data: dailySalesData.map(item => item.total_sales)
            }],
            xaxis: {
                categories: dailySalesData.map(item => item.date)
            }
        }
        var chart = new ApexCharts(document.querySelector("#daily-sales"), options);
        chart.render();

        // Mini Charts Data from Database
        var chartDates = {!! json_encode($dates) !!};
        var productsData = {!! json_encode($productsChartData) !!};
        var salesData = {!! json_encode($salesChartData) !!};
        var purchasesData = {!! json_encode($purchasesChartData) !!};
        var usersData = {!! json_encode($usersChartData) !!};

        // Total Products Mini Chart
        var productsChartOptions = {
        chart: {
            type: 'area',
            fontFamily: 'inherit',
            height: 45,
            sparkline: { enabled: true },
            animations: { enabled: false },
        },
        dataLabels: { enabled: false },
        fill: { opacity: 0.16, type: 'solid' },
        stroke: { width: 2, lineCap: 'round', curve: 'smooth' },
        series: [{
            name: 'Products',
            data: productsData
        }],
        tooltip: { theme: 'light' },
        grid: { strokeDashArray: 4 },
        xaxis: {
            labels: { padding: 0 },
            tooltip: { enabled: false },
            axisBorder: { show: false },
            type: 'datetime',
        },
        yaxis: { labels: { padding: 4 } },
        labels: chartDates,
        colors: ['#537AEF'],
        legend: { show: false },
    };
    var productsChart = new ApexCharts(document.querySelector('#website-visitors'), productsChartOptions);
    productsChart.render();

    // Today's Sales Mini Chart
    var salesChartOptions = {
        chart: {
            type: 'area',
            fontFamily: 'inherit',
            height: 45,
            sparkline: { enabled: true },
            animations: { enabled: false },
        },
        dataLabels: { enabled: false },
        fill: { opacity: 0.16, type: 'solid' },
        stroke: { width: 2, lineCap: 'round', curve: 'smooth' },
        series: [{
            name: 'Sales',
            data: salesData
        }],
        tooltip: { 
            theme: 'light',
            y: {
                formatter: function(val) {
                    return '$' + val.toFixed(2);
                }
            }
        },
        grid: { strokeDashArray: 4 },
        xaxis: {
            labels: { padding: 0 },
            tooltip: { enabled: false },
            axisBorder: { show: false },
            type: 'datetime',
        },
        yaxis: { labels: { padding: 4 } },
        labels: chartDates,
        colors: ['#ec8290'],
        legend: { show: false },
    };
    var salesChart = new ApexCharts(document.querySelector('#conversion-visitors'), salesChartOptions);
    salesChart.render();

    // Today's Purchases Mini Chart
    var purchasesChartOptions = {
        chart: {
            type: 'line',
            height: 45,
            sparkline: { enabled: true },
            animations: { enabled: false },
        },
        fill: { opacity: 1 },
        stroke: { width: [2], dashArray: [0, 3], lineCap: 'round', curve: 'smooth' },
        series: [{
            name: 'Purchases',
            data: purchasesData
        }],
        tooltip: { 
            theme: 'light',
            y: {
                formatter: function(val) {
                    return '$' + val.toFixed(2);
                }
            }
        },
        grid: { strokeDashArray: 4 },
        xaxis: { labels: { padding: 0 }, tooltip: { enabled: false }, type: 'datetime' },
        yaxis: { labels: { padding: 4 } },
        labels: chartDates,
        colors: ['#537AEF', '#343a40'],
        legend: { show: false },
    };
    var purchasesChart = new ApexCharts(document.querySelector('#session-visitors'), purchasesChartOptions);
    purchasesChart.render();

    // Total Users Mini Chart
    var usersChartOptions = {
        series: [{
            data: usersData
        }],
        chart: {
            height: 45,
            type: 'bar',
            sparkline: { enabled: true },
            animations: { enabled: false },
        },
        colors: ['#537AEF'],
        plotOptions: { bar: { columnWidth: '35%', borderRadius: 3 } },
        dataLabels: { enabled: false },
        fill: { opacity: 1 },
        grid: { strokeDashArray: 4 },
        labels: chartDates,
        xaxis: { crosshairs: { width: 1 } },
        yaxis: { labels: { padding: 4 } },
        tooltip: { theme: 'light' },
    };
        var usersChart = new ApexCharts(document.querySelector('#active-users'), usersChartOptions);
        usersChart.render();
    });
</script>
@endsection
