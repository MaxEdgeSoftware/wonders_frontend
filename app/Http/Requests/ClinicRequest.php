<?php

namespace App\Http\Requests;

use App\Models\Clinic;
use Illuminate\Foundation\Http\FormRequest;

class ClinicRequest extends FormRequest
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
            'name' => 'required|string',
            'about' => 'required|string',
            'facilities' => 'nullable|array',
            'specialization' => 'nullable|array',
            'language' => 'required|string',
            'payment_option' => 'required|array',
            'currency' => 'required|string',
            'pricing' => 'required|array',
            'cancelation' => 'required',
            // 'email' => 'required|string|email|unique:clinics,email',
            // 'tel_no' => 'required|string',
            // 'contact_name' => 'required|string',
            // 'address' => 'required|string',
            // 'country' => 'required|string',
            // 'longitude' => 'nullable|string',
            // 'latitude' => 'string|required_unless:longitude,null',
            // 'display_pic' => 'nullable|image|mimes:jpeg,jpg,png,gif',
            // 'gallery' => 'nullable|array|max:4',
            // 'gallery.*' => 'image|mimes:jpg,jpeg,png,gif',
            // 'facilities' => 'nullable|array',
            // 'patient_type' => 'required|array',
        ];
    }
}
