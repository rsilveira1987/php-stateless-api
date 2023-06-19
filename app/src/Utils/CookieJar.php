<?

    namespace App\Utils;

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    class CookieJar {
        /**
         * @param Response $response
         * @param string $key
         * @param string $value
         * @return Response
         */
        public static function deleteCookie(Response &$response, $key)
        {
            $cookie = urlencode($key).'='.
                urlencode('deleted').'; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0; path=/; secure; httponly';
            $response = $response->withAddedHeader('Set-Cookie', $cookie);
            return $response;
        }

        /**
         * @param Response $response
         * @param string $cookieName
         * @param string $cookieValue
         * @return Response
         */
        public static function addCookie(Response &$response, $cookieName, $cookieValue)
        {
            // $expirationMinutes = 10;
            $expirationMinutes = 525600; // 1 year
            $expiry = new \DateTimeImmutable('now + '.$expirationMinutes.'minutes');
            $cookie = urlencode($cookieName).'='.
                urlencode($cookieValue).'; expires='.$expiry->format(\DateTime::COOKIE).'; Max-Age=' .
                $expirationMinutes * 60 . '; path=/; secure; httponly';
            $response = $response->withAddedHeader('Set-Cookie', $cookie);
            return $response;
        }

        /**
         * @param Request $request
         * @param string $cookieName
         * @return string
         */
        public static function getCookie(Request &$request, $cookieName)
        {
            $cookies = $request->getCookieParams();
            return isset($cookies[$cookieName]) ? $cookies[$cookieName] : null;
        }

        /**
         * @param Request $request
         * @return array
         */
        public static function getCookies(Request $request)
        {
            return $request->getCookieParams();
        }

    }