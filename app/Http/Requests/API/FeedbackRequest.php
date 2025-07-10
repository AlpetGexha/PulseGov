<?php

declare(strict_types=1);

namespace App\Http\Requests\API;

use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackStatus;
use App\Enum\FeedbackType;
use App\Enum\UrgencyLevel;
use App\Http\Requests\FormRequest;

final class FeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Anyone can submit feedback, but they must be authenticated
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => $this->stringRule(255),
            'body' => ['required', 'string', 'min:10'],
            'location' => $this->stringRule(255, true),
            'service' => $this->stringRule(255, true),
        ];

        // Only allow these fields if it's an update or from an admin
        if ($this->isMethod('PUT') || $this->isMethod('PATCH') || auth()->user()?->role === 'admin') {
            $rules = array_merge($rules, [
                'sentiment' => $this->enumRule(FeedbackSentiment::class, true),
                'status' => $this->enumRule(FeedbackStatus::class, true),
                'feedback_type' => $this->enumRule(FeedbackType::class, true),
                'urgency_level' => $this->enumRule(UrgencyLevel::class, true),
                'intent' => $this->stringRule(255, true),
                'topic_cluster' => $this->stringRule(255, true),
                'department_assigned' => $this->stringRule(255, true),
            ]);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for your feedback.',
            'body.required' => 'Please provide details about your feedback.',
            'body.min' => 'Please provide more details about your feedback (at least 10 characters).',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'Feedback title',
            'body' => 'Feedback details',
            'location' => 'Location',
        ];
    }
}
