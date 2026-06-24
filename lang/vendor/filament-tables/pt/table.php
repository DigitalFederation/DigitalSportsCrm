<?php

return [

    'column_toggle' => [

        'heading' => 'Colunas',

    ],

    'columns' => [

        'actions' => [
            'label' => 'Acao|Acoes',
        ],

        'text' => [

            'actions' => [
                'collapse_list' => 'Mostrar menos :count',
                'expand_list' => 'Mostrar mais :count',
            ],

            'more_list_items' => 'e mais :count',

        ],

    ],

    'fields' => [

        'bulk_select_page' => [
            'label' => 'Selecionar/desselecionar todos os itens para acoes em massa.',
        ],

        'bulk_select_record' => [
            'label' => 'Selecionar/desselecionar item :key para acoes em massa.',
        ],

        'bulk_select_group' => [
            'label' => 'Selecionar/desselecionar grupo :title para acoes em massa.',
        ],

        'search' => [
            'label' => 'Pesquisar',
            'placeholder' => 'Pesquisar',
            'indicator' => 'Pesquisar',
        ],

    ],

    'summary' => [

        'heading' => 'Resumo',

        'subheadings' => [
            'all' => 'Todos :label',
            'group' => 'Resumo de :group',
            'page' => 'Esta pagina',
        ],

        'summarizers' => [

            'average' => [
                'label' => 'Media',
            ],

            'count' => [
                'label' => 'Contagem',
            ],

            'sum' => [
                'label' => 'Soma',
            ],

        ],

    ],

    'actions' => [

        'disable_reordering' => [
            'label' => 'Terminar reordenacao de registos',
        ],

        'enable_reordering' => [
            'label' => 'Reordenar registos',
        ],

        'filter' => [
            'label' => 'Filtrar',
        ],

        'group' => [
            'label' => 'Agrupar',
        ],

        'open_bulk_actions' => [
            'label' => 'Acoes em massa',
        ],

        'toggle_columns' => [
            'label' => 'Alternar colunas',
        ],

    ],

    'empty' => [

        'heading' => 'Sem :model',

        'description' => 'Crie um :model para comecar.',

    ],

    'filters' => [

        'actions' => [

            'apply' => [
                'label' => 'Aplicar filtros',
            ],

            'remove' => [
                'label' => 'Remover filtro',
            ],

            'remove_all' => [
                'label' => 'Remover todos os filtros',
                'tooltip' => 'Remover todos os filtros',
            ],

            'reset' => [
                'label' => 'Repor',
            ],

        ],

        'heading' => 'Filtros',

        'indicator' => 'Filtros ativos',

        'multi_select' => [
            'placeholder' => 'Todos',
        ],

        'select' => [
            'placeholder' => 'Todos',
        ],

        'trashed' => [

            'label' => 'Registos eliminados',

            'only_trashed' => 'Apenas registos eliminados',

            'with_trashed' => 'Com registos eliminados',

            'without_trashed' => 'Sem registos eliminados',

        ],

    ],

    'grouping' => [

        'fields' => [

            'group' => [
                'label' => 'Agrupar por',
                'placeholder' => 'Agrupar por',
            ],

            'direction' => [

                'label' => 'Direcao do agrupamento',

                'options' => [
                    'asc' => 'Ascendente',
                    'desc' => 'Descendente',
                ],

            ],

        ],

    ],

    'reorder_indicator' => 'Arraste e largue os registos para ordenar.',

    'selection_indicator' => [

        'selected_count' => '1 registo selecionado|:count registos selecionados',

        'actions' => [

            'select_all' => [
                'label' => 'Selecionar todos :count',
            ],

            'deselect_all' => [
                'label' => 'Desselecionar todos',
            ],

        ],

    ],

    'sorting' => [

        'fields' => [

            'column' => [
                'label' => 'Ordenar por',
            ],

            'direction' => [

                'label' => 'Direcao da ordenacao',

                'options' => [
                    'asc' => 'Ascendente',
                    'desc' => 'Descendente',
                ],

            ],

        ],

    ],

];
