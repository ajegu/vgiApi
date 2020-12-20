<?php


namespace App\Http\Controllers;


use App\Database\Repositories\CategoryRepository;
use App\Database\Repositories\FoodRepository;
use App\Database\Repositories\MonthRepository;
use App\Exceptions\ItemNotFound;
use App\Mappers\FoodMapper;
use App\Rules\CategoryUnknown;
use App\Rules\LocaleUnknown;
use App\Rules\MonthUnknown;
use App\Storage\ClientAdapter;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FoodController extends Controller
{
    public function __construct(
        private LocaleUnknown $localeUnknown,
        private CategoryUnknown $categoryUnknown,
        private MonthUnknown $monthUnknown,
        private ClientAdapter $clientAdapter,
        private FoodMapper $mapper,
        private FoodRepository $repo,
        private MonthRepository $monthRepo,
        private CategoryRepository $categoryRepo,
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
     * @throws FileNotFoundException
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse
    {
        $requestData = $this->validate($request, [
            'names' => ['required', 'array'],
            'names.*.localeId' => [$this->localeUnknown],
            'names.*.name' => ['required', 'string'],
            'descriptions' => ['array'],
            'descriptions.*.localeId' => [$this->localeUnknown],
            'descriptions.*.name' => ['required', 'string'],
            'categoryId' => ['required', $this->categoryUnknown],
            'image' => ['required', 'file'],
            'monthIdList' => ['required', 'array', $this->monthUnknown]
        ]);

        $requestData['imageName'] = $this->clientAdapter->putObject($requestData['image']);

        $food = $this->mapper->mapRequestDataToFood($requestData);
        $food = $this->repo->create($food);

        return new JsonResponse($food, 201);
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
        $lastFood = $this->repo->findOne($id);

        $requestData = $this->validate($request, [
            'names' => ['required', 'array'],
            'names.*.localeId' => [$this->localeUnknown],
            'names.*.name' => ['required', 'string'],
            'descriptions' => ['array'],
            'descriptions.*.localeId' => [$this->localeUnknown],
            'descriptions.*.name' => ['required', 'string'],
            'categoryId' => ['required', $this->categoryUnknown],
            'monthIdList' => ['required', 'array', $this->monthUnknown]
        ]);

        $requestData['id'] = $lastFood->getId();
        $requestData['imageName'] = $lastFood->getImageName();
        $requestData['image'] = $lastFood->getImage();

        $nextFood = $this->mapper->mapRequestDataToFood($requestData);
        $foodUpdated = $this->repo->update($lastFood, $nextFood);

        return new JsonResponse($foodUpdated, 200);
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws ItemNotFound
     */
    public function delete(string $id): JsonResponse
    {
        $food = $this->repo->findOne($id);
        $this->repo->delete($food);
        $this->clientAdapter->deleteObject($food->getImageName());
        return new JsonResponse(null, 204);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     * @throws FileNotFoundException
     * @throws ItemNotFound
     * @throws ValidationException
     */
    public function updateImage(Request $request, string $id): JsonResponse
    {
        $food = $this->repo->findOne($id);

        $requestData = $this->validate($request, [
            'image' => ['file', 'required']
        ]);

        $this->clientAdapter->deleteObject($food->getImageName());
        $imageName = $this->clientAdapter->putObject($requestData['image']);

        $food = $this->repo->updateImage($food, $imageName);

        return new JsonResponse($food, 200);
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws ItemNotFound
     */
    public function listByCategory(string $id): JsonResponse
    {
        $category = $this->categoryRepo->findOne($id);
        $foodList = $this->repo->findAllByCategory($category);

        return new JsonResponse($foodList, 200);
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws ItemNotFound
     */
    public function listByMonth(string $id): JsonResponse
    {
        $month = $this->monthRepo->findOne($id);
        $foodList = $this->repo->findAllByMonth($month);

        return new JsonResponse($foodList, 200);
    }
}
