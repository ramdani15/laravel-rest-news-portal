<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ReactionType;
use Illuminate\Foundation\Http\FormRequest;

class ReactionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:'.ReactionType::LIKE->value.','.ReactionType::DISLIKE->value],
        ];
    }
}
