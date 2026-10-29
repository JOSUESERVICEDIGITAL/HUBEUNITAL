<?php

namespace App\Http\Requests;
namespace App\Http\Requests\SocialMedia;


use Illuminate\Foundation\Http\FormRequest;

class StoreSocialMediaRequest extends FormRequest
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
            'name' => 'required|string',
            'lien' => 'required|string',
            'icon' => 'nullable|string|max:255',
             'label' => 'required|string|max:50',
            'count' => 'required|integer|min:0',
            'bg_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
