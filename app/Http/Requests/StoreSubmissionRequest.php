<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user       = $this->user();
        $assignment = $this->route('assignment');

        // TODO minggu 7: dipindah ke SubmissionPolicy
        return $user->role === 'mahasiswa'
            && $assignment->status === 'published'
            && $assignment->course->isAccessibleBy($user);
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'Anda tidak memiliki akses ke sumber daya ini.');
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,zip', 'max:10240'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Berkas wajib diunggah.',
            'file.mimes'    => 'Berkas harus berformat PDF, DOC, DOCX, atau ZIP.',
            'file.max'      => 'Ukuran berkas maksimal 10 MB.',
        ];
    }
}