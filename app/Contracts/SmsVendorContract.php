<?php

namespace App\Contracts;

interface SmsVendorContract
{
    /**
     * Send a single SMS through this vendor.
     *
     * @return array{
     *     success: bool,
     *     reference_id: ?string,
     *     vendor_message_id: ?string,
     *     message: string
     * }
     */
    public function send(string $senderId, string $phone, string $message): array;

    /** Slug the caller passes in the request payload to select this vendor. */
    public function key(): string;
}
