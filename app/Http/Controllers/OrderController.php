<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $userId = $request->user_id;
        $orders = Orders::query();

        // filter
        $orders->when($userId, function($query) use ($userId){
            return $query->where('user_id', $userId);
        });

        return response()->json([
            'status'    => 'success',
            'data'      => $orders->get()
        ]);
    }


    public function create(Request $request)
    {
        # code...
        $user   = $request->input('user');
        $course = $request->input('course');

        $order  = Orders::create([
            'user_id'   => $user['id'],
            'course_id' => $course['id'],
        ]);

        $transactionDetails     = [
            'order_id'  => $order->id.'-'.Str::random(5),
            'gross_amount'     => $course['price']
        ];
        $itemDetails = [
            [
                'id'        => $course['id'],
                'price'     => $course['price'],
                'quantity'  => 1,
                'name'      => $course['name'],
                'brand'     => 'Nuild With Angga',
                'category'  => 'Online Course',
            ]
        ];

        $customerDetails = [
            'first_name'    => $user['name'],
            'email'         => $user['email']
        ];

        // Call Midtrans
        $midtransParams = [
            'transaction_details'   => $transactionDetails,
            'item_details'          => $itemDetails,
            'customer_detals'       => $customerDetails
        ];

        // get snap url midtrans
        $midtransSnapUrl = $this->getMidtransSnapUrl($midtransParams);

        $order->snap_url = $midtransSnapUrl;

        $order->metadata = [
            'course_id'     => $course['id'],
            'course_price'  => $course['price'],
            'course_name'   => $course['name'],
            'course_thumbnail' =>$course['thumbnail'],
            'course_leve'   => $course['level']
        ];

        // Save data
        $order->save();

        return response()->json([
            'status'    => 'success',
            'data'      => $order
        ]);

    }

    private function getMidtransSnapUrl($params)
    {
        # code...
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_PRODUCTION');
        \Midtrans\Config::$is3ds = (bool) env('MIDTRANS_3DS');

        $snapUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;
        return $snapUrl;
    }
}
