<?php

namespace App\DTO;

class AppticaResponseDTO
{
    public function __construct(
        private array $positions
    ) {}

    public static function fromArray(array $data): self
    {
        $positions = [];

        foreach ($data as $categoryId => $subCategories) {
            if (!is_array($subCategories)) {
                continue; // Пропускаем, если данные категории не являются массивом
            }

            $allPositions = [];
            foreach ($subCategories as $subCategoryId => $dates) {
                if (!is_array($dates)) {
                    continue; // Пропускаем, если данные подкатегории не являются массивом
                }

                foreach ($dates as $date => $position) {
                    $allPositions[$date][] = $position;
                }
            }

            foreach ($allPositions as $date => $positionsList) {
                $positions[$date][$categoryId] = min($positionsList);
            }
        }

        return new self($positions);
    }


    public function getPositions(): array
    {
        return $this->positions;
    }

}
