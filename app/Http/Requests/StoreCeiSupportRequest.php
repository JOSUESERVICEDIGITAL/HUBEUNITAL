<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCeiSupportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // En-tête
            'titre' => 'nullable|string|max:255',
            'slogan' => 'nullable|string',

            // Image
            'image_path' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // Pourquoi soutenir le CEI
            'introduction' => 'nullable|string',
            'raison_1' => 'nullable|string',
            'raison_2' => 'nullable|string',
            'raison_3' => 'nullable|string',
            'raison_4' => 'nullable|string',

            // Actions
            'action1_icon' => 'nullable|string|max:255',
            'action1_title' => 'nullable|string|max:255',
            'action1_text' => 'nullable|string',
            'action1_url' => 'nullable|url',

            'action2_icon' => 'nullable|string|max:255',
            'action2_title' => 'nullable|string|max:255',
            'action2_text' => 'nullable|string',
            'action2_url' => 'nullable|url',

            'action3_icon' => 'nullable|string|max:255',
            'action3_title' => 'nullable|string|max:255',
            'action3_text' => 'nullable|string',
            'action3_url' => 'nullable|url',

            // Réseaux sociaux
            'social_name' => 'nullable|string|max:255',
            'social_url' => 'nullable|url|max:255',
            'social_icon' => 'nullable|string|max:255',
        ];
    }
}
