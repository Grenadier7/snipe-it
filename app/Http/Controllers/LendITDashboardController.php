<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LendITDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $categoryId = $request->integer('category_id') ?: null;
        $locationId = $request->integer('location_id') ?: null;
        $availability = in_array($request->input('availability'), ['available', 'unavailable'], true)
            ? $request->input('availability')
            : null;

        $assets = Asset::with(['model.category', 'defaultLoc', 'location', 'status'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_tag', 'like', "%{$search}%")
                        ->orWhere('serial', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->whereHas('model', function ($query) use ($categoryId) {
                    $query->where('category_id', $categoryId);
                });
            })
            ->when($locationId, function ($query) use ($locationId) {
                $query->where(function ($query) use ($locationId) {
                    $query->where('location_id', $locationId)
                        ->orWhere('rtd_location_id', $locationId);
                });
            })
            ->when($availability === 'available', function ($query) {
                $query->whereNull('assigned_to')
                    ->whereHas('status', function ($query) {
                        $query->where('deployable', 1)
                            ->where('archived', 0);
                    });
            })
            ->when($availability === 'unavailable', function ($query) {
                $query->where(function ($query) {
                    $query->whereNotNull('assigned_to')
                        ->orWhereDoesntHave('status')
                        ->orWhereHas('status', function ($query) {
                            $query->where('deployable', 0)
                                ->orWhere('archived', 1);
                        });
                });
            })
            ->orderBy('name')
            ->limit(25)
            ->get();

        $accessories = Accessory::with(['category', 'location'])
            ->withCount('checkouts as checkouts_count')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($locationId, function ($query) use ($locationId) {
                $query->where('location_id', $locationId);
            })
            ->when($availability === 'available', function ($query) {
                $query->havingRaw('(qty - checkouts_count) > 0');
            })
            ->when($availability === 'unavailable', function ($query) {
                $query->havingRaw('(qty - checkouts_count) <= 0');
            })
            ->orderBy('name')
            ->limit(25)
            ->get();

        $consumables = Consumable::with(['category', 'location'])
            ->withCount('users as consumables_users_count')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($locationId, function ($query) use ($locationId) {
                $query->where('location_id', $locationId);
            })
            ->when($availability === 'available', function ($query) {
                $query->havingRaw('(qty - consumables_users_count) > 0');
            })
            ->when($availability === 'unavailable', function ($query) {
                $query->havingRaw('(qty - consumables_users_count) <= 0');
            })
            ->orderBy('name')
            ->limit(25)
            ->get();

        return view('lendit.dashboard', [
            'search' => $search,
            'categoryId' => $categoryId,
            'locationId' => $locationId,
            'availability' => $availability,
            'categories' => Category::whereIn('category_type', ['asset', 'accessory', 'consumable'])
                ->orderBy('name')
                ->get(),
            'locations' => Location::orderBy('name')->get(),
            'assets' => $assets,
            'accessories' => $accessories,
            'consumables' => $consumables,
        ]);
    }
}
