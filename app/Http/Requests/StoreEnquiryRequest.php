<?php

namespace App\Http\Requests;

use App\Models\ContentEntry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnquiryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'submission_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'service' => ['required', Rule::in([...ContentEntry::ofKind('service')->published()->pluck('slug')->all(), 'not-sure-yet'])],
            'message' => ['required', 'string', 'min:15', 'max:5000'],
            'website' => ['nullable', 'string', 'max:0'],
            'consent' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return ['message.min' => 'Tell us a little more about your project (at least 15 characters).', 'consent.accepted' => 'Please confirm we can use these details to respond to your enquiry.'];
    }
}
