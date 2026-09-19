<?php

namespace App\Services\SmsVendors;

use App\Contracts\SmsVendorContract;
use Illuminate\Support\Facades\Log;

class GenNetVendor implements SmsVendorContract
{
    public function key(): string
    {
        return 'gennet';
    }

    public function send(string $senderId, string $phone, string $message): array
    {
        $url = rtrim(config('sms.vendors.gennet.domain'), '/') . '/api/v3/send-sms';
        $csmsId = $this->generateCsmsId();

        $params = [
            'api_token' => config('sms.vendors.gennet.api_token'),
            'sid'       => $senderId,
            'msisdn'    => $phone,
            'sms'       => $message,
            'csms_id'   => $csmsId,
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, ['json' => $params]);

            $responseData = json_decode($response->getBody(), true);
            $smsInfo = $responseData['smsinfo'][0] ?? [];

            $isSuccess = ($responseData['status'] ?? null) === 'SUCCESS'
                && ($smsInfo['sms_status'] ?? null) === 'SUCCESS';

            return [
                'success'           => $isSuccess,
                'reference_id'      => $smsInfo['reference_id'] ?? null,
                'vendor_message_id' => $smsInfo['csms_id'] ?? $csmsId,
                'message'           => $isSuccess
                    ? 'SUCCESS'
                    : ($smsInfo['status_message'] ?? $responseData['error_message'] ?? 'Unknown Error'),
            ];
        } catch (\Exception $e) {
            Log::channel('sms_log')->error("GenNet SMS request failed for $phone: " . $e->getMessage());

            return [
                'success'           => false,
                'reference_id'      => null,
                'vendor_message_id' => $csmsId,
                'message'           => $e->getMessage(),
            ];
        }
    }

    protected function generateCsmsId(): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $csmsId = '';

        for ($i = 0; $i < 20; $i++) {
            $csmsId .= $letters[random_int(0, strlen($letters) - 1)];
        }

        return $csmsId;
    }
}
