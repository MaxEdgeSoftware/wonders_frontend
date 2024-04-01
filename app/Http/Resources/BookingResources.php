<?php

namespace App\Http\Resources;

use App\Models\BookingShift;
use App\Models\Clinic;
use App\Models\ClinicTreatment;
use App\Models\Shift;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResources extends JsonResource
{
    public function treatment($treatment, $clinic){
        $t = ClinicTreatment::where("clinic_id", $clinic)->where("treatment_id", $treatment)->select("treatment_id","price")->first();
        $tt = Treatment::find($t->treatment_id);
        $t->treatment = $tt->name;
        return $t;
    }
    public function bookedBy($user){
        return User::find($user);
    }
    public function clinic($clinic){
        $clinic = Clinic::with([
            'clinicGallery',
            'facilities',
            'socialReview',
            'patientType',
            'rating',
            'review',
            'paymentOptions',
            'days',
            'treatments',
            'unavailable',
            'specializations',
            'insurance_cards'
        ])->where('id', $clinic)->first();
        return new ShowClinicResource($clinic);
    }
    public function shifts(){
        $shifts = BookingShift::where("booking_id", $this->id)->get("shift_id");
        $output = [];
        foreach ($shifts as $shi) {
            $s = Shift::where("id", $shi->shift_id)->first();
            array_push($output, $s->name);
        }
        return $output;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'treatment' => $this->treatment($this->treatment_id, $this->clinic_id),
            'cancel_reason' => $this->cancel_reason,
            'clinic' => $this->clinic($this->clinic_id),
            'check_in_date' => $this->check_in_date,
            'check_out_date' => $this->check_out_date,
            'status' => strtolower($this->status),
            'language' => $this->language,
            'treatment_days' => json_decode($this->days, true),
            'treatment_shifts' => $this->shifts(),
            'price' => $this->price,
            'patient'=> $this->patient,
            'booking_code'=> $this->booking_code,
            'booking_code'=> $this->booking_code,
            'created_at'=> $this->created_at,
        ];
    }
}
