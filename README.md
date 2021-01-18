# Datables Using Laravel's Pagination format

[![Latest Version on Packagist](https://img.shields.io/packagist/v/savannabits/laravel-pagetables.svg?style=flat-square)](https://packagist.org/packages/savannabits/laravel-pagetables)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/savannabits/laravel-pagetables/run-tests?label=tests)](https://github.com/savannabits/laravel-pagetables/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/savannabits/laravel-pagetables.svg?style=flat-square)](https://packagist.org/packages/savannabits/laravel-pagetables)

Generate Laravel pagination with support to search, sort, per_page and page queries. The Datatables support searching and ordering by children/parent relationships e.g user.role.name.

If you are on Vue.js, we recommend using the **[Pagetables Component](https://github.com/savannabits/pagetables)** which renders nice datatables styled in tailwindcss and supports the payload from this package out of the box.
## Installation

You can install the package via composer:

```bash
composer require savannabits/laravel-pagetables
```
## Usage
Under the hood, this package uses laravel pagination and returns a LengthAwarePaginator object together with the columns specified.
The package has a Column class which helps to construct columns with names and other properties for the sake of frontend rendering and formatting.
Use the package in three simple steps:
1. Specify the columns as Savannabits\Pagetables\Column[] array.
2. Create the datatables from a query, passing in the columns array from step 1.
3. Query the controller method from the frontend with parameters such as `search`,`sort`,`per_page`, `sort_direction` and `page`

**Example**
In the Controller:
```php
use Savannabits\Pagetables\Column;
use Savannabits\Pagetables\Pagetables;
use App\Models\Article;
$columns = [
            Column::name("id")->title("ID")->sortDesc()->searchable(),
            Column::name("slug")->title("Slug")->sort()->searchable(),
            Column::name("name")->title("Name")->sort()->searchable(),
            Column::name("weighted_cost")->title("Weighted Cost")->sort()->searchable(),
            Column::name("itemGroup.name")->title("Group")->sort()->searchable(),
            Column::name("actions")->title("")->raw(true),
        ];
$dt = Pagetables::of(Article::where("enabled", "=",true))
            ->columns($columns)
            ->make(true);
return response()->json($dt);
```
API Request from your client:
```http request
GET https://myserver.test/api/articles?search=&per_page=10&sort=weighted_cost&sort_direction=asc&page=1
```

Response JSON:

```json
  {
    "current_page": 1,
    "data": [
        {
            "id": 1949,
            "slug": "mocha-frappe-500-ml-pa",
            "name": "MOCHA FRAPPE 500 ML (PA)",
            "description": "Created while importing Recipe .MOCHA FRAPPE 500 ML from Micros by smaosa at 2019-12-19 16:52:15",
            "article_type_id": 1,
            "item_group_id": 1,
            "default_depot_id": 44,
            "derived_unit_id": 22,
            "last_purchase_price": 0,
            "last_ordered_quantity": 0,
            "last_order_time": null,
            "lifespan_days": 2,
            "is_product": true,
            "last_received_quantity": 0,
            "last_receiving_price": 0,
            "last_received_at": null,
            "weighted_cost": 0,
            "enabled": true,
            "created_at": "2019-12-19 16:52:15",
            "updated_at": "2020-08-31 20:41:19",
            "is_consumable": 0,
            "api_route": "https://myserver.test/api/articles",
            "item_group": {
                "id": 1,
                "slug": "bakery-products",
                "name": "Bakery Products",
                "description": "Assigned to Profit Contribution. Imported at 2019-12-19 13:59:43 by smaosa",
                "enabled": true,
                "major_group_id": 6,
                "created_at": "2019-12-19 13:59:43",
                "updated_at": "2020-08-31 20:41:18",
                "api_route": "https://myserver.test/api/item-groups"
            }
        },
        {"id": 1950 },
        {"id": 1951 }
    ],
    "first_page_url": "https://myserver.test/api/articles?page=1",
    "from": 1,
    "last_page": 189,
    "last_page_url": "https://myserver.test/api/articles?page=189",
    "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "https://myserver.test/api/articles?page=1",
            "label": 1,
            "active": true
        },
        {
            "url": "https://myserver.test/api/articles?page=2",
            "label": 2,
            "active": false
        },
        {
            "url": "https://myserver.test/api/articles?page=3",
            "label": 3,
            "active": false
        },
        {
            "url": "https://myserver.test/api/articles?page=4",
            "label": 4,
            "active": false
        },
        {
            "url": "https://myserver.test/api/articles?page=5",
            "label": 5,
            "active": false
        },
        {
            "url": "https://myserver.test/api/articles?page=6",
            "label": 6,
            "active": false
        },
        {
            "url": "https://myserver.test/api/articles?page=7",
            "label": 7,
            "active": false
        },
        {
            "url": "https://myserver.test/api/articles?page=8",
            "label": 8,
            "active": false
        },
        {
            "url": "https://myserver.test/api/articles?page=9",
            "label": 9,
            "active": false
        },
        {
            "url": "https://myserver.test/api/articles?page=10",
            "label": 10,
            "active": false
        },
        {
            "url": null,
            "label": "...",
            "active": false
        },
        {
            "url": "https://myserver.test/api/articles?page=188",
            "label": 188,
            "active": false
        },
        {
            "url": "https://myserver.test/api/articles?page=189",
            "label": 189,
            "active": false
        },
        {
            "url": "https://myserver.test/api/articles?page=2",
            "label": "Next &raquo;",
            "active": false
        }
    ],
    "next_page_url": "https://myserver.test/api/articles?page=2",
    "path": "https://myserver.test/api/articles",
    "per_page": "10",
    "prev_page_url": null,
    "to": 10,
    "total": 1884,
    "columns": [
        {
            "title": "ID",
            "name": "id",
            "raw": false,
            "sortable": true,
            "searchable": true,
            "sort_key": "id",
            "search_key": "id",
            "sort_direction": "desc"
        },
        {
            "title": "Slug",
            "name": "slug",
            "raw": false,
            "sortable": true,
            "searchable": true,
            "sort_key": "slug",
            "search_key": "slug",
            "sort_direction": "asc"
        },
        {
            "title": "Name",
            "name": "name",
            "raw": false,
            "sortable": true,
            "searchable": true,
            "sort_key": "name",
            "search_key": "name",
            "sort_direction": "asc"
        },
        {
            "title": "Weighted Cost",
            "name": "weighted_cost",
            "raw": false,
            "sortable": true,
            "searchable": true,
            "sort_key": "weighted_cost",
            "search_key": "weighted_cost",
            "sort_direction": "asc"
        },
        {
            "title": "Group",
            "name": "itemGroup.name",
            "raw": false,
            "sortable": true,
            "searchable": true,
            "sort_key": "itemGroup.name",
            "search_key": "itemGroup.name",
            "sort_direction": "asc"
        },
        {
            "title": "",
            "name": "actions",
            "raw": true,
            "sortable": false,
            "searchable": false,
            "sort_key": "actions",
            "search_key": "actions",
            "sort_direction": "asc"
        }
    ]
}
```
From here, how you use the payload to render the datatable is entirely up to you.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Sam Arossi Maosa](https://github.com/coolsam726)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
