<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Plan extends Model
{
    // Colunas que realmente existem
    protected $fillable = [
        'name',
        'slug',
        'price',
        'interval',
        'limits',
        'active',
    ];

    protected $casts = [
        'limits' => 'array',
        'active' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    /**
     * Relação com subscriptions
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Obter preço - compatibilidade com código existente
     */
    public function price($interval = null)
    {
        // Sua tabela só tem 'price', não tem monthly/yearly
        return $this->price;
    }

    /**
     * Métodos para compatibilidade - preço mensal
     */
    public function getPriceMonthlyAttribute()
    {
        return $this->price;
    }

    /**
     * Métodos para compatibilidade - preço anual
     */
    public function getPriceYearlyAttribute()
    {
        // Calcular anual baseado no mensal
        return $this->price * 12;
    }

    /**
     * Obter limite específico
     */
    public function limit($key)
    {
        return $this->limits[$key] ?? null;
    }

    /**
     * Verificar se tem funcionalidade
     */
    public function hasFeature($feature): bool
    {
        // Sua tabela não tem coluna 'features'
        return false;
    }

    /**
     * Verificar se é plano gratuito
     */
    public function isFree(): bool
    {
        return $this->price == 0;
    }

    /**
     * Métodos para compatibilidade com código que espera novas colunas
     */
    public function getIsPublicAttribute()
    {
        return $this->active; // Usar 'active' como 'is_public'
    }

    public function getIsActiveAttribute()
    {
        return $this->active;
    }

    public function getIsDefaultAttribute()
    {
        return false; // Ou lógica baseada no slug
    }

    public function getTrialDaysAttribute()
    {
        return 14; // Valor padrão
    }

    public function getDescriptionAttribute()
    {
        return "Plano " . $this->name;
    }

    public function getFeaturesAttribute()
    {
        return []; //
  }
}
