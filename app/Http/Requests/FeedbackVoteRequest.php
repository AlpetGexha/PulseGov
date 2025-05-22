<?php

namespace App\Http\Requests;

use App\Enum\VoteType;

class FeedbackVoteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'feedback_id' => ['required', 'exists:feedback,id'],
            'vote' => $this->enumRule(VoteType::class),
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
            'vote' => 'Vote',
        ];
    }
}
