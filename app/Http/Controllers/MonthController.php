<?php


namespace App\Http\Controllers;


use App\Database\Repositories\MonthRepository;
use App\Exceptions\ItemNotFound;
use App\Mappers\MonthMapper;
use App\Rules\LocaleUnknown;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MonthController extends Controller
{
    public function __construct(
        private MonthRepository $repo,
        private MonthMapper $mapper,
        private LocaleUnknown $localeUnknown
    ) {}

    public function list(): JsonResponse
    {
        return new JsonResponse($this->repo->findAll());
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws ItemNotFound
     */
    public function get(string $id): JsonResponse
    {
        return new JsonResponse($this->repo->findOne($id));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse
    {
        $requestData = $this->validate($request, [
            'id' => ['required', 'string'],
            'names' => ['required', 'array'],
            'names.*.localeId' => [$this->localeUnknown],
            'names.*.name' => ['required', 'string'],
        ]);

        $month = $this->mapper->mapRequestDataToMonth($requestData);

        $this->repo->create($month);

        return new JsonResponse($month, 201);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     * @throws ItemNotFound
     * @throws ValidationException
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $requestData = $this->validate($request, [
            'id' => ['required', 'string'],
            'names' => ['required', 'array'],
            'names.*.localeId' => [$this->localeUnknown],
            'names.*.name' => ['required', 'string']
        ]);

        $lastMonth = $this->repo->findOne($id);
        $nextMonth = $this->mapper->mapRequestDataToMonth($requestData);

        $monthUpdated = $this->repo->update($lastMonth, $nextMonth);

        return new JsonResponse($monthUpdated, 200);
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws ItemNotFound
     */
    public function delete(string $id): JsonResponse
    {
        $month = $this->repo->findOne($id);
        $this->repo->delete($month);
        return new JsonResponse(null, 204);
    }
}
