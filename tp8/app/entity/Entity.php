<?php
declare(strict_types=1);

namespace app\entity;

use think\Model;

abstract class Entity extends Model
{
    protected $readonly = [];
    protected $jsonAssoc = true;

    public function toArray(): array
    {
        return $this->toArray();
    }

    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return $this->toJson($options);
    }

    public function assignData(array $data): self
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key) || in_array($key, $this->field ?? [])) {
                $this->$key = $value;
            }
        }
        return $this;
    }

    public function getPrimaryKey(): mixed
    {
        return $this->getPk();
    }

    public function getCreatedAt(): ?string
    {
        return $this->create_time ?? null;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->update_time ?? null;
    }

    public function isNewRecord(): bool
    {
        return $this->isEmpty();
    }

    public function refresh(): void
    {
        $this->data = [];
    }

    protected function serializeData(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value instanceof \DateTime) {
                $data[$key] = $value->format('Y-m-d H:i:s');
            }
        }
        return $data;
    }
}