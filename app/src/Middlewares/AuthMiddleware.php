<?php

    namespace App\Middlewares;   

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    /**
     * Auth protection middleware
     * Protect route with authentication
     */
    class AuthMiddleware extends BaseMiddleware
    {
        /**
         * Invoke function:
         * 
         * @param  RequestInterface  $request  PSR7 request object
         * @param  ResponseInterface $response PSR7 response object
         * @param  callable          $next     Next middleware callable
         *
         * @return ResponseInterface PSR7 response object
         */
        // public function __invoke(Request $request, Response $response, $next) {
        //     // Token
        //     $token = \App\Utils\CookieJar::getCookie($request,'JWT') ?? '';

        //     if(!\App\Services\AuthenticationService::checkJWT($token)) {
        //         // JWT nao existe no banco de dados
        //         $response = \App\Utils\CookieJar::deleteCookie($response,'JWT');
        //         return $response->withRedirect($this->container->router->pathFor('login'));
        //     }

        //     // Decode JWT
        //     if ($account = $this->container->auth->decodeToken($token)) {
        //         $this->container->account = $account;
        //         $response = $next($request, $response);
        //         return $response;    
        //     }

        //     return $response->withRedirect($this->container->router->pathFor('login'));

            
        // }
        public function __invoke(Request $request, Response $response, $next) {
         

            if(!$this->container->auth->getCurrentUser()) {
                // flash a message
                return $response->withRedirect($this->container->router->pathFor('login'));
            }

            $response = $next($request, $response);
            return $response;
            
        }
    }