<?php

namespace App\Services;

use App\Support\SmsLengthCalculator;


class SmsDispatcher
{
    /**
     * @param  array<int, array{phone: string, message: string, ref?: string}>  $recipients
     * @return array<int, array{
     *     ref: ?string,
     *     phone: string,
     *     success: bool,
     *     segments: int,
     *     reference_id: ?string,
     *     vendor_message_id: ?string,
     *     message: string
     * }>
     */
    public function dispatch(string $vendorKey, string $senderId, array $recipients): array
    {
        $vendor = SmsVendorFactory::make($vendorKey);
        $results = [];

        foreach ($recipients as $recipient) {
            $segments = SmsLengthCalculator::segments($recipient['message']);
            $result = $vendor->send($senderId, $recipient['phone'], $recipient['message']);

            $results[] = [
                'ref'               => $recipient['ref'] ?? null,
                'phone'             => $recipient['phone'],
                'success'           => $result['success'],
                // How many balance units the caller should deduct for THIS recipient
                // if (and only if) success is true.
                'segments'          => $segments,
                'reference_id'      => $result['reference_id'],
                'vendor_message_id' => $result['vendor_message_id'],
                'message'           => $result['message'],
            ];
        }

        return $results;
    }
}
