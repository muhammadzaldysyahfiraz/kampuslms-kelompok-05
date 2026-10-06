<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi (dosen + pemilik mata kuliah) dicek di controller.
        // TODO minggu 7: dipindah ke AssignmentPolicy.
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id'    => ['required', 'integer', 'exists:courses,id'],
            'title'        => ['required', 'string', 'max:255'],
            'instructions' => ['required', 'string'],
            'due_at'       => ['required', 'date'],
            'max_score'    => ['required', 'integer', 'between:1,100'],
            'allow_late'   => ['nullable', 'boolean'],
            'status'       => ['nullable', 'in:draft,published'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.exists'  => 'Mata kuliah tidak ditemukan.',
            'max_score.between' => 'Nilai maksimum harus antara 1 sampai 100.',
            'due_at.date'       => 'Tenggat harus berupa tanggal yang valid.',
        ];
    }
}