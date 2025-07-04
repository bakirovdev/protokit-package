<?php

namespace Bakirov\Protokit\Base\Model;

use Illuminate\Support\Facades\DB;

trait ModelSafelyTrait
{
    public function safelySave(array $attributes = []): void
    {
        $this->safelyDBProcess(function () use ($attributes){
            $this->fill($attributes);
            $this->usesTimestamps() ? $this->touch() : $this->save();
        });
    }

    public function safelyDelete(): void
    {
        $this->safelyDBProcess(function () {
            $this->delete();
        });
    }

    public function safelyRestore(): void
    {
        $this->safelyDBProcess(function () {
            $this->restore();
        });
    }

    public function safelyDBProcess(callable $callback): void
    {
        if (DB::transactionLevel() > 0) {
            $callback();
            return;
        }

        DB::beginTransaction();
        try {
            $callback();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            abort(400, $e->getMessage());
        }
    }
}
