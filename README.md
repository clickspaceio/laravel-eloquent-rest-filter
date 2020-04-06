# Laravel Eloquent Rest Filter




## Instalação

1. O pacote deve ser instalado pelo [Composer](https://getcomposer.org/). Para instalar, basta executar o comando abaixo.

    ```sh
    composer require clickspaceio/laravel-eloquent-rest-filter
    ```
    
2. Nos métodos dos Controllers com retorno de listagem, seguir o exemplo abaixo:

    ```
    $filter = new Filter([ // filtros ativos
        'name' => StringFilter::class,
        'description' => StringFilter::class,
        'hidden' => BooleanFilter::class,
        'default' => BooleanFilter::class,
        'acl' => JsonFilter::class,
        'metadata' => JsonFilter::class,
        'created_at' => DateFilter::class,
        'updated_at' => DateFilter::class
    ], [ // possibilidades de ordenação (ASC/DESC)
        'name',
        'description',
        'hidden',
        'default',
        'created_at',
        'updated_at'
    ], [ // campos para busca geral (?q=)
        'name',
        'description',
        'metadata'
    ]);

    $query = Catalog::query();

    $query->whereHas('account', function ($query) use ($accountId) {
        $query->where('id', $accountId);
    });

    $query = $filter->apply($query);

    $accounts = $query->paginate($filter->limit);

    return CatalogResource::collection($accounts);
    ```