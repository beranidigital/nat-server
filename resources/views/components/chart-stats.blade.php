<div style="border: 1px solid rgba(0, 0, 0, 0.1)" class="card z-index-2">
    <div class="card-header pb-0 d-md-flex justify-content-between align-items-center">
        <div>
            <h6>{{$title}}</h6>
        </div>
        <div>
            <p class="text mb-0">
                {{$info}}
            </p>
        </div>
    </div>


    <div class="card-body p-3">
        <div class="chart-week">
            <canvas id="{{$key}}" class="chart-canvas" height="450" width="1389"
                    style="display: block; box-sizing: border-box; height: 300px; width: 926px;"></canvas>
        </div>
    </div>

    @push('scripts')
        <script>
            // anonymous function to avoid conflicts
            (() => {
                const ctx2 = document.getElementById("{{$key}}").getContext("2d");

                const gradientStroke1 = ctx2.createLinearGradient(72, 221, 71, 50);
                gradientStroke1.addColorStop(1, 'rgba(72, 221, 71, 100)');
                gradientStroke1.addColorStop(0.2, 'rgba(72,221,71,0.0)');
                gradientStroke1.addColorStop(0, 'rgba(203,12,159,0)'); //purple colors


                const gradientStroke2 = ctx2.createLinearGradient(0, 230, 0, 50);
                gradientStroke2.addColorStop(1, 'rgba(215,70,192,100)');
                gradientStroke2.addColorStop(0.2, 'rgba(215,70,192,0.0)');
                gradientStroke2.addColorStop(0, 'rgba(20,23,39,0)'); //purple colors

                const gradientStroke3 = ctx2.createLinearGradient(0, 230, 0, 50);
                gradientStroke2.addColorStop(1, 'rgba(101,140,216,100)');
                gradientStroke2.addColorStop(0.2, 'rgba(101,140,216,0.0)');
                gradientStroke2.addColorStop(0, 'rgba(101,140,216,0)'); //purple colors

                const gradientStroke4 = ctx2.createLinearGradient(0, 230, 0, 50);
                gradientStroke2.addColorStop(1, 'rgba(248,219,71,100)');
                gradientStroke2.addColorStop(0.2, 'rgba(248,219,71, 0.0)');
                gradientStroke2.addColorStop(0, 'rgba(248,219,71,0)');

                const gradientStroke5 = ctx2.createLinearGradient(0, 230, 0, 50);
                gradientStroke2.addColorStop(1, 'rgba(255,69,69,100)');
                gradientStroke2.addColorStop(0.2, 'rgba(255,69,69,0.0)');
                gradientStroke2.addColorStop(0, 'rgba(255,69,69,0)'); //purple colors


                new Chart(ctx2, {
                    type: "line",
                    data: {
                        // labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                        labels: @json($labels),
                        datasets: [{
                            label: "{{$title}}",
                            tension: 0.4,
                            borderColor: "#48DD47",
                            borderWidth: 3,
                            backgroundColor: gradientStroke1,

                            data: @json($values),
                            maxBarThickness: 6,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false,
                                    display: true,
                                    drawOnChartArea: true,
                                    drawTicks: false,
                                    borderDash: [5, 5]
                                },
                                ticks: {
                                    precision: 2,
                                    display: true,
                                    padding: 10,
                                    color: '#b2b9bf',
                                    font: {
                                        size: 11,
                                        family: "Open Sans",
                                        style: 'normal',
                                        lineHeight: 2
                                    },
                                }
                            },
                            x: {
                                grid: {
                                    drawBorder: false,
                                    display: false,
                                    drawOnChartArea: false,
                                    drawTicks: false,
                                    borderDash: [5, 5]
                                },
                                ticks: {
                                    display: true,
                                    color: '#b2b9bf',
                                    padding: 20,
                                    font: {
                                        size: 11,
                                        family: "Open Sans",
                                        style: 'normal',
                                        lineHeight: 2
                                    },
                                }
                            },
                        },
                    },
                })

            })()


        </script>
    @endpush
</div>
