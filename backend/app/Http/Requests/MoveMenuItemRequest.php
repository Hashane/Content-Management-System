<?php

namespace App\Http\Requests;

use App\Models\MenuItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('item'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('menu_items', 'id')->where('menu_id', $this->route('menu')?->id),
            ],
            'position' => ['required', 'integer', 'min:0'],
        ];
    }


    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $parentId = $this->input('parent_id');
            $item = $this->route('item');

            if ($parentId === null || ! $item) {
                return;
            }

            if ((int) $parentId === $item->id) {
                $validator->errors()->add('parent_id', 'A menu item cannot be its own parent.');

                return;
            }

            $ancestorId = $parentId;

            while ($ancestorId !== null) {
                if ((int) $ancestorId === $item->id) {
                    $validator->errors()->add('parent_id', 'A menu item cannot be moved under one of its own descendants.');

                    return;
                }

                $ancestorId = MenuItem::whereKey($ancestorId)->value('parent_id');
            }
        });
    }
}
