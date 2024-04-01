<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

class VerifyEmailController extends Controller
{
    public function verify($id, $hash)
    {
        try {
            $user = User::find($id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }

        if (!isset($user)) {
            return response()->json(['message' => 'No user record']);
        }

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Credentials mismatch']);
        }

        if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();

                event(new Verified($user));
        }

        return view('verified.email');
    }
}
