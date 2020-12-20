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
            $names[] = new LocalizedText(
                name: $row[1],
                localeId: $row[0]
            );
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
                    '01-spr',
                    [
                        ['en', 'Spring'],
                        ['fr', 'Printemps'],
                    ]
                ],
                [
                    '02-sum',
                    [
                        ['en', 'Summer'],
                        ['fr', 'Eté'],
                    ]
                ],
                [
                    '03-fal',
                    [
                        ['en', 'Fall'],
                        ['fr', 'Automne'],
                    ]
                ],
                [
                    '04-win',
                    [
                        ['en', 'Winter'],
                        ['fr', 'Hiver'],
                    ]
                ]
            ],
            Month::ENTITY_NAME => [
                [
                    '01-jan',
                    [
                        ['en', 'January'],
                        ['fr', 'Janvier'],
                    ],
                    'win'
                ],
                [
                    '02-feb',
                    [
                        ['en', 'February'],
                        ['fr', 'Février'],
                    ],
                    'win'
                ],
                [
                    '03-mar',
                    [
                        ['en', 'March'],
                        ['fr', 'Mars'],
                    ],
                    'win'
                ],
                [
                    '04-apr',
                    [
                        ['en', 'April'],
                        ['fr', 'Avril'],
                    ],
                    'spr'
                ],
                [
                    '05-may',
                    [
                        ['en', 'May'],
                        ['fr', 'Mai'],
                    ],
                    'spr'
                ],
                [
                    '06-jun',
                    [
                        ['en', 'June'],
                        ['fr', 'Juin'],
                    ],
                    'spr'
                ],
                [
                    '07-jul',
                    [
                        ['en', 'July'],
                        ['fr', 'Juillet'],
                    ],
                    'sum'
                ],
                [
                    '08-aug',
                    [
                        ['en', 'August'],
                        ['fr', 'Août'],
                    ],
                    'sum'
                ],
                [
                    '09-sep',
                    [
                        ['en', 'September'],
                        ['fr', 'Septembre'],
                    ],
                    'sum'
                ],
                [
                    '10-oct',
                    [
                        ['en', 'October'],
                        ['fr', 'Octobre'],
                    ],
                    'fal'
                ],
                [
                    '11-nov',
                    [
                        ['en', 'November'],
                        ['fr', 'Novembre'],
                    ],
                    'fal'
                ],
                [
                    '12-dec',
                    [
                        ['en', 'December'],
                        ['fr', 'Décembre'],
                    ],
                    'fal'
                ],
            ],
            Category::ENTITY_NAME => [
                [
                    '01-veg',
                    [
                        ['en', 'Vegetables'],
                        ['fr', 'Légumes'],
                    ]
                ],
                [
                    '02-fru',
                    [
                        ['en', 'Fruits'],
                        ['fr', 'Fruits'],
                    ]
                ],
                [
                    '03-leg',
                    [
                        ['en', 'Legume'],
                        ['fr', 'Légumineuse'],
                    ]
                ],
                [
                    '04-med',
                    [
                        ['en', 'Medicinal plants'],
                        ['fr', 'Plantes médicinales'],
                    ]
                ]
            ]
        ];
    }
}
