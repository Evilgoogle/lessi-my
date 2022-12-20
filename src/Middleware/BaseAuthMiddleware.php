<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 2019-04-14
 * Time: 11:27
 */

namespace App\Middleware;


class BaseAuthMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $headers = $request->getHeaders();
        if (!isset($headers['HTTP_AUTHORIZATION'])) {
            return $response->withStatus(403);
        }
        $authorization = array_pop($headers['HTTP_AUTHORIZATION']);
        $authorization = explode(':', $authorization);
        $isLogged = $this->container->Auth->mobileAttemp($authorization[0], $authorization[1]);
        if (!$isLogged) {
            return $response->withStatus(403);
        }
        return $next($request, $response);
    }
}
