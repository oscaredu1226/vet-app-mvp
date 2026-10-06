<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArchivoRequest extends FormRequest
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
            'archivo' => [
                'required',
                'file',
                'max:5120', // 5 MB en KB
                'mimes:jpg,jpeg,png,webp,pdf,doc,docx'
            ],
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
            'archivo.required' => 'El archivo es obligatorio',
            'archivo.file' => 'Debe seleccionar un archivo válido',
            'archivo.max' => 'El archivo no puede superar los 5 MB',
            'archivo.mimes' => 'Solo se permiten archivos JPG, JPEG, PNG, WEBP, PDF, DOC y DOCX',
        ];
    }
}
