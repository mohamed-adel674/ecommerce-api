<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            // يجب أن يكون المنتج موجودًا في قاعدة البيانات وكميته صحيحة
            'product_id' => 'required|integer|exists:products,id', 
            'quantity' => 'required|integer|min:1|max:100', // كمية بين 1 و 100
        ];
    }
}
