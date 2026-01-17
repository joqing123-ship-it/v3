<?php
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return view('welcome');
});
Route::get("/testing", function(Request $request) {
            broadcast(new \App\Events\PublicMessageSent("This is a test message"));
            return "Event has been sent!";
});

Route::get('/test-email', function () {
    Mail::raw('Resend email test successful', function ($message) {
        $message->to('sorvictsor89@gmail.com')
                ->subject('Test Email via Resend');
    });

    return 'Email sent!';  
});

