<?php

namespace App\Http\Controllers\Packing;

use App\Http\Controllers\Controller;
use App\Models\Packing\PackingOut;
use Illuminate\Http\Request;

class PackingOutController extends Controller
{
    public function index(Request $request)
    {
        $query = PackingOut::with(['packingSlip.order.customer', 'warehouse', 'packer']);

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $packingOuts = $query->latest()->paginate(10)->appends($request->query());

        return view('pages.packing-out.index', compact('packingOuts'));
    }

    public function show(string $id)
    {
        $packingOut = PackingOut::with(['packingSlip.order.customer', 'packingSlip.items', 'warehouse', 'handover'])->findOrFail($id);
        return view('pages.packing-out.show', compact('packingOut'));
    }

    public function create(string $packing_slip_id)
    {
        $packingSlip = \App\Models\Packing\PackingSlip::with(['order'])->findOrFail($packing_slip_id);
        return view('pages.packing-out.create', compact('packingSlip'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'packing_slip_id' => 'required|exists:packing_slips,id',
        ]);

        $packingSlip = \App\Models\Packing\PackingSlip::with(['order.items'])->findOrFail($request->packing_slip_id);

        $packingOut = PackingOut::create([
            'packing_slip_id' => $request->packing_slip_id,
            'warehouse_id' => $request->warehouse_id,
            'status' => 'ready',
        ]);

        $packingSlip->update(['status' => 'packed']);

        $order = $packingSlip->order;
        if ($order) {
            // 1. Create Invoice
            $invoice = \App\Models\Invoice::create([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'invoice_number' => 'INV-' . $order->order_number . '-' . rand(100, 999),
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'courier_id' => $order->courier_id,
                'shipping_addresses_id' => $order->shipping_addresses_id,
                'status' => 0,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'settlement_id' => $order->settlement_id,
                'subtotal' => $order->subtotal,
                'tax' => $order->tax,
                'discount' => $order->discount,
                'total' => $order->total,
                'shipping_cost' => $order->shipping_cost,
                'shipping_cost_subsidy' => $order->shipping_cost_subsidy,
                'transaction_fee' => $order->transaction_fee,
                'voucher_id' => $order->voucher_id,
                'voucher_nominal' => $order->voucher_nominal,
                'notes' => $order->notes,
                'meta' => $order->meta,
                'creator' => $order->creator,
                'editor' => $order->editor,
                'due_date' => now()->addDays(1),
            ]);

            // 2. Create Invoice Items
            foreach ($order->items as $item) {
                \App\Models\InvoiceItem::create([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_color_id' => $item->product_color_id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_nominal' => $item->discount_nominal,
                    'discount_percent' => $item->discount_percent,
                    'total' => $item->total,
                    'weight' => $item->weight,
                    'item_notes' => $item->item_notes,
                    'meta' => $item->meta,
                ]);
            }

            // 3. Move Credit Memo to Payment
            $creditMemo = \App\Models\CreditMemo::where('order_id', $order->id)->first();
            if ($creditMemo) {
                \App\Models\Payment::create([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'payment_number' => 'PAY-' . $order->order_number . '-' . rand(100, 999),
                    'order_id' => $creditMemo->order_id,
                    'gateway' => $creditMemo->gateway,
                    'transaction_id' => $creditMemo->transaction_id,
                    'amount' => $creditMemo->amount,
                    'status' => $creditMemo->status,
                    'payload' => $creditMemo->payload,
                    'paid_at' => $creditMemo->paid_at,
                ]);
            }
        }

        return redirect()->route('packing-out.show', $packingOut->id)->with('success', 'Packing out created');
    }

    public function confirmOut(string $id)
    {
        $packingOut = PackingOut::findOrFail($id);
        $packingOut->update(['status' => 'out']);
        $packingOut->packingSlip->order->update(['status' => \App\Models\Order\Order::STATUS_SHIPPED]);

        return back()->with('success', 'Order marked as shipped');
    }
}