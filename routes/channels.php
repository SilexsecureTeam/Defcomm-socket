<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) decryptHelper($id);
});

Broadcast::channel('myPrivateChannel.user.{id}', function ($user, $id) {
    return (int) $user->id === (int) decryptHelper($id);
});

Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    // Allow only the two involved users
    return (int) $user->id === (int) decryptHelper($receiverId) ?? false;
});

Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    return $user->companyGroupUser()->where('group_id', decryptHelper($groupId))->exists();
});

Broadcast::channel('walkie.{walkieId}', function ($user, $walkieId) {
    return $user->wailkieTalkieSubscriber()->where('channel_id', decryptHelper($walkieId))->exists();
});
