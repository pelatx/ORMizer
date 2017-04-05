<?php

require_once('../../ORMizer.inc.php');
use ORMizer\ORMizer;

/* Our book model */
class Book {

    private $title;
    private $author;
    private $year;
    // This property will contain an instance of the class 'Book' already transformed by ORMizer,
    // and will not be reflected in the database table. Thanks to the initial 'ormizer_excluded' string.
    private $ormizedBook = 'ormizer_excluded';

    function __construct($title='', $author='', $year=0) {
        $this->title = $title;
        $this->author = $author;
        $this->year = $year;
    }

    // Get a copy of the object transformed by ORMizer and save it in '$this->ormizedBook'
    public function ormize() {
        $this->ormizedBook = ORMizer::persist($this);
        // Let's configure how type conversion will be
        if(!$this->ormizedBook->existsTable()) {
            $this->ormizedBook
                ->setCasting('title', 'varchar', 255)
                ->setCasting('author', 'varchar', 50)
                ->setCasting('year', 'int', 4);
            // Create an empty table reflecting the book object
            $this->ormizedBook->createTable();
        }
        return $this->ormizedBook;
    }
}

/* Application Logic */
$book = new Book();
$book = $book->ormize();
switch($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $books = $book->getSavedInstances();
        header('Content-Type: application/json; charset=utf8');
        echo json_encode($books);
        break;
    case 'POST':
        if(isset($_POST['title']) && $_POST['title'] !== '') {
            echo 'You\'re in "POST" case</br>';
            $book->title = ucwords(strtolower($_POST['title']));
            $book->author = $_POST['author'] !== '' ? ucwords(strtolower($_POST['author'])) : 'Unknow';
            $book->year = $_POST['year'] !== '' ? $_POST['year'] : 0;
            $book->save();
        }
        header('Location: index.html');
        break;
}
?>
