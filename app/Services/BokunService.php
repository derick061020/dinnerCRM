<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BokunService
{
    protected $baseUrl = 'https://api.bokun.io';
    protected $accessKey;
    protected $secretKey;

    public function __construct()
    {
        $this->accessKey = env('BOKUN_KEY');
        $this->secretKey = env('BOKUN_SECRET');
    }

    protected function headers($path, $method = 'GET')
    {
        $date = gmdate('Y-m-d H:i:s');

        $stringToSign = $date . $this->accessKey . strtoupper($method) . $path;

        $signature = base64_encode(
            hash_hmac('sha1', $stringToSign, $this->secretKey, true)
        );

        return [
            'X-Bokun-Date' => $date,
            'X-Bokun-AccessKey' => $this->accessKey,
            'X-Bokun-Signature' => $signature,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    // ✅ NUEVA API: availability
    public function getAvailability($productId, $startDate, $endDate)
    {
        $path = "/activity.json/{$productId}/availabilities?start={$startDate}&end={$endDate}";

        return Http::withHeaders($this->headers($path, 'GET'))
            ->get($this->baseUrl . $path)
            ->json();
    }

   public function createBookingCheckout($productId, $date, $timeId, $rateId, $pricingCategoryId)
{
    // 🔹 1. OPTIONS
    $optionsPath = "/checkout.json/options/booking-request";

    $options = Http::withHeaders($this->headers($optionsPath, 'POST'))
        ->asJson()
        ->post($this->baseUrl . $optionsPath, [
            "checkoutRequest" => [
                "checkoutOption" => [
                    "paymentMethod" => "CUSTOMER_NO_PAYMENT"
                ],
                "source" => "DIRECT_REQUEST",
                "directBooking" => [
                    "bookingRequest" => [
                        "activityBookings" => [
                            [
                                "activityId" => (int) $productId,
                                "date" => $date,
                                "startTimeId" => $timeId,
                                "rateId" => (int) $rateId,
                                "pricingCategoryBookings" => [
                                    [
                                        "pricingCategoryId" => (int) $pricingCategoryId,
                                        "passengerDetails" => [
                                            "firstName" => "Test",
                                            "lastName" => "User"
                                        ]
                                    ]
                                ],
                                "pickup" => false,
                                "dropoff" => false
                            ]
                        ],
                        "customer" => [
                            "firstName" => "Test",
                            "lastName" => "User",
                            "email" => "test@example.com"
                        ]
                    ]
                ]
            ]
        ])->json();

    // 🔹 2. SUBMIT (CREA EL BOOKING)
    $submitPath = "/checkout.json/submit";

    $submit = Http::withHeaders($this->headers($submitPath, 'POST'))
        ->asJson()
        ->post($this->baseUrl . $submitPath, [
            "checkoutRequest" => [
                "checkoutOption" => [
                    "paymentMethod" => "CUSTOMER_NO_PAYMENT"
                ],
                "source" => "DIRECT_REQUEST",
                "directBooking" => [
                    "bookingRequest" => [
                        "activityBookings" => [
                            [
                                "activityId" => (int) $productId,
                                "date" => $date,
                                "startTimeId" => $timeId,
                                "rateId" => (int) $rateId,
                                "pricingCategoryBookings" => [
                                    [
                                        "pricingCategoryId" => (int) $pricingCategoryId,
                                        "passengerDetails" => [
                                            "firstName" => "Test",
                                            "lastName" => "User"
                                        ]
                                    ]
                                ],
                                "pickup" => false,
                                "dropoff" => false
                            ]
                        ],
                        "customer" => [
                            "firstName" => "Test",
                            "lastName" => "User",
                            "email" => "test@example.com"
                        ]
                    ]
                ]
            
            ]
        ]);

    return $submit->json();
}
public function testCheckoutProper()
{
    $path = '/checkout.json/submit';

    $json = [
        "checkoutRequest" => [
            "checkoutOption" => [
                "paymentMethod" => "CUSTOMER_NO_PAYMENT"
            ],
            "source" => "DIRECT_REQUEST",
            "directBooking" => [
                "bookingRequest" => [
                    "activityBookings" => [
                        [
                            "activityId" => 1181233,
                            "date" => "2026-03-24",
                            "startTimeId" => "4860049_20260324",
                            "rateId" => 2343163,
                            "pricingCategoryBookings" => [
                                [
                                    "pricingCategoryId" => 1058524,
                                    "passengerDetails" => [
                                        "firstName" => "Test",
                                        "lastName" => "User"
                                    ]
                                ]
                            ],
                            "pickup" => false,
                            "dropoff" => false
                        ]
                    ],
                    "customer" => [
                        "firstName" => "Test",
                        "lastName" => "User",
                        "email" => "testuser@example.com"
                    ]
                ]
            ],
            "sendNotificationToMainContact" => false,
            "showPricesInNotification" => false
        ]
    ];

    $date = gmdate('Y-m-d H:i:s');
    $stringToSign = $date . env('BOKUN_KEY') . 'POST' . $path;
    $signature = base64_encode(hash_hmac('sha1', $stringToSign, env('BOKUN_SECRET'), true));

    $response = Http::withHeaders([
        'X-Bokun-Date' => $date,
        'X-Bokun-AccessKey' => env('BOKUN_KEY'),
        'X-Bokun-Signature' => $signature,
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ])->post('https://api.bokun.io' . $path, $json);

    return $response->json();
}
}