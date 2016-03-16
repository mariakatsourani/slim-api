<?php

require '../vendor/autoload.php';

//use Database;
require 'Database/Database.php';

$app = new Slim\App();

$db = new Database();
//var_dump($db);
//var_dump($app);

function buildJSONResponse($data, $response){
    if($data) {
        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($data));

    } else { throw new PDOException('No records found');}
}


//get dishes
$app->get('/dishes', function ($request, $response, $args) use ($db) {

    $statement = 'SELECT * FROM dishes';
    $db->query($statement);
    $db->execute();
    $dishes = $db->resultset();

    buildJSONResponse($dishes, $response);

});

//get dish
$app->get('/dish/{id}', function ($request, $response, $args) use ($db) {
    
    $id = $args['id'];

    $statement1 = 'SELECT * FROM dishes WHERE id = :id';
    $db->query($statement1);
    $db->bind(':id', $id);
    $db->execute();
    $dish = $db->single();

    $statement2 = 'SELECT * FROM comments WHERE dish_id = :dish_id';
    $db->query($statement2);
    $db->bind(':dish_id', $dish->id);
    $db->execute();
    $comments = $db->resultset();

    $dish->comments = $comments;

    buildJSONResponse($dish, $response);

//    if($dish) {
//        return $response->withStatus(200)
//            ->withHeader('Content-Type', 'application/json')
//            ->write(json_encode($dish));
//
//    } else { throw new PDOException('No records found');}
});

//get promotion

//get dish comments
$app->get('/comments/{dish_id}', function ($request, $response, $args) use ($db)  {

    $dish_id = $args['dish_id'];

    $statement = 'SELECT * FROM comments WHERE dish_id = :dish_id';
    $db->query($statement);
    $db->bind(':dish_id', $dish_id);
    $db->execute();
    $comments = $db->resultset();

    buildJSONResponse($comments, $response);

});

//get leadership
$app->get('/leadership', function ($request, $response, $args) use ($db) {

    $statement = 'SELECT * FROM leadership';
    $db->query($statement);
    $db->execute();
    $leadership = $db->resultset();

    buildJSONResponse($leadership, $response);

});

//get person
$app->get('/leadership/{id}', function ($request, $response, $args) use ($db){

    $id = $args['id'];

    $statement = 'SELECT * FROM leadership WHERE id = :id';
    $db->query($statement);
    $db->bind(':id', $id);
    $db->execute();
    $person = $db->single();

    buildJSONResponse($person, $response);

});

//post comment
$app->post('/comment', function ($request, $response, $args) use ($db){
    try {
        //get parsed body
        $params = $request->getQueryParams();

        $statement = 'INSERT INTO comments (dish_id, rating, author, comment) VALUES (:dish_id, :rating, :author, :comment)';
        $db->query($statement);

        $db->bind(':dish_id', $params['dish_id']);
        $db->bind(':rating', $params['rating']);
        $db->bind(':author', $params['author']);
        $db->bind(':comment', $params['comment']);

        if($db->execute()) {
            return $response->withStatus(201);
        }

    } catch(PDOException $e) {
        // $app->response()->setStatus(404);
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
});

//post feedback
$app->post('/feedback', function ($request, $response, $args) {
    try 
    {
        $db = getDB();
 
        $sth = $db->prepare("INSERT INTO feedback 
                                (firstName, lastName, email, comments)
                            VALUES
                                (:firstName, :lastName, :email, :comments)
                ");

        $params = $request->getQueryParams();
        var_dump($params);

 
        $sth->bindParam(':firstName', $params['firstName'], PDO::PARAM_STR);
        $sth->bindParam(':lastName', $params['lastName'], PDO::PARAM_STR);
        $sth->bindParam(':email', $params['email'], PDO::PARAM_STR);
        $sth->bindParam(':comments', $params['comments'], PDO::PARAM_STR);



        if($sth->execute()) {
            return $response->withStatus(201);

        } else {
            throw new PDOException('wont happen.');
        }
 
    } catch(PDOException $e) {
        // $app->response()->setStatus(404);
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
});

$app->run();
