<?php

    // bootstrap
    require '../bootstrap.php';

    //---------------------------------------------------
    // 1. Create Slim App
    //---------------------------------------------------
    $configuration = [
        'settings' => [
            'displayErrorDetails' => true,
        ],
    ];
    $c = new \Slim\Container($configuration);

    // initialize slim app
    $app = new \Slim\App($c);

    //----------------------------------------------------
    // 2. Configure Slim App
    //----------------------------------------------------
    // Slim container 
    $container = $app->getContainer();

    $container['ApiController'] = function($container) {
        return new \App\Controllers\ApiController($container);
    };
    $container['AccountController'] = function($container) {
        return new \App\Controllers\AccountController($container);
    };
    $container['AvatarController'] = function($container) {
        return new \App\Controllers\AvatarController($container);
    };
    $container['AuthController'] = function($container) {
        return new \App\Controllers\AuthController($container);
    };
    $container['HtmlPageController'] = function($container) {
        return new \App\Controllers\HtmlPageController($container);
    };

    // Auth object
    $container['auth'] = function($container){
        return new \App\Auth\Authenticator($container);
    };
    
    // Criar objeto FLASH MESSAGES no container
    $container['flash'] = function($container) {
        return new \Slim\Flash\Messages;
    };

    // Criar objeto do VIEW no container ( TWIG VIEW )
    $container['view'] = function($container){
        
        $twig = new \Slim\Views\Twig( VIEWS_PATH, [
            'debug' => true,
            'cache' => false
        ]);

        // add debug extension
        $twig->addExtension(new \Twig\Extension\DebugExtension());

        // add path_for and base_url extensions
        $twig->addExtension(new \Slim\Views\TwigExtension(
            $container->router,
            $container->request->getUri()
        ));
        
        // add global user
        $twig->getEnvironment()->addGlobal('auth',[
            'user' => $container->auth->getCurrentUser()
        ]);

        // add global flash message
        $twig->getEnvironment()->addGlobal('flash',$container->flash);

        return $twig;

    };

    //---------------------------------------------------------------------------------------
    // 3. Routes
    //---------------------------------------------------------------------------------------
    $app->get('/login', 'HtmlPageController:login')->setName('login');
    $app->post('/login', 'AuthController:login')->setName('login');
    $app->get('/logout', 'AuthController:logout')->setName('logout');

    $app->get('/', 'HtmlPageController:index')->setName('index');
    $app->get('/token[/{jwt_token}]', 'AuthController:token')->setName('token');
    $app->get('/cookies', 'HtmlPageController:cookies')->setName('cookies');
    $app->get('/phpinfo', function(){
        phpinfo();
    });

    
    $app->get('/api/v1/thumbnail/{initials}', 'AvatarController:getDefaultThumbnail')->setName('api.thumbnail');
    
    $app->get('/accounts', 'HtmlPageController:listAccounts')->setName('account.list');
    $app->get('/accounts/create', 'HtmlPageController:addAccount')->setName('account.create');
    $app->post('/accounts/create', 'AccountController:create')->setName('account.create');
    $app->get('/accounts/{uuid}/view', 'HtmlPageController:viewAccount')->setName('account.view');
    $app->get('/accounts/{uuid}/edit', 'HtmlPageController:editAccount')->setName('account.edit');
    $app->post('/accounts/{uuid}/edit', 'AccountController:update')->setName('account.edit');
    $app->post('/accounts/{uuid}/change-password', 'AccountController:changePassword')->setName('account.change-password');
    $app->post('/accounts/{uuid}/delete', 'AccountController:delete')->setName('account.delete');

    $app->get('/api/ssr/search-accounts', 'HtmlPageController:ssrSearchAccounts')->setName('ssr.search-accounts');
    $app->get('/api/ssr/me', 'HtmlPageController:ssrMe')->setName('ssr.me');


    $app->group('/', function () use ($app) {
        $app->get('home', 'HtmlPageController:home')->setName('home');
        $app->get('search', 'HtmlPageController:searchAccounts')->setName('search');
        $app->get('search/{query}', 'HtmlPageController:searchAccountsParams')->setName('search.params');
        $app->get('me', 'HtmlPageController:me')->setName('me');
        $app->get('me/edit', 'HtmlPageController:meEdit')->setName('me.edit');
        $app->post('me/edit', 'AccountController:update')->setName('me.edit');

        $app->get('tokens', 'HtmlPageController:getTokens')->setName('tokens');
        $app->post('tokens/remover', 'AccountController:removeToken')->setName('tokens.remove');
    })->add(new \App\Middlewares\AuthMiddleware($container));
    

    // $app->get('/api/v1/echo', 'ApiController:echo')->setName('api.echo')
    //     ->add(new \App\Middlewares\AuthMiddleware($container))
    //     ->add(new \App\Middlewares\JwtTokenMiddleware($container));


    
    
    $app->post('/api/v1/login', 'ApiController:login')->setName('api.login')->add(new \App\Middlewares\ApplicationJsonMiddleware($container));

    $app->get('/api/v1/account/me','ApiController:getInfo')->setName('api.getInfo')
        ->add(new \App\Middlewares\JwtTokenMiddleware($container))
        ->add(new \App\Middlewares\ApplicationJsonMiddleware($container));
    $app->get('/api/v1/account/{uuid}', 'ApiController:retrieveAccount')->setName('api.getAccountInfo')
        ->add(new \App\Middlewares\JwtTokenMiddleware($container))
        ->add(new \App\Middlewares\ApplicationJsonMiddleware($container));
    
    
    
    $app->group('/api/v1', function () use ($app) {
        $app->post('/account', 'ApiController:createAccount')->setName('api.createAccount');
        $app->post('/accounts/search', 'ApiController:searchAccounts')->setName('api.searchAccounts');
       
        $app->put('/account/{uuid}/password', 'ApiController:updateAccountPassword')->setName('api.updateAccountPassword');
        $app->delete('/account/{uuid}', 'ApiController:deleteAccount')->setName('api.deleteAccount');
        $app->get('/accounts', 'ApiController:retrieveAllAccounts')->setName('api.retrieveAllAccounts');

        
        $app->put('/account/{uuid}', 'ApiController:updateAccount')->setName('api.updateAccount');
        
        // $app->get('/logout', 'APIController:logout')->setName('api.logout');

    })->add(new \App\Middlewares\ApplicationJsonMiddleware($container));

    //-----------------------------------------------------------------------
    // 4. Run app
    //-----------------------------------------------------------------------
    $app->run();

    // Destroy ghost session
    if(!$container->auth->getCurrentUser() && !\App\Utils\Session::get('slimFlash'))
        \App\Utils\Session::destroy();
    
   