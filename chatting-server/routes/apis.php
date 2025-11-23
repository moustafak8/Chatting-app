<?php
$apis = [
    '/users'         => ['controller' => 'usercontroller', 'method' => 'getUsers'],
    '/users/create' => ['controller' => 'usercontroller', 'method' => 'newUser'],
    '/users/update' => ['controller' => 'usercontroller', 'method' => 'updateuser'],
    '/users/delete' => ['controller' => 'usercontroller', 'method' => 'deleteuser'],
    '/users/login' => ['controller' => 'AuthController', 'method' => 'login'],
    '/conversation/create' => ['controller' => 'conversationcontroller', 'method' => 'newconversation'],
    '/participant/create' => ['controller' => 'participantscontroller', 'method' => 'newparticipants'],
    '/message/create' => ['controller' => 'messagecontroller', 'method' => 'new_message'],
    '/message' => ['controller' => 'messagecontroller', 'method' => 'get_message'],
    '/status' => ['controller' => 'status_controller', 'method' => 'get_status'],
    '/status/create' => ['controller' => 'status_controller', 'method' => 'new_status']
];
