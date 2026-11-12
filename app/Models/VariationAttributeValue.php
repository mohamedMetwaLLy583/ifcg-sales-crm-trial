<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VariationAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = ['variation_id', 'attribute_id', 'option_id', 'value'];

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    public function attribute()
    {
        return $this->belongsTo(VariationAttribute::class, 'attribute_id');
    }

    public function option()
    {
        return $this->belongsTo(AttributeOption::class, 'option_id');
    }
}