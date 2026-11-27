<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\AvailableSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'regex:/^[a-z0-9][a-z0-9-]{1,38}[a-z0-9]$/', new AvailableSlug, 'unique:merchants,slug'],
            'governorate' => ['required', Rule::in(config('egypt.governorates'))],
            'business_type' => ['required', Rule::in(config('egypt.business_types'))],
            'phone' => ['required', 'regex:/^01[0125][0-9]{8}$/'],
            'whatsapp' => ['nullable', 'regex:/^01[0125][0-9]{8}$/'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'اكتب رقم موبايل مصري صحيح (11 رقم).',
            'whatsapp.regex' => 'اكتب رقم موبايل مصري صحيح (11 رقم).',
            'slug.regex' => 'الرابط حروف إنجليزي صغيرة وأرقام وشرطة بس، من 3 لـ 40 حرف.',
            'slug.unique' => 'الرابط ده مستخدم، اختار واحد تاني.',
        ];
    }
}
