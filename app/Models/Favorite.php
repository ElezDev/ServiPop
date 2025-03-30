<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $guarded = [];
    protected $table = 'favorites';

    protected $casts = [
        'is_checked' => 'boolean'
    ];
      public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene el servicio marcado como favorito
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }


     /**
     * Marcar como seleccionado
     */
    public function markAsChecked()
    {
        $this->update(['is_checked' => true]);
        return $this;
    }
    
    /**
     * Desmarcar como seleccionado
     */
    public function unmarkAsChecked()
    {
        $this->update(['is_checked' => false]);
        return $this;
    }
    
    /**
     * Alternar estado de selección
     */
    public function toggleChecked()
    {
        $this->update(['is_checked' => !$this->is_checked]);
        return $this;
    }
    
    /**
     * Scope para favoritos seleccionados
     */
    public function scopeChecked($query)
    {
        return $query->where('is_checked', true);
    }
    
    /**
     * Scope para favoritos no seleccionados
     */
    public function scopeUnchecked($query)
    {
        return $query->where('is_checked', false);
    }
}
