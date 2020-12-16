<?php


namespace App\Http\Controllers;


use App\Database\Repositories\SeasonRepository;
use App\Exceptions\ItemNotFound;
use App\Mappers\SeasonMapper;
use App\Rules\LocaleUnknown;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SeasonController extends Controller
{
    public function __construct(
        private SeasonRepository $repo,
        private SeasonMapper $mapper,
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

        $season = $this->mapper->mapRequestDataToSeason($requestData);

        $this->repo->create($season);

        return new JsonResponse($season, 201);
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

        $last = $this->repo->findOne($id);
        $next = $this->mapper->mapRequestDataToSeason($requestData);

        $updated = $this->repo->update($last, $next);

        return new JsonResponse($updated, 200);
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws ItemNotFound
     */
    public function delete(string $id): JsonResponse
    {
        $season = $this->repo->findOne($id);
        $this->repo->delete($season);
        return new JsonResponse(null, 204);
    }
}
