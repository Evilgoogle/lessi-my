<?php

namespace App\Middleware;

class AuthMiddleware extends Middleware
{

    public function __invoke ($request, $response, $next)
    {

        if (!$this->container->Auth->check())
        {
            $uri                    = $request->getUri();
//            dd($uri->getQuery());
            $_SESSION['post_login'] = $uri->getPath() . '?' . $uri->getQuery();
            return $response->withRedirect($this->container->router->pathFor('login'));
        }

        return $next($request, $response);
    }

}
