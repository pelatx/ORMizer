$(document).ready(function () {

    // Print the json list
    function printBooks(bookList) {
        var i, bookItem;
        for (i = 0; i < bookList.length; i++) {
            bookItem = "- " + bookList[i].title + " (" + bookList[i].year + "). Author: " + bookList[i].author + "</br>";
            $("#bookList").append(bookItem);
        }
    }

    // Retrieve the book list and insert it into index.html
    function getBookList() {
        $.getJSON("books.php")
            .done(function (bookList) {
                if ($.isEmptyObject(bookList)) {
                    $("#bookList").append("No Books in the Database");
                } else {
                    printBooks(bookList);
                }
            });
    }

    getBookList();
});
