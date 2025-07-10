<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\VoteType;
use Illuminate\Foundation\Http\FormRequest;

final class FeedbackVoteRequest extends FormRequest
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
            'vote' => ['required', 'string', 'in:' . implode(',', array_column(VoteType::cases(), 'value'))],
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
