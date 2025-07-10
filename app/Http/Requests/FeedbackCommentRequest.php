<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class FeedbackCommentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'feedback_id' => ['required', 'exists:feedback,id'],
            'parent_id' => ['nullable', 'exists:feedback_comments,id'],
            'content' => ['required', 'string', 'min:2', 'max:5000'],
            // 'is_pinned' => ['boolean'],
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
            'parent_id' => 'Parent Comment',
            'content' => 'Comment',
            'is_pinned' => 'Pinned Status',
        ];
    }
}
