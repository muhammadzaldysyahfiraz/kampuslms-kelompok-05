<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user       = $this->user();
        $assignment = $this->route('assignment');

        // TODO minggu 7: dipindah ke AssignmentPolicy
        return $user->role === 'dosen'
            && $assignment->course->lecturer_id === $user->id;
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'Anda tidak memiliki akses ke sumber daya ini.');
    }

    public function rules(): array
    {
        return [
            'title'        => ['sometimes', 'required', 'string', 'max:255'],
            'instructions' => ['sometimes', 'required', 'string'],
            'due_at'       => ['sometimes', 'required', 'date'],
            'max_score'    => ['sometimes', 'required', 'integer', 'between:1,100'],
            'allow_late'   => ['sometimes', 'boolean'],
            'status'       => ['sometimes', 'in:draft,published'],
        ];
    }

    public function messages(): array
    {
        return [
            'max_score.between' => 'Nilai maksimum harus antara 1 sampai 100.',
            'due_at.date'       => 'Tenggat harus berupa tanggal yang valid.',
        ];
    }
}