<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:todo,done',
            'priority' => 'sometimes|integer|min:1|max:5',
            'search' => 'sometimes|string',
            'sort' => 'sometimes|string',
        ];
    }

    public function filters(): array
    {
        return $this->only(['status', 'priority']);
    }

    public function search(): ?string
    {
        return $this->input('search');
    }

    public function sort(): ?string
    {
        return $this->input('sort');
    }
}
