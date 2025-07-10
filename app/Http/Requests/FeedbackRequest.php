<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackStatus;
use App\Enum\UrgencyLevel;
use Illuminate\Foundation\Http\FormRequest;

final class FeedbackRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'user_id' => $this->integerRule(),
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
            'location' => ['string', 'nullable'],
            'service' => ['string', 'nullable'],
            'feedback_type' => ['required', 'string', 'in:suggestion,complaint,question,compliment'],
            'is_public' => ['boolean'],
            // 'sentiment' => $this->enumRule(FeedbackSentiment::class, true),
            // 'status' => $this->enumRule(FeedbackStatus::class),
            // 'tracking_code' => $this->stringRule(255),
            // 'urgency_level' => $this->enumRule(UrgencyLevel::class, true),
            // 'intent' => $this->stringRule(255, true),
            // 'topic_cluster' => $this->stringRule(255, true),
            // 'department_assigned' => $this->stringRule(255, true),
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
            'user_id' => 'User',
            'title' => 'Title',
            'body' => 'Details',
            'service' => 'Service',
            'sentiment' => 'Sentiment',
            'status' => 'Status',
            'feedback_type' => 'Feedback Type',
            'tracking_code' => 'Tracking Code',
            'urgency_level' => 'Urgency Level',
            'intent' => 'Intent',
            'topic_cluster' => 'Topic Cluster',
            'department_assigned' => 'Department Assigned',
        ];
    }
}
