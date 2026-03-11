<?php

namespace App\Custom\Http\Controllers;

use App\Custom\Http\Requests\StoreSparePartRequest;
use App\Custom\Models\SparePart;
use App\Custom\Services\StockService;
use App\Http\Controllers\Controller;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SparePartController extends Controller
{
    public function __construct(protected StockService $stockService)
    {
    }

    /**
     * GET /fom/spare-parts — paginated list with stock status filter.
     */
    public function index(Request $request)
    {
        $query = SparePart::with(['category', 'manufacturer', 'location']);

        if ($request->filled('stock_status')) {
            match ($request->stock_status) {
                'critical' => $query->where('quantity_on_hand', '<=', 0),
                'low'      => $query->lowStock()->where('quantity_on_hand', '>', 0),
                'ok'       => $query->whereColumn('quantity_on_hand', '>', 'minimum_quantity'),
                default    => null,
            };
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('part_number', 'like', "%{$search}%");
            });
        }

        $parts = $query->orderBy('name')
            ->paginate(20)
            ->appends($request->query());

        return view('custom::spare-parts.index', compact('parts'));
    }

    /**
     * GET /fom/spare-parts/create — new spare part form.
     */
    public function create()
    {
        $categories    = Category::orderBy('name')->get(['id', 'name']);
        $manufacturers = Manufacturer::orderBy('name')->get(['id', 'name']);
        $suppliers     = Supplier::orderBy('name')->get(['id', 'name']);
        $locations     = Location::orderBy('name')->get(['id', 'name']);
        $assetModels   = AssetModel::orderBy('name')->get(['id', 'name', 'model_number']);

        return view('custom::spare-parts.create', compact(
            'categories', 'manufacturers', 'suppliers', 'locations', 'assetModels',
        ));
    }

    /**
     * POST /fom/spare-parts — store new spare part.
     */
    public function store(StoreSparePartRequest $request)
    {
        $data = $request->validated();
        $modelIds = $data['asset_model_ids'] ?? [];
        unset($data['asset_model_ids']);

        $part = SparePart::create($data);

        if (! empty($modelIds)) {
            $part->assetModels()->attach($modelIds);
        }

        return redirect()
            ->route('fom.spare-parts.show', $part->id)
            ->with('success', "Yedek parça oluşturuldu: {$part->name}");
    }

    /**
     * GET /fom/spare-parts/{id} — detail with movements and compatible models.
     */
    public function show(int $id)
    {
        $part = SparePart::with([
            'category',
            'manufacturer',
            'supplier',
            'location',
            'assetModels',
        ])->findOrFail($id);

        $movements = $part->movements()
            ->with('performedBy')
            ->latest('created_at')
            ->paginate(15);

        return view('custom::spare-parts.show', compact('part', 'movements'));
    }

    /**
     * GET /fom/spare-parts/{id}/stock-in — stock entry form.
     */
    public function stockIn(int $id)
    {
        $part = SparePart::findOrFail($id);

        return view('custom::spare-parts.stock-in', compact('part'));
    }

    /**
     * POST /fom/spare-parts/{id}/stock-in — process stock entry.
     */
    public function storeStockIn(Request $request, int $id)
    {
        $part = SparePart::findOrFail($id);

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes'    => 'nullable|string|max:500',
        ]);

        $this->stockService->addStock(
            $part,
            $request->quantity,
            'manual',
            0,
            $request->user(),
        );

        return redirect()
            ->route('fom.spare-parts.show', $part->id)
            ->with('success', "{$request->quantity} adet stok girişi yapıldı.");
    }

    /**
     * GET /fom/spare-parts/low-stock — parts below minimum quantity.
     */
    public function lowStock()
    {
        $parts = SparePart::with(['manufacturer', 'location'])
            ->lowStock()
            ->orderBy('quantity_on_hand')
            ->paginate(20);

        return view('custom::spare-parts.low-stock', compact('parts'));
    }
}
