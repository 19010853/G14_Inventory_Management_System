@extends('admin.admin_master')
@section('admin')
  <div class="content">
    <!-- Start Content-->
    <div class="container-xxl">
      <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
          <h4 class="fs-18 fw-semibold m-0">All WareHouse</h4>
        </div>

        @if (Auth::guard('web')->user()->can('all.warehouse'))
        <div class="text-end">
          <ol class="breadcrumb m-0 py-0">
            <a href="{{ route('add.warehouse') }}" class="btn btn-primary">
              Add WareHouse
            </a>
          </ol>
        </div>
        @endif
      </div>

      <!-- Datatables  -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header"></div>
            <!-- end card header -->

            <div class="card-body">
              <div class="table-responsive">
                <table
                  id="datatable"
                  class="table table-bordered dt-responsive nowrap"
                >
                  <thead>
                    <tr>
                      <th style="width: 50px;">Sl</th>
                      <th style="min-width: 150px;">WareHouse Name</th>
                      <th style="min-width: 180px;">Email</th>
                      <th style="min-width: 120px;">Phone</th>
                      <th style="min-width: 200px; max-width: 300px;">City</th>
                      <th style="width: 120px;">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($warehouse as $key => $item)
                      <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->phone }}</td>
                        <td style="word-wrap: break-word; word-break: break-word; white-space: normal; max-width: 300px;">
                          {{ $item->city }}
                        </td>
                        <td>
                          @if (Auth::guard('web')->user()->can('all.warehouse'))
                          <a
                            href="{{ route('edit.warehouse', $item->id) }}"
                            class="btn btn-success btn-sm"
                          >
                            Edit
                          </a>
                          <a
                            href="{{ route('delete.warehouse', $item->id) }}"
                            class="btn btn-danger btn-sm"
                            id="delete"
                            data-delete-text="this warehouse"
                          >
                            Delete
                          </a>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            
            <style>
              /* Responsive table styles for warehouse */
              @media (max-width: 1200px) {
                #datatable th:nth-child(5),
                #datatable td:nth-child(5) {
                  min-width: 250px;
                  max-width: 350px;
                }
              }
              
              @media (max-width: 992px) {
                #datatable th:nth-child(5),
                #datatable td:nth-child(5) {
                  min-width: 200px;
                  max-width: 300px;
                }
              }
              
              /* Ensure City column wraps properly */
              #datatable td:nth-child(5) {
                word-wrap: break-word;
                word-break: break-word;
                white-space: normal;
                line-height: 1.5;
              }
              
              /* Responsive adjustments when sidebar is toggled */
              .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
              }
              
              /* Ensure table doesn't break layout */
              #datatable {
                width: 100% !important;
                table-layout: auto;
              }
            </style>
          </div>
        </div>
      </div>
    </div>
    <!-- container-fluid -->
  </div>
  <!-- content -->
@endsection
