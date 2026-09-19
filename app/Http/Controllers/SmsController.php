<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendSmsRequest;
use App\Services\SmsDispatcher;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    public function __construct(protected SmsDispatcher $dispatcher)
    {
    }

    /**
     * POST /api/v1/sms/send
     *
     * Body:
     * {
     *   "vendor": "gennet" | "automas",
     *   "sender_id": "880...",                 // resolved by the caller (masking/non-masking sid)
     *   "recipients": [
     *     { "phone": "017XXXXXXXX", "message": "...", "ref": "optional-caller-id" }
     *   ]
     * }
     *
     * This service does NOT check or deduct balance and does NOT know what an
     * "institute" is — that logic stays entirely with the caller. It just sends
     * and reports back what happened, per recipient, so the caller can deduct
     * exactly the segments actually used.
     */
    public function send(SendSmsRequest $request)
    {
        $data = $request->validated();
        $clientName = $request->attributes->get('client_name');

        $results = $this->dispatcher->dispatch($data['vendor'], $data['sender_id'], $data['recipients']);

        Log::channel('sms_log')->info('SMS batch dispatched', [
            'client'  => $clientName,
            'vendor'  => $data['vendor'],
            'count'   => count($results),
            'success' => count(array_filter($results, fn ($r) => $r['success'])),
        ]);

        return response()->json([
            'status'  => 'success',
            'vendor'  => $data['vendor'],
            'results' => $results,
        ]);
    }
}
