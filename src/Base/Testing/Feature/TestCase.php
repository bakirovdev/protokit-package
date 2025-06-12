<?php

namespace Bakirov\Protokit\Testing\Feature;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Testing\File;
use Illuminate\Testing\TestResponse;
use Bakirov\Protokit\Base\Testing\CreateApplicationTrait;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as TestingTestCase;

class TestCase extends TestingTestCase
{
    use CreateApplicationTrait;
    use DatabaseTransactions;

    public int $userId;

    protected array $requestHeaders = [];

    private array $allRequestHeaders = [
        'Accept' => 'application/json',
        'Accept-Language' => 'en'
    ];

    private string $requestMethod;
    public string $requestUrl;
    private array $requestQuery = [];
    private string $requestQueryToString;

    private array $requestBody = [];
    private array $requestFiles = [];

    public TestResponse $response;

    public function sendRequest(
        string $method = 'GET',
        string $path = '',
        array $query = [],
        array $body = [],
        int $assertStatus = 200
    ):void {
        $this->requestMethod = $method;
        $this->requestUrl .= $path ? "/$path" : '';
        $this->requestQuery = $query;
        $this->requestBody = $body;

        $this->response = $this->sendRequestAndPrepareData();

        try {
            $this->response->assertStatus($assertStatus);
        } catch (\Throwable $th) {
            $response = $this->response->baseResponse;

            $content = $response->getContent();
            $content = json_decode($content);
            if ($content->trace) {
                $content->trace = array_slice($content->trace, 0,5);
            }
            $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            $response->setContent($content);
            throw new \Exception($response);
        }
    }

    private function sendRequestAndPrepareData(): TestResponse
    {
        if (in_array($this->requestMethod, ['PUT', 'PATCH'])) {
            $this->requestQuery['_method'] = $this->requestMethod;
            $this->requestMethod = 'POST';
        }
        $this->requestQueryToString = http_build_query($this->requestQuery);
        $this->allRequestHeaders = array_merge($this->allRequestHeaders, $this->requestHeaders);

        if (isset($this->userId)) {
            $this->allRequestHeaders['Authorization'] = '<TOKEN>';
        }

        return $this->call(
            method: $this->requestMethod,
            uri: $this->requestQueryToString ? "$this->requestUrl?$this->requestQueryToString" : $this->requestUrl,
            parameters: $this->requestBody,
            server: $this->transformHeadersToServerVars($this->allRequestHeaders)
        );
    }

    public function callBeforeApplicationDestroyedCallbacks():void
    {
        $hasFile = $this->fileChecker($this->requestBody);
        $this->requestQuery = $this->prepareHttpQuery($this->requestQuery);
        $this->requestBody = $this->convertDataToSingleDimensionalArray($this->requestBody);
        $this->requestFiles = $this->convertDataToSingleDimensionalArray($this->requestFiles);

        //get target test file
        $target = $this->provides()[0]->getTarget();
        $targetPath = preg_replace('/^Http\\\|Tests\\\|test_/', '', $target);
        $targetPath = str_replace(['\\', '::'], '.', $targetPath);
        $targetPath = str_replace('___', ' | ', $targetPath);

        //getting target file Class
        $targetArr = explode('::', $target);
        $targetClass = new \ReflectionClass($targetArr[0]);

        //plucking comments
        $comment = explode("\n", $targetClass->getDocComment());
        $comment = array_map(fn ($value) => ltrim($value, '/*'), $comment);
        $comment = implode("\n", $comment);
        $comment = trim($comment);

        //Prepare data
        $fileName = base_path('storage/protokit/tests/_postman.json');
        $items = is_file($fileName) ? file_get_contents($fileName) : '[]';
        $items = json_decode($items, true);

        $baseResponse = $this->response->baseResponse;

        if ($baseResponse instanceof StreamedResponse) {
            $responseData = [
                'headers' => $baseResponse->headers->allPreserveCase(),
                'body' => 'Binary data',
                'status' => [
                    'code' => 200,
                    'text' => 'OK'
                ]
            ];
        } else {
            $responseData = [
                'headers' => $baseResponse->headers->allPreserveCase(),
                'body' => json_decode($baseResponse->content(), true),
                'status' => [
                    'code' => $baseResponse->status(),
                    'text' => $baseResponse->statusText(),
                ]
            ];
        }
        $items[$targetPath] = [
            'is_request' => true,
            'request' => [
                'headers' => $this->allRequestHeaders,
                'body' => $hasFile ? $this->requestBody : ['mode' => 'raw', 'raw' => $this->requestBody],
                'files' => $this->requestFiles,
                'method' => $this->requestMethod,
                'url' => [
                    'path' => $this->requestUrl,
                    'query' => $this->requestQuery,
                    'raw' => $this->requestQueryToString ? "$this->requestUrl?$this->requestQueryToString": $this->requestUrl,
                ],
            ],
            'response' => $responseData,
            'comment' => $comment,
        ];

        // Saving to file
        $items = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        file_put_contents($fileName, $items);

        parent::callBeforeApplicationDestroyedCallbacks();
    }

    private function prepareHttpQuery(array $query): array
    {
        if (!$query) return [];

        $query = http_build_query($query);
        $query = urldecode($query);
        $query = explode('&', $query);

        $query = array_map(function($value) {
            $value = explode('=', $value);

            return [
                'key' => $value[0] ?? '',
                'value' => $value[1] ?? ''
            ];
        }, $query);
        $query = Arr::pluck($query, 'value', 'key');

        return $query;
    }

    private function convertDataToSingleDimensionalArray(array $data): array
    {
        $result = Arr::map($data, function ($value, $key) {
            $key = Str::replaceFirst('.', '[', $key);
            $key = str_replace('.', '][', $key);
            $key = str_contains($key, '[') ? "$key]" : $key;

            return [
                'key' => $key,
                'value' => $value,
            ];
        });

        $result = Arr::pluck($result, 'value', 'key');
        return $result;
    }

    private function fileChecker(): bool
    {
        $hasFile = false;
        foreach ($this->requestBody as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key => $arrayItem) {
                    if ($arrayItem instanceof File) $hasFile = true;
                }

                if ($hasFile === true) break;
            }

            if ($value instanceof File) {
                $hasFile = true;
            }
        }

        if ($hasFile) {
            $this->requestBody = Arr::dot($this->requestBody);
            foreach ($this->requestBody as $key => $value) {
                if ($value instanceof File) {
                    $this->requestFiles[$key] = $value->name;
                    unset($this->requestBody[$key]);
                }
            }
        }

        return $hasFile;
    }

}
