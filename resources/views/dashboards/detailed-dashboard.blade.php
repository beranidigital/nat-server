@php use App\Models\AppSettings; @endphp
<style>
    .bg-color {
        background-color: #344767 !important;
    }
</style>
@extends('user_type.auth', ['parentFolder' => 'waterpool', 'childFolder' => 'items'])

@section('content')
    <div class="row">
        <div class="col-xl-12  mt-xl-0 mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="card bg-color">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8 my-auto">
                                    <div class="numbers d-flex align-items-center justify-content-between">
                                        <h5 class="text-white font-weight-bolder mb-0">
                                            {{ AppSettings::translateDeviceName($deviceName) }}
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-4"> <!-- Menambahkan div dengan col-4 untuk h6 -->
                                    <div class="d-flex justify-content-end">
                                        <h6 class="text-white font-weight-bolder mb-0">
                                            @if (isset($latestState['created_at']))
                                                <span>{{ date('d M | H:i', strtotime($latestState['created_at'])) }}</span>
                                            @endif
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-0">


                @php
                    unset($latestState['timestamp']);
                @endphp
                {{-- @dd($device,$formatted_state); --}}
                {{-- @dd($device['scores']['ph']); --}}
                @foreach($latestState['scores'] as $sensor_name => $score)
                    {{-- Dont add battery sensor to the dashboard --}}
                    @php
                        $shouldContinue = false;
                    @endphp
                    @foreach(AppSettings::$batterySensors as $batterySensor)
                        @if($sensor_name === $batterySensor)
                            @php
                                $shouldContinue = true;
                            @endphp
                        @endif
                    @endforeach
                    @if($shouldContinue)
                        @continue
                    @endif


                    <div class="col-md-4 col-12 mt-4 ">
                        @if( $score > \App\Http\Controllers\SensorDataController::$parameterThresholdDisplay['green'])
                            <div class="card bg-success">
                                @elseif($score > \App\Http\Controllers\SensorDataController::$parameterThresholdDisplay['yellow'])
                            <div class="card bg-warning">
                        @else
                            <div class="card bg-danger">
                                @endif
                                <div class="card-body text-center">

                                    <h1 class="text-white text-primary">
                                    <span id="{{$sensor_name}}_state">
                                        @if( $latestState['formatted_sensors'][$sensor_name] == 'unknown')
                                            -
                                        @else
                                            {{  $latestState['formatted_sensors'][$sensor_name]['value'] }}
                                        @endif
                                    </span>
                                        <span
                                            class="text-lg ms-n2">{{ $latestState['formatted_sensors'][$sensor_name]['unit']}}</span>


                                    </h1>
                                    <h6 class="mb-0 font-weight-bolder">{{ $latestState['formatted_sensors'][$sensor_name]['label']}}</h6>

                                </div>
                            </div>

                            </div>

                @endforeach

            </div>

            {{-- <div class="row mt-4">

                <div class="col-md-2 mt-md-0 mt-4 ">

                        <div class="card text-center">
                            <form method="GET">
                                <input type="date" name="date" id="date" class="form-control"
                                       value="{{isset($_GET['date']) ? $_GET['date'] : ''}}"
                                       max="{{$date_filter['max']}}" min="{{$date_filter['min']}}"
                                       onchange="this.form.submit()"
                                />
                            </form>
                        </div>

                </div>
            </div> --}}
        </div>
    </div>

    <div class="card mt-4 p-4">
        <div class="d-md-flex flex-column flex-md-row justify-content-md-between align-items-md-center">
            <h5 class="mb-0">{{ \App\Models\AppSettings::translateDeviceName($deviceName) }} Analytic</h5>
            <div class="col-md-2 mt-md-0 mt-4 ml-md-auto mt-sm-0">
                <div class="text-center">
                    <form method="GET">
                        <input type="hidden" name="device" value="{{ $deviceName }}">
                        <input style="border: 1px solid rgba(0,0,0,0.2);" type="date" name="date" id="date"
                               class="form-control"
                               value="{{ isset($_GET['date']) ? $_GET['date'] : '' }}"
                               max="{{ date('Y-m-d', $date_filter['max']) }}"
                               min="{{ date('Y-m-d', $date_filter['min']) }}"
                               onchange="this.form.submit()"
                        />
                    </form>
                </div>
            </div>
        </div>


        @foreach(($stats) as $key => $stat)
            <div class="row mt-4">
                <div class="col-lg-12">
                    <x-chart-stats :title='AppSettings::translateSensorKey($key)'
                                   :labels="$stat['timestamp']"
                                   :values="$stat['data']"
                                   :info="date('d M Y', strtotime($stat['timestamp'][count($stat['timestamp']) - 1]))"

                    ></x-chart-stats>
                </div>
            </div>
        @endforeach


    </div>
    <hr class="horizontal dark my-5">
    <div class="row mt-4">
        <div class="col-12">
            <x-detail-table :device-name='$deviceName'/>
        </div>
    </div>
    <div class="row">
    </div>

@endsection

@push('js')
    <script>
        // Fungsi untuk memuat ulang halaman setiap menit (60 * 1000 milidetik)
        function autoReload() {
            setTimeout(function () {
                location.reload();
            }, 30 * 60 * 1000); // 1 menit
        }

        // Panggil fungsi autoReload saat halaman dimuat
        window.onload = autoReload;
    </script>



    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> --}}
    <script src="{{ URL::asset('assets/js/plugins/choices.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/plugins/countup.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/plugins/chartjs.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/plugins/round-slider.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/plugins/datatables.js') }}"></script>
    <script>
        if (document.getElementById('items-list')) {
            const dataTableSearch = new simpleDatatables.DataTable("#items-list", {
                searchable: true,
                fixedHeight: false,
                perPage: 7
            });

            document.querySelectorAll(".export").forEach(function (el) {
                el.addEventListener("click", function (e) {
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
        }

    </script>
    <script>
        $(document).ready(function () {
            $("#alert-success").delay(3000).slideUp(300);

        });
    </script>
    <script>
        // Rounded slider
        const setValue = function (value, active) {
            document.querySelectorAll("round-slider").forEach(function (el) {
                if (el.value === undefined) return;
                el.value = value;
            });
            const span = document.querySelector("#value");
            span.innerHTML = value;
            if (active)
                span.style.color = 'red';
            else
                span.style.color = 'black';
        }

        document.querySelectorAll("round-slider").forEach(function (el) {
            el.addEventListener('value-changed', function (ev) {
                if (ev.detail.value !== undefined)
                    setValue(ev.detail.value, false);
                else if (ev.detail.low !== undefined)
                    setLow(ev.detail.low, false);
                else if (ev.detail.high !== undefined)
                    setHigh(ev.detail.high, false);
            });

            el.addEventListener('value-changing', function (ev) {
                if (ev.detail.value !== undefined)
                    setValue(ev.detail.value, true);
                else if (ev.detail.low !== undefined)
                    setLow(ev.detail.low, true);
                else if (ev.detail.high !== undefined)
                    setHigh(ev.detail.high, true);
            });
        });

        // Count To
        if (document.getElementById('status1')) {
            const countUp = new CountUp('status1', document.getElementById("status1").getAttribute("countTo"));
            if (!countUp.error) {
                countUp.start();
            } else {
                console.error(countUp.error);
            }
        }
        if (document.getElementById('status2')) {
            const countUp = new CountUp('status2', document.getElementById("status2").getAttribute("countTo"));
            if (!countUp.error) {
                countUp.start();
            } else {
                console.error(countUp.error);
            }
        }
        if (document.getElementById('status3')) {
            const countUp = new CountUp('status3', document.getElementById("status3").getAttribute("countTo"));
            if (!countUp.error) {
                countUp.start();
            } else {
                console.error(countUp.error);
            }
        }
        if (document.getElementById('status4')) {
            const countUp = new CountUp('status4', document.getElementById("status4").getAttribute("countTo"));
            if (!countUp.error) {
                countUp.start();
            } else {
                console.error(countUp.error);
            }
        }
        if (document.getElementById('status5')) {
            const countUp = new CountUp('status5', document.getElementById("status5").getAttribute("countTo"));
            if (!countUp.error) {
                countUp.start();
            } else {
                console.error(countUp.error);
            }
        }


        //chart line per 8 jam sehari


    </script>
    @stack('scripts')
@endpush
