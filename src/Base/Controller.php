<?php

namespace Bakirov\Protokit\Base;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Bakirov\Protokit\Base\Model\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function successResponse(int $status = 200): JsonResponse
    {
        return response()->json(['message' => 'Success'], $status);
    }

    public function __construct(
        public Model $model,
        protected ?Search $search = null,
        protected ?Filter $filter = null,
        protected ?string $resourceClass = JsonResource::class,
        protected ?string $requestClass = null
    )
    {
        if ($this->filter) {
            Route::bind('model', function ($value) {
                return $this->filter->process($this->model->query())
                    ->where($this->model->getKeyName(), $value)
                    ->firstOrFail();
            });
        } else {
            Route::model('model', $this->model::class);
        }

        $this->middleware(function ($request, $next){
            if ($this->search !== null) {
                $this->search
                    ->setQuery($this->model->query())
                    ->filter((array) $request->get('filter'))
                    ->with((array) $request->get('with'))
                    ->setPG((int) $request->get('pg'))
                    ->extraQuery();

                if ($this->filter) {
                    $this->filter->process($this->search->query);
                }
            }
            if ($this->requestClass !== null) {
                app()->bind(ValidatesWhenResolved::class, $this->requestClass);
            }
            return $next($request);
        });
    }

    public function index(): Response
    {
        $data = $this->search->query->paginate($this->search->pg);
        return $this->resourceClass::collection($data)->response();
    }

    public function store(ValidatesWhenResolved $request): Response
    {
        $request->model->safelySave($request->validatedData);
        $data = $this->resourceClass::make($request->model);

        return response()->json($data, 201);
    }

    public function update(ValidatesWhenResolved $request): Response
    {
        $request->model->safelySave($request->validatedData);
        $data = $this->resourceClass::make($request->model);

        return response()->json($data, 201);
    }

    public function show(Model $model): Response
    {
        $model = $this->search->query->findOrFail($model->getKey());
        $data = $this->resourceClass::make($model);

        return response()->json($data, 200);
    }

    public function delete(Model $model): Response
    {
        $model->safelyDelete();
        return $this->successResponse();
    }

    public function restore(string $value): Response
    {
        if (!in_array(SoftDeletes::class, class_uses_recursive($this->model))) {
            abort(400, 'Model doesn\'t use soft delete trait');
        }

        $this->search->query->onlyTrashed()->findOrFail($value)->safelyRestore();

        return $this->successResponse();
    }
}
