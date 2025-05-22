<?php

namespace App\Http\Requests\API;

use App\Http\Requests\FormRequest;

class FeedbackCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Anyone can submit a comment, but they must be authenticated
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
            'parent_id' => ['nullable', 'integer', 'exists:feedback_comments,id'],
            'content' => ['required', 'string', 'min:2', 'max:1000'],
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
            'content.required' => 'Comment content cannot be empty.',
            'content.min' => 'Comment content must be at least 2 characters.',
            'content.max' => 'Comment content cannot exceed 1000 characters.',
        ];
    }
}
