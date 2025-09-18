<?php

namespace Chriha\DataObjects\Concerns;

use Chriha\DataObjects\Attributes\Ignore;
use Illuminate\Contracts\Support\Arrayable;

trait WithArrayableData
{
    /** @var Arrayable<array-key, mixed> */
    #[Ignore]
    protected Arrayable $data;

    /** @param Arrayable<array-key, mixed> $object */
    public function withData(Arrayable $object): self
    {
        $this->data = $object;

        return $this;
    }

    /** @return Arrayable<array-key, mixed> */
    public function data(): Arrayable
    {
        return $this->data;
    }
}
