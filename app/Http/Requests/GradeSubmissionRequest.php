<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user       = $this->user();
        $submission = $this->route('submission');

        // TODO minggu 7: dipindah ke SubmissionPolicy
        return $user->role === 'dosen'
            && $submission->assignment->course->lecturer_id === $user->id;
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'Anda tidak memiliki akses ke sumber daya ini.');
    }

    public function rules(): array
    {
        $maxScore = $this->route('submission')->assignment->max_score;

        return [
            'score'    => ['required', 'numeric', 'min:0', "max:{$maxScore}"],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        $maxScore = $this->route('submission')->assignment->max_score;

        return [
            'score.max' => "Nilai tidak boleh melebihi nilai maksimum tugas ({$maxScore}).",
            'score.min' => 'Nilai tidak boleh negatif.',
        ];
    }
}