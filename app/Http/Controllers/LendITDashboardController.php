<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\LendITTag;
use App\Models\Location;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LendITDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $tagId = $request->integer('tag_id') ?: null;
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
                        ->orWhere('serial', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($tagId, function ($query) use ($tagId) {
                $this->whereHasLendITTag($query, Asset::class, 'assets.id', $tagId);
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
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhere('model_number', 'like', "%{$search}%");
                });
            })
            ->when($tagId, function ($query) use ($tagId) {
                $this->whereHasLendITTag($query, Accessory::class, 'accessories.id', $tagId);
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
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhere('item_no', 'like', "%{$search}%")
                        ->orWhere('model_number', 'like', "%{$search}%");
                });
            })
            ->when($tagId, function ($query) use ($tagId) {
                $this->whereHasLendITTag($query, Consumable::class, 'consumables.id', $tagId);
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

        $itemTags = collect()
            ->merge($this->tagsForItems($assets, Asset::class))
            ->merge($this->tagsForItems($accessories, Accessory::class))
            ->merge($this->tagsForItems($consumables, Consumable::class));

        return view('lendit.dashboard', [
            'search' => $search,
            'tagId' => $tagId,
            'categoryId' => $categoryId,
            'locationId' => $locationId,
            'availability' => $availability,
            'tags' => LendITTag::orderBy('name')->get(),
            'itemTags' => $itemTags,
            'categories' => Category::whereIn('category_type', ['asset', 'accessory', 'consumable'])
                ->orderBy('name')
                ->get(),
            'locations' => Location::orderBy('name')->get(),
            'assets' => $assets,
            'accessories' => $accessories,
            'consumables' => $consumables,
        ]);
    }

    public function updateItemTags(Request $request): RedirectResponse
    {
        $this->authorize('reports.view');

        $validated = $request->validate([
            'item_type' => 'required|in:asset,accessory,consumable',
            'item_id' => 'required|integer|min:1',
            'tags' => 'nullable|string|max:500',
        ]);

        $typeMap = [
            'asset' => Asset::class,
            'accessory' => Accessory::class,
            'consumable' => Consumable::class,
        ];

        $itemType = $typeMap[$validated['item_type']];
        $itemType::findOrFail($validated['item_id']);

        $tagIds = collect(preg_split('/[,;]+/', (string) ($validated['tags'] ?? '')))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->map(function ($tag) {
                $name = ltrim($tag, '#');
                $slug = Str::slug($name);

                if ($slug === '') {
                    return null;
                }

                return LendITTag::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name]
                )->id;
            })
            ->filter()
            ->unique()
            ->values();

        DB::table('lendit_taggables')
            ->where('item_type', $itemType)
            ->where('item_id', $validated['item_id'])
            ->delete();

        if ($tagIds->isNotEmpty()) {
            DB::table('lendit_taggables')->insert(
                $tagIds->map(fn ($tagId) => [
                    'tag_id' => $tagId,
                    'item_type' => $itemType,
                    'item_id' => $validated['item_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all()
            );
        }

        return redirect()->back()->with('success', 'LendIT-Tags wurden aktualisiert.');
    }

    public function checkouts(Request $request): View
    {
        $this->authorize('reports.view');

        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status') === 'overdue' ? 'overdue' : 'all';
        $today = Carbon::today();

        $assets = Asset::with(['model.category', 'assignedTo'])
            ->where('assigned_type', User::class)
            ->whereNotNull('assigned_to')
            ->when($status === 'overdue', function ($query) use ($today) {
                $query->whereNotNull('expected_checkin')
                    ->whereDate('expected_checkin', '<', $today);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_tag', 'like', "%{$search}%")
                        ->orWhereHasMorph('assignedTo', [User::class], function ($query) use ($search) {
                            $query->where('username', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('display_name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByRaw('expected_checkin is null')
            ->orderBy('expected_checkin')
            ->orderByDesc('last_checkout')
            ->limit(100)
            ->get();

        $accessoryCheckouts = AccessoryCheckout::with(['accessory.category', 'assignedTo'])
            ->where('assigned_type', User::class)
            ->when($status === 'overdue', function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('accessory', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })->orWhereHasMorph('assignedTo', [User::class], function ($query) use ($search) {
                        $query->where('username', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('display_name', 'like', "%{$search}%");
                    });
                });
            })
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('lendit.checkouts', [
            'search' => $search,
            'status' => $status,
            'today' => $today,
            'assets' => $assets,
            'accessoryCheckouts' => $accessoryCheckouts,
        ]);
    }

    public function userHistory(Request $request, ?User $user = null): View
    {
        $canViewAllUsers = $request->user()->can('reports.view');

        $userSearch = trim((string) $request->input('user_search', ''));
        $historySearch = trim((string) $request->input('history_search', ''));
        $selectedUserId = $canViewAllUsers
            ? ($user?->id ?: ($request->integer('user_id') ?: null))
            : $request->user()->id;

        if ($user && ! $canViewAllUsers && $user->id !== $request->user()->id) {
            abort(403);
        }

        $users = collect();

        if ($canViewAllUsers) {
            $users = User::select('id', 'first_name', 'last_name', 'display_name', 'username')
                ->where('activated', 1)
                ->when($userSearch !== '', function ($query) use ($userSearch) {
                    $query->where(function ($query) use ($userSearch) {
                        $query->where('username', 'like', "%{$userSearch}%")
                            ->orWhere('first_name', 'like', "%{$userSearch}%")
                            ->orWhere('last_name', 'like', "%{$userSearch}%")
                            ->orWhere('display_name', 'like', "%{$userSearch}%")
                            ->orWhere('email', 'like', "%{$userSearch}%");
                    });
                })
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->limit(250)
                ->get();
        }

        $selectedUser = $canViewAllUsers
            ? ($user ?: ($selectedUserId ? User::withTrashed()->find($selectedUserId) : null))
            : $request->user();

        $assets = collect();
        $accessoryCheckouts = collect();
        $history = collect();

        if ($selectedUser) {
            $assets = Asset::with(['model.category'])
                ->where('assigned_type', User::class)
                ->where('assigned_to', $selectedUser->id)
                ->orderByDesc('last_checkout')
                ->get();

            $accessoryCheckouts = AccessoryCheckout::with(['accessory.category'])
                ->where('assigned_type', User::class)
                ->where('assigned_to', $selectedUser->id)
                ->orderByDesc('created_at')
                ->get();

            $history = Actionlog::with(['item', 'adminuser'])
                ->where('target_type', User::class)
                ->where('target_id', $selectedUser->id)
                ->whereIn('action_type', ['checkout', 'checkin from', 'force checkin'])
                ->when($historySearch !== '', function ($query) use ($historySearch) {
                    $query->where(function ($query) use ($historySearch) {
                        $query->where('action_type', 'like', "%{$historySearch}%")
                            ->orWhere('note', 'like', "%{$historySearch}%")
                            ->orWhereHasMorph('item', [Asset::class, Accessory::class, Consumable::class], function ($query, string $type) use ($historySearch) {
                                $query->where('name', 'like', "%{$historySearch}%");

                                if ($type === Asset::class) {
                                    $query->orWhere('asset_tag', 'like', "%{$historySearch}%")
                                        ->orWhere('serial', 'like', "%{$historySearch}%");
                                }
                            });
                    });
                })
                ->orderByDesc('action_date')
                ->orderByDesc('created_at')
                ->limit(100)
                ->get();
        }

        return view('lendit.user-history', [
            'users' => $users,
            'userSearch' => $userSearch,
            'historySearch' => $historySearch,
            'selectedUserId' => $selectedUserId,
            'selectedUser' => $selectedUser,
            'canViewAllUsers' => $canViewAllUsers,
            'assets' => $assets,
            'accessoryCheckouts' => $accessoryCheckouts,
            'history' => $history,
        ]);
    }

    public function statistics(): View
    {
        $this->authorize('reports.view');

        $today = Carbon::today();

        $assetTotal = Asset::count();
        $assetCheckedOut = Asset::where('assigned_type', User::class)
            ->whereNotNull('assigned_to')
            ->count();
        $assetAvailable = Asset::whereNull('assigned_to')
            ->whereHas('status', function ($query) {
                $query->where('deployable', 1)
                    ->where('archived', 0);
            })
            ->count();
        $assetOverdue = Asset::where('assigned_type', User::class)
            ->whereNotNull('assigned_to')
            ->whereNotNull('expected_checkin')
            ->whereDate('expected_checkin', '<', $today)
            ->count();

        $accessoryTotalQty = (int) Accessory::sum('qty');
        $accessoryCheckedOut = AccessoryCheckout::where('assigned_type', User::class)->count();
        $accessoryAvailable = max($accessoryTotalQty - $accessoryCheckedOut, 0);

        $consumableTotalQty = (int) Consumable::sum('qty');
        $consumableIssued = (int) DB::table('consumables_users')->count();
        $consumableAvailable = max($consumableTotalQty - $consumableIssued, 0);

        $topLoanedItems = Actionlog::with('item')
            ->select('item_type', 'item_id', DB::raw('count(*) as checkout_count'))
            ->where('action_type', 'checkout')
            ->whereIn('item_type', [Asset::class, Accessory::class, Consumable::class])
            ->whereNotNull('item_id')
            ->groupBy('item_type', 'item_id')
            ->orderByDesc('checkout_count')
            ->limit(10)
            ->get();

        $recentCheckouts = Actionlog::with(['item', 'target'])
            ->where('action_type', 'checkout')
            ->where('target_type', User::class)
            ->whereIn('item_type', [Asset::class, Accessory::class, Consumable::class])
            ->orderByDesc('action_date')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $categoryStats = collect()
            ->merge(
                Asset::join('models', 'assets.model_id', '=', 'models.id')
                    ->join('categories', 'models.category_id', '=', 'categories.id')
                    ->select('categories.name', DB::raw("'Assets' as type"), DB::raw('count(*) as total'))
                    ->groupBy('categories.name')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get()
            )
            ->merge(
                Accessory::join('categories', 'accessories.category_id', '=', 'categories.id')
                    ->select('categories.name', DB::raw("'Zubehör' as type"), DB::raw('sum(accessories.qty) as total'))
                    ->groupBy('categories.name')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get()
            )
            ->merge(
                Consumable::join('categories', 'consumables.category_id', '=', 'categories.id')
                    ->select('categories.name', DB::raw("'Verbrauchsmaterial' as type"), DB::raw('sum(consumables.qty) as total'))
                    ->groupBy('categories.name')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get()
            )
            ->sortByDesc('total')
            ->take(10);

        return view('lendit.statistics', [
            'assetTotal' => $assetTotal,
            'assetCheckedOut' => $assetCheckedOut,
            'assetAvailable' => $assetAvailable,
            'assetOverdue' => $assetOverdue,
            'accessoryTotalQty' => $accessoryTotalQty,
            'accessoryCheckedOut' => $accessoryCheckedOut,
            'accessoryAvailable' => $accessoryAvailable,
            'consumableTotalQty' => $consumableTotalQty,
            'consumableIssued' => $consumableIssued,
            'consumableAvailable' => $consumableAvailable,
            'topLoanedItems' => $topLoanedItems,
            'recentCheckouts' => $recentCheckouts,
            'categoryStats' => $categoryStats,
        ]);
    }

    private function whereHasLendITTag($query, string $itemType, string $itemIdColumn, int $tagId): void
    {
        $query->whereExists(function ($query) use ($itemType, $itemIdColumn, $tagId) {
            $query->select(DB::raw(1))
                ->from('lendit_taggables')
                ->whereColumn('lendit_taggables.item_id', $itemIdColumn)
                ->where('lendit_taggables.item_type', $itemType)
                ->where('lendit_taggables.tag_id', $tagId);
        });
    }

    private function tagsForItems($items, string $itemType)
    {
        $itemIds = $items->pluck('id');

        if ($itemIds->isEmpty()) {
            return collect();
        }

        return DB::table('lendit_taggables')
            ->join('lendit_tags', 'lendit_tags.id', '=', 'lendit_taggables.tag_id')
            ->where('lendit_taggables.item_type', $itemType)
            ->whereIn('lendit_taggables.item_id', $itemIds)
            ->orderBy('lendit_tags.name')
            ->get(['lendit_taggables.item_id', 'lendit_tags.name'])
            ->groupBy(fn ($row) => $itemType.':'.$row->item_id);
    }
}
