<?php
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RouteNameMiddleware;

$app->group('/leads-auto/hot', function () use ($app) {
    $app->get('', 'LeadsAuto\\HotController:open');
    $app->post('/classification', 'LeadsAuto\\HotController:classification')->setName('leads_auto.hot.classification');
})
    ->add(new AuthMiddleware($container))
    ->add(new RouteNameMiddleware($app->getContainer()));

$app->group('/leads-auto', function () use ($app) {
    $app->get('/my', 'LeadsAuto\\LeadController:my')->setName('leads_auto.my');
    $app->get('/all', 'LeadsAuto\\LeadController:all');
    $app->get('/show/{id}', 'LeadsAuto\\LeadController:show');
    $app->post('/called', 'LeadsAuto\\LeadController:called');
    $app->post('/written', 'LeadsAuto\\LeadController:written');
    $app->post('/email-type', 'LeadsAuto\\LeadController:email_type');
    $app->post('/call-history', 'LeadsAuto\\LeadController:call_history');
    $app->post('/complete', 'LeadsAuto\\LeadController:complete');
    $app->post('/follow-up-insert', 'LeadsAuto\\LeadController:follow_up_insert');
    
    $app->group('/test', function () use ($app) {
        $app->get('/add', 'LeadsAuto\\TestController:add');
        $app->post('/insert', 'LeadsAuto\\TestController:insert')->setName('leads_auto.test.insert');
        $app->get('/hot', 'LeadsAuto\\TestController:hot');
        $app->post('/classification', 'LeadsAuto\\TestController:classification')->setName('leads_auto.test.classification');
        $app->get('/my', 'LeadsAuto\\TestController:my')->setName('test_leads_auto.my');
        $app->get('/show/{id}', 'LeadsAuto\\TestController:show');
        $app->post('/called', 'LeadsAuto\\TestController:called');
        $app->post('/called-wait', 'LeadsAuto\\TestController:called_wait');
        $app->post('/written', 'LeadsAuto\\TestController:written');
        $app->post('/call-history', 'LeadsAuto\\TestController:call_history');
        $app->post('/complete', 'LeadsAuto\\TestController:complete');
        $app->post('/follow-up-insert', 'LeadsAuto\\TestController:follow_up_insert');
    });
})
    ->add(new AuthMiddleware($container))
    ->add(new RouteNameMiddleware($app->getContainer()));

$app->group('/cron/leads-auto', function () use ($app) {

    $app->get('/re-distribute/{hash}/{lead_id}/{user_id}/{id}', function ($request, $responce, $args) use ($app) {
        $set = new App\Support\LeadAuto\Distribute($args);
        $set->run();
    });
    
    $app->get('/run', function ($req) use ($app) {
	$a = new App\Support\LeadAuto\Start();
	$a->run((new \DateTime('2022-03-25 00:00:00')));
    });
});
