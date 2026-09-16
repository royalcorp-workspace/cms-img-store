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
    public function index(Request $request)
    {
        $query = Event::with(['popup', 'priceProductSettings'])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('event_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $events = $query->paginate(15)->withQueryString();

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
            'banner_image' => $request->hasFile('banner_image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'slug' => 'nullable|string|max:255|unique:events,slug',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',

            // Linked Price Product Setting
            'price_product_setting_id' => 'required|string|exists:price_product_settings,id',

            // Popup Details
            'popup_title' => 'nullable|string|max:255',
            'popup_image' => $request->hasFile('popup_image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'popup_link' => 'nullable|string|max:500',
            'popup_button_text' => 'nullable|string|max:100',
            'popup_active' => 'boolean',
        ]);

        $eventId = (string) Str::uuid();
        $eventSlug = $request->input('slug') ?: Str::slug($request->input('title'));
        $isActive = $request->has('is_active');

        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
        $bannerPath = null;
        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('events', $uploadDisk);
        } elseif ($request->filled('banner_image')) {
            $bannerPath = $request->input('banner_image');
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

        if ($request->hasFile('popup_image')) {
            $popupData['image_url'] = $request->file('popup_image')->store('popups', $uploadDisk);
        } elseif ($request->filled('popup_image')) {
            $popupData['image_url'] = $request->input('popup_image');
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
            'banner_image' => $request->hasFile('banner_image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'slug' => 'nullable|string|max:255|unique:events,slug,' . $id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',

            // Linked Price Product Setting
            'price_product_setting_id' => 'required|string|exists:price_product_settings,id',

            // Popup Details
            'popup_title' => 'nullable|string|max:255',
            'popup_image' => $request->hasFile('popup_image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
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

        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';

        if ($request->hasFile('banner_image')) {
            if ($event->banner_image) {
                unlink_media($event->banner_image);
            }
            $eventData['banner_image'] = $request->file('banner_image')->store('events', $uploadDisk);
        } elseif ($request->filled('banner_image')) {
            $newBanner = $request->input('banner_image');
            if ($event->banner_image && $event->banner_image !== $newBanner) {
                unlink_media($event->banner_image);
            }
            $eventData['banner_image'] = $newBanner;
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

        if ($request->hasFile('popup_image')) {
            if ($popup && $popup->image_url) {
                unlink_media($popup->image_url);
            }
            $popupData['image_url'] = $request->file('popup_image')->store('popups', $uploadDisk);
        } elseif ($request->filled('popup_image')) {
            $newPopup = $request->input('popup_image');
            if ($popup && $popup->image_url && $popup->image_url !== $newPopup) {
                unlink_media($popup->image_url);
            }
            $popupData['image_url'] = $newPopup;
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

        if ($event->banner_image) {
            unlink_media($event->banner_image);
        }
        $popup = EventPopup::where('event_id', $event->id)->first();
        if ($popup && $popup->image_url) {
            unlink_media($popup->image_url);
        }

        return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
    }
}
