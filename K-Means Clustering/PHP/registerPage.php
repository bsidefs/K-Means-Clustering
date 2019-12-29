<?php
/**
 * Contains the html information for the register page of the web application.
 */
require_once "loginCredentials.php";
require_once "functions.php";
// establishing the connection with MySQL
$conn = new mysqli($hn,$un,$pw,$db);
if ($conn->connect_error) die(my_sql_fatal_error());

// outputting the html webpage
echo <<< _FINISH
    <html>
        <head>
            <title>K-Means Based Clustering Web Application</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="registerPage.css"  type="text/css" rel="stylesheet" />
            <link rel="stylesheet" href="https://use.typekit.net/nar5czb.css">
            <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
            <script src="validateRegistration.js"></script>

            <!-- activate link script-->
            <script>
                $(document).ready(function()
                {
                    var navLinks = $(".nav .navlinks a");
                    navLinks.click(function()
                    {
                        var selectedLink = $(this);
                        activateLink(selectedLink);
                    })

                    function activateLink(selectedLink)
                    {
                        $(".nav .navlinks a.active").removeClass("active");
                        selectedLink.addClass("active");
                    }
                });
            </script>
        </head>
        <body>
            <!-- main container-->
            <div class="container">

                <header>
                    <a id="top"></a>
                    <div class="nav">

                        <div class="left-side-title">
                            <h1>CS 174</h1>
                        </div>
                        

                        <div class="navlinks">
                            <ul>
                                <li><a href="homePage.php">Home</a></li>
                                <li><a href="loginPage.php">Login</a></li>
                                <li><a href="" class="active">Register</a></li>
                            </ul>
                        </div>


                        <div class="right-side-title">
                            <h1>Brian Tamsing</h1>
                        </div>
                    </div> <!-- end of the "navbar titles" container-->
                </header>

                <!-- home or landing page section-->
                <section id="landing-page">
                    <div class="container">
                        <div class="landing-title">
                            <h1>Register</h1>
                        </div> 
                        <div class="description">
                            <form action="registerPage.php" method="post" onsubmit="return validateForm(this)">
                                <fieldset>
                                    <input type="text" name="username" placeholder="username">
                                    <input type="email" name="email" placeholder="email">
                                    <input type="password" name="password" placeholder="password">

                                    <div class="submission-area">
                                        <label for="arrow"><i class="far fa-arrow-alt-circle-right fa-3x"></i></label>
                                        <input type="submit" id="arrow" value="">
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                        <div class="insertedText">
                        </div>
                    </div> <!-- end of the "main-content" container-->
                </section>
                <!-- end of home or landing page section-->

            </div> 
            <!-- end of the "main" container-->
        </body>
    </html>
_FINISH;

// calling the register page functions
create_user($conn);

// closing the now finished MySQL connection
$conn->close();

/**
 * Securely adds a user to the database.
 */
function create_user($conn)
{
    if (isset($_POST["username"]) && isset($_POST["email"]) 
    && isset($_POST["password"]))
    {
        // creating a random salt for the user currently being added to the database
        $salt = random_int(PHP_INT_MIN, PHP_INT_MAX);

        // preparing and executing the query
        $statement = $conn->prepare("INSERT INTO users VALUES(?,?,?,?)");
        $statement->bind_param('sssi',$username,$email,$token,$salt);

        // initializing the statement parameters
        $username = fix_string($conn,$_POST["username"]);
        $email    = fix_string($conn,$_POST["email"]);
        $password = fix_string($conn,$_POST["password"]);

        // statement parameters are now sanitized, but now we must validate them
        $fail = null;
        $fail .= validate_username($conn,$username);
        $fail .= validate_email($conn,$email);
        $fail .= validate_password($password);

        // lastly, if everything has now successfully been sanitized and validated, hash and salt the password, and then execute the query
        if ($fail == "")
        {
            $token = hash("ripemd128", $salt.$password);

            // execute the query
            $statement->execute();

            // ensure the statement was properly executed
            if (!($statement->affected_rows > 0))
                echo my_sql_fatal_error();
            else
                $statement->close();

            // notify the user of a successful registration
            registrationComplete();
            
            // directing them to now access the login page
            insertHTML("registerPage.php",".insertedText","&#8594; Please continue to the Login page.");
        }
        // otherwise, report an error and close the statement connection
        else
        {
            insertHTML("registerPage.php",".insertedText","The following problem(s) occured while completing your request:<br> $fail");
            $statement->close();
        }
    }
}


/**
 * Validates a user's inputted username (not only for proper contents, but ensures that only one account may be associated with that username).
 */
function validate_username($conn,$field)
{
    $usernameAlreadyExists = check_for_duplicate_credential($conn,$field,"username");
    if ($field == "")
        return "&#8226" . "No username was entered.<br>";
    elseif(preg_match("/[^a-zA-Z0-9_-]/", $field))
        return "&#8226" . "Usernames may contain only the following characters: a-z, A-Z, 0-9, _ , and -<br>";
    elseif($usernameAlreadyExists)
        return "&#8226" . "An account with that username already exists.<br>";
    return "";
}


/**
 * Validates a user's inputted email (not only for proper contents, but ensures that the email may only be associated with a single account)
 */
function validate_email($conn,$field)
{
    // the email has already been sanitized, so it will now be safe to query the database with the $field variable
    $emailAlreadyExists = check_for_duplicate_credential($conn,$field,"email");

    if ($field == "")
        return "&#8226" . "No email was entered.<br>";
    elseif(!((strpos($field, ".") > 0) && (strpos($field, "@") > 0)) || preg_match("/[^a-zA-Z0-9._@-]/", $field))
        return "&#8226" . "Email entered was invalid.<br>";
    elseif ($emailAlreadyExists)
        return "&#8226" . "An account with that email already exists.<br>";
    return "";
}


/**
 * Validates a user's inputted password.
 */
function validate_password($field)
{
    if ($field == "")
        return "&#8226" . "No password was entered.<br>";
    elseif(strlen($field) < 6)
        return "&#8226" . "Password must be longer than 6 characters.<br>";
    elseif(preg_match("/[^a-zA-Z0-9!]/", $field))
        return "&#8226" . "Passwords must contain only the following characters: a-z, A-Z, 0-9, and !<br>";
    return "";
}


/**
 * Outputs a message signifying a successful account/user registration.
 */
function registrationComplete()
{
    echo <<< _FINISH
    <html>
        <script>
            $.ajax({url:"registerPage.php", success: function(){
                $(".landing-title h1").html("Registration Complete.");
            }});
        </script>
    </html>
_FINISH;
}


?>