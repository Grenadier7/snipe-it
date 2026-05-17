<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy extends CheckoutablePermissionsPolicy
{
    protected function columnName()
    {
        return 'assets';
    }

    public function viewRequestable(User $user, ?Asset $asset = null)
    {
        return $user->hasAccess('assets.view.requestable');
    }

    public function audit(User $user, ?Asset $asset = null)
    {
        return $user->hasAccess('assets.audit');
    }

    public function files(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }
    public function checkoutSelf(User $user, $item = null): bool
    {
        // Erlaubt, wenn der User generell auschecken darf, ODER die spezifische self-Berechtigung hat
        return $user->hasAccess('assets.checkout') || $user->hasAccess('assets.checkout_self');
    }

    public function checkinSelf(User $user, $item = null): bool
    {
        // Wichtig: Hier in der Policy nur prüfen, ob er *generell* das Recht dazu hat.
        // Die Prüfung, ob das Asset *wirklich ihm* gehört, machst du im Controller.
        return $user->hasAccess('assets.checkin') || $user->hasAccess('assets.checkin_self');
    }
}
