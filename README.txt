To run the project you have to have php-sqlite3 and sqlite3 on your machine.

Run in your terminal:
    chmod +x run.sh && ./run.sh
It creates db, tables and few users with created messaeges.

As web server it is possible to use built-in php server and run it like this:
    php -S localhost:8000

In your browser go to http://localhost:8000/gui
On the page enter any new name or one from db (nk or rk).

Then you can sent messages using next syntax:
@<userName> <some message>

