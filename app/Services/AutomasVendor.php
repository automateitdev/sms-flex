<?php

namespace App\Services;

use App\Contracts\SmsVendorContract;
use App\Support\SmsLengthCalculator;
use Illuminate\Support\Facades\Log;

class AutomasVendor implements SmsVendorContract
{
    protected const STATUS_CODES = [
        0    => 'Success',
        101  => 'Invalid Message Length',
        102  => 'Sender Not Valid',
        103  => 'Authentication Failed',
        104  => 'Invalid User',
        105  => 'Invalid MSISDN',
        106  => 'Incorrect API Key',
        107  => 'User Account Suspended',
        108  => 'IP Address Not Allowed',
        109  => 'API Access Not Allowed',
        110  => 'Do Not Disturb (DND)',
        111  => 'Spam Word Detected in Message',
        1000 => 'Insufficient Balance',
        2300 => 'Destination Route Issue',
        2400 => 'Destination Route Not Permitted',
        3300 => 'System Error',
        2000 => 'Destination Provider Unavailable',
        3000 => 'Destination Provider Unavailable',
        4000 => 'Destination Provider Unavailable',
    ];

    public function key(): string
    {
        return 'automas';
    }

    public function send(string $senderId, string $phone, string $message): array
    {
        $url = rtrim(config('sms.vendors.automas.base_url'), '/') . '/smsapiv3';

        $params = [
            'apikey'  => config('sms.vendors.automas.api_key'),
            'sender'  => $senderId,
            'msisdn'  => $phone,
            'smstext' => $message,
        ];

        if (SmsLengthCalculator::isUnicode($message)) {
            $params['smsformat'] = 8;
        }
        if (SmsLengthCalculator::segments($message) > 1) {
            $params['type'] = 'long';
        }

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, ['form_params' => $params]);

            $responseData = json_decode($response->getBody(), true);
            $entry = $responseData['response'][0] ?? [];

            $statusCode = $entry['status'] ?? null;
            $isSuccess = $statusCode === 0;

            return [
                'success'           => $isSuccess,
                'reference_id'      => isset($entry['id']) ? (string) $entry['id'] : null,
                'vendor_message_id' => null,
                'message'           => self::STATUS_CODES[$statusCode] ?? "Unknown status code: {$statusCode}",
            ];
        } catch (\Exception $e) {
            Log::channel('sms_log')->error("Automas SMS request failed for $phone: " . $e->getMessage());

            return [
                'success'           => false,
                'reference_id'      => null,
                'vendor_message_id' => null,
                'message'           => $e->getMessage(),
            ];
        }
    }
}
