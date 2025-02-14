<?php

namespace App\Services;
use App\DTO\AppPositionDTO;
use App\DTO\AppticaResponseDTO;
use App\Models\AppPosition;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AppticaService
{
    private const BASE_URL = 'https://api.apptica.com/package/top_history';
    private const APP_ID = '1421444';
    private const COUNTRY_ID = '1';
    private const API_KEY = 'fVN5Q9KVOlOHDx9mOsKPAQsFBlEhBOwguLkNEDTZvKzJzT3l';
    private const CACHE_TTL = 3600;

    public function getPositionsForDate(string $date): AppPositionDTO
    {
        $positions = AppPosition::where('date', $date)->get();

        if ($positions->isEmpty()) {
            $this->fetchAndSaveData($date);
            $positions = AppPosition::where('date', $date)->get();

            if ($positions->isEmpty()) {
                return AppPositionDTO::fromModel($date, []);
            }
        }

        return AppPositionDTO::fromModel(
            $date,
            $positions->pluck('position', 'category_id')->toArray()
        );
    }

    private function fetchAndSaveData(string $date): void
    {
        $cacheKey = "apptica_data_{$date}";

        if (!Cache::has($cacheKey)) {
            $responseDto = $this->fetchData($date, $date);
            $this->savePositions($responseDto);

            Cache::put($cacheKey, true, self::CACHE_TTL);
        }
    }

    private function fetchData(string $dateFrom, string $dateTo): AppticaResponseDTO
    {
        try {
            $response = Http::get(self::BASE_URL . '/' . self::APP_ID . '/' . self::COUNTRY_ID, [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'B4NKGg' => self::API_KEY
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch data from Apptica: ' . $response->body());
            }

            $data = $response->json();

            Log::info('Data received from Apptica API:', $data);

            if (!is_array($data) || !isset($data['data']) || !is_array($data['data'])) {
                throw new \Exception('Invalid data format received from Apptica API');
            }

            return AppticaResponseDTO::fromArray($data['data']);
        } catch (\Exception $e) {
            Log::error('Error fetching data from Apptica: ' . $e->getMessage());
            throw $e;
        }
    }

    private function savePositions(AppticaResponseDTO $dto): void
    {
        foreach ($dto->getPositions() as $date => $categories) {
            foreach ($categories as $categoryId => $position) {
                AppPosition::updateOrCreate(
                    [
                        'date' => $date,
                        'category_id' => $categoryId
                    ],
                    [
                        'position' => $position
                    ]
                );
            }
        }
    }
}
