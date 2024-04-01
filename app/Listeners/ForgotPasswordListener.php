<?php

namespace App\Listeners;

use App\Events\ForgotPasswordEvent;
use App\Mail\PasswordResetMail;
use App\Models\PasswordReset;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class ForgotPasswordListener //implements ShouldQueue
{
    // use InteractsWithQueue;

    // public $tries = 5;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    // public function retryUntil()
    // {
    //     return now()->addMinutes(10);
    // }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ForgotPasswordEvent $event)
    {
        return $event;
        // Mail::to($event->email)->send(new PasswordResetMail());

        $data = [
            //'email' => $event->email,
            'token' => Str::random(40)
        ];

        try {
            Mail::to($event->email)->send(new PasswordResetMail($data));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }

        try {
            PasswordReset::updateOrCreate(
                ['email' => $event->email],
                ['token' => $data['token']],
                ['created_at' => Carbon::now()->format('Y-m-d H:i:s')]
            );
            // return response()->json(['message' => 'Check your mail for the password reset link']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }
}
