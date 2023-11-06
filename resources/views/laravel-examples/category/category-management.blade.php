@extends('user_type.auth', ['parentFolder' => 'laravel', 'childFolder' => 'category'])

@section('content')
<div class="row">
    <div class="col-12">
      <div class="card">
        <!-- Card header -->
        <div class="card-header pb-0">
          <div class="d-lg-flex">
            <div>
              <h5 class="mb-0">All Categories</h5>
            </div>
            <div class="ms-auto my-auto mt-lg-0 mt-4">
              <div class="ms-auto my-auto">
                <a href="{{ url('laravel-new-category') }}" class="btn bg-gradient-primary btn-sm mb-0">+&nbsp; New Category</a>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body px-0 pb-0">
          <div class="table-responsive">
          @if($errors->get('msgError'))
              <div class="m-3  alert alert-warning alert-dismissible fade show" role="alert">
                  <span class="alert-text text-white">
                  {{$errors->first()}}</span>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                      <i class="fa fa-close" aria-hidden="true"></i>
                  </button>
              </div>
            @endif
            @if(session('success'))
              <div class="m-3  alert alert-success alert-dismissible fade show" id="alert-success" role="alert">
                  <span class="alert-text text-white">
                  {{ session('success') }}</span>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                      <i class="fa fa-close" aria-hidden="true"></i>
                  </button>
              </div>
            @endif
            <table class="table table-flush" id="category-list">
              <thead class="thead-light">
                <tr>
                  <th>ID</th>
                  <th>NAME</th>
                  <th>DESCRIPTION</th>
                  <th>CREATION DATE</th>
                  <th>ACTION</th>
                </tr>
              </thead>
              <tbody>
              @if(count($categories) > 1)
                  @foreach($categories as $category)
                      <tr>
                        <td class="text-sm">{{$category->id}}</td>
                        <td class="text-sm">{{$category->name}}</td>
                        <td class="text-sm">{{$category->description}}</td>
                        <td class="text-sm">{{$category->created_at}}</td>
                        <td class="text-sm">
                          <a href="{{ url('laravel-edit-categories/' . $category->id) }}" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit category">
                            <i class="fas fa-user-edit text-secondary"></i>
                          </a>
                          <a href="{{ url('laravel-delete-category/' . $category->id) }}" data-bs-toggle="tooltip" data-bs-original-title="Delete category">
                            <i class="fas fa-trash text-secondary"></i>
                          </a>
                        </td>
                      </tr>
                  @endforeach
              @else
                  <tr> no content </tr>
              @endif
                
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

  @push('js')
    <script src="{{ URL::asset('assets/js/plugins/datatables.js') }}"></script>  
    <script>
      if (document.getElementById('category-list')) {
        const dataTableSearch = new simpleDatatables.DataTable("#category-list", {
          searchable: true,
          fixedHeight: false,
          perPage: 7
        });

        document.querySelectorAll(".export").forEach(function(el) {
          el.addEventListener("click", function(e) {
            var type = el.dataset.type;

            var data = {
              type: type,
              filename: "soft-ui-" + type,
            };

            if (type === "csv") {
              data.columnDelimiter = "|";
            }

            dataTableSearch.export(data);
          });
        });
      };
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
    $(document).ready(function(){
        $("#alert-success").delay(3000).slideUp(300);

        });
    </script>
  @endpush