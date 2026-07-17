<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'federation_dashboard' => 'Painel da Federação',
    'admin_dashboard' => 'Painel de Administração',
    'federation' => 'Federação',
    'entity_billing_title' => 'Total por Entidade',
    'entity_name' => 'Entidade',
    'district' => 'Distrito',
    'total_billed' => 'Total (EUR)',
    'no_billing_data' => 'Sem dados de faturação.',
    'entity_billing_explanation' => 'Total calculado a partir de todos os documentos pagos no ano corrente, incluindo: planos de filiação, planos de seguros, inscrições em eventos, certificações, licenças e encomendas manuais.',
    'entity_billing_affiliation_desc' => 'Total de documentos pagos de planos de filiação de membros individuais e entidades solicitados por cada entidade no ano corrente.',
    'monthly_affiliation_revenue' => 'Receita Mensal de Filiações',
    'annual_entity_affiliation_revenue' => 'Receita Anual de Planos de Filiação das Entidades',
    'annual_individual_affiliation_revenue' => 'Receita Anual de Planos de Filiação dos Individuais',
    'revenue_eur' => 'Receita (EUR)',
    'entities_by_district' => 'Entidades por Distrito',
    'entity_count' => 'Número de Entidades',
    'pending_entity_approvals' => 'Entidades para Aprovar',
    'pending_individual_approvals' => 'Membros Individuais para Aprovar',
    'view_all' => 'Ver Todas',
    'no_pending' => 'Sem aprovações pendentes.',
    'requested_at' => 'Solicitado',

    // Individual Members Distribution Table
    'members_distribution_title' => 'Distribuição de Membros Individuais por Distrito',
    'members_distribution_desc' => "Numero de membros individuais total de {$primaryShortName} e filiados ativos na associacao territorial.",
    'age_group' => 'Faixa Etária / Género',
    'total' => 'Total',
    'registered' => 'Reg.',
    'affiliated' => 'Fil.',
    'female_up_to_12' => 'Feminino até 12 anos',
    'male_up_to_12' => 'Masculino até 12 anos',
    'female_13_to_17' => 'Feminino 13 a 17 anos',
    'male_13_to_17' => 'Masculino 13 a 17 anos',
    'female_18_to_45' => 'Feminino 18 a 45 anos',
    'male_18_to_45' => 'Masculino 18 a 45 anos',
    'female_46_plus' => 'Feminino 46+ anos',
    'male_46_plus' => 'Masculino 46+ anos',
    'no_members_data' => 'Sem dados de membros disponíveis.',
    'members_registered_help' => 'Membros registados na plataforma',
    'members_affiliated_help' => 'Membros com filiação ativa',

    // Welcome Banner
    'welcome_back' => 'Bem-vindo de volta',
    'federation_overview' => 'Resumo da atividade da sua federação',
    'member_number' => 'N.º Filiado',
    'id_number' => 'N.º ID',

    // Stats Widget
    'individual_members' => 'Membros Individuais',
    'total_in_federation' => 'Total na federação',
    'active_affiliations' => 'Filiações ativas',
    'collective_entities' => 'Entidades Coletivas',

    // Recent Activity
    'recent_actions' => 'Ações Recentes',

    // Licenses by Sport Chart
    'licenses_by_sport_heading' => ':role por Desporto (:year)',
    'active_licenses' => 'Licenças Ativas',
    'count' => 'Quantidade',
    'role_athlete' => 'Atletas',
    'role_coach' => 'Treinadores',
    'role_technical_official' => 'Oficiais Técnicos',

    // Territorial Affiliations Table
    'territorial_affiliation_income' => 'Receita de Filiações por Associação Territorial (:year)',
    'territorial_association' => 'Associação Territorial',
    'no_data' => 'Sem dados para apresentar.',
    'grand_total' => 'Total Geral',

    // Entities by District Chart
    'entity_count_label' => 'Número de Entidades',

    // Individuals by District Chart
    'individuals_by_district' => 'Membros Individuais por Distrito',
    'total_active_individual_members' => 'Número total de membros individuais ativos',
    'individual_count_label' => 'Número de Membros',

    // Chart Labels (Total vs Active)
    'total_registered' => 'Total',
    'active_members' => 'Ativos',
    'active_entities' => 'Ativas',
    'total_active_entities' => 'Número total de entidades ativas',

    // Chart Descriptions
    'entity_affiliation_revenue_desc' => 'Receita mensal de planos de filiação de entidades',
    'individual_affiliation_revenue_desc' => 'Receita mensal de planos de filiação de individuais',
    'entity_license_revenue_desc' => 'Receita mensal de compras de licenças de entidades',
    'individual_license_revenue_desc' => 'Receita mensal de compras de licenças de individuais',
    'individual_sport_licenses_desc' => 'Licenças desportivas ativas por função (atletas, treinadores, oficiais)',
    'entity_sport_licenses_desc' => 'Licenças desportivas ativas detidas por clubes',

    // Stats Overview
    'total_description' => 'Total',

    // Dashboard Sections
    'statistics_section' => 'Estatísticas',
    'ranking' => 'Ranking',
    'logo' => 'Logo',

    // License Revenue Charts
    'annual_entity_license_revenue' => 'Receita Anual de Licenças de Entidades',
    'annual_individual_license_revenue' => 'Receita Anual de Licenças de Individuais',

    // Sport License Charts
    'individual_sport_licenses_by_role' => 'Licenças de Individuais de Desporto',
    'entity_sport_licenses' => 'Licenças de Clubes Desportivos',
    'license_count' => 'Número de Licenças',

    // Info Messages
    'license_revenue_organization_only' => 'Os resultados apresentados são relativamente apenas às licenças da sua organização.',

    // Monthly Payments Table
    'monthly_payments_title' => 'Pagamentos Mensais',
    'monthly_payments_desc' => 'Total de documentos pagos por categoria e mês',
    'year' => 'Ano',
    'category' => 'Categoria',
    'entity_affiliations' => 'Filiações de Entidades',
    'individual_affiliations' => 'Filiações de Individuais',
    'entity_licenses' => 'Licenças de Entidades',
    'individual_licenses' => 'Licenças de Individuais',
    'event_registrations' => 'Inscrições em Eventos',
    'certifications' => 'Certificações',
    'entity_insurances' => 'Seguros de Entidades',
    'individual_insurances' => 'Seguros de Individuais',
    'others' => 'Outros',
    'month_jan' => 'Jan',
    'month_feb' => 'Fev',
    'month_mar' => 'Mar',
    'month_apr' => 'Abr',
    'month_may' => 'Mai',
    'month_jun' => 'Jun',
    'month_jul' => 'Jul',
    'month_aug' => 'Ago',
    'month_sep' => 'Set',
    'month_oct' => 'Out',
    'month_nov' => 'Nov',
    'month_dec' => 'Dez',
];
