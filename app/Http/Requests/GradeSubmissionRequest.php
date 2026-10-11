<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $submission = $this->route('submission');
        return $submission && ($this->user()?->can('create', [\App\Models\Grade::class, $submission]) ?? false);
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
