<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, SoftDeletes, HasFactory, Notifiable;
    static $currencies = ["USD", "EUR", "GBP", "WON", "YEN", "YUAN", "RUPEE"];
    static $notifications = ["promotions", "reminders", "updates"];
    static $user = "user";
    static $admin = "admin";
    static $clinic = "clinic";
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'profile_image',
        'first_name',
        'last_name',
        'dob',
        'phone_no',
        'user_id',
        'reason',
        'verification_token',
        'notifications',
        'user_type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'email_verified_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function Clinic() {
        return Clinic::where("user_id", $this->id)->first();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function review()
    {
        return $this->hasMany(Review::class);
    }
}
