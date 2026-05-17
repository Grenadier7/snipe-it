<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Consumable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LendITDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));

        $assets = Asset::with(['model.category', 'defaultLoc', 'location', 'status'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_tag', 'like', "%{$search}%")
                        ->orWhere('serial', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        $accessories = Accessory::with(['category', 'location'])
            ->withCount('checkouts as checkouts_count')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        $consumables = Consumable::with(['category', 'location'])
            ->withCount('users as consumables_users_count')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        return view('lendit.dashboard', [
            'search' => $search,
            'assets' => $assets,
            'accessories' => $accessories,
            'consumables' => $consumables,
        ]);
    }
}
