<?php

namespace App\Middleware;

class OldInputMiddleware extends Middleware {

  public function __invoke($request, $response, $next) {

	if (isset($_SESSION['old'])) {
	  $this->container->view->getEnvironment()->addGlobal('old', $_SESSION['old']);
	  unset($_SESSION['old']);
	}
	$response = $next($request, $response);
	return $response;
  }

}
