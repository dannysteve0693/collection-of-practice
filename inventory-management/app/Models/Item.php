<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'quantity',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function getTotalPurchasedAttribute()
    {
        return $this->purchases()->sum('quantity');
    }

    public function getTotalSoldAttribute()
    {
        return $this->sales()->sum('quantity');
    }

    public function getCurrentStockAttribute()
    {
        return $this->getTotalPurchasedAttribute() - $this->getTotalSoldAttribute();
    }
}
