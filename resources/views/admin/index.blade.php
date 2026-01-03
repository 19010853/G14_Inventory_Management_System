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
</script>
@endsection
