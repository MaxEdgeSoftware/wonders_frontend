<?php

namespace App\Http\Resources;

use App\Models\CancellationPolicy;
use App\Models\ClinicDay;
use App\Models\Currency;
use App\Models\Day;
use App\Models\DayTreatment;
use App\Models\InsuranceCard;
use App\Models\Rating;
use App\Models\Shift;
use App\Models\ShiftTreatment;
use App\Models\Specialization;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowClinicResource extends JsonResource
{

    public function days($rows)
    {
        $result = [];
        foreach ($rows as $day) {
            $row = [];
            $row = [
                'id' => $day->id,
                'day' => $day->name,
                'opening_hours' =>  $day->pivot->opening_hours,
                'closing_hours' =>  $day->pivot->closing_hours,
                'status' =>  $day->pivot->status,
            ];
            array_push($result, $row);
        }
        return $result;
    }

    public function shifts($records)
    {
        $output = [];
        foreach ($this->treatments as $treatment) {
           $treatment_id = $treatment->pivot->treatment_id;
           $clinic_id = $treatment->pivot->clinic_id;
           $days = DayTreatment::select('day_id')->where('treatment_id', $treatment_id)->where('clinic_id', $clinic_id)->get();
           $days = $days->toArray();
           $temp_day = [];
           foreach ($days as $value) {
                $day = Day::select(['id', 'name as day'])->where('id', $value)->first();
                array_push($temp_day, $day);
           }

           $shifts = ShiftTreatment::select('shift_id')->where('treatment_id', $treatment_id)->where('clinic_id', $clinic_id)->get();
           $shifts = $shifts->toArray();
           $temp_shift = [];
           foreach ($shifts as $item) {
                $shift = Shift::select(['id', 'name as shift'])->where('id', $item)->first();
                array_push($temp_shift, $shift);
           }
           
           $row = [];
           $row = [
            'id' => $treatment->id,
            'name' => $treatment->name,
            'price' => $treatment->pivot->price,
            'price_session' => $treatment->pivot->price_session,
            'days' => $temp_day,
            'shifts' => $temp_shift
           ];
           array_push($output, $row);
        }

        return $output;
        // $output = [];
        // foreach ($records as $treatment) {
        //    $treatment_id = $treatment->pivot->treatment_id;
        //    $clinic_id = $treatment->pivot->clinic_id;
        //    $days = DayTreatment::select('day')->where('treatment_id', $treatment_id)->where('clinic_id', $clinic_id)->get();
        //    $days = $days->toArray();
        //    $temp_day = [];
        //    foreach ($days as $value) {
        //         array_push($temp_day, $value['day']);
        //    }

        //    $shifts = ShiftTreatment::select('shift')->where('treatment_id', $treatment_id)->where('clinic_id', $clinic_id)->get();
        //    $shifts = $shifts->toArray();
        //    $temp_shift = [];
        //    foreach ($shifts as $item) {
        //         array_push($temp_shift, $item['shift']);
        //    }
           
        //    $row = [];
        //    $row = [
        //     'id' => $treatment->id,
        //     'name' => $treatment->name,
        //     'price' => $treatment->pivot->price,
        //     'price_session' => $treatment->pivot->price_session,
        //     'days' => $temp_day,
        //     'shifts' => $temp_shift
        //    ];
        //    array_push($output, $row);
        // }

        // return $output;
    }
    public function specialGallery() {
        return [
            ['image' => "https://res.cloudinary.com/djoo9wwvo/image/upload/v1709734890/hsgejncetw27ygfxexnk.jpg"],
            ['image' => "https://res.cloudinary.com/djoo9wwvo/image/upload/v1709734890/hsgejncetw27ygfxexnk.jpg"],
            ['image' => "https://res.cloudinary.com/djoo9wwvo/image/upload/v1709734890/hsgejncetw27ygfxexnk.jpg"],
            ['image' => "https://res.cloudinary.com/djoo9wwvo/image/upload/v1709734890/hsgejncetw27ygfxexnk.jpg"],
        ];
    }
    public function InsuranceCards($records){
        $output = [];
        foreach ($records as $insurance) {
            $insuranc = InsuranceCard::where("id", $insurance->insurance_card)->select("insurance_card", "id")->first();
            array_push($output, $insuranc);
        }
        return $output;
    }
    public function Specialization($records){
        $output = [];
        foreach ($records as $specialization) {
            $spec = Specialization::where("status", "active")->where("id", $specialization->specialization)->select("name", "id")->first();
            if($spec)array_push($output, $spec);
        }
        return $output;

    }
    public function getCancelation($cancel){
        $output = CancellationPolicy::find($cancel);
        return $output;

    }
    public function clinicRating($id) {
        return Rating::where("clinic_id", $id)->select("rating_total", "rating_count", "rating_average")->first();
    }
    public function getCurrency($curr) {
        return Currency::where("name", $curr)->first();
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'tel_no' => $this->tel_no,
            'country_code' => $this->country_code,
            'about' => $this->about,
            'contact_name' => $this->contact_name,
            'address' => $this->address,
            'country' => $this->country,
            'currency' => $this->getCurrency($this->currency),
            'language' => $this->language,
            'distance' => isset($this->distance) ? $this->distance : null,
            'longitude' => $this->longitude,
            'city' => $this->city,
            'latitude' => $this->latitude,
            'display_pic' => $this->display_pic,
            'facilities' => FacilitiesResource::collection($this->facilities),
            'patient_types' => PatientTypeResource::collection($this->patientType),
            'payment_options' => PaymentOptionResource::collection($this->paymentOptions),
            'gallery' =>$this->clinicGallery->count() == 0 ? $this->specialGallery() : ClinicGalleryResource::collection($this->clinicGallery),
            'days' => $this->days($this->days),
            'unavailabilities' => $this->unavailable, 
            'treatments' => $this->shifts($this->treatments),
            'specializations' => $this->Specialization($this->specializations),
            'insurance_cards'=> $this->InsuranceCards($this->insurance_cards),
            'cancelation'=> $this->getCancelation($this->cancelation),
            'certificates'=> $this->certificates,
            'zip_code'=> $this->zip_code,
            'state'=> $this->state,
            'facebook'=> $this->facebook,
            'instagram'=> $this->instagram,
            'linkedIn'=> $this->linkedIn,
            'whatsapp'=> $this->whatsapp,
            'offer_transportation' => $this->offer_transportation,
            'rating' => $this->clinicRating($this->id),
            'near_by'=> $this->near_by ? json_decode($this->near_by, true) : [],
        ];
    }
}
