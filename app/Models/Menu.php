<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Menu extends Model
{
    protected $table = 'menus';
    
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'icon',
        'route_name',
        'url',
        'permission',
        'parent_id',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'order'     => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // Cucun-center compatibility accessors / mutators
    public function getNameAttribute(): string
    {
        return $this->title ?? '';
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['title'] = $value;
    }

    public function getRouteAttribute(): ?string
    {
        return $this->route_name;
    }

    public function setRouteAttribute($value): void
    {
        $this->attributes['route_name'] = $value;
    }

    public function getAdditionalAttribute(): ?string
    {
        return $this->icon;
    }

    public function setAdditionalAttribute($value): void
    {
        $this->attributes['icon'] = $value;
    }

    public function getStatusAttribute(): int
    {
        return $this->is_active ? 1 : 2;
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['is_active'] = in_array($value, [1, true, '1', 'true'], true);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function childs(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    public function children(): HasMany
    {
        return $this->childs();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get all route permissions associated with this menu and all its descendants.
     */
    public function getAllRoutes(): array
    {
        $routes = [];
        if (!empty($this->route_name)) {
            $routes = array_merge($routes, explode('|', $this->route_name));
        }

        foreach ($this->childs as $child) {
            $routes = array_merge($routes, $child->getAllRoutes());
        }

        return array_unique(array_filter($routes));
    }
}