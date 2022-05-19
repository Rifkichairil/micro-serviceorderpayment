<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\PaymentLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WebhookController extends Controller
{
    //
    public function midtransHandler(Request $request)
    {
        # code...
        $data = $request->all();

        // get data signature key
        $signatureKey   = $data['signature_key'];
        $orderId        = $data['order_id'];
        $statusCode     = $data['status_code'];
        $grossAmount    = $data['gross_amount'];
        $serverKey      = env('MIDTRANS_SERVER_KEY');

        $mySignatureKey = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $transactionStatus  = $data['transaction_status'];
        $payment_type       = $data['payment_type'];
        $fraudStatus        = $data['fraud_status'];

        // dd($signatureKey, $orderId, $statusCode, $grossAmount, $serverKey);


        if ($signatureKey !== $mySignatureKey) {
            # code...
            return response()->json([
                'status'    => 'error',
                'message'   => 'invalid Signature'
            ], 400);
        }

        $realOrderId = explode('-', $orderId);
        $order = Orders::find($realOrderId[0]);
        // dd($signatureKey, $mySignatureKey);

        if (!$order) {
            # code...
            return response()->json([
                'status'    => 'error',
                'message'   => 'order ID not found'
            ]);
        }

        if ($order->status === 'success') {
            # code...
            return response()->json([
                'status'    => 'error',
                'message'   => 'operation not permited'
            ], 405);
        }

        if ($transactionStatus == 'capture'){
            if ($fraudStatus == 'challenge'){
                $order->status = 'challenge';
            } else if ($fraudStatus == 'accept'){
                $order->status = 'success';
            }
        } else if ($transactionStatus == 'settlement'){
            $order->status = 'success';
        } else if ($transactionStatus == 'cancel' ||
            $transactionStatus == 'deny' ||
            $transactionStatus == 'expire'){
                $order->status = 'failure';
        } else if ($transactionStatus == 'pending'){
            $order->status = 'pending';
        }

        $logsData = [
            'status'        => $transactionStatus,
            'raw_response'  => json_encode($data),
            'order_id'      => $realOrderId[0],
            'payment_type'  => $payment_type
        ];

        PaymentLogs::create($logsData);
        $order->save();

        if ($order->status === 'success') {
            # code...
            createPremiumAccess([
                'user_id'   => $order->user_id,
                'course_id' => $order->course_id
            ]);
        }

        return response()->json('OK');
    }
}
