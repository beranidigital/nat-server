@extends('user_type.auth', ['parentFolder' => 'appSettings', 'childFolder' => 'none'])

@section('content')

    <div class="card-body pt-4 m-4 p-3" style="height: 100vh">
        @foreach($profileParameter as $profileName => $threshold)
            @foreach($charted[$profileName] as $sensor => $chart)
                <div class="mb-4">
                    <x-chart-stats
                        :title="$profileName . ' - ' . \App\Models\AppSettings::translateSensorKey($sensor)"
                        :labels="$chart['labels']"
                        :values="$chart['values']"
                    />
                </div>
            @endforeach
        @endforeach
    </div>

@endsection
@push('js')
    <script src="{{ URL::asset('assets/js/plugins/chartjs.min.js') }}"></script>
    @stack('scripts')
@endpush
