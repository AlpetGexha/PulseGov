<?php

declare(strict_types=1);

namespace App\Http\Requests\API;

use App\Enum\VoteType;
use App\Http\Requests\FormRequest;

final class FeedbackVoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Anyone can vote, but they must be authenticated
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'feedback_id' => ['required', 'integer', 'exists:feedback,id'],
            'vote' => ['required', 'string', 'in:' . implode(',', array_column(VoteType::cases(), 'value'))],
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
            'feedback_id.required' => 'Please specify which feedback item you are voting on.',
            'feedback_id.exists' => 'The specified feedback item does not exist.',
            'vote.required' => 'Please specify your vote (upvote or downvote).',
            'vote.in' => 'Invalid vote type. Please use upvote or downvote.',
        ];
    }
}
