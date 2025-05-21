<?php

namespace App\Http\Requests;

use App\Enum\FeedbackStatus;

class FeedbackStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'feedback_id' => $this->integerRule(),
            'status' => $this->enumRule(FeedbackStatus::class),
            'comment' => ['required', 'string', 'min:3'],
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
            'feedback_id' => 'Feedback',
            'status' => 'Status',
            'comment' => 'Comment',
        ];
    }
}
