<?php

namespace App\DTO;

class AppPositionDTO
{
    public function __construct(
        private string $date,
        private array $positions
    ) {}

    public static function fromModel(string $date, array $positions): self
    {
        return new self($date, $positions);
    }

    public function toArray(): array
    {
        if (empty($this->positions)) {
            return [
                'status_code' => 404,
                'message' => 'No data found for specified date',
                'data' => []
            ];
        }

        return [
            'status_code' => 200,
            'message' => 'ok',
            'data' => $this->positions
        ];
    }
}
