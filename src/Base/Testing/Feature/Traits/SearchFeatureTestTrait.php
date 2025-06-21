<?php

namespace Bakirov\Protokit\Base\Testing\Feature\Traits;

use Bakirov\Protokit\Base\Search;

trait SearchFeatureTestTrait
{
    protected Search $search;

    public function test_available_relations(): void
    {
        $this->search = new ($this->searchClass)();

        $this->sendRequest(
            query: ['with' => $this->search->relations],
        );
    }

    public function test_show_with_deleted(): void
    {
        $this->sendRequest(
            query: ['show' => ['with-deleted']],
        );
    }

    public function test_show_only_deleted(): void
    {
        $this->sendRequest(
            query: ['show' => ['only-deleted']],
        );
    }

    public function test_pagination(): void
    {
        $this->search = new ($this->searchClass)();

        $this->sendRequest(
            query: [
                'page-size' => $this->search->pg,
                'page' => 1,
            ],
        );
    }

    private function sendRequestWithFilters(array $params): void
    {
        $this->sendRequest(
            query: ['filter' => $params],
        );
    }
}
