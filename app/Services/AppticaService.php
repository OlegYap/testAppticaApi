<?php

namespace App\Services;
use App\DTO\AppPositionDTO;
use App\DTO\AppticaResponseDTO;
use App\Models\AppPosition;

class AppticaService
{
    private AppticaClientService $appticaClientService;
    private PositionCacheService $positionCacheService;
    public function __construct(AppticaClientService $appticaClientService, PositionCacheService $positionCacheService) {
        $this->appticaClientService = $appticaClientService;
        $this->positionCacheService = $positionCacheService;
    }

    public function getPositionsForDate(string $date): AppPositionDTO
    {
        // используем метод remember для получения данных из кэша
        $positions = $this->positionCacheService->remember($date, function () use ($date) {
            // если данных в кэше нету, проверяем бд
            $positions = AppPosition::where('date', $date)->get();
            // если данных в бд нету, запрашиваем их из Api и сохраняем
            if ($positions->isEmpty()) {
                $this->fetchAndSaveData($date);
                $positions = AppPosition::where('date', $date)->get();
            }
            // возвращаем данные в формате для кэша
            return $positions->pluck('position','category_id')->toArray();
        });
        return AppPositionDTO::fromModel($date, $positions);
    }

    private function fetchAndSaveData(string $date): void
    {
        // проверяем на наличие данных в кэше, чтобы избежать лишних запроов к Api
        if (!$this->positionCacheService->has($date)) {
            $responseDto = $this->appticaClientService->fetchPositionsData($date, $date);
            $this->savePositions($responseDto);
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
