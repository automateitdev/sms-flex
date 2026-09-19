<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendSmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Caller-level auth already happened in AuthenticateSmsClient middleware.
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor'               => 'required|string|in:gennet,automas',
            'sender_id'            => 'required|string',
            'recipients'           => 'required|array|min:1|max:1000',
            'recipients.*.phone'   => 'required|string',
            'recipients.*.message' => 'required|string',
            // Optional passthrough — the microservice doesn't store or act on this,
            // it's just echoed back in each result so the caller can match it to
            // its own records (e.g. category, internal recipient id).
            'recipients.*.ref'     => 'nullable|string',
        ];
    }
}
