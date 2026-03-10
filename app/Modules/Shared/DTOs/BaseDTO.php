<?php

namespace App\Modules\Shared\DTOs;

abstract class BaseDTO
{
    public static function fromRequest(\Illuminate\Http\Request $request): static
    {
        return new static(...$request->validated());
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
