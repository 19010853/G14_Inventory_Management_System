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
              <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4" style="background: linear-gradient(135deg, #0d6efd 0%, #17a2b8 100%);">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-white">
                      <div class="fs-14 mb-1 opacity-90">Total Products</div>
                      <div class="fs-28 mb-0 fw-bold text-white">
                        {{ $totalProducts }}
                      </div>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                      <i data-feather="package" class="text-white" style="width: 24px; height: 24px;"></i>
                    </div>
                  </div>
                  <div id="website-visitors" class="apex-charts" style="height: 45px;"></div>
                </div>
              </div>
            </div>

            <div class="col-md-6 col-xl-3">
              <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-white">
                      <div class="fs-14 mb-1 opacity-90">Total Sales</div>
                      <div class="fs-28 mb-0 fw-bold text-white">
                        ${{ number_format($totalSales, 2) }}
                      </div>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                      <i data-feather="trending-up" class="text-white" style="width: 24px; height: 24px;"></i>
                    </div>
                  </div>
                  <div id="conversion-visitors" class="apex-charts" style="height: 45px;"></div>
                </div>
              </div>
            </div>

            <div class="col-md-6 col-xl-3">
              <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-white">
                      <div class="fs-14 mb-1 opacity-90">Total Purchases</div>
                      <div class="fs-28 mb-0 fw-bold text-white">
                        ${{ number_format($totalPurchases, 2) }}
                      </div>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                      <i data-feather="shopping-cart" class="text-white" style="width: 24px; height: 24px;"></i>
                    </div>
                  </div>
                  <div id="session-visitors" class="apex-charts" style="height: 45px;"></div>
                </div>
              </div>
            </div>

            <div class="col-md-6 col-xl-3">
              <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-white">
                      <div class="fs-14 mb-1 opacity-90">Total Users</div>
                      <div class="fs-28 mb-0 fw-bold text-white">
                        {{ $totalUsers }}
                      </div>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                      <i data-feather="users" class="text-white" style="width: 24px; height: 24px;"></i>
                    </div>
                  </div>
                  <div id="active-users" class="apex-charts" style="height: 45px;"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- end sales -->
      </div>
      <!-- end row -->

      <!-- Start Monthly Sales -->
      <div class="row mt-4">
        <div class="col-md-6 col-xl-8">
          <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-white border-0 pb-0" style="border-radius: 12px 12px 0 0;">
              <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                  <i data-feather="bar-chart" class="text-primary" style="width: 20px; height: 20px;"></i>
                </div>
                <h5 class="card-title mb-0 fw-semibold">Daily Sales</h5>
              </div>
            </div>

            <div class="card-body pt-3">
              <div id="daily-sales" class="apex-charts"></div>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-xl-4">
          <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
            <div class="card-header bg-white border-0 pb-0" style="border-radius: 12px 12px 0 0;">
              <div class="d-flex align-items-center">
                <div class="bg-success bg-opacity-10 rounded p-2 me-3">
                  <i data-feather="award" class="text-success" style="width: 20px; height: 20px;"></i>
                </div>
                <h5 class="card-title mb-0 fw-semibold">Best Selling Products</h5>
              </div>
            </div>

            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="ps-4">Product</th>
                      <th class="text-end pe-4">Quantity Sold</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($bestSellingProducts as $product)
                    <tr>
                      <td class="ps-4">{{ $product->name }}</td>
                      <td class="text-end pe-4">
                        <span class="badge bg-primary">{{ $product->total_quantity }}</span>
                      </td>
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
                type: 'line',
                height: 350,
                toolbar: { show: true },
                zoom: { enabled: true }
            },
            series: [{
                name: 'Sales',
                data: dailySalesData.map(item => item.total_sales)
            }],
            xaxis: {
                categories: dailySalesData.map(item => item.date),
                labels: { style: { fontSize: '12px' } }
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return '$' + val.toFixed(2);
                    }
                }
            },
            colors: ['#0d6efd'],
            stroke: { width: 3, curve: 'smooth' },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'vertical',
                    shadeIntensity: 0.3,
                    gradientToColors: ['#17a2b8'],
                    inverseColors: false,
                    opacityFrom: 0.7,
                    opacityTo: 0.1,
                    stops: [0, 100]
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return '$' + val.toFixed(2);
                    }
                }
            },
            grid: {
                borderColor: '#e9ecef',
                strokeDashArray: 4
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
        colors: ['#ffffff'],
        legend: { show: false },
    };
    var productsChart = new ApexCharts(document.querySelector('#website-visitors'), productsChartOptions);
    productsChart.render();

    // Total Sales Mini Chart
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
        colors: ['#ffffff'],
        legend: { show: false },
    };
    var salesChart = new ApexCharts(document.querySelector('#conversion-visitors'), salesChartOptions);
    salesChart.render();

    // Total Purchases Mini Chart
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
        colors: ['#ffffff'],
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
        colors: ['#ffffff'],
        plotOptions: { bar: { columnWidth: '35%', borderRadius: 3 } },
        dataLabels: { enabled: false },
        fill: { opacity: 1 },
        grid: { strokeDashArray: 4 },
        labels: chartDates,
        xaxis: { crosshairs: { width: 1 } },
        yaxis: { labels: { padding: 4 } },
        tooltip: { theme: 'dark' },
    };
        var usersChart = new ApexCharts(document.querySelector('#active-users'), usersChartOptions);
        usersChart.render();
    });
</script>
@endsection
