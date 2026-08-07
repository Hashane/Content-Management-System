<?php

namespace App\Http\Requests;

use App\Enums\MenuItemType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateMenuItemRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:255'],
            'item_type' => ['required', new Enum(MenuItemType::class)],
            'page_id' => [
                'nullable',
                'integer',
                'required_if:item_type,page',
                'prohibited_if:item_type,group',
                'exists:pages,id',
            ],
        ];
    }
}
