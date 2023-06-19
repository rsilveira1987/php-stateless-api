<?php

    namespace App\Middlewares;   

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    /**
     * Auth protection middleware
     * Protect route with authentication
     */
    class CookieMessageMiddleware extends BaseMiddleware
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
        public function __invoke(Request $request, Response $response, $next)
        {
            // Token
            // $message = \App\Utils\CookieJar::getCookie($request,'COOKIE_MSG') ?? '';
            $message = $this->container->get('request')->getCookieParam('COOKIE_MSG');
            \App\Utils\CookieJar::deleteCookie($response,'COOKIE_MSG');

            $this->container->flash->setMessage($message);
            
            $response = $next($request, $response);

            return $response;

        }
    }