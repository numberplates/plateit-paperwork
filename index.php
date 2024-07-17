<?php
require_once 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');

$twig = new \Twig\Environment($loader);

$payload = json_decode(file_get_contents('php://input'), true);

echo $twig->render('pages.html.twig', ['packages' => $payload]);