<?php return array (
  'ajthinking/archetype' => 
  array (
    'providers' => 
    array (
      0 => 'Archetype\\ServiceProvider',
    ),
    'dont-discover' => 
    array (
    ),
  ),
  'aryehraber/statamic-captcha' => 
  array (
    'providers' => 
    array (
      0 => 'AryehRaber\\Captcha\\CaptchaServiceProvider',
    ),
  ),
  'barryvdh/laravel-debugbar' => 
  array (
    'aliases' => 
    array (
      'Debugbar' => 'Barryvdh\\Debugbar\\Facades\\Debugbar',
    ),
    'providers' => 
    array (
      0 => 'Barryvdh\\Debugbar\\ServiceProvider',
    ),
  ),
  'intervention/image' => 
  array (
    'aliases' => 
    array (
      'Image' => 'Intervention\\Image\\Facades\\Image',
    ),
    'providers' => 
    array (
      0 => 'Intervention\\Image\\ImageServiceProvider',
    ),
  ),
  'laravel/tinker' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Tinker\\TinkerServiceProvider',
    ),
  ),
  'nesbot/carbon' => 
  array (
    'providers' => 
    array (
      0 => 'Carbon\\Laravel\\ServiceProvider',
    ),
  ),
  'nunomaduro/collision' => 
  array (
    'providers' => 
    array (
      0 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    ),
  ),
  'nunomaduro/termwind' => 
  array (
    'providers' => 
    array (
      0 => 'Termwind\\Laravel\\TermwindServiceProvider',
    ),
  ),
  'pecotamic/sitemap' => 
  array (
    'providers' => 
    array (
      0 => 'Pecotamic\\Sitemap\\ServiceProvider',
    ),
  ),
  'rebing/graphql-laravel' => 
  array (
    'aliases' => 
    array (
      'GraphQL' => 'Rebing\\GraphQL\\Support\\Facades\\GraphQL',
    ),
    'providers' => 
    array (
      0 => 'Rebing\\GraphQL\\GraphQLServiceProvider',
    ),
  ),
  'rocketeers-app/rocketeers-api-client' => 
  array (
    'aliases' => 
    array (
      'Rocketeers' => 'Rocketeers\\Facades\\RocketeersFacade',
    ),
    'providers' => 
    array (
      0 => 'Rocketeers\\RocketeersServiceProvider',
    ),
  ),
  'rocketeers-app/rocketeers-laravel' => 
  array (
    'aliases' => 
    array (
      'RocketeersLogger' => 'Rocketeers\\Laravel\\Facades\\RocketeersLoggerFacade',
    ),
    'providers' => 
    array (
      0 => 'Rocketeers\\Laravel\\RocketeersLoggerServiceProvider',
    ),
  ),
  'statamic/cms' => 
  array (
    'aliases' => 
    array (
      'Statamic' => 'Statamic\\Statamic',
    ),
    'providers' => 
    array (
      0 => 'Statamic\\Providers\\StatamicServiceProvider',
    ),
  ),
  'stillat/blade-parser' => 
  array (
    'providers' => 
    array (
      0 => 'Stillat\\BladeParser\\ServiceProvider',
      1 => 'Stillat\\BladeParser\\Providers\\ValidatorServiceProvider',
    ),
  ),
  'wilderborn/partyline' => 
  array (
    'aliases' => 
    array (
      'Partyline' => 'Wilderborn\\Partyline\\Facade',
    ),
    'providers' => 
    array (
      0 => 'Wilderborn\\Partyline\\ServiceProvider',
    ),
  ),
);