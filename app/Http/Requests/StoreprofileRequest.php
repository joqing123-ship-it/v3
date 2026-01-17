<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreprofileRequest extends FormRequest
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
            'name' => 'required',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048|dimensions:max_width=3000,max_height=3000',
            'phone' => 'required',
        ];
    }
    protected function prepareForValidation(){
        if($this->hasFile("profileImage")){
            $this->merge([
                'profile_image' => $this->file("profileImage"),
            ]);
        };
    }

}
