<?php
// app/Models/PaymentGateway.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name', 'code', 'type', 'config', 'is_active', 'is_default', 'sort_order'
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean'
    ];

    /**
     * Get gateway configuration from config/payment.php
     */
    public function getGatewayConfigAttribute()
    {
        return Config::get("payment.gateways.{$this->code}", []);
    }

    /**
     * Get specific credential from config
     */
    public function getCredential($key)
    {
        $config = $this->gateway_config;
        return $config[$key] ?? null;
    }

    /**
     * Check if gateway is properly configured
     */
    public function isConfigured()
    {
        $config = $this->gateway_config;
        
        // Check required keys based on gateway type
        if ($this->type === 'bank') {
            return !empty($config['consumer_key']) && !empty($config['consumer_secret']);
        }
        
        if ($this->type === 'mobile_money') {
            return !empty($config['api_key'] ?? $config['consumer_key'] ?? null);
        }
        
        return true;
    }

    /**
     * Get all active gateways that are configured
     */
    public function scopeActiveAndConfigured($query)
    {
        return $query->where('is_active', true)
            ->get()
            ->filter(function ($gateway) {
                return $gateway->isConfigured();
            });
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }
}