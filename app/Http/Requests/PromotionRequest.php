<?php
// app/Http/Requests/PromotionRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromotionRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('manage-promotions');
    }
    
    public function rules()
    {
        $rules = [
            'promotion_type' => 'required|in:semester,level',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ];
        
        if ($this->has('select_all') && $this->select_all) {
            $rules['student_ids'] = 'sometimes|array';
        }
        
        return $rules;
    }
    
    public function messages()
    {
        return [
            'student_ids.required' => 'Please select at least one student to promote.',
            'promotion_type.required' => 'Please select promotion type.'
        ];
    }
}