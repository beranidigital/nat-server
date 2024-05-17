<div class="card">
    <!-- Card header -->
    <div class="card-header pb-0">
        <div class="d-lg-flex">
            <div>
                <h5 class="mb-0">All Items</h5>
            </div>
            <div hidden class="ms-auto my-auto mt-lg-0 mt-4">
                <div class="ms-auto my-auto">
                    <a href="{{ url('laravel-new-item') }}" class="btn bg-gradient-primary btn-sm mb-0">+&nbsp;
                        New Item</a>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{route('detailed-dashboard.export')}}" class="btn bg-gradient-primary btn-sm mb-0"
               type="button">Export Excel</a>
            <a href="{{route('detailed-dashboard.export', ['isPdf=1'])}}"
               class="btn bg-gradient-primary btn-sm mb-0" type="button">Export PDF</a>
        </div>

    </div>
    <div class="card-body px-0 pb-0">
        <div class="table-responsive">
            @if ($errors->get('msgError'))
                <div class="m-3  alert alert-warning alert-dismissible fade show" role="alert">
                                <span class="alert-text text-white">
                                    {{ $errors->first() }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        <i class="fa fa-close" aria-hidden="true"></i>
                    </button>
                </div>
            @endif
            @if (session('success'))
                <div class="m-3  alert alert-success alert-dismissible fade show" id="alert-success"
                     role="alert">
                                <span class="alert-text text-white">
                                    {{ session('success') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        <i class="fa fa-close" aria-hidden="true"></i>
                    </button>
                </div>
            @endif
            <table class="table table-flush" id="items-list">

                @if (is_array($status) && count($status) > 0)
                    <thead class="thead-light">
                    <tr>
                        @foreach($status[0]['formatted_sensors'] as $key => $data)
                            <th class="text-sm">{{ $data['label'] }}</th>
                        @endforeach
                        <th class="text-sm"> Timestamp</th>

                    </tr>
                    </thead>
                    <tbody>

                    @foreach ($status as $stat)
                        <tr>
                            @foreach($stat['formatted_sensors'] as $key => $data)
                                <td class="text-sm">{{ $data['value'] }}</td>
                            @endforeach
                            <td class="text-sm">{{ date('Y-m-d H:i:s', strtotime($stat['created_at'])) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                @endif
            </table>
        </div>
    </div>
</div>
