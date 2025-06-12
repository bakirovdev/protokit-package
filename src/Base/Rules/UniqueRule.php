<?php

namespace App\Rules;

use Bakirov\Protokit\Base\Model\Model;
use Bakirov\Protokit\Base\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class UniqueRule extends Rule
{
    public function __construct(
        private Model $model,
        private bool $fieldIsLocalized,
        private bool $showDetailedErrorMessage = true,
        private ?\Closure $additionalQuery = null,
    ) {}

    public function passes($attribute, $value): bool
    {
        if (gettype($value) !== 'string') return false;

        $attributeArr = explode('.', $attribute);

        if ($this->fieldIsLocalized) {
            $fieldColumn = $attributeArr[count($attributeArr) - 2];
            $fieldPath = end($attributeArr);

            $field = "$fieldColumn->>'$fieldPath'";
        } else {
            $field = end($attributeArr);
        }

        $query = $this->model->query()
            ->where(DB::raw("lower($field)"), mb_strtolower($value))
            ->whereKeyNot($this->model->getKey());

        if (in_array(SoftDeletes::class, class_uses_recursive($this->model))) {
            $query->withTrashed();
        }

        if ($this->additionalQuery) {
            ($this->additionalQuery)($query);
        }

        $modelExists = $query->first();

        if (!$modelExists) return true;

        if ($this->showDetailedErrorMessage) {
            $this->errorMessage = __("validation.unique", [
                'attribute' => $modelExists->getKey(),
            ]);
        } else {
            $this->errorMessage = __('validation.unique');
        }

        return false;
    }
}
