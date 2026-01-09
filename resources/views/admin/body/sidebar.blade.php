<div class="app-sidebar-menu">
  <div class="h-100" data-simplebar>
    <!--- Sidemenu -->
    <div id="sidebar-menu">
      <div class="logo-box">
        <a href="{{ route('dashboard') }}" class="logo logo-light">
          <span class="logo-sm">
            <img
              src="{{ asset('backend/assets/images/logo-sm.svg') }}"
              alt="G14 Inventory"
              height="40"
            />
          </span>
          <span class="logo-lg">
            <img
              src="{{ asset('backend/assets/images/logo-light.svg') }}"
              alt="G14 Inventory"
              height="50"
            />
          </span>
        </a>
        <a href="{{ route('dashboard') }}" class="logo logo-dark">
          <span class="logo-sm">
            <img
              src="{{ asset('backend/assets/images/logo-sm.svg') }}"
              alt="G14 Inventory"
              height="40"
            />
          </span>
          <span class="logo-lg">
            <img
              src="{{ asset('backend/assets/images/logo-dark.svg') }}"
              alt="G14 Inventory"
              height="50"
            />
          </span>
        </a>
      </div>

      <ul id="side-menu">
        <li class="menu-title">Menu</li>

        <li>
          <a href="{{ route('dashboard') }}" class="tp-link">
            <i data-feather="home"></i>
            <span>Dashboard</span>
          </a>
        </li>

        <li class="menu-title">Pages</li>

        @if (Auth::guard('web')->user()->can('brand.menu'))
          <li>
            <a href="#sidebarAuth" data-bs-toggle="collapse">
              <i data-feather="users"></i>
              <span>Brand</span>
              <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarAuth">
              <ul class="nav-second-level">
                @if (Auth::guard('web')->user()->can('all.brand'))
                  <li>
                    <a href="{{ route('all.brand') }}" class="tp-link">
                      All Brand
                    </a>
                  </li>
                @endif
              </ul>
            </div>
          </li>
        @endif

        @if (Auth::guard('web')->user()->can('warehouse.menu'))
          <li>
            <a href="#WareHouse" data-bs-toggle="collapse">
              <i data-feather="users"></i>
              <span>WareHouse</span>
              <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="WareHouse">
              <ul class="nav-second-level">
                @if (Auth::guard('web')->user()->can('all.warehouse'))
                  <li>
                    <a href="{{ route('all.warehouse') }}" class="tp-link">
                      All WareHouse
                    </a>
                  </li>
                @endif
              </ul>
            </div>
          </li>
        @endif

        @if (Auth::guard('web')->user()->can('supplier.menu'))
          <li>
            <a href="#Supplier" data-bs-toggle="collapse">
              <i data-feather="users"></i>
              <span>Supplier</span>
              <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="Supplier">
              <ul class="nav-second-level">
                @if (Auth::guard('web')->user()->can('all.supplier'))
                  <li>
                    <a href="{{ route('all.supplier') }}" class="tp-link">
                      All Supplier
                    </a>
                  </li>
                @endif
              </ul>
            </div>
          </li>
        @endif

        @if (Auth::guard('web')->user()->can('customer.menu'))
          <li>
            <a href="#Customer" data-bs-toggle="collapse">
              <i data-feather="users"></i>
              <span>Customer</span>
              <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="Customer">
              <ul class="nav-second-level">
                @if (Auth::guard('web')->user()->can('all.customer'))
                  <li>
                    <a href="{{ route('all.customer') }}" class="tp-link">
                      All Customer
                    </a>
                  </li>
                @endif
              </ul>
            </div>
          </li>
        @endif

        @if (Auth::guard('web')->user()->can('product.menu'))
          <li>
            <a href="#Product" data-bs-toggle="collapse">
              <i data-feather="users"></i>
              <span>Product</span>
              <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="Product">
              <ul class="nav-second-level">
                @if (Auth::guard('web')->user()->can('all.category'))
                  <li>
                    <a href="{{ route('all.category') }}" class="tp-link">
                      All Category
                    </a>
                  </li>
                @endif

                @if (Auth::guard('web')->user()->can('all.product'))
                  <li>
                    <a href="{{ route('all.product') }}" class="tp-link">
                      All Product
                    </a>
                  </li>
                @endif
              </ul>
            </div>
          </li>
        @endif

        @if (Auth::guard('web')->user()->can('purchase.menu'))
          <li>
            <a href="#Purchase" data-bs-toggle="collapse">
              <i data-feather="users"></i>
              <span>Purchase</span>
              <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="Purchase">
              <ul class="nav-second-level">
                @if (Auth::guard('web')->user()->can('all.purchase'))
                  <li>
                    <a href="{{ route('all.purchase') }}" class="tp-link">
                      All Purchase
                    </a>
                  </li>
                @endif
                @if (Auth::guard('web')->user()->can('return.purchase'))
                  <li>
                    <a href="{{ route('all.return.purchase') }}" class="tp-link">
                      All Purchase Return
                    </a>
                  </li>
                @endif
              </ul>
            </div>
          </li>
        @endif

        @if (Auth::guard('web')->user()->can('sale.menu'))
          <li>
            <a href="#Sale" data-bs-toggle="collapse">
              <i data-feather="users"></i>
              <span>Sale</span>
              <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="Sale">
              <ul class="nav-second-level">
                @if (Auth::guard('web')->user()->can('all.sale'))
                  <li>
                    <a href="{{ route('all.sale') }}" class="tp-link">All Sale</a>
                  </li>
                @endif
                @if (Auth::guard('web')->user()->can('return.sale'))
                  <li>
                    <a href="{{ route('all.return.sale') }}" class="tp-link">
                      All Sale Return
                    </a>
                  </li>
                @endif
              </ul>
            </div>
          </li>
        @endif

        @if (Auth::guard('web')->user()->can('due.menu'))
          <li>
            <a href="#Due" data-bs-toggle="collapse">
              <i data-feather="alert-octagon"></i>
              <span>Due</span>
              <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="Due">
              <ul class="nav-second-level">
                @if (Auth::guard('web')->user()->can('due.sales'))
                  <li>
                    <a href="{{ route('due.sale') }}" class="tp-link">All Sales Due</a>
                  </li>
                @endif
                @if (Auth::guard('web')->user()->can('due.sales.return'))
                  <li>
                    <a href="{{ route('due.sale.return') }}" class="tp-link">
                      All Sales Return Due
                    </a>
                  </li>
                @endif
              </ul>
            </div>
          </li>
        @endif

        @if (Auth::guard('web')->user()->can('transfers.menu'))
          <li>
            <a href="#Transfers" data-bs-toggle="collapse">
              <i data-feather="alert-octagon"></i>
              <span>Transfer</span>
              <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="Transfers">
              <ul class="nav-second-level">
                @if (Auth::guard('web')->user()->can('all.transfers'))
                  <li>
                    <a href="{{ route('all.transfer') }}" class="tp-link">
                      All Transfers
                    </a>
                  </li>
                @endif
              </ul>
            </div>
          </li>
        @endif

        @if (Auth::guard('web')->user()->can('reports.all'))
          <li>
            <a href="#Report" data-bs-toggle="collapse">
              <i data-feather="alert-octagon"></i>
              <span>Report</span>
              <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="Report">
              <ul class="nav-second-level">
                <li>
                  <a href="{{ route('all.report') }}" class="tp-link">
                    All Reports
                  </a>
                </li>
              </ul>
            </div>
          </li>
        @endif

        @if (Auth::guard('web')->user()->can('role_and_permission.all'))
        <li class="menu-title mt-2">Human Resource</li>
          <li>
            <a href="#sidebarBaseui" data-bs-toggle="collapse">
              <i data-feather="package"></i>
              <span>Role & Permission</span>
              <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarBaseui">
              <ul class="nav-second-level">
                <li>
                  <a href="{{ route('all.permission') }}" class="tp-link">
                    All Permission
                  </a>
                </li>
                <li>
                  <a href="{{ route('all.roles') }}" class="tp-link">
                    All Roles
                  </a>
                </li>

                <li>
                  <a href="{{ route('add.roles.permission') }}" class="tp-link">
                    Role In Permission
                  </a>
                </li>
                <li>
                  <a href="{{ route('all.roles.permission') }}" class="tp-link">
                    All Role Permission
                  </a>
                </li>
              </ul>
            </div>
          </li>

          <li>
            <a href="#sidebarAdmin" data-bs-toggle="collapse">
              <i data-feather="package"></i>
              <span>Manage Employee</span>
              <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarAdmin">
              <ul class="nav-second-level">
                <li>
                  <a href="{{ route('all.employee') }}" class="tp-link">
                    All Employee
                  </a>
                </li>
              </ul>
            </div>
          </li>
        @endif
      </ul>
    </div>
    <!-- End Sidebar -->

    <div class="clearfix"></div>
  </div>
</div>
