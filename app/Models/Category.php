<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
        protected $table = 'categories';
        protected $fillable = ['nmv', 'name', 'skidka', 'kol_p','is_soz','is_auto',
            'is_gaz','user_id', 'komment','kat_dop'];
        
        protected $casts = [
        'created_at' => 'datetime:d.m.Y H:i:s',
        'updated_at' => 'datetime:d.m.Y H:i:s',
        ];
        
         /**
        * Получить пользователя (оператора), связанного с категорией.
        */
        public function user()
        {
            return $this->belongsTo(User::class);
        }

}
