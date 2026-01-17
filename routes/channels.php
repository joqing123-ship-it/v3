<?php
use Illuminate\Support\Facades\Broadcast;



Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id; // only user with that ID can listen
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id; // only user with that ID can listen
});
