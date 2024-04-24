<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCatalogue extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id','item_id','file_type', 'image', 'video'
    ];
    
    public function catalogues()
    {
        return $this->belongsTo(Item::class,'item_id');
    }
}
