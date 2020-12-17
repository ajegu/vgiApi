<?php


namespace App\Console\Commands;


use App\Database\Repositories\CategoryRepository;
use App\Database\Repositories\LocaleRepository;
use App\Database\Repositories\MonthRepository;
use App\Database\Repositories\SeasonRepository;
use App\Models\Category;
use App\Models\Locale;
use App\Models\LocalizedText;
use App\Models\Month;
use App\Models\Season;
use Illuminate\Console\Command;

class DatabaseSeed extends Command
{
    protected $signature = 'database:seed';

    protected $description = 'Create mandatory items';

    public function __construct(
        private LocaleRepository $localeRepo,
        private SeasonRepository $seasonRepo,
        private MonthRepository $monthRepo,
        private CategoryRepository $categoryRepo,
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->output->note("Start seed items.");
        foreach ($this->getData() as $entity => $data) {
            foreach ($data as $row) {
                switch ($entity){
                    case Locale::ENTITY_NAME:
                        $locale = new Locale($row[0], $row[1]);
                        $this->localeRepo->create($locale);
                        break;
                    case Season::ENTITY_NAME:
                        $season = new Season($row[0], $this->fillNames($row[1]));
                        $this->seasonRepo->create($season);
                        break;
                    case Month::ENTITY_NAME:
                        $month = new Month($row[0], $this->fillNames($row[1]), $row[2]);
                        $this->monthRepo->create($month);
                        break;
                    case Category::ENTITY_NAME:
                        $category = new Category($row[0], $this->fillNames($row[1]));
                        $this->categoryRepo->create($category);
                        break;
                }
            }
        }

        $this->output->note("Seed is done!");
    }

    private function fillNames(array $data): array
    {
        $names = [];
        foreach ($data as $row) {
            $names[] = new LocalizedText($row[0], $row[1]);
        }
        return $names;
    }

    private function getData(): array
    {
        return [
            Locale::ENTITY_NAME => [
                ['en', 'English'],
                ['fr', 'Français'],
            ],
            Season::ENTITY_NAME => [
                [
                    'spr',
                    [
                        ['en', 'Spring'],
                        ['fr', 'Printemps'],
                    ]
                ],
                [
                    'sum',
                    [
                        ['en', 'Summer'],
                        ['fr', 'Eté'],
                    ]
                ],
                [
                    'fal',
                    [
                        ['en', 'Fall'],
                        ['fr', 'Automne'],
                    ]
                ],
                [
                    'win',
                    [
                        ['en', 'Winter'],
                        ['fr', 'Hiver'],
                    ]
                ]
            ],
            Month::ENTITY_NAME => [
                [
                    'jan',
                    [
                        ['en', 'January'],
                        ['fr', 'Janvier'],
                    ],
                    'win'
                ],
                [
                    'feb',
                    [
                        ['en', 'February'],
                        ['fr', 'Février'],
                    ],
                    'win'
                ],
                [
                    'mar',
                    [
                        ['en', 'March'],
                        ['fr', 'Mars'],
                    ],
                    'win'
                ],
                [
                    'apr',
                    [
                        ['en', 'April'],
                        ['fr', 'Avril'],
                    ],
                    'spr'
                ],
                [
                    'may',
                    [
                        ['en', 'May'],
                        ['fr', 'Mai'],
                    ],
                    'spr'
                ],
                [
                    'jun',
                    [
                        ['en', 'June'],
                        ['fr', 'Juin'],
                    ],
                    'spr'
                ],
                [
                    'jul',
                    [
                        ['en', 'July'],
                        ['fr', 'Juillet'],
                    ],
                    'sum'
                ],
                [
                    'aug',
                    [
                        ['en', 'August'],
                        ['fr', 'Août'],
                    ],
                    'sum'
                ],
                [
                    'sep',
                    [
                        ['en', 'September'],
                        ['fr', 'Septembre'],
                    ],
                    'sum'
                ],
                [
                    'oct',
                    [
                        ['en', 'October'],
                        ['fr', 'Octobre'],
                    ],
                    'fal'
                ],
                [
                    'nov',
                    [
                        ['en', 'November'],
                        ['fr', 'Novembre'],
                    ],
                    'fal'
                ],
                [
                    'dec',
                    [
                        ['en', 'December'],
                        ['fr', 'Décembre'],
                    ],
                    'fal'
                ],
            ],
            Category::ENTITY_NAME => [
                [
                    'veg',
                    [
                        ['en', 'Vegetables'],
                        ['fr', 'Légumes'],
                    ]
                ],
                [
                    'fru',
                    [
                        ['en', 'Fruits'],
                        ['fr', 'Fruits'],
                    ]
                ],
                [
                    'leg',
                    [
                        ['en', 'Legume'],
                        ['fr', 'Légumineuse'],
                    ]
                ],
                [
                    'med',
                    [
                        ['en', 'Medicinal plants'],
                        ['fr', 'Plantes médicinales'],
                    ]
                ]
            ]
        ];
    }
}
