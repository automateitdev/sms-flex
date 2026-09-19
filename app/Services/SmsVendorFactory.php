<?php

namespace App\Services;

use App\Contracts\SmsVendorContract;
use App\Services\AutomasVendor;
use App\Services\SmsVendors\GenNetVendor;

class SmsVendorFactory
{
    /**
     * To add a new vendor: implement SmsVendorContract in its own class,
     * add one line here, add its credentials to config/sms.php.
     */
    public static function make(string $vendorKey): SmsVendorContract
    {
        return match ($vendorKey) {
            'gennet'  => app(GenNetVendor::class),
            'automas' => app(AutomasVendor::class),
            default   => throw new \InvalidArgumentException("Unknown SMS vendor: {$vendorKey}"),
        };
    }
}
