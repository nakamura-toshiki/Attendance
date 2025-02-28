<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_out' => 'after:clock_in',
            'break_start' => 'nullable | after:clock_in',
            'break_end' => 'nullable | before:clock_out',
            'description' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'description.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breaks = $this->input('breaks', []);

            foreach ($breaks as $break) {
                $breakStart = $break['break_start'] ?? null;
                $breakEnd = $break['break_end'] ?? null;

                if (!$breakStart || !$breakEnd) {
                    continue;
                }

                if ($breakStart < $clockIn || $breakEnd > $clockOut) {
                    $validator->errors()->add('break_error', '休憩時間が勤務時間外です');
                    break;
                }
            }
        });
    }
}
