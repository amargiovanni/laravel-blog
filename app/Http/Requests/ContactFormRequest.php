<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactFormRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'website' => ['nullable', 'string'], // Honeypot field - checked in controller
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('Please enter your name.'),
            'name.max' => __('Name is too long.'),
            'email.required' => __('Please enter your email address.'),
            'email.email' => __('Please enter a valid email address.'),
            'email.max' => __('Email address is too long.'),
            'subject.required' => __('Please enter a subject.'),
            'subject.max' => __('Subject is too long.'),
            'message.required' => __('Please enter your message.'),
            'message.max' => __('Message is too long. Maximum 5000 characters allowed.'),
        ];
    }

    /**
     * Check if the honeypot field was filled (indicating a bot).
     */
    public function isSpam(): bool
    {
        return ! empty($this->input('website'));
    }
}
