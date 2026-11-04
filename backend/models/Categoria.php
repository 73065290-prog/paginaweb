<?php
declare(strict_types=1);

final class Categoria
{
    public function __construct(
        public readonly string $id,
        public readonly string $nombre,
        public readonly string $badge = '',
        public readonly string $badge_tone = '',
        public readonly int $count_hint = 0,
        public readonly string $cat_filtro = '',
        public readonly string $img = '',
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (string) ($row['id'] ?? ''),
            nombre: (string) ($row['nombre'] ?? ''),
            badge: (string) ($row['badge'] ?? ''),
            badge_tone: (string) ($row['badge_tone'] ?? ''),
            count_hint: isset($row['count_hint']) ? (int) $row['count_hint'] : 0,
            cat_filtro: (string) ($row['cat_filtro'] ?? ''),
            img: (string) ($row['img'] ?? ''),
        );
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'badge' => $this->badge,
            'badge_tone' => $this->badge_tone,
            'count_hint' => $this->count_hint,
            'cat_filtro' => $this->cat_filtro,
            'img' => $this->img,
        ];
    }
}

