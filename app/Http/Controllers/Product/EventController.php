<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Event;
use App\Models\Product\EventPopup;
use App\Models\Promo\PriceProductSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['popup', 'priceProductSettings'])->orderBy('created_at', 'desc')->get();
        return view('pages.events.index', compact('events'));
    }

    public function create()
    {
        $priceProductSettings = PriceProductSetting::where('deleted', false)
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
        return view('pages.events.create', compact('priceProductSettings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Event Details
            'title' => 'required|string|max:255',
            'event_type' => 'required|string',
            'banner_image' => 'nullable|image|max:2048',
            'slug' => 'nullable|string|max:255|unique:events,slug',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',

            // Linked Price Product Setting
            'price_product_setting_id' => 'required|string|exists:price_product_settings,id',

            // Popup Details
            'popup_title' => 'nullable|string|max:255',
            'popup_image' => 'nullable|image|max:2048',
            'popup_link' => 'nullable|string|max:500',
            'popup_button_text' => 'nullable|string|max:100',
            'popup_active' => 'boolean',
        ]);

        $eventId = (string) Str::uuid();
        $eventSlug = $request->input('slug') ?: Str::slug($request->input('title'));
        $isActive = $request->has('is_active');

        $bannerPath = null;
        if ($request->file('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('events', 'public');
        }

        // 1. Create Event
        $event = Event::create([
            'id' => $eventId,
            'title' => $request->input('title'),
            'event_type' => $request->input('event_type'),
            'banner_image' => $bannerPath,
            'slug' => $eventSlug,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'is_active' => $isActive,
        ]);

        // 2. Link Price Product Setting
        PriceProductSetting::where('id', $request->input('price_product_setting_id'))
            ->update(['event_id' => $event->id]);

        // 3. Create Event Popup
        $popupData = [
            'id' => (string) Str::uuid(),
            'event_id' => $event->id,
            'title' => $request->input('popup_title') ?: $request->input('title'),
            'link_url' => $request->input('popup_link'),
            'button_text' => $request->input('popup_button_text') ?: 'Lihat Promo',
            'is_active' => $request->has('popup_active'),
        ];

        if ($request->file('popup_image')) {
            $popupData['image_url'] = $request->file('popup_image')->store('popups', 'public');
        }

        EventPopup::create($popupData);

        return redirect()->route('events.index')->with('success', 'Event campaign configured successfully.');
    }

    public function edit($id)
    {
        $event = Event::with(['popup'])->findOrFail($id);
        $promo = PriceProductSetting::where('event_id', $event->id)->first();
        $priceProductSettings = PriceProductSetting::where('deleted', false)
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
        return view('pages.events.edit', compact('event', 'promo', 'priceProductSettings'));
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $popup = EventPopup::where('event_id', $event->id)->first();

        $validated = $request->validate([
            // Event Details
            'title' => 'required|string|max:255',
            'event_type' => 'required|string',
            'banner_image' => 'nullable|image|max:2048',
            'slug' => 'nullable|string|max:255|unique:events,slug,' . $id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',

            // Linked Price Product Setting
            'price_product_setting_id' => 'required|string|exists:price_product_settings,id',

            // Popup Details
            'popup_title' => 'nullable|string|max:255',
            'popup_image' => 'nullable|image|max:2048',
            'popup_link' => 'nullable|string|max:500',
            'popup_button_text' => 'nullable|string|max:100',
            'popup_active' => 'boolean',
        ]);

        $isActive = $request->has('is_active');
        $eventSlug = $request->input('slug') ?: Str::slug($request->input('title'));

        $eventData = [
            'title' => $request->input('title'),
            'event_type' => $request->input('event_type'),
            'slug' => $eventSlug,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'is_active' => $isActive,
        ];

        if ($request->file('banner_image')) {
            if ($event->banner_image) {
                Storage::disk('public')->delete($event->banner_image);
            }
            $eventData['banner_image'] = $request->file('banner_image')->store('events', 'public');
        }

        // 1. Update Event
        $event->update($eventData);

        // 2. Update linked Price Product Setting
        // First dissociate old ones
        PriceProductSetting::where('event_id', $event->id)->update(['event_id' => null]);
        // Associate selected one
        PriceProductSetting::where('id', $request->input('price_product_setting_id'))
            ->update(['event_id' => $event->id]);

        // 3. Update Popup
        $popupData = [
            'title' => $request->input('popup_title') ?: $request->input('title'),
            'link_url' => $request->input('popup_link'),
            'button_text' => $request->input('popup_button_text') ?: 'Lihat Promo',
            'is_active' => $request->has('popup_active'),
        ];

        if ($request->file('popup_image')) {
            if ($popup && $popup->image_url) {
                Storage::disk('public')->delete($popup->image_url);
            }
            $popupData['image_url'] = $request->file('popup_image')->store('popups', 'public');
        }

        if ($popup) {
            $popup->update($popupData);
        } else {
            $popupData['id'] = (string) Str::uuid();
            $popupData['event_id'] = $event->id;
            EventPopup::create($popupData);
        }

        return redirect()->route('events.index')->with('success', 'Event campaign updated successfully.');
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->update(['deleted' => true]);

        // Dissociate settings
        PriceProductSetting::where('event_id', $event->id)->update(['event_id' => null]);

        return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
    }
}
