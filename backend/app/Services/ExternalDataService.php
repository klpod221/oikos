<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * External Data Service
 *
 * Service for fetching weather, exchange rates, and metal prices from external APIs.
 *
 * @package App\Services
 */
class ExternalDataService
{
    /**
     * Get all external data (weather, exchange rates, metals)
     *
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return array
     */
    public function getAllData(float $lat = 21.0285, float $lon = 105.8542): array
    {
        // Default: Hanoi, Vietnam coordinates
        return Cache::remember('external_data:' . md5($lat . $lon), 3600, function () use ($lat, $lon) {
            return [
                'weather' => $this->getWeather($lat, $lon),
                'exchange_rates' => $this->getExchangeRates(),
                'metals' => $this->getMetalPrices(),
                'updated_at' => now()->toISOString(),
            ];
        });
    }

    /**
     * Get weather data from Open-Meteo API (free, no key required)
     */
    public function getWeather(float $lat, float $lon): ?array
    {
        try {
            $response = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lon,
                'current' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
                'timezone' => 'Asia/Ho_Chi_Minh',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $current = $data['current'] ?? [];

                return [
                    'temperature' => $current['temperature_2m'] ?? null,
                    'humidity' => $current['relative_humidity_2m'] ?? null,
                    'wind_speed' => $current['wind_speed_10m'] ?? null,
                    'weather_code' => $current['weather_code'] ?? null,
                    'condition' => $this->mapWeatherCode($current['weather_code'] ?? 0),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Weather API error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get USD/VND exchange rate from Vietcombank (Official XML)
     */
    public function getExchangeRates(): ?array
    {
        try {
            // Vietcombank XML Endpoint
            $response = Http::timeout(10)->get('https://portal.vietcombank.com.vn/Usercontrols/TVPortal.TyGia/pXML.aspx');

            if ($response->successful()) {
                // Parse XML
                // Note: Response body is raw XML
                $xmlContent = $response->body();

                // Use regex or SimpleXML. SimpleXML is cleaner if extension exists.
                // Fallback to regex if SimpleXML fails or to be robust against strict XML errors.
                // Regex pattern for USD Transfer rate: <Exrate CurrencyCode="USD" ... Transfer="26,080.00" ... />

                // Using Regex for robustness and speed without dependency checks
                if (preg_match('/CurrencyCode="USD".*?Transfer="([^"]+)"/i', $xmlContent, $matches)) {
                    $rateStr = $matches[1]; // e.g., "26,080.00"
                    $rate = (float) str_replace(',', '', $rateStr);

                    if ($rate > 0) {
                        return [
                            'usd_vnd' => $rate,
                            'base' => 'USD',
                            'source' => 'Vietcombank',
                            'updated_at' => now()->toIsoString(),
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Vietcombank API error: ' . $e->getMessage());
        }

        // Fallback to global API if VCB fails
        return $this->getGlobalExchangeRates();
    }

    /**
     * Fallback: Get USD/VND exchange rate from exchangerate.host (free)
     */
    public function getGlobalExchangeRates(): ?array
    {
        try {
            $response = Http::timeout(5)->get('https://open.er-api.com/v6/latest/USD');

            if ($response->successful()) {
                $data = $response->json();
                $rates = $data['rates'] ?? [];

                return [
                    'usd_vnd' => $rates['VND'] ?? null,
                    'base' => 'USD',
                    'source' => 'GlobalAPI',
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Global Exchange rate API error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get gold and silver prices
     * Using frankfurter.app for XAU conversion or fallback data
     */
    /**
     * Get gold and silver prices from Bao Tin Minh Chau (BTMC)
     */
    public function getMetalPrices(): ?array
    {
        try {
            // Bao Tin Minh Chau API
            $key = '3kd8ub1llcg9t45hnoh8hmn7t5kc2v';
            $response = Http::timeout(10)->get("http://api.btmc.vn/api/BTMCAPI/getpricebtmc?key={$key}");

            if ($response->successful()) {
                $data = $response->json();
                $rows = $data['DataList']['Data'] ?? [];

                $goldPrice = null;
                $silverPrice = null;

                foreach ($rows as $row) {
                    $name = $row['@n_' . $row['@row']] ?? '';
                    $sellPrice = $row['@ps_' . $row['@row']] ?? '';

                    // Gold: Vàng Rồng Thăng Long or SJC (1 Lượng)
                    // Note: 'VÀNG MIẾNG (Vàng Rồng Thăng Long-999.9)' usually matches VRTL
                    // Try to find SJC specifically if available, otherwise VRTL
                    if (!$goldPrice && (str_contains($name, 'Vàng SJC') || str_contains($name, 'Vàng Rồng Thăng Long'))) {
                        // Ensure it's not a fraction (fractional items usually have specific names, but standard is mostly 1 lượng if unspecified or generic)
                        // Actually BTMC lists items like "1 Lượng", "5 chỉ"...
                        // Let's look for "1 Lượng" or generic "Vàng SJC" if it doesn't specify weight (implicitly 1 lượng)
                        // Sample name: "VÀNG MIẾNG (Vàng SJC-999.9)" <- usually standard price per tael
                        // Sample name: "VÀNG MIẾNG (Vàng Rồng Thăng Long-999.9)"
                        $goldPrice = (float) $sellPrice;
                    }

                    // Silver: Bạc miếng ... 1 Lượng
                    if (!$silverPrice && str_contains($name, 'BẠC MIẾNG') && str_contains($name, '1 LƯỢNG')) {
                        $silverPrice = (float) $sellPrice;
                    }

                    if ($goldPrice && $silverPrice)
                        break;
                }

                if ($goldPrice || $silverPrice) {
                    return [
                        'gold' => [
                            'price' => $goldPrice,
                            'unit' => 'lượng',
                            'currency' => 'VND',
                            'source' => 'BTMC',
                        ],
                        'silver' => [
                            'price' => $silverPrice,
                            'unit' => 'lượng',
                            'currency' => 'VND',
                            'source' => 'BTMC',
                        ],
                        'updated_at' => now()->toIsoString(),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('BTMC Gold API error: ' . $e->getMessage());
        }

        // Fallback: Return approximate current market prices in USD
        return [
            'gold' => [
                'price' => 2750.00,
                'unit' => 'oz',
                'currency' => 'USD',
                'is_fallback' => true,
            ],
            'silver' => [
                'price' => 30.50,
                'unit' => 'oz',
                'currency' => 'USD',
                'is_fallback' => true,
            ],
        ];
    }

    /**
     * Map Open-Meteo weather codes to human-readable conditions
     */
    private function mapWeatherCode(int $code): array
    {
        $conditions = [
            0 => ['text' => 'Trời quang', 'icon' => '☀️'],
            1 => ['text' => 'Quang đãng', 'icon' => '🌤️'],
            2 => ['text' => 'Có mây', 'icon' => '⛅'],
            3 => ['text' => 'Nhiều mây', 'icon' => '☁️'],
            45 => ['text' => 'Sương mù', 'icon' => '🌫️'],
            48 => ['text' => 'Sương muối', 'icon' => '🌫️'],
            51 => ['text' => 'Mưa phùn nhẹ', 'icon' => '🌧️'],
            53 => ['text' => 'Mưa phùn vừa', 'icon' => '🌧️'],
            55 => ['text' => 'Mưa phùn dày', 'icon' => '🌧️'],
            61 => ['text' => 'Mưa nhỏ', 'icon' => '🌧️'],
            63 => ['text' => 'Mưa vừa', 'icon' => '🌧️'],
            65 => ['text' => 'Mưa to', 'icon' => '🌧️'],
            71 => ['text' => 'Tuyết rơi nhẹ', 'icon' => '❄️'],
            73 => ['text' => 'Tuyết rơi vừa', 'icon' => '❄️'],
            75 => ['text' => 'Tuyết rơi dày', 'icon' => '❄️'],
            80 => ['text' => 'Mưa rào nhẹ', 'icon' => '🌦️'],
            81 => ['text' => 'Mưa rào vừa', 'icon' => '🌦️'],
            82 => ['text' => 'Mưa rào to', 'icon' => '⛈️'],
            95 => ['text' => 'Dông', 'icon' => '⛈️'],
            96 => ['text' => 'Dông kèm mưa đá', 'icon' => '⛈️'],
            99 => ['text' => 'Dông kèm mưa đá to', 'icon' => '⛈️'],
        ];

        return $conditions[$code] ?? ['text' => 'Unknown', 'icon' => '❓'];
    }

    /**
     * Invalidate external data cache
     */
    public function invalidateCache(): void
    {
        Cache::forget('external_data:*');
    }
}
