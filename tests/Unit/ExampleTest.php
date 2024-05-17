<?php

namespace Tests\Unit;

use App\Http\Controllers\SensorDataController;
use App\Http\Controllers\WaterpoolController;
use App\Livewire\SettingsParameter;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{

    public function test_interpolation()
    {
        $testCase = [[
            'input' => [
                [1.0, 10],
                [2.0, 20],
                [3.0, 30]
            ],
            'output' => [
                [1.0, 10],
                [1.5, 15],
                [2.0, 20],
                [2.5, 25],
                [3.0, 30]
            ]
        ]
        ];

        foreach ($testCase as $test) {
            foreach ($test['output'] as $i => $output) {
                $result = SensorDataController::interpolate($test['input'], $output[0]);
                $this->assertEquals($output[1], $result);
            }
        }
    }

    public function test_example()
    {
        $this->assertTrue(true);
    }


    public function test_array_to_kv()
    {
        $testCase = [
            [
                "input" => [
                    "name" => "sensor",
                    "value" => "cool"
                ],
                "output" => [
                    "sensor" => "cool"
                ]
            ],
            [
                "input" => [
                    [
                        "name" => "sensor",
                        "value" => "cool"
                    ],
                    [
                        "name" => "sensor2",
                        "value" => "cool2"
                    ]
                ],
                "output" => [
                    "sensor" => "cool",
                    "sensor2" => "cool2"
                ]
            ],
            [
                "input" => [
                    [
                        "name" => "sensor",
                        "value" => [
                            [
                                "name" => "sensor2",
                                "value" => [
                                    [
                                        "name" => "sensor3",
                                        "value" => "cool3"
                                    ],
                                    [
                                        "name" => "sensor4",
                                        "value" => "cool4"
                                    ]
                                ],
                            ]
                        ]
                    ]
                ],
                "output" => [
                    "sensor" => [
                        "sensor2" => [
                            "sensor3" => "cool3",
                            "sensor4" => "cool4"
                        ]
                    ]
                ]

            ],
            [
                "input" => [
                    [
                        "name" => "sensor",
                        "value" => [
                            [
                                "name" => "sensor2",
                                "value" => [
                                    [
                                        "name" => "sensor3",
                                        "value" => "cool3"
                                    ],
                                    [
                                        "name" => "sensor4",
                                        "value" => "cool4"
                                    ]
                                ],
                            ],
                            [
                                "name" => "sensor5",
                                "value" => [
                                    [
                                        "name" => "sensor6",
                                        "value" => "cool6"
                                    ],
                                    [
                                        "name" => "sensor7",
                                        "value" => "cool7"
                                    ]
                                ],
                            ]
                        ]
                    ]
                ],
                "output" => [
                    "sensor" => [
                        "sensor2" => [
                            "sensor3" => "cool3",
                            "sensor4" => "cool4"
                        ],
                        "sensor5" => [
                            "sensor6" => "cool6",
                            "sensor7" => "cool7"
                        ]
                    ]
                ]

            ],
            [
                "input" => [
                    [
                        "name" => "sensor",
                        "value" => [
                            [
                                "name" => "sensor2",
                                "value" => [
                                    [
                                        "name" => "sensor3",
                                        "value" => "cool3"
                                    ],
                                    [
                                        "name" => "sensor4",
                                        "value" => "cool4"
                                    ]
                                ],
                            ],
                            [
                                "name" => "sensor5",
                                "value" => [
                                    [
                                        "a" => "sensor6",
                                        "b" => "cool6"
                                    ],
                                    [
                                        "c" => "sensor7",
                                        "d" => "cool7"
                                    ]
                                ],
                            ]
                        ]
                    ],
                ],
                "output" => [
                    "sensor" => [
                        "sensor2" => [
                            "sensor3" => "cool3",
                            "sensor4" => "cool4"
                        ],
                        "sensor5" => [
                            [
                                "a" => "sensor6",
                                "b" => "cool6"
                            ],
                            [
                                "c" => "sensor7",
                                "d" => "cool7"
                            ]
                        ]
                    ]
                ]
            ]

        ];

        foreach ($testCase as $test) {

            $this->assertEquals($test['output'], SettingsParameter::arrayToKv($test['input']));
        }
    }

    public function test_kv_to_array()
    {
        $testCases = [
            [
                'input' => [
                    'a' => 'b',
                    'c' => [
                        'd' => 'e',
                        'f' => 'g'
                    ],
                    'd' => [
                        'a', 'b', 'c'
                    ]
                ],
                'output' => [
                    [
                        'name' => 'a',
                        'value' => 'b'
                    ],
                    [
                        'name' => 'c',
                        'value' => [
                            [
                                'name' => 'd',
                                'value' => 'e'
                            ],
                            [
                                'name' => 'f',
                                'value' => 'g'
                            ]
                        ]
                    ],
                    [
                        'name' => 'd',
                        'value' => [
                            'a', 'b', 'c'
                        ]
                    ]
                ]
            ]
        ];

        foreach ($testCases as $testCase) {
            $this->assertEquals($testCase['output'], SettingsParameter::kvToArray($testCase['input']));
        }
    }

    public function test_format()
    {
        WaterpoolController::formatSensor('battery', 22);
    }
}
