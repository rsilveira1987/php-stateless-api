<?php

    namespace App\Middlewares;

    /**
     * json header and body check middleware
     * Check if the requests has a application/json header and a correct json body
     */
    class ApplicationJsonMiddleware extends BaseMiddleware
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

            try {

                //
                // Content-type header check
                //
                $header = strtolower($request->getHeader('Content-type')[0] ?? '');
                $pattern = '#application/json#';
                preg_match($pattern,$header,$matches);
                if( empty($matches) ){
                    // we have a problem with the content-type header
                    throw new \Exception('Invalid JSON header');
                }

                //
                // JSON body check
                //
                if ( $request->getBody()->getSize() > 0 ){
                    if( !is_array($request->getParsedBody()) ){
                        // we have a problem with the body
                        throw new \Exception('Invalid JSON body');
                    }
                }

                // everything is fine
                $response = $next($request, $response);
                
            } catch (\Exception $e) {
                $response = $response->withJson([
                    'status' => 'error',
                    'response' => $e->getMessage()
                ], 500);
            }

            return $response;
        }
    }