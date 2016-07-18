<?php
$app->group('/user', function() use ($app){
    $app->post('(/)', function() use ($app) {
        $user = new \Lyverva\lyvUser();
        $user->sFirstname = $app->request->post('firstname');
        $user->sSurname = $app->request->post('lastname');
        $user->sEmail = $app->request->post('email');
        $user->save();
        
        $app->response->setStatus(201);
        $app->response->headers->set('location', $app->urlFor('userbyid', array('id' => $user->iLyvUserId)));
    });

    $app->get('/:id(/)', function($id) use ($app) {
        $user = new \Lyverva\lyvUser($id);
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode($user));
    })->name('userbyid');
});