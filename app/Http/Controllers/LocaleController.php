<?php


namespace App\Http\Controllers;


use App\Database\Repositories\LocaleRepository;
use App\Exceptions\ItemNotFound;
use App\Mappers\LocaleMapper;
use App\Rules\LocaleExists;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LocaleController extends Controller
{

    public function __construct(
        private LocaleRepository $localeRepo,
        private LocaleMapper $localeMapper
    ) {}

    public function list(): JsonResponse
    {
        return new JsonResponse($this->localeRepo->findAll());
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws ItemNotFound
     */
    public function get(string $id): JsonResponse
    {
        return new JsonResponse($this->localeRepo->findOne($id));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse
    {
        $this->validate($request, [
            'id' => [
                'required',
                new LocaleExists($this->localeRepo)
            ],
            'name' => 'required',
        ]);

        $locale = $this->localeMapper->mapRequestDataToLocale($request->all());
        $this->localeRepo->create($locale);

        return new JsonResponse($locale, 201);
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
        $lastLocale = $this->localeRepo->findOne($id);

        $requestData = $this->validate($request, [
            'name' => 'required',
        ]);

        $requestData = array_merge($requestData, ['id' => $lastLocale->getId()]);
        $nextLocale = $this->localeMapper->mapRequestDataToLocale($requestData);
        $locale = $this->localeRepo->update($lastLocale, $nextLocale);

        return new JsonResponse($locale, 200);
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws ItemNotFound
     */
    public function delete(string $id): JsonResponse
    {
        $locale = $this->localeRepo->findOne($id);
        $this->localeRepo->delete($locale);

        return new JsonResponse(null, 204);
    }
}
