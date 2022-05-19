<?php

use Illuminate\Support\Facades\Http;

function createPremiumAccess($data)
{
    # code...
    $url = env('SERVICE_COURSE_URL').'api/my-courses/premium';

    try {
        //code...
        $response   = Http::post($url, $data);
        $data       = $response->json();
        $data['http_code'] = $response->getStatusCode();
        return $data;

    } catch (\Throwable $th) {
        //throw $th;
        return [
            'status'    => 'error',
            'http_code' => 500,
            'message'   => 'services course unavailable'
        ];
    }

}
