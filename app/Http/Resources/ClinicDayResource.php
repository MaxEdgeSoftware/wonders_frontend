<?php

namespace App\Http\Resources;

use App\Models\Day;
use App\Models\DayShiftSlot;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicDayResource extends JsonResource
{
    public function Day($day) {
        $d = Day::where("id", $day)->first();
        return $d->name;
    }
    public function Shifts ($shiftArray) {
        foreach ($shiftArray as $shift) {
            $slots = DayShiftSlot::with("Treatment")->where("clinic_day_shifts_id", $shift->id)->get();
            $s = Shift::where("id", $shift->shift_id)->first();
            $shift->shift = $s ? $s->name : "";
            $shift->slot = DayShiftSlotResource::collection($slots);
        }
        return $shiftArray;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'clinic_day_id' => $this->id,
            'opening_hours' => $this->opening_hours,
            'closing_hours' => $this->closing_hours,
            'day' => $this->Day($this->day_id),
            "shifts" => $this->Shifts($this->DayShifts)
        ];
    }
}
