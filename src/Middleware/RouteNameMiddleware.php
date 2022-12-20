<?php

namespace App\Middleware;

class RouteNameMiddleware extends Middleware {

  public function __invoke($request, $response, $next) {


	$this->container->view->getEnvironment()->addGlobal('route', [
		'name' => $request->getAttribute('route')->getName()
	]);

	return $next($request, $response);
  }

}
