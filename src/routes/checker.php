<?php

/**
 * This file is part of Lessi project.
 * В© byvlad, 2019
 */
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RouteNameMiddleware;

$app->group('/checker', function() use ($app) {  
    $app->get('', 'Checker\\IndexController:list')->setName('checker.list');
    $app->get('/{id:[0-9]+}/{step}', 'Checker\\IndexController:step')->setName('checker.step');
    $app->group('/insert', function() use ($app) {
        $app->get('/search', 'Checker\\IndexController:search')->setName('checker.insert.search');
        $app->post('/add', 'Checker\\IndexController:add')->setName('checker.insert.add');
    });
    $app->group('/step/kundenannahme', function() use ($app) {
        $app->group('/kundenhinweise', function() use ($app) {
            $app->group('/js', function() use ($app) {
                $app->post('/modal', 'Checker\\Step\\Kundenannahme\\Kundenhinweise:modal');
                $app->post('/add', 'Checker\\Step\\Kundenannahme\\Kundenhinweise:add');
                $app->post('/remove', 'Checker\\Step\\Kundenannahme\\Kundenhinweise:remove');
                $app->post('/more', 'Checker\\Step\\Kundenannahme\\Kundenhinweise:more');
            });
        });
        $app->group('/infos', function() use ($app) {
            $app->group('/js', function() use ($app) {
                $app->post('/set', 'Checker\\Step\\Kundenannahme\\Infos:set');
                $app->post('/remove', 'Checker\\Step\\Kundenannahme\\Infos:remove');
            });
        });
    });
    $app->group('/step/fahrzeugannahme', function() use ($app) {
        $app->group('/kundenhinweise', function() use ($app) {
            $app->group('/js', function() use ($app) {
                $app->post('/modal', 'Checker\\Step\\Fahrzeugannahme\\Kundenhinweise:modal');
                $app->post('/add', 'Checker\\Step\\Fahrzeugannahme\\Kundenhinweise:add');
                $app->post('/edit', 'Checker\\Step\\Fahrzeugannahme\\Kundenhinweise:edit');
                $app->post('/set', 'Checker\\Step\\Fahrzeugannahme\\Kundenhinweise:set');
                $app->post('/remove', 'Checker\\Step\\Fahrzeugannahme\\Kundenhinweise:remove');
                $app->post('/more', 'Checker\\Step\\Fahrzeugannahme\\Kundenhinweise:more');
                $app->post('/save', 'Checker\\Step\\Fahrzeugannahme\\Kundenhinweise:save');
            });
            $app->group('/tasks', function() use ($app) {
                $app->group('/technische', function() use ($app) {
                    $app->post('/index', 'Checker\\Tasks\\Technische:index');
                    $app->post('/forms', 'Checker\\Tasks\\Technische:forms');
                    $app->post('/add', 'Checker\\Tasks\\Technische:add');
                });
            });
        });
        $app->group('/reparaturhinweise', function() use ($app) {
            $app->group('/js', function() use ($app) {
                $app->post('/add', 'Checker\\Step\\Fahrzeugannahme\\Reparaturhinweise:add');
                $app->post('/edit', 'Checker\\Step\\Fahrzeugannahme\\Reparaturhinweise:edit');
                $app->post('/update', 'Checker\\Step\\Fahrzeugannahme\\Reparaturhinweise:update');
                $app->post('/remove', 'Checker\\Step\\Fahrzeugannahme\\Reparaturhinweise:remove');
                $app->post('/status', 'Checker\\Step\\Fahrzeugannahme\\Reparaturhinweise:status');
            });
        });
        $app->group('/auftrag', function() use ($app) {
            $app->post('/add', 'Checker\\IndexController:attach')->setName('checker.step.fahrzeugannahme.auftrag.add');
        });
    });
    $app->group('/step/reparatur', function() use ($app) {
        $app->group('/kundenhinweise', function() use ($app) {
            $app->group('/js', function() use ($app) {
                $app->post('/modal', 'Checker\\Step\\Kundenannahme\\Kundenhinweise:modal');
                $app->post('/add', 'Checker\\Step\\Kundenannahme\\Kundenhinweise:add');
                $app->post('/remove', 'Checker\\Step\\Kundenannahme\\Kundenhinweise:remove');
            });
        });
        $app->group('/auftragspositionen', function() use ($app) {
            $app->group('/js', function() use ($app) {
                $app->post('/status', 'Checker\\Step\\Diagnosereparatur\\Auftragspositionen:status');
            });
        });
        /*$app->group('/kundenbeanstandung', function() use ($app) {
            $app->group('/js', function() use ($app) {
                $app->post('/add', 'Checker\\Step\\Diagnosereparatur\\Kundenbeanstandung:add');
                $app->post('/edit', 'Checker\\Step\\Diagnosereparatur\\Kundenbeanstandung:edit');
                $app->post('/update', 'Checker\\Step\\Diagnosereparatur\\Kundenbeanstandung:update');
                $app->post('/remove', 'Checker\\Step\\Diagnosereparatur\\Kundenbeanstandung:remove');
            });
        });
        $app->group('/dokumentation', function() use ($app) {
            $app->group('/js', function() use ($app) {
                $app->post('/update', 'Checker\\Step\\Diagnosereparatur\\Dokumentation:update');
            });
        });*/
    });
    $app->group('/step/reparaturabnahme', function() use ($app) {
        $app->group('/probefahrt', function() use ($app) {
            $app->group('/js', function() use ($app) {
                $app->post('/status', 'Checker\\Step\\Reparaturabnahme\\Probefahrt:status');
            });
        });
        $app->group('/statusmeldung-kundenhinweis', function() use ($app) {
            $app->group('/js', function() use ($app) {
                $app->group('/kommentar', function() use ($app) {
                    $app->post('/add', 'Checker\\Step\\Reparaturabnahme\\KundenhinweiseKomentar:add');
                    $app->post('/edit', 'Checker\\Step\\Reparaturabnahme\\KundenhinweiseKomentar:edit');
                });
                $app->post('/erledigt', 'Checker\\Step\\Reparaturabnahme\\Kundenhinweise:erledigt');
            });
        });
        $app->group('/dokumentation', function() use ($app) {
            $app->group('/js', function() use ($app) {
                $app->post('/update', 'Checker\\Step\\Reparaturabnahme\\Dokumentation:update');
            });
        });
    });
    
    $app->post('/status/save', 'Checker\\StepController:save_child');
    
    $app->post('/head-quests', 'Checker\\IndexController:quests');
    
    $app->group('/garantie-kulanzantrag', function() use ($app) {
        $app->post('/insert', 'Checker\\GarantieKulanzantragController:insert');
        $app->post('/edit', 'Checker\\GarantieKulanzantragController:edit');
        $app->post('/update', 'Checker\\GarantieKulanzantragController:update');
    });
    
    $app->group('/termin', function() use ($app) {
        $app->get('', 'Checker\\TerminController:list')->setName('checker.termin.list');
        $app->get('/data', 'Checker\\TerminController:datas')->setName('checker.termin.datas');
        $app->group('/js', function() use ($app) {
            $app->post('/type', 'Checker\\TerminController:type');
            $app->post('/type-contact', 'Checker\\TerminController:type_contact');
        });
    });
})
->add(new AuthMiddleware($container))
->add(new RouteNameMiddleware($app->getContainer()));
