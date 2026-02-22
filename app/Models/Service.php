<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subcategory_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'subcategory_id' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static array $schemaColumnCache = [];

    protected static array $schemaTableCache = [];

    public function isFillable($key)
    {
        // Keep legacy alias inputs working (admin/tests) without advertising them as real persisted fields.
        if (in_array($key, ['category', 'status'], true)) {
            return true;
        }

        return parent::isFillable($key);
    }

    protected function fillableFromArray(array $attributes)
    {
        $fillable = parent::fillableFromArray($attributes);

        foreach (['category', 'status'] as $alias) {
            if (array_key_exists($alias, $attributes)) {
                $fillable[$alias] = $attributes[$alias];
            }
        }

        return $fillable;
    }

    public function fixers()
    {
        return $this->belongsToMany(Fixer::class, 'fixer_service');
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function scopeActive($query)
    {
        if (static::hasServiceColumn('is_active')) {
            return $query->where($this->qualifyColumn('is_active'), true);
        }

        if (static::hasServiceColumn('status')) {
            return $query->whereRaw('LOWER(' . $this->qualifyColumn('status') . ') = ?', ['active']);
        }

        return $query;
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getStatusAttribute($value): string
    {
        if (is_string($value) && $value !== '') {
            return static::normalizeStatusValue($value);
        }

        if (array_key_exists('is_active', $this->attributes)) {
            return static::normalizeActiveBoolean($this->attributes['is_active']) ? 'active' : 'inactive';
        }

        return 'inactive';
    }

    public function setStatusAttribute($value): void
    {
        $isActive = static::normalizeActiveBoolean($value);

        if (static::hasServiceColumn('status')) {
            $this->attributes['status'] = $isActive ? 'active' : 'inactive';
        }

        if (static::hasServiceColumn('is_active')) {
            $this->attributes['is_active'] = $isActive ? 1 : 0;
        }
    }

    public function getIsActiveAttribute($value): bool
    {
        if ($value !== null) {
            return static::normalizeActiveBoolean($value);
        }

        if (array_key_exists('status', $this->attributes)) {
            return static::normalizeActiveBoolean($this->attributes['status']);
        }

        return false;
    }

    public function setIsActiveAttribute($value): void
    {
        $isActive = static::normalizeActiveBoolean($value);

        if (static::hasServiceColumn('is_active')) {
            $this->attributes['is_active'] = $isActive ? 1 : 0;
        }

        if (static::hasServiceColumn('status')) {
            $this->attributes['status'] = $isActive ? 'active' : 'inactive';
        }
    }

    public function getCategoryAttribute($value): ?string
    {
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        $subcategory = $this->resolveSubcategoryRelation();
        $name = $subcategory?->name;

        return is_string($name) && trim($name) !== '' ? trim($name) : null;
    }

    public function setCategoryAttribute($value): void
    {
        $category = is_string($value) ? trim($value) : null;

        if ($category === null || $category === '') {
            if (static::hasServiceColumn('category')) {
                $this->attributes['category'] = null;
            }

            return;
        }

        if (static::hasServiceColumn('category')) {
            $this->attributes['category'] = $category;
        }

        if (! static::hasServiceColumn('subcategory_id') || ! static::hasTableCached('subcategories')) {
            return;
        }

        if (array_key_exists('subcategory_id', $this->attributes) && $this->attributes['subcategory_id']) {
            return;
        }

        $match = Subcategory::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($category)])
            ->first();

        if ($match) {
            $this->attributes['subcategory_id'] = $match->id;
        }
    }

    protected function resolveSubcategoryRelation(): ?Subcategory
    {
        if ($this->relationLoaded('subcategory')) {
            $loaded = $this->getRelation('subcategory');

            return $loaded instanceof Subcategory ? $loaded : null;
        }

        if (! static::hasServiceColumn('subcategory_id') || ! static::hasTableCached('subcategories')) {
            return null;
        }

        if (! $this->getAttributeFromArray('subcategory_id')) {
            return null;
        }

        return $this->subcategory;
    }

    protected static function hasServiceColumn(string $column): bool
    {
        return static::$schemaColumnCache[$column]
            ??= Schema::hasColumn((new static())->getTable(), $column);
    }

    protected static function hasTableCached(string $table): bool
    {
        return static::$schemaTableCache[$table] ??= Schema::hasTable($table);
    }

    protected static function normalizeStatusValue(mixed $value): string
    {
        return static::normalizeActiveBoolean($value) ? 'active' : 'inactive';
    }

    protected static function normalizeActiveBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            return in_array($normalized, ['1', 'true', 'yes', 'on', 'active', 'enabled'], true);
        }

        return false;
    }
}
