<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AppTopCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ip = $this->ip();
        $key = "rate_limit_{$ip}";

        $requests = Cache::get($key, 0);

        if ($requests >= 5) {
            throw new HttpResponseException(
                response()->json([
                    'status_code' => 429,
                    'message' => 'Too Many Requests',
                    'error' => 'Rate limit exceeded. Maximum 5 requests per minute allowed.'
                ], 429)
            );
        }

        Cache::put($key, $requests + 1, 60);
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) {
                    $date = Carbon::parse($value);
                    $thirtyDaysAgo = Carbon::now()->subDays(30);

                    if ($date->isBefore($thirtyDaysAgo)) {
                        $fail('The date must be within the last 30 days.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Date parameter is required',
            'date.date_format' => 'Date must be in YYYY-MM-DD format',
        ];
    }

    public function getDate(): string
    {
        return $this->get('date');
    }

    protected function failedValidation(Validator $validator)
    {

        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => collect($validator->errors())->map(function ($errors, $field) {
                return [
                    'field' => $field,
                    'message' => $errors[0]
                ];
            })->values()->all()
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
