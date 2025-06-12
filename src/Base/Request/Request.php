<?php

namespace App\Http\Requests;

use Bakirov\Protokit\Base\Model\Model;
use Bakirov\Protokit\Base\Helpers\FileHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{
    public Model $model;
    public array $validatedData;
    protected array $filesSavePaths = [];

    public function __construct()
    {
        $this->model ??= request()->route('model') ?? request()->route()->controller->model;
        parent::__construct();
    }

    protected function localizedRules(): array
    {
        return [];
    }

    protected function nonLocalizedRules(): array
    {
        return [];
    }

    public function rules(): array
    {
        $rules = $this->nonLocalizedRules();

        $localizedRules = $this->localizedRules();
        $languages = array_keys(app('languages')->all);

        foreach ($localizedRules as $key => $rule) {
            foreach ($languages as $language) {
                $rules["$key.$language"] = $rule;
            }
        }
        return $rules;
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $data = parent::validationData();

        $this->merge($data);
    }

    protected function passedValidation(): void
    {
        parent::passedValidation();

        if (method_exists($this, 'additionalValidation')) {
            $this->additionalValidation();

            if ($this->validator->errors()->messages()) {
                $this->failedValidation($this->validator);
            }
        }

        $this->validatedData = $this->validated();
        $this->saveFiles();
    }

    private function saveFiles(): void
    {
        $this->validatedData = Arr::dot($this->validatedData);

        foreach ($this->validatedData as $key => &$value) {
            if (!($value instanceof UploadedFile)) continue;

            $savePath = 'files';
            foreach ($this->filesSavePaths as $field => $fieldPath) {
                if (Str::is($field, $key)) {
                    $savePath = $fieldPath;
                    break;
                }
            }

            $value = $savePath ? FileHelper::upload($value, $savePath) : null;
        }

        $this->validatedData = Arr::undot($this->validatedData);
    }

}
