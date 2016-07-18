<?php
$app->group('/v1/user', function() use ($app){
    
    // Post a new instance
    $app->post('(/)', function() use ($app) {
        $user = new \Lyverva\lyvUser();
        $user->sFirstname = $app->request->post('firstname');
        $user->sSurname = $app->request->post('lastname');
        $user->sEmail = $app->request->post('email');
        $user->save();
        
        $app->response->setStatus(201);
        $app->response->headers->set('location', $app->urlFor('userbyid', array('id' => $user->iLyvUserId)));
    });
    
    // Replace instance
    $app->put('/:id(/)', function($id) use ($app) {
        $user = new \Lyverva\lyvUser($id);
        $user->sFirstname = $app->request->put('firstname');
        $user->sSurname = $app->request->put('lastname');
        $user->sEmail = $app->request->put('email');
        $user->save();
        
        $app->response->setStatus(201);
        $app->response->headers->set('location', $app->urlFor('userbyid', array('id' => $user->iLyvUserId)));
    });
    
    // Patch
    $app->patch('/:id(/)', function($id) use ($app) {
        $user = new \Lyverva\lyvUser($id);
        // TODO: Get body
        
        // TODO: Check operation
        
        // TODO: Update required fields
        $user->sFirstname = $app->request->put('firstname');
        $user->sSurname = $app->request->put('lastname');
        $user->sEmail = $app->request->put('email');
        $user->save();
        
        $app->response->setStatus(201);
        $app->response->headers->set('location', $app->urlFor('userbyid', array('id' => $user->iLyvUserId)));
    });
    
    
    // Get details for a specific user by ID
    $app->get('/:id(/)', function($id) use ($app) {
        $user = new \Lyverva\lyvUser($id);
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode($user));
    })->name('userbyid');
});