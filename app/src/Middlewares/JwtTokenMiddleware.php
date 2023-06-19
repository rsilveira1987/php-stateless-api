<?php

    namespace App\Middlewares;

use App\Models\AccountModel;
use App\Utils\CookieJar;
use Exception;
    use Firebase\JWT\JWT;

    /**
     * Auth protection middleware
     * Protect route with authentication
     */
    class JwtTokenMiddleware extends BaseMiddleware
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
        public function __invoke($request, $response, $next)
        {           

            // get JWT token if exists
            $jwt_cookie = CookieJar::getCookie($request,'JWT');

            // check JWT Authorization Header
            // Authorization: "Bearer $jwt_payload";
            $authrorization = $request->getHeader('Authorization')[0] ?? "Bearer {$jwt_cookie}";
            if (!$authrorization) {
                $payload = ['message' => 'Missing authorization header'];
                return $response = $response->withJson(['fail' => $payload],500);
            }

            $pattern = '/Bearer (.*)/i';
            preg_match($pattern,$authrorization,$matches);
            if(empty($matches)) {
                $payload = ['message' => 'Missing Bearer token'];
                return $response = $response->withJson(['fail' => $payload],500);
                // return $response = $next($request, $response);
            }
            
            try {
                $token = $matches[1]; // contem o token JWT enviado // $request->withHeader("Authorization", "Bearer {$token}");

                // Decode JWT
                if ($account = $this->container->auth->decodeToken($token)) {
                    $this->container->account = $account;
                    $response = $next($request, $response);
                    return $response;    
                }
                
                // \App\Utils\Session::set('jwt',$token);
                // $decoded = JWT::decode($token, JWT_SECRET_KEY ,array('HS256'));
                // $account = AccountModel::fromArray((array)$decoded->account);
                // $this->container->decoded = $account;

            } catch (Exception $e) {
                return $response = $response->withJson([
                    'status' => 'error',
                    'response' => $e->getMessage()
                ],500);
            }         

        }
    }