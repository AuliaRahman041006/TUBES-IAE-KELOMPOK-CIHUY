<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Controls the HTTP route that your GraphQL server responds to.
    | You may set `route` => false, to disable the default route
    | registration and take full control.
    |
    */

    'route' => [
        /*
         * The URI the endpoint responds to, e.g. mydomain.com/graphql.
         */
        'uri' => '/graphql',

        /*
         * Lighthouse creates a named route for convenient URL generation and redirects.
         */
        'name' => 'graphql',

        /*
         * Beware that middleware defined here runs before the GraphQL execution phase,
         * make sure to return spec-compliant responses in every case.
         */
        'middleware' => [
            \App\Http\Middleware\CorsMiddleware::class,
        ],

        /*
         * The `prefix` and `domain` configuration options are optional.
         */
        'prefix' => '',
        // 'domain' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Location
    |--------------------------------------------------------------------------
    |
    | Path to your .graphql schema file.
    | Additional schema files may be imported through #import directives.
    |
    */

    'schema_path' => base_path('graphql/schema.graphql'),

    /*
    |--------------------------------------------------------------------------
    | Schema Cache
    |--------------------------------------------------------------------------
    |
    | A large part of schema generation consists of parsing and AST manipulation.
    | This operation is very expensive, so it is highly recommended to enable
    | caching of the final schema to optimize performance of every request.
    |
    */

    'schema_cache' => [
        'enable' => env('LIGHTHOUSE_SCHEMA_CACHE', env('APP_ENV') !== 'local'),
        'path' => env('LIGHTHOUSE_SCHEMA_CACHE_PATH', base_path('bootstrap/cache/lighthouse-schema.php')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    | These are the default namespaces where Lighthouse looks for classes to
    | extend functionality of the schema. You may pass in either a string
    | or an array, they are tried in order and the first match is used.
    |
    */

    'namespaces' => [
        'models' => ['App\\Models'],
        'queries' => 'App\\GraphQL\\Queries',
        'mutations' => 'App\\GraphQL\\Mutations',
        'subscriptions' => 'App\\GraphQL\\Subscriptions',
        'interfaces' => 'App\\GraphQL\\Interfaces',
        'unions' => 'App\\GraphQL\\Unions',
        'scalars' => 'App\\GraphQL\\Scalars',
        'directives' => ['App\\GraphQL\\Directives'],
        'validators' => ['App\\GraphQL\\Validators'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Control how Lighthouse handles security related query validation.
    | Read more at https://webonyx.github.io/graphql-php/security/
    |
    */

    'security' => [
        'max_query_complexity' => \GraphQL\Validator\Rules\QueryComplexity::DISABLED,
        'max_query_depth' => \GraphQL\Validator\Rules\QueryDepth::DISABLED,
        'disable_introspection' => (bool) env('LIGHTHOUSE_SECURITY_DISABLE_INTROSPECTION', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Set defaults for the pagination features within Lighthouse. This is only
    | used if no argument is given by the user when using a pagination directive.
    |
    */

    'pagination' => [
        'default_count' => null,
        'max_count' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug
    |--------------------------------------------------------------------------
    |
    | Control the debug level as described in
    | https://webonyx.github.io/graphql-php/error-handling/
    | Debugging is only applied if the global Laravel debug config is set to true.
    |
    | When you set this value through an environment variable, use the
    | numeric values for the debug flags.
    |
    */

    'debug' => env(
        'LIGHTHOUSE_DEBUG',
        \GraphQL\Error\DebugFlag::INCLUDE_DEBUG_MESSAGE | \GraphQL\Error\DebugFlag::INCLUDE_TRACE
    ),

    /*
    |--------------------------------------------------------------------------
    | Error Handlers
    |--------------------------------------------------------------------------
    |
    | Register error handlers that receive the Errors that occur during execution
    | and handle them. You may use this to log, filter or format the errors.
    | The classes must implement \Nuwave\Lighthouse\Execution\ErrorHandler
    |
    */

    'error_handlers' => [
        \Nuwave\Lighthouse\Execution\AuthenticationErrorHandler::class,
        \Nuwave\Lighthouse\Execution\AuthorizationErrorHandler::class,
        \Nuwave\Lighthouse\Execution\ValidationErrorHandler::class,
        \Nuwave\Lighthouse\Execution\ReportingErrorHandler::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Middleware
    |--------------------------------------------------------------------------
    |
    | Register global field middleware directives that wrap around every field.
    | Execution happens in the defined order - laterass specific directives
    | that are defined directly on a field are wrapped by these.
    |
    */

    'field_middleware' => [
        \Nuwave\Lighthouse\Validation\ValidateDirective::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Global ID
    |--------------------------------------------------------------------------
    |
    | The name that is used for the global id field on the Node interface.
    | When creating a Relay compliant server, this must be named "id".
    |
    */

    'global_id_field' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Batched Queries
    |--------------------------------------------------------------------------
    |
    | GraphQL query batching means sending multiple queries to the server in a
    | single HTTP request. This setting controls if batched queries are executed.
    |
    */

    'batched_queries' => true,

    /*
    |--------------------------------------------------------------------------
    | Transactional Mutations
    |--------------------------------------------------------------------------
    |
    | If set to true, mutations such as @create or @update will be
    | wrapped in a transaction to ensure atomicity.
    |
    */

    'transactional_mutations' => true,

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment Protection
    |--------------------------------------------------------------------------
    |
    | If set to true, mutations will use forceFill() over fill() to ignore
    | mass assignment protection. Since GraphQL constrains allowed inputs
    | by design, this is safe to do and improves convenience.
    |
    */

    'force_fill' => true,

    /*
    |--------------------------------------------------------------------------
    | Batchload Relations
    |--------------------------------------------------------------------------
    |
    | If set to true, relations marked with directives like @belongsTo or @hasMany
    | are optimized by combining the queries of multiple fields into a single query.
    |
    */

    'batchload_relations' => true,

];
