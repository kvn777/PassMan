# PassMan
This is another one of my pet projects. 
All data is stored in AES-256 encrypted on the server, decryption is done in the browser, the master password for decryption is not sent or saved anywhere.
The server doesn't even store the user's email, only the MD5 hash with salt.
Written on core PHP with SQLite database and jQuery+CryptoJS on the front end.

You need to rename config-example.php to config.php and update with following variables:
```bash
define('passman_salt','YOUR STRING');
define('passman_key','YOUR STRING');
define('cookie_time',60*60*24);

define('Yandex_client_id','YOUR STRING');
define('Yandex_client_secret','YOUR STRING');
```

I use SQLite3 for database, because it is lightweight and fast.
This project created for fun and personal use.

Maybe someday I'll add new oAuth methods.
If you notice any bugs or have any comments, you can send pull request or email viktor.dev.work@gmail.com