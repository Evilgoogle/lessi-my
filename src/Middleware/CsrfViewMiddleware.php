<?php

namespace App\Middleware;

class CsrfViewMiddleware extends Middleware {

  public function __invoke($request, $response, $next) {


	$this->container->view->getEnvironment()->addGlobal('csrf', [
		'field' =>
		'<input type="hidden" name="' . $this->container->csrf->getTokenNameKey() . '" value="'
		. $this->container->csrf->getTokenName() . '"/>' . PHP_EOL
		. '<input type="hidden" name="' . $this->container->csrf->getTokenValueKey() . '" value="'
		. $this->container->csrf->getTokenValue() . '"/>' . PHP_EOL,
        'json' => json_encode([
            $this->container->csrf->getTokenNameKey() => $this->container->csrf->getTokenName(),
            $this->container->csrf->getTokenValueKey() => $this->container->csrf->getTokenValue()
        ])
	]);


	$response = $next($request, $response);
	return $response;
  }

}
