<?php

namespace App\Jobs;

use App\Models\Waterpool;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Carbon;

class ProcessGetApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */

    public function getTime()
    {
        $timestamp = Carbon::now('Asia/Jakarta')->getTimestampMs();
        // return '1698452857642';
        return $timestamp;
    }




    public function handle()
    {
        $pertama = $this->pertama();
        $this->getDataPool();

        return response()->json($pertama);
    }

    public function pertama()
    {
        //$apiUrl = env('API_TOKEN_URL');
        $clientId = env('API_CLIENT_ID');
        $clientSecret = env('API_CLIENT_SECRET');
       	//$deviceId = env('API_DEVICE_ID');
        $sign = '';
        $timestamp = $this->getTime();
        Log::info('timestamp -> ' . $timestamp);
        $headers = [
            'client_id' => $clientId,
            'sign' => '',
            't' => $timestamp,
            'sign_method' => 'HMAC-SHA256',
            'nonce' => null,
            'stringToSign' => null
        ];
        $query = [
            'grant_type' => 1
        ];
        $body = null;
        $mode = $body ? $body['mode'] : null;
        $secret = $clientSecret;
        $url = '/v1.0/token';
        $host = 'https://openapi.tuyaus.com';
        $signMap = $this->stringToSign($query, $mode, 'GET', $secret, $headers, $body, $url);
        $urlStr = $signMap["url"];
        $signStr = $signMap["signUrl"];
        $requestUrl = $host . $urlStr;
        if (array_key_exists("nonce", $headers)) {
            $nonce = $headers['nonce'];
        }
        $sign = $this->calcSign($clientId, $timestamp, $nonce, $signStr, $secret);
        $easySign = $sign;
        $finalHeaders = [
            'client_id' => $headers['client_id'],
            'sign' => $easySign,
            't' => $headers['t'],
            'sign_method' => $headers['sign_method'],
            'nonce' => $headers['nonce'],
            'stringToSign' => $headers['stringToSign']
        ];

        $response = Http::withHeaders($finalHeaders)->get($requestUrl);
        Log::info('result => ' . $response);
        return $response->json();
    }

    private function calcSign($clientId, $timestamp, $nonce, $signStr, $secret)
    {
        $str = $clientId . $timestamp . $nonce . $signStr;
        $hash = hash_hmac('sha256', "$str", $secret);
        $signUp = strtoupper($hash);
        return $signUp;
    }

    private function stringToSign($query, $mode, $method, $secret, $headers, $body = null, $url)
    {
        $sha256 = "";
        $q = "";
        $headersStr = "";
        $map = (object)[];
        $arr = [];
        $bodyStr = "";
        if ($query) {
            $query = $this->toJsonObj($query, $arr);
        }
        Log::info('query -> ' . json_encode($query));

        if ($body && $mode) {
        }

        $sha256 = hash('sha256', $bodyStr);

        sort($query);
        foreach ($query as $key => $item) {
            $k = array_keys($item)[0];
            $v = array_values($item)[0];
            $q .= $k . "=" . $v . "&";
        }
        if (strlen($q) > 0) {
            $query = rtrim($q, '&');
            $url = $url . "?" . $query;
        } else {
            $url = $url;
        }
        $map = [
            'signUrl' => $method . "\n" . $sha256 . "\n" . $headersStr . "\n" . $url,
            'url' => $url
        ];
        return $map;
    }

    private function toJsonObj($params, $arr = [])
    {
        $jsonBodyStr = json_encode($params);
        $jsonBody = json_decode($jsonBodyStr);
        $data = [];
        foreach ($jsonBody as $key => $item) {
            $arr[$key] = $item;
            // $data[] = (object)[$arr];
        }
        $data = [$arr];
        return $data;
    }

    // bener
    public function getDataPool()
    {
        $authResult = $this->pertama();
        $accessToken = $authResult['result']['access_token'];
        // $refreshToken = $authResult['result']['refresh_token'];

        $apiUrl = env('API_DEVICE_URL');
        $clientId = env('API_CLIENT_ID');
        $clientSecret = env('API_CLIENT_SECRET');
        $deviceId = env('API_DEVICE_ID');
        $sign = '';
        $timestamp = $this->getTime();

        $headers = [
            'client_id' => $clientId,
            'access_token' => $accessToken,
            'sign' => '',
            't' => $timestamp,
            'sign_method' => 'HMAC-SHA256',
            'nonce' => null,
            'stringToSign' => null
        ];

        $query = [];

        $body = null;
        $mode = $body ? $body['mode'] : null;
        $secret = $clientSecret;
        $url = "/v1.0/devices/$deviceId";

        $host = 'https://openapi.tuyaus.com';
        $signMap = $this->stringToSignPoll($query, $mode, 'GET', $secret, $headers, $body, $url);
        $urlStr = $signMap["url"];
        $signStr = $signMap["signUrl"];
        $requestUrl = $host . $urlStr;
        if (array_key_exists("nonce", $headers)) {
            $nonce = $headers['nonce'];
        }

        $sign = $this->calcSignPool($clientId, $accessToken, $timestamp, $nonce, $signStr, $secret);
        $easySign = $sign;
        $finalHeaders = [
            'client_id' => $headers['client_id'],
            'access_token' => $accessToken,
            'sign' => $easySign,
            't' => $headers['t'],
            'sign_method' => $headers['sign_method'],
            'nonce' => $headers['nonce'],
            'stringToSign' => $headers['stringToSign']
        ];

        $response = Http::withHeaders($finalHeaders)->get($requestUrl);
        Log::info('result data pool => ' . $response);

        $statusData = $response['result']['status'];
        $waterpool = new Waterpool;
        // Mengisi atribut model dengan data "status"
        foreach ($statusData as $statusItem) {
            $code = $statusItem['code'];
            $value = $statusItem['value'];

            if (in_array($code, $waterpool->getFillable())) {
                $waterpool->$code = $value;
            }
        }
        $waterpool->save();
        return $response->json();
    }

    private function calcSignPool($clientId, $accessToken, $timestamp, $nonce, $signStr, $secret)
    {
        $str = $clientId . $accessToken . $timestamp . $nonce . $signStr;
        $hash = hash_hmac('sha256', "$str", $secret);
        $signUp = strtoupper($hash);
        return $signUp;
    }

    private function stringToSignPoll($query, $mode, $method, $secret, $headers, $body = null, $url)
    {
        $sha256 = "";
        $q = "";
        $headersStr = "";
        $map = (object)[];
        $arr = [];
        $bodyStr = "";
        if ($query) {
            $query = $this->toJsonObjPool($query, $arr);
        }

        if ($body && $mode) {
        }

        $sha256 = hash('sha256', $bodyStr);

        sort($query);
        foreach ($query as $key => $item) {
            $k = array_keys($item)[0];
            $v = array_values($item)[0];
            $q .= $k . "=" . $v . "&";
        }
        if (strlen($q) > 0) {
            $query = rtrim($q, '&');
            $url = $url . "?" . $query;
        } else {
            $url = $url;
        }
        $map = [
            'signUrl' => $method . "\n" . $sha256 . "\n" . $headersStr . "\n" . $url,
            'url' => $url
        ];
        return $map;
    }

    private function toJsonObjPool($params, $arr = [])
    {
        $jsonBodyStr = json_encode($params);
        $jsonBody = json_decode($jsonBodyStr);
        $data = [];
        foreach ($jsonBody as $key => $item) {
            $arr[$key] = $item;
            // $data[] = (object)[$arr];
        }
        $data = [$arr];
        return $data;
    }
}
